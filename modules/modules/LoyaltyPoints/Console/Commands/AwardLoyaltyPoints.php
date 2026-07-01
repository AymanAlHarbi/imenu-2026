<?php

namespace Modules\LoyaltyPoints\Console\Commands;

use App\Order;
use App\Status;
use Illuminate\Console\Command;
use Modules\LoyaltyPoints\Models\LoyaltyTransaction;
use Modules\LoyaltyPoints\Services\LoyaltyService;

class AwardLoyaltyPoints extends Command
{
    protected $signature = 'loyalty:award-points';

    protected $description = 'Award loyalty points for orders that have been actually delivered/picked up and paid';

    public function handle(LoyaltyService $loyaltyService)
    {
        // "Delivered" alias is used in delivery/pickup (isft) projects,
        // "closed" alias is used in QR / dine-in (isqrsaas) projects.
        $finalStatusIds = Status::whereIn('alias', ['delivered', 'closed'])->pluck('id')->toArray();

        if (empty($finalStatusIds)) {
            $this->info('No delivered/closed status found, nothing to do.');

            return;
        }

        // order_ids already credited, to skip them cheaply before the heavier check
        $alreadyAwardedOrderIds = LoyaltyTransaction::where('type', 'earn')
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->toArray();

        $candidates = Order::where('payment_status', 'paid')
            ->whereNotIn('id', $alreadyAwardedOrderIds)
            ->whereHas('status', function ($q) use ($finalStatusIds) {
                $q->whereIn('status.id', $finalStatusIds);
            })
            ->with(['restorant', 'client', 'laststatus'])
            ->limit(200) // safety batch size per run
            ->get();

        $awardedCount = 0;

        foreach ($candidates as $order) {
            // Make sure the *last* status really is delivered/closed,
            // not just one that happened to be passed through earlier.
            $lastStatus = $order->laststatus->first();
            if (! $lastStatus || ! in_array($lastStatus->id, $finalStatusIds)) {
                continue;
            }

            $transaction = $loyaltyService->awardForOrder($order);
            if ($transaction) {
                $awardedCount++;
            }
        }

        $this->info("Loyalty points processed. Orders credited: {$awardedCount}.");
    }
}
