<?php

namespace Modules\LoyaltyPoints\Services;

use Akaunting\Module\Facade as Module;
use App\Order;
use App\Restorant;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\LoyaltyPoints\Models\LoyaltyAccount;
use Modules\LoyaltyPoints\Models\LoyaltyTransaction;
use Modules\LoyaltyPoints\Notifications\LoyaltyPointsEarned;

class LoyaltyService
{
    /**
     * Is the loyalty program turned on for this vendor?
     */
    public function isEnabled(Restorant $vendor): bool
    {
        return (bool) $vendor->getConfig('loyalty_enable', false);
    }

    /**
     * Get the client's account for this vendor, creating it (and granting
     * the welcome points bonus) the very first time the client is seen.
     */
    public function getOrCreateAccount(User $client, Restorant $vendor): LoyaltyAccount
    {
        $account = LoyaltyAccount::where('client_id', $client->id)
            ->where('restorant_id', $vendor->id)
            ->first();

        if ($account) {
            return $account;
        }

        $account = LoyaltyAccount::create([
            'client_id' => $client->id,
            'restorant_id' => $vendor->id,
            'points_balance' => 0,
            'lifetime_points' => 0,
        ]);

        $welcomePoints = (int) $vendor->getConfig('loyalty_welcome_points', 0);
        if ($welcomePoints > 0) {
            $this->credit($account, $welcomePoints, 'welcome', null, __('loyaltypoints::general.welcome_bonus_description'));
        }

        return $account;
    }

    /**
     * Award points for an order that has actually been delivered / picked up
     * and paid. Safe to call repeatedly - the unique order_id constraint on
     * loyalty_transactions guarantees an order is only ever credited once.
     */
    public function awardForOrder(Order $order): ?LoyaltyTransaction
    {
        $result = $this->diagnose($order);

        if (! $result['eligible']) {
            return null;
        }

        $account = $this->getOrCreateAccount($order->client, $order->restorant);

        return $this->credit(
            $account,
            $result['points'],
            'earn',
            $order->id,
            __('loyaltypoints::general.points_for_order_description', ['order_id' => $order->id_formated])
        );
    }

    /**
     * Explain exactly why an order is (or is not) eligible for points.
     * Used both internally and by the diagnostics screen in the dashboard,
     * so support/owners can self-troubleshoot without server access.
     *
     * @return array{eligible:bool, reason:string, points:int|null}
     */
    public function diagnose(Order $order): array
    {
        $vendor = $order->restorant;

        if (! $vendor) {
            return ['eligible' => false, 'reason' => 'no_vendor', 'points' => null];
        }

        if (! $this->isEnabled($vendor)) {
            return ['eligible' => false, 'reason' => 'program_disabled', 'points' => null];
        }

        if (! $order->client_id) {
            return ['eligible' => false, 'reason' => 'guest_order_no_client', 'points' => null];
        }

        if ($order->payment_status !== 'paid') {
            return ['eligible' => false, 'reason' => 'not_paid', 'points' => null];
        }

        $lastStatus = $order->laststatus->first();
        $finalAliases = ['delivered', 'closed'];
        if (! $lastStatus || ! in_array($lastStatus->alias, $finalAliases)) {
            return ['eligible' => false, 'reason' => 'not_final_status', 'points' => null];
        }

        if (LoyaltyTransaction::where('order_id', $order->id)->where('type', 'earn')->exists()) {
            return ['eligible' => false, 'reason' => 'already_awarded', 'points' => null];
        }

        $pointsPerRiyal = (float) $vendor->getConfig('loyalty_points_per_riyal', 1);
        if ($pointsPerRiyal <= 0) {
            return ['eligible' => false, 'reason' => 'zero_ratio', 'points' => null];
        }

        $orderValue = max(0, (float) $order->order_price_with_discount);
        $points = (int) floor($orderValue * $pointsPerRiyal);

        if ($points <= 0) {
            return ['eligible' => false, 'reason' => 'zero_points_computed', 'points' => null];
        }

        return ['eligible' => true, 'reason' => 'ok', 'points' => $points];
    }

