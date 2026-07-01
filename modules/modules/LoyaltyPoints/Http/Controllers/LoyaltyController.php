<?php

namespace Modules\LoyaltyPoints\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Restorant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LoyaltyPoints\Services\LoyaltyService;

class LoyaltyController extends Controller
{
    private function clientOnly()
    {
        if (! auth()->user() || ! auth()->user()->hasRole('client')) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index(Restorant $restorant, LoyaltyService $loyaltyService): View
    {
        $this->clientOnly();

        $account = $loyaltyService->getOrCreateAccount(auth()->user(), $restorant);
        $account->load('transactions');

        return view('loyaltypoints::points.index', [
            'restorant' => $restorant,
            'account' => $account,
            'enabled' => $loyaltyService->isEnabled($restorant),
            'ratio' => (float) $restorant->getConfig('loyalty_redeem_points_per_riyal', 10),
            'minPoints' => (int) $restorant->getConfig('loyalty_min_redeem_points', 10),
        ]);
    }

    public function redeem(Request $request, Restorant $restorant, LoyaltyService $loyaltyService): RedirectResponse
    {
        $this->clientOnly();

        $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $loyaltyService->redeem(auth()->user(), $restorant, (int) $request->points);

            return redirect()
                ->route('loyaltypoints.index', $restorant)
                ->withStatus(__('loyalty.redeem_success', [
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
