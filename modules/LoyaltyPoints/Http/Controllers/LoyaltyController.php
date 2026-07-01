<?php

namespace Modules\LoyaltyPoints\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Order;
use App\Restorant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LoyaltyPoints\Models\LoyaltyAccount;
use Modules\LoyaltyPoints\Services\LoyaltyService;

class LoyaltyController extends Controller
{
    private function clientOnly()
    {
        if (! auth()->user() || ! auth()->user()->hasRole('client')) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Central hub: every restaurant the client has a loyalty account with,
     * plus any restaurant they've ordered from where the program is enabled
     * but they haven't visited their points page yet. This is the page
     * linked from the client sidebar menu (no restaurant id needed there).
     */
    public function myAccounts(): View
    {
        $this->clientOnly();

        $existingAccounts = LoyaltyAccount::where('client_id', auth()->user()->id)
            ->with('restorant')
            ->get()
            ->keyBy('restorant_id');

        $orderedRestorantIds = Order::withoutGlobalScope(\App\Scopes\RestorantScope::class)
            ->where('client_id', auth()->user()->id)
            ->distinct()
            ->pluck('restorant_id');

        $restorants = Restorant::whereIn('id', $orderedRestorantIds->merge($existingAccounts->keys()))
            ->get()
            ->filter(function ($restorant) {
                return (bool) $restorant->getConfig('loyalty_enable', false);
            });

        $rows = $restorants->map(function ($restorant) use ($existingAccounts) {
            $account = $existingAccounts->get($restorant->id);

            return [
                'restorant' => $restorant,
                'points_balance' => $account->points_balance ?? 0,
            ];
        })->values();

        return view('loyaltypoints::points.mine', [
            'rows' => $rows,
        ]);
    }

    public function index(Restorant $restorant, LoyaltyService $loyaltyService): View
    {
        $this->clientOnly();

        $account = $loyaltyService->getOrCreateAccount(auth()->user(), $restorant);
        $account->load('transactions');

        $vendorUrl = null;
        try {
            if (\Illuminate\Support\Facades\Route::has('vendor')) {
                $vendorUrl = route('vendor', $restorant->alias);
            }
        } catch (\Throwable $e) {
            $vendorUrl = null;
        }

        $ratio = (float) $restorant->getConfig('loyalty_redeem_points_per_riyal', 10);
        $minPoints = (int) $restorant->getConfig('loyalty_min_redeem_points', $ratio);
        $usablePoints = $ratio > 0 ? (int) (floor($account->points_balance / $ratio) * $ratio) : 0;
        $usableValue = $ratio > 0 ? floor($usablePoints / $ratio) : 0;

        return view('loyaltypoints::points.index', [
            'restorant' => $restorant,
            'account' => $account,
            'enabled' => $loyaltyService->isEnabled($restorant),
            'ratio' => $ratio,
            'minPoints' => $minPoints,
            'usablePoints' => $usablePoints,
            'usableValue' => $usableValue,
            'canRedeem' => $usablePoints > 0 && $account->points_balance >= $minPoints,
            'vendorUrl' => $vendorUrl,
        ]);
    }

    public function redeem(Request $request, Restorant $restorant, LoyaltyService $loyaltyService): RedirectResponse
    {
        $this->clientOnly();

        try {
            $result = $loyaltyService->redeem(auth()->user(), $restorant);

            return redirect()
                ->route('loyaltypoints.index', $restorant)
                ->with('redeemed_code', $result['coupon_code'])
                ->with('redeemed_value', $result['deduct_value'])
                ->withStatus(__('loyaltypoints::general.redeem_success', [
                    'value' => $result['deduct_value'],
                    'code' => $result['coupon_code'],
                ]));
        } catch (\Throwable $e) {
            return redirect()
                ->route('loyaltypoints.index', $restorant)
                ->withErrors(['points' => $e->getMessage()]);
        }
    }
}

