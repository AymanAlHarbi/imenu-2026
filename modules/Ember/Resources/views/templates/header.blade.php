<!-- header (Ember: Bold Appetite cover) -->
<div class="em-cover" style="background-image:url('{{ $restorant->coverm }}');">
    <div class="em-logo" style="@if(strlen($restorant->logom)>5)background-image:url('{{ $restorant->logom }}');@endif">
        @if(!(strlen($restorant->logom)>5))<i class="las la-fire"></i>@endif
    </div>
    <h1 class="em-rest-name notranslate">{{ $restorant->name }}</h1>
    <p class="em-rest-desc">{{ $restorant->description }}</p>



    <div class="em-tabs">
        <a href="#place-menu" class="em-tab menu-tab is-active"><i class="las la-utensils"></i>{{ __('Menu') }}</a>
        <a href="#place-info" class="em-tab menu-tab"><i class="las la-info-circle"></i>{{ __('About') }}</a>

        @if (!$restorant->getConfig('disable_callwaiter',false))
            <a data-toggle="modal" data-target="#modal-form" href="javascript:;" class="em-tab"><i class="las la-bell"></i>{{ __('Waiter') }}</a>
        @endif

        @if (\Akaunting\Module\Facade::has('cards')&&$restorant->getConfig('enable_loyalty', false))
            <a href="{{ route('loyalty.landing',['alias'=>$restorant->subdomain]) }}" class="em-tab"><i class="las la-gift"></i>{{ __('loyalty.loyalty_program') }}</a>
        @endif

        @if ($canDoOrdering&&$restorant->getConfig('clients_enable','false')!='false')
<a href="{{ auth()->check() ? route('orders.index') : route('client.phone.show') }}" class="em-tab"><i class="las la-receipt"></i>{{ __('My Orders') }}</a>

        @elseif(isset($hasGuestOrders)&&$hasGuestOrders&&$canDoOrdering)
          <a href="{{ auth()->check() ? route('guest.orders') : route('client.phone.show') }}" class="em-tab"><i class="las la-receipt"></i>{{ __('My Orders') }}</a>

        @endif
                @if (strlen($restorant->phone ?? '')>2)
            <a href="tel:{{ $restorant->phone }}" class="em-tab"><i class="las la-phone"></i>{{ __('Call') }}</a>
        @endif

        @if (strlen($restorant->address)>2)
            <a href="https://maps.google.com/maps?q={{ $restorant->address }}" target="_blank" class="em-tab"><i class="las la-map-marker"></i>{{ __('Directions') }}</a>
        @endif
    </div>
</div>
