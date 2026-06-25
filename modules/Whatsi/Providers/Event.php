<?php

namespace Modules\Whatsi\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Whatsi\Listeners\WebhookOrder;

class Event extends ServiceProvider
{
    protected $listen = [];

    protected $subscribe = [
        WebhookOrder::class,
    ];
}