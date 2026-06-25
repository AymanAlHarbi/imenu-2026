<?php

namespace App\Repositories\Orders\Social;

use App\Repositories\Orders\SocialOrderRepository;
use App\Traits\Expedition\HasPickup;
use App\Traits\Payments\HasStripe;

class PickupStripeOrder extends SocialOrderRepository
{
    use HasStripe;
    use HasPickup;
}
