<thead class="thead-light">
    <tr>
        <th scope="col">{{ __('ID') }}</th>
        @hasrole('admin|driver')
            <th scope="col">{{ __('Restaurant') }}</th>
        @endhasrole
        <th scope="col">{{ __('Client') }}</th>
        <th class="table-web" scope="col">{{ __('Created') }}</th>
        <th class="table-web" scope="col">{{ __('Method') }}</th>
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
    //iMenu 2026 - العرض 05XXXXXXXX ورابط واتساب 9665… من خدمة واحدة
    $waPhone = \App\Helpers\SaudiPhone::wa($customerPhone);
    $customerPhone = \App\Helpers\SaudiPhone::local($customerPhone);
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
            <a class="text-sm text-success" href="https://wa.me/{{ $waPhone }}" target="_blank" dir="ltr" @if(! $waPhone) style="pointer-events:none;text-decoration:none" @endif>
                <i class="fab fa-whatsapp"></i> {{ $customerPhone }}
            </a>
        @endif
        {{-- iMenu 2026 — حالة الثقة مع العميل، لا مع طريقة الاستلام --}}
        @if (!isset($hideAction) && $order->client && \App\Services\Trust::isTrusted($order->client))
            <br/><small style="color:#2E5C43;font-weight:600">{{ __('Trusted customer') }}</small>
        @elseif (!isset($hideAction) && $order->client && \App\Services\Trust::isBlocked($order->client))
            <br/><small style="color:#C0392B;font-weight:600">{{ __('Ordering paused') }}</small>
        @endif
    </td>
    <td class="table-web">
        <span class="text-sm">{{ $order->created_at->locale(Config::get('app.locale'))->calendar() }}</span>
        <br/>
        <small class="text-muted">{{ $order->created_at->locale(Config::get('app.locale'))->diffForHumans() }}</small>
        {{-- iMenu 2026 — الوعد مع الوقت، لا مع الطريقة --}}
        @if (!isset($hideAction) && $order->getConfig('ready_promise_at', false))
            <br/><small class="text-muted">{{ __('Promised') }}
                <span style="font-variant-numeric:tabular-nums">{{ \Carbon\Carbon::parse($order->getConfig('ready_promise_at'))->format('H:i') }}</span>
            </small>
        @endif
    </td>
    <td class="table-web">
        {{-- iMenu 2026 — سطران فقط: الطريقة، ثم المركبة مضغوطة في سطر واحد.
             حالة الثقة انتقلت لخلية العميل، والوعد لخلية التاريخ، و«لم يُستلم»
             لعمود الإجراءات — فالخلية كانت تجمع ست معلومات غير متجانسة. --}}
        <span class="badge badge-pill badge-{{ $methodColors[$order->delivery_method] ?? 'secondary' }}">
            {{ $order->table ? $order->table->getFullNameAttribute().' / ' : '' }}{{ $order->getConfig('pickup_method','') == 'car' ? __('From my car') : __('From the coffee shop') }}
        </span>
        @if ($order->getConfig('pickup_method','') == 'car')
            @php
                $imenuCar = array_filter([
                    trim($order->getConfig('vehicle_brand','').' '.$order->getConfig('vehicle_model','')),
                    $order->getConfig('vehicle_color',''),
                    $order->getConfig('vehicle_plate',''),
                ]);
            @endphp
            @if (count($imenuCar))
                <br/><span class="text-sm font-weight-bold"><i class="fa fa-car text-muted"></i>
                    {{ implode(' · ', $imenuCar) }}
                </span>
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