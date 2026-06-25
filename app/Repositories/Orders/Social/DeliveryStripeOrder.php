<?php

namespace App\Repositories\Orders\Social;

use App\Repositories\Orders\SocialOrderRepository;
use App\Traits\Expedition\HasSimpleDelivery;
use App\Traits\Payments\HasStripe;

class DeliveryStripeOrder extends SocialOrderRepository
{
    use HasStripe;
    use HasSimpleDelivery;
}
