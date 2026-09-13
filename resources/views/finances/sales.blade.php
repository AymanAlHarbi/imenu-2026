{{--
    iMenu 2026 — صفحة «المبيعات» لصاحب الكوفي (استلام فقط، صفر عمولة).

    تُعرض حين settings.pickup_only = true من FinanceController::ownerSales.
    الصفحة الأصلية (finances/index.blade.php ببطاقات الرسوم والتوصيل و Stripe)
    لم تُمسّ إطلاقًا — PICKUP_ONLY=false يعيدها كما هي.
    المعاينة المعتمدة: Artifact «صفحة المبيعات — لوحة الكوفي» — ١٣ سبتمبر ٢٠٢٦.
--}}
@extends('layouts.app', ['title' => __('Sales')])

@section('admin_title')
    {{ __('Sales') }}
@endsection

@section('head')
    <link rel="stylesheet" href="{{ asset('imenu/sales/sales.css') }}?v=1">
@endsection

@php
    $ranges = [
        'today'     => __('Today'),
        'yesterday' => __('Yesterday'),
        'week'      => __('This week'),
        'month'     => __('This month'),
        'lastmonth' => __('Last month'),
    ];
    $currency = config('settings.cashier_currency');
    $convert = config('settings.do_convertion');
@endphp

@section('content')
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8"></div>
<div class="container-fluid mt--7">
  <div class="card shadow is-shell">
    <div class="card-body is-wrap">

