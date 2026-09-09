<thead class="thead-light">
    <tr>
        <th scope="col">{{ __('ID') }}</th>
        @hasrole('admin|driver')
            <th scope="col">{{ __('Restaurant') }}</th>
        @endhasrole
        <th scope="col">{{ __('Client') }}</th>
        <th class="table-web" scope="col">{{ __('Created') }}</th>
        <th class="table-web" scope="col">{{ !config('settings.is_whatsapp_ordering_mode') ? __('Table / Method') : __('Method') }}</th>
        <th class="table-web" scope="col">{{ __('Price') }}</th>
        <th class="table-web" scope="col">{{ __('Payment status') }}</th>
        <th scope="col">{{ __('Last status') }}</th>
        @if (!isset($hideAction))
            <th scope="col">{{ __('Actions') }}</th>
        @endif
    </tr>
</thead>
<tbody>
@foreach($orders as $order)
@php
    $customerName = $order->client ? $order->client->name : null;
    $customerPhone = $order->phone ? $order->phone : ($order->client ? $order->client->phone : null);
    if (!$customerName || !$customerPhone) {
        foreach ($order->getAllConfigs() as $cKey => $cValue) {
            $lKey = mb_strtolower($cKey);
            if (!$customerName && $cValue && (str_contains($lKey, 'name') || str_contains($cKey, 'اسم'))) {
                $customerName = $cValue;
            }
            if (!$customerPhone && $cValue && (str_contains($lKey, 'phone') || str_contains($lKey, 'mobile') || str_contains($cKey, 'جوال') || str_contains($cKey, 'هاتف') || str_contains($cKey, 'رقم'))) {
                $customerPhone = $cValue;
            }
        }
    }
    $waPhone = null;
    if ($customerPhone) {
        $waPhone = preg_replace('/[^0-9]/', '', $customerPhone);
        if (substr($waPhone, 0, 2) == '00') {
            $waPhone = substr($waPhone, 2);
        } elseif (substr($waPhone, 0, 1) == '0') {
            $waPhone = '966'.substr($waPhone, 1);
        }
    }
    $methodColors = [1 => 'primary', 2 => 'info', 3 => 'warning'];
@endphp
<tr>
    <td>
        <a class="btn badge badge-success badge-pill" href="{{ route('orders.show',$order->id )}}">#{{ $order->id_formated }}</a>
    </td>
    @hasrole('admin|driver')
    <th scope="row">
        <div class="media align-items-center">
            <a class="avatar-custom mr-3">
                <img class="rounded" alt="..." src={{ $order->restorant->icon }}>
            </a>
            <div class="media-body">
                <span class="mb-0 text-sm">{{ $order->restorant->name }}</span>
            </div>
        </div>
    </th>
    @endif
    <td>
        @if ($customerName)
            <span class="text-sm font-weight-bold">{{ $customerName }}</span>
        @else
            <span class="text-sm text-muted">{{ __('Guest') }}</span>
        @endif
        @if ($customerPhone)
            <br/>
            <a class="text-sm text-success" href="https://wa.me/{{ $waPhone }}" target="_blank" dir="ltr">
                <i class="fab fa-whatsapp"></i> {{ $customerPhone }}
            </a>
        @endif
    </td>
    <td class="table-web">
        <span class="text-sm">{{ $order->created_at->locale(Config::get('app.locale'))->calendar() }}</span>
        <br/>
        <small class="text-muted">{{ $order->created_at->locale(Config::get('app.locale'))->diffForHumans() }}</small>
    </td>
    <td class="table-web">
        <span class="badge badge-pill badge-{{ $methodColors[$order->delivery_method] ?? 'secondary' }}">
            {{ $order->table ? $order->table->getFullNameAttribute()." / " : '' }}{{ $order->getExpeditionType() }}
        </span>
        @if ($order->getConfig('pickup_method','') == 'car')
            <br/>
            <span class="text-sm font-weight-bold"><i class="fa fa-car"></i>
                {{ trim($order->getConfig('vehicle_brand','').' '.$order->getConfig('vehicle_model','')) }}
                @if ($order->getConfig('vehicle_color',''))
                    · {{ $order->getConfig('vehicle_color') }}
                @endif
            </span>
            @if ($order->getConfig('vehicle_plate',''))
                <br/><small class="text-muted" dir="ltr">{{ $order->getConfig('vehicle_plate') }}</small>
            @endif
            @if ($order->getConfig('arrived_at',''))
                <br/><span class="badge badge-pill badge-success">{{ __('Customer has arrived') }}</span>
            @endif
        @endif
    </td>
    <td class="table-web">
        <span class="font-weight-bold">@money( $order->order_price_with_discount, config('settings.cashier_currency'),config('settings.do_convertion'))</span>
        @if (!isset($hideAction))
            <br/><small class="text-muted">{{ count($order->items) }} {{ __('Items') }}</small>
        @endif
    </td>
    <td class="table-web">
        @if ($order->payment_status == 'paid')
            <span class="badge badge-pill badge-success">{{ __('Paid') }}</span>
        @else
            <span class="badge badge-pill badge-danger">{{ __(ucfirst($order->payment_status)) }}</span>
        @endif
    </td>
    <td>
        @include('orders.partials.laststatus')
    </td>
    @if (!isset($hideAction))
        @include('orders.partials.actions.table',['order' => $order ])
    @endif
</tr>
@endforeach
</tbody>