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
            $this->credit($account, $welcomePoints, 'welcome', null, __('loyalty.welcome_bonus_description'));
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
        $vendor = $order->restorant;
        if (! $vendor || ! $this->isEnabled($vendor)) {
            return null;
        }

        if (! $order->client_id) {
            return null;
        }

        // Already awarded? (unique constraint protects us anyway, this avoids a DB hit failing loudly)
        if (LoyaltyTransaction::where('order_id', $order->id)->where('type', 'earn')->exists()) {
            return null;
        }

        $pointsPerRiyal = (float) $vendor->getConfig('loyalty_points_per_riyal', 1);
        if ($pointsPerRiyal <= 0) {
            return null;
        }

        $orderValue = max(0, (float) $order->order_price_with_discount);
        $points = (int) floor($orderValue * $pointsPerRiyal);

        if ($points <= 0) {
            return null;
        }

        $account = $this->getOrCreateAccount($order->client, $vendor);

        return $this->credit(
            $account,
            $points,
            'earn',
            $order->id,
            __('loyalty.points_for_order_description', ['order_id' => $order->id_formated])
        );
    }

    /**
     * Redeem points for a discount. Generates a single-use fixed-amount
     * coupon through the existing Coupons module, so the regular checkout
     * "promo code" flow (already wired into the cart) applies the discount
     * with zero changes to the core ordering code.
     *
     * @return array{coupon_code:string,deduct_value:float,points_used:int}
     */
    public function redeem(User $client, Restorant $vendor, int $pointsToRedeem): array
    {
        if (! $this->isEnabled($vendor)) {
            throw new \Exception(__('loyalty.program_not_enabled'));
        }

        if (! Module::has('coupons')) {
            throw new \Exception(__('loyalty.coupons_module_required'));
        }

        $ratio = (float) $vendor->getConfig('loyalty_redeem_points_per_riyal', 10);
        $minPoints = (int) $vendor->getConfig('loyalty_min_redeem_points', $ratio);

        if ($ratio <= 0) {
            throw new \Exception(__('loyalty.program_not_enabled'));
        }

        if ($pointsToRedeem < $minPoints) {
            throw new \Exception(__('loyalty.minimum_points_required', ['points' => $minPoints]));
        }

        $account = $this->getOrCreateAccount($client, $vendor);

        // Only redeem in whole "ratio" blocks so we never give fractional riyal coupons
        $usablePoints = (int) (floor($pointsToRedeem / $ratio) * $ratio);

        if ($usablePoints <= 0 || $usablePoints > $account->points_balance) {
            throw new \Exception(__('loyalty.not_enough_points'));
        }

        $deductValue = floor($usablePoints / $ratio);

        $coupon = \Modules\Coupons\Models\Coupons::create([
            'name' => __('loyalty.redeem_coupon_name'),
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
            __('loyalty.points_redeemed_description', ['code' => $coupon->code]),
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
