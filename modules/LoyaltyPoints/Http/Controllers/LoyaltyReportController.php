<?php

namespace Modules\LoyaltyPoints\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\LoyaltyPoints\Models\LoyaltyAccount;
use Modules\LoyaltyPoints\Models\LoyaltyTransaction;
use Modules\LoyaltyPoints\Services\LoyaltyService;

class LoyaltyReportController extends Controller
{
    public function index(): View
    {
        $this->ownerAndStaffOnly();

        $vendor = $this->getCompany();

        $accounts = LoyaltyAccount::where('restorant_id', $vendor->id);

        $stats = [
            'members' => $accounts->count(),
            'points_outstanding' => (clone $accounts)->sum('points_balance'),
            'points_given' => LoyaltyTransaction::whereIn('loyalty_account_id', (clone $accounts)->pluck('id'))
                ->where('type', 'earn')->sum('points'),
            'points_redeemed' => abs(LoyaltyTransaction::whereIn('loyalty_account_id', (clone $accounts)->pluck('id'))
                ->where('type', 'redeem')->sum('points')),
        ];

        $topClients = LoyaltyAccount::where('restorant_id', $vendor->id)
            ->with('client')
            ->orderBy('lifetime_points', 'DESC')
            ->limit(10)
            ->get();

        return view('loyaltypoints::report.index', [
            'vendor' => $vendor,
            'stats' => $stats,
            'topClients' => $topClients,
            'enabled' => (bool) $vendor->getConfig('loyalty_enable', false),
        ]);
    }

    /**
     * Manually trigger the points sweep for the current vendor right now,
     * and show exactly why each recent paid/finalized order was or wasn't
     * credited. Built for hosting setups without SSH/Terminal access.
     */
    public function runNow(LoyaltyService $loyaltyService): View
    {
        $this->ownerAndStaffOnly();

        $vendor = $this->getCompany();

        $result = $loyaltyService->sweepAndAward($vendor->id);

        return view('loyaltypoints::report.run', [
            'vendor' => $vendor,
            'result' => $result,
            'enabled' => (bool) $vendor->getConfig('loyalty_enable', false),
        ]);
    }
}

