<?php

namespace Modules\LoyaltyPoints\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Restorant;
use Illuminate\Http\JsonResponse;
use Modules\LoyaltyPoints\Services\LoyaltyService;

class LoyaltyApiController extends Controller
{
    public function balance(Restorant $restorant, LoyaltyService $loyaltyService): JsonResponse
    {
        $account = $loyaltyService->getOrCreateAccount(auth()->user(), $restorant);

        return response()->json([
            'points_balance' => $account->points_balance,
            'lifetime_points' => $account->lifetime_points,
            'redeemable_value' => $account->redeemable_value,
        ]);
    }

    public function history(Restorant $restorant, LoyaltyService $loyaltyService): JsonResponse
    {
        $account = $loyaltyService->getOrCreateAccount(auth()->user(), $restorant);

        return response()->json([
            'transactions' => $account->transactions()->limit(50)->get([
                'id', 'type', 'points', 'description', 'created_at',
            ]),
        ]);
    }
}