<div class="is">

  {{-- الترويسة --}}
  <div class="is-head">
    <div>
      <h1>{{ __('Sales') }}</h1>
      <p>{{ __('Orders of') }} <strong>{{ $restaurant->name }}</strong> {{ __('that were handed over and paid on pickup.') }}</p>
    </div>
    <a class="is-btn" href="{{ request()->fullUrlWithQuery(['report' => 'true']) }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
      <span>{{ __('Download Excel') }}</span>
    </a>
  </div>

  {{-- الفترات --}}
  <div class="is-ranges">
    @foreach($ranges as $key => $label)
      <a class="is-range {{ $range === $key ? 'is-on' : '' }}" href="{{ route('finances.owner', ['range' => $key]) }}">{{ $label }}</a>
    @endforeach
    <button type="button" class="is-range {{ $range === 'custom' ? 'is-on' : '' }}" id="is-custom-toggle">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
      <span>{{ __('Custom range') }}</span>
    </button>
  </div>

  <div class="is-custom {{ $range === 'custom' ? 'is-open' : '' }}" id="is-custom">
    <form method="GET" action="{{ route('finances.owner') }}">
      <input type="hidden" name="range" value="custom">
      <label>{{ __('Date From') }}<input type="date" name="fromDate" value="{{ $from->toDateString() }}"></label>
      <label>{{ __('Date to') }}<input type="date" name="toDate" value="{{ $to->toDateString() }}"></label>
      <button type="submit" class="is-btn go">{{ __('Filter') }}</button>
    </form>
  </div>

  {{-- البطاقات --}}
  <div class="is-cards">

    <div class="is-card">
      <div class="top"><span>{{ __('Orders') }}</span>
        <span class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l1 13H5z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></span>
      </div>
      <div class="val"><b class="num">{{ number_format($stats['count']) }}</b><i>{{ __('orders') }}</i></div>
      <div class="sub">{{ __('In the selected period') }}</div>
    </div>

    <div class="is-card accent">
      <div class="top"><span>{{ __('Sales') }}</span>
        <span class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9"/><path d="m7 14 5-5 5 5"/><path d="M5 3h14"/></svg></span>
      </div>
      <div class="val"><b class="num">@money($stats['sales'], $currency, $convert)</b></div>
      <div class="sub">{{ $withVat ? __('VAT included · paid on pickup') : __('Paid on pickup') }}</div>
    </div>

    <div class="is-card">
      <div class="top"><span>{{ __('Average order') }}</span>
        <span class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="m5 15 4-4 4 3 6-7"/></svg></span>
      </div>
      <div class="val"><b class="num">@money($stats['average'], $currency, $convert)</b></div>
      <div class="sub">{{ __('Sales ÷ orders') }}</div>
    </div>

    @if($withVat)
      <div class="is-card">
        <div class="top"><span>{{ __('VAT') }}</span>
          <span class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19 19 5"/><circle cx="7.5" cy="7.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/></svg></span>
        </div>
        <div class="val"><b class="num">@money($stats['vat'], $currency, $convert)</b></div>
        <div class="sub">{{ __('Included in sales') }}</div>
      </div>

      <div class="is-card">
        <div class="top"><span>{{ __('Net') }}</span>
          <span class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg></span>
        </div>
        <div class="val"><b class="num">@money($stats['net'], $currency, $convert)</b></div>
        <div class="sub">{{ __('Sales without VAT') }}</div>
      </div>
    @endif

    <div class="is-card">
      <div class="top"><span>{{ __('Pickup method') }}</span>
        <span class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16 6.5 10h11L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg></span>
      </div>
      <div class="split">
        <div class="part"><b class="num">{{ number_format($stats['fromCar']) }}</b><span>{{ __('From the car') }}</span></div>
        <span class="sep"></span>
        <div class="part"><b class="num">{{ number_format($stats['fromShop']) }}</b><span>{{ __('From the cafe') }}</span></div>
      </div>
      <div class="bar"><span style="width: {{ $stats['count'] > 0 ? round($stats['fromCar'] / $stats['count'] * 100) : 0 }}%"></span></div>
    </div>

    <div class="is-card {{ $stats['notCollected'] > 0 ? 'danger' : '' }}">
      <div class="top"><span>{{ __('Not collected') }}</span>
        <span class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></span>
      </div>
      <div class="val"><b class="num">{{ number_format($stats['notCollected']) }}</b>@if($stats['notCollected'] > 0)<i>{{ __('orders') }}</i>@endif</div>
      <div class="sub">{{ $stats['notCollected'] > 0 ? __('Accepted reports in the period').' · '.$stats['notCollectedPercent'].'%' : __('No unclaimed orders') }}</div>
    </div>

  </div>

  {{-- الجدول --}}
  <div class="is-table">
    <div class="head">
      <b>{{ __('Collected orders') }}</b>
      <span class="num">{{ $orders->total() }} {{ __('orders') }} · {{ $ranges[$range] ?? __('Custom range') }}</span>
    </div>

    @if($orders->total() > 0)
      <div class="scroll">
        <table>
          <thead>
            <tr>
              <th>{{ __('Order') }}</th>
              <th class="is-when-row">{{ __('Time') }}</th>
              <th class="is-pickup-col">{{ __('Pickup') }}</th>
              <th class="is-items">{{ __('Items') }}</th>
              @if($withVat)<th class="is-items">{{ __('VAT') }}</th>@endif
              <th>{{ __('Amount') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
              @php $isCar = ($pickupMethods[$order->id] ?? '') === 'car'; @endphp
              <tr>
                <td>
                  <a class="oid num" href="{{ route('orders.show', $order->id) }}">#{{ $order->id_formated }}</a>
                  <div class="is-when-inline num" dir="ltr">{{ $order->created_at->format(config('settings.datetime_display_format')) }}</div>
                  <div class="is-tag-inline">@include('finances.partials.pickup_tag', ['isCar' => $isCar])</div>
                </td>
                <td class="is-when-row"><span class="when num" dir="ltr">{{ $order->created_at->format(config('settings.datetime_display_format')) }}</span></td>
                <td class="is-pickup-col">@include('finances.partials.pickup_tag', ['isCar' => $isCar])</td>
                <td class="is-items num">{{ $order->items->sum('pivot.qty') }}</td>
                @if($withVat)<td class="is-items num">@money($order->vatvalue ?: 0, $currency, $convert)</td>@endif
                <td><span class="amount num">@money($order->order_price_with_discount, $currency, $convert)</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="foot">
        <span class="num" style="font-size: 13px; color: #8FA3B8;">{{ $orders->firstItem() }}–{{ $orders->lastItem() }} {{ __('of') }} {{ $orders->total() }}</span>
        {{ $orders->appends(request()->query())->links() }}
      </div>
    @else
      <div class="empty">
        {{ __('No collected orders in this period.') }}<br>
        <span style="font-size: 13px; color: #8FA3B8;">{{ __('An order appears here once the cashier screen marks it handed over.') }}</span>
      </div>
    @endif
  </div>

</div>

    </div>
  </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        var toggle = document.getElementById('is-custom-toggle');
        var box = document.getElementById('is-custom');
        if (toggle && box) {
            toggle.addEventListener('click', function () { box.classList.toggle('is-open'); });
        }
    })();
</script>
@endsection
