<?php

namespace Modules\LoyaltyPoints\Console\Commands;

use Illuminate\Console\Command;
use Modules\LoyaltyPoints\Services\LoyaltyService;

class AwardLoyaltyPoints extends Command
{
    protected $signature = 'loyalty:award-points {--vendor= : Restrict to a single restorant/company id}';

    protected $description = 'Award loyalty points for orders that have been actually delivered/picked up and paid';

    public function handle(LoyaltyService $loyaltyService)
    {
        $result = $loyaltyService->sweepAndAward($this->option('vendor') ?: null);

        $this->info("Loyalty sweep done. Checked: {$result['checked']}, awarded: {$result['awarded']}.");

        foreach ($result['details'] as $row) {
            $this->line(sprintf(
                'Order #%s | last_status=%s | payment=%s | client_id=%s | %s',
                $row['id_formated'],
                $row['last_status'],
                $row['payment_status'],
                $row['client_id'] ?? 'NULL',
                $row['reason']
            ));
        }
    }
}