    /**
     * Scan orders and award points for every eligible one. Deliberately
     * bypasses App\Scopes\RestorantScope (which filters by the current
     * visitor's session('restaurant_id')) since this can run in the
     * background on an arbitrary visitor's request and must always see
     * every restaurant's orders, not just whichever one happened to be in
     * that visitor's session.
     *
     * @param  int|null  $vendorId  restrict to a single vendor (used by the manual "run now" button)
     * @return array{checked:int, awarded:int, details:array}
     */
    public function sweepAndAward(?int $vendorId = null, int $limit = 200): array
    {
        $finalAliases = ['delivered', 'closed'];

        $query = Order::withoutGlobalScope(\App\Scopes\RestorantScope::class)
            ->where('payment_status', 'paid')
            ->with(['restorant', 'client', 'laststatus'])
            ->orderBy('id', 'DESC')
            ->limit($limit);

        if ($vendorId) {
            $query->where('restorant_id', $vendorId);
        }

        $orders = $query->get()->filter(function ($order) use ($finalAliases) {
            $last = $order->laststatus->first();

            return $last && in_array($last->alias, $finalAliases);
        });

        $checked = 0;
        $awarded = 0;
        $details = [];

        foreach ($orders as $order) {
            $checked++;
            $diagnosis = $this->diagnose($order);

            $entry = [
                'order_id' => $order->id,
                'id_formated' => $order->id_formated,
                'last_status' => optional($order->laststatus->first())->alias,
                'payment_status' => $order->payment_status,
                'client_id' => $order->client_id,
                'reason' => $diagnosis['reason'],
                'points' => $diagnosis['points'],
            ];

            if ($diagnosis['eligible']) {
                $transaction = $this->awardForOrder($order);
                if ($transaction) {
                    $awarded++;
                    $entry['reason'] = 'awarded';
                }
            }

            $details[] = $entry;
        }

        return [
            'checked' => $checked,
            'awarded' => $awarded,
            'details' => $details,
        ];
    }

    /**
     * Redeem points for a discount. Generates a single-use fixed-amount
     * coupon through the existing Coupons module, so the regular checkout
     * "promo code" flow (already wired into the cart) applies the discount
     * with zero changes to the core ordering code.
     *
     * @return array{coupon_code:string,deduct_value:float,points_used:int}
     */
    public function redeem(User $client, Restorant $vendor, ?int $pointsToRedeem = null): array
    {
        if (! $this->isEnabled($vendor)) {
            throw new \Exception(__('loyaltypoints::general.program_not_enabled'));
        }

        if (! Module::has('coupons')) {
            throw new \Exception(__('loyaltypoints::general.coupons_module_required'));
        }

        $ratio = (float) $vendor->getConfig('loyalty_redeem_points_per_riyal', 10);
        $minPoints = (int) $vendor->getConfig('loyalty_min_redeem_points', $ratio);

        if ($ratio <= 0) {
            throw new \Exception(__('loyaltypoints::general.program_not_enabled'));
        }

        $account = $this->getOrCreateAccount($client, $vendor);

        // No specific amount requested (the simple on/off toggle case) -> use
        // the client's entire balance, rounded down to a whole ratio block.
        if ($pointsToRedeem === null) {
            $pointsToRedeem = $account->points_balance;
        }

        if ($pointsToRedeem < $minPoints) {
            throw new \Exception(__('loyaltypoints::general.minimum_points_required', ['points' => $minPoints]));
        }

        // Only redeem in whole "ratio" blocks so we never give fractional riyal coupons.
        // e.g. balance = 19, ratio = 10 -> only 10 points are used, 9 stay on the account.
        $usablePoints = (int) (floor($pointsToRedeem / $ratio) * $ratio);

        if ($usablePoints <= 0 || $usablePoints > $account->points_balance) {
            throw new \Exception(__('loyaltypoints::general.not_enough_points'));
        }

        $deductValue = floor($usablePoints / $ratio);

        $coupon = \Modules\Coupons\Models\Coupons::create([
            'name' => __('loyaltypoints::general.redeem_coupon_name'),
            'code' => $this->generateCouponCode($vendor),
            'type' => 0, // fixed amount
            'price' => $deductValue,
            'active_from' => Carbon::now(),
            'active_to' => Carbon::now()->addDays(30),
            'limit_to_num_uses' => 1,
            'used_count' => 0,
            'company_id' => $vendor->id,
        ]);

        $this->credit(
            $account,
            -$usablePoints,
            'redeem',
            null,
            __('loyaltypoints::general.points_redeemed_description', ['code' => $coupon->code]),
            $coupon->code
        );

        return [
            'coupon_code' => $coupon->code,
            'deduct_value' => $deductValue,
            'points_used' => $usablePoints,
        ];
    }

    /**
     * Apply a points movement to an account's balance and write the ledger entry.
     */
    private function credit(LoyaltyAccount $account, int $points, string $type, ?int $orderId, ?string $description, ?string $couponCode = null): LoyaltyTransaction
    {
        $account->points_balance = max(0, $account->points_balance + $points);
        if ($points > 0) {
            $account->lifetime_points += $points;
        }
        $account->save();

        $transaction = LoyaltyTransaction::create([
            'loyalty_account_id' => $account->id,
            'order_id' => $orderId,
            'type' => $type,
            'points' => $points,
            'coupon_code' => $couponCode,
            'description' => $description,
        ]);

        if ($points > 0 && $account->client) {
            try {
                $account->client->notify(new LoyaltyPointsEarned($account, $transaction));
            } catch (\Throwable $e) {
                // Never let a notification failure break the points flow
            }
        }

        return $transaction;
    }

    private function generateCouponCode(Restorant $vendor): string
    {
        do {
            $code = 'LP-'.strtoupper(Str::random(6));
        } while (\Modules\Coupons\Models\Coupons::where('code', $code)->exists());

        return $code;
    }
}
