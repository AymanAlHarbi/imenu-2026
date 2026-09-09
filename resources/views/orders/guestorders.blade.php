

{{--
  صفحة طلباتي (طلبات الضيف) — جاهزة للتركيب
  المسار: resources/views/orders/guestorders.blade.php  (route: guest.orders)
  المتغيّرات (يمرّرها OrderController@guestOrders): $orders, $statuses, $backUrl, $showWhatsApp
  أحدث طلب يُفتح تلقائياً؛ البقية مطويّة (نقر العنوان يفتح/يغلق).
--}}
@extends('layouts.front', ['title' => __('Orders'), 'class' => 'imenu-clean-page'])

@section('content')
@php
  $currency = config('settings.cashier_currency');
  $convert  = config('settings.do_convertion');
  $firstResto = $orders->count() ? $orders->first()->restorant : null;
  $brand = ($firstResto && method_exists($firstResto,'getConfig')) ? $firstResto->getConfig('theme_color', '#FA8128') : '#FA8128';
@endphp
<div dir="rtl" id="imenu-guest-orders" style="--brand: {{ $brand ?: '#FA8128' }}; --teal:#48AAAD; min-height:100vh; background:#ECEAE6; display:flex; justify-content:center; font-family:'Cairo',system-ui,sans-serif; color:#1B1B1A;">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
  <div style="width:100%; max-width:480px; background:#F7F6F4; min-height:100vh; display:flex; flex-direction:column; box-shadow:0 0 60px rgba(20,20,18,.08);">



        <header style="position:sticky; top:0; z-index:20; background:rgba(247,246,244,.92); backdrop-filter:blur(12px); border-bottom:1px solid #E9E7E2; padding:11px 16px; display:flex; align-items:center; gap:11px;">
      <a href="{{ $backUrl }}" aria-label="{{ __('Go Back') }}" style="width:40px;height:40px;flex:none;background:#fff;border:1px solid #E9E7E2;border-radius:12px;display:grid;place-items:center;color:#1B1B1A;text-decoration:none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
      </a>
      <div style="display:flex;flex-direction:column;line-height:1.3;min-width:0;flex:1;">
        <span style="font-weight:700;font-size:1.15rem;">{{ __('My Orders') }}</span>
        <span style="font-size:.8rem;color:#8A8983;">{{ $orders->count() }} {{ __('Orders') }}</span>
      </div>
      @auth
      <button type="button" onclick="var m=document.getElementById('acctMenu'); m.style.display = m.style.display==='block' ? 'none' : 'block';" aria-label="{{ __('My profile') }}" style="width:40px;height:40px;flex:none;border:none;border-radius:12px;background:var(--brand);color:#fff;display:grid;place-items:center;font-weight:700;font-size:1.05rem;cursor:pointer;">
        {{ mb_substr(auth()->user()->name,0,1) }}
      </button>
      @endauth
    </header>

    @auth
    <div id="acctMenu" style="display:none; margin:10px 16px 0; background:#fff; border:1px solid #E9E7E2; border-radius:14px; box-shadow:0 8px 30px rgba(20,20,18,.10); overflow:hidden;">
      <div style="padding:13px 16px; border-bottom:1px solid #E9E7E2;">
        <div style="font-weight:700;">{{ auth()->user()->name }}</div>
        <div style="font-size:.82rem; color:#8A8983;" dir="ltr">{{ auth()->user()->phone }}</div>
      </div>
      <a href="{{ $backUrl }}" style="display:block; padding:12px 16px; color:#1B1B1A; text-decoration:none; border-bottom:1px solid #E9E7E2; font-weight:600;">{{ __('Go back to restaurant') }}</a>
      <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="display:block; padding:12px 16px; color:#C0392B; text-decoration:none; font-weight:600;">{{ __('Logout') }}</a>
    </div>
    @endauth



    <main style="flex:1; padding:16px; display:flex; flex-direction:column; gap:13px;">

      @forelse($orders as $i => $order)
        @php
          $lastId   = $order->status->pluck('id')->last();
          $lastName = $order->status->pluck('name')->last() ?? 'Just created';
          $isDone   = in_array($lastId, [7, 11]);
          $isRej    = in_array($lastId, [8, 9, 12]);
          $stColor  = $isDone ? '#1F8A5B' : ($isRej ? '#C0453B' : '#FA8128');
          $count    = $order->items->sum(fn($it) => $it->pivot->qty);
          $total    = $order->delivery_price + $order->order_price_with_discount;
          $open     = $i === 0;
        @endphp
        <section style="background:#fff;border:1px solid #E9E7E2;border-radius:18px;box-shadow:0 1px 2px rgba(20,20,18,.04);overflow:hidden;">
          <button type="button" class="go-toggle" data-target="go-body-{{ $order->id }}" style="width:100%;text-align:start;background:none;border:none;cursor:pointer;font-family:inherit;padding:15px 16px;display:flex;align-items:center;gap:12px;">
            <div style="flex:1;min-width:0;">
              <div style="display:flex;align-items:center;gap:9px;margin-bottom:6px;">
                <span style="font-weight:800;font-size:1.05rem;direction:ltr;">#{{ $order->id_formated }}</span>
                <span style="font-size:.78rem;font-weight:700;padding:3px 11px;border-radius:999px;color:{{ $stColor }};background:color-mix(in oklab, {{ $stColor }} 12%, #fff);border:1px solid color-mix(in oklab, {{ $stColor }} 26%, #fff);">{{ __($lastName) }}</span>
              </div>
              <div style="font-size:.83rem;color:#8A8983;">{{ $order->created_at->locale(config('app.locale'))->isoFormat('LLLL') }}</div>
              <div style="font-size:.85rem;color:#6B6A66;margin-top:4px;">{{ $count }} {{ __('items') }} · @money($total, $currency, true)</div>
            </div>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#A8A7A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none;transition:transform .2s ease;{{ $open ? 'transform:rotate(180deg);' : '' }}"><path d="M6 9l6 6 6-6"/></svg>
          </button>

          <div id="go-body-{{ $order->id }}" class="go-body" style="padding:0 16px 16px;{{ $open ? '' : 'display:none;' }}">

            <div style="display:flex;align-items:center;gap:12px;background:#FBFAF8;border:1px solid #EFEDE8;border-radius:14px;padding:12px;margin-bottom:14px;">
              <div style="width:40px;height:40px;flex:none;border-radius:11px;background:var(--brand);color:#fff;display:grid;place-items:center;font-weight:700;">{{ mb_substr($order->restorant->name,0,1) }}</div>
              <div style="flex:1;min-width:0;">
                <div style="font-weight:700;">{{ $order->restorant->name }}</div>
                @if(strlen($order->restorant->phone) > 2)
                <div style="font-size:.82rem;color:#8A8983;direction:ltr;text-align:right;">{{ $order->restorant->phone }}</div>
                @endif
              </div>
              @if(strlen($order->restorant->phone) > 2)
              <a href="tel:{{ $order->restorant->phone }}" aria-label="{{ __('Call') }}" style="width:40px;height:40px;flex:none;border-radius:11px;background:#fff;border:1px solid #E4E2DD;display:grid;place-items:center;color:var(--brand);text-decoration:none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h3l2 5-2 1.5a11 11 0 0 0 5 5L17 18l5 2v3a1 1 0 0 1-1 1A17 17 0 0 1 3 5a1 1 0 0 1 1-1z"/></svg>
              </a>
              @endif
            </div>

            <div style="font-size:.95rem;font-weight:700;margin-bottom:11px;">{{ __('Order') }}</div>
            <div style="display:flex;flex-direction:column;gap:12px;">
              @foreach($order->items as $item)
                @php $price = $item->pivot->variant_price ?: $item->price; @endphp
                @if($item->pivot->qty > 0)
                <div style="display:flex;align-items:center;gap:11px;">
                  <span style="min-width:30px;height:28px;flex:none;border-radius:8px;background:color-mix(in oklab, var(--brand) 11%, #fff);color:var(--brand);font-weight:700;font-size:.82rem;display:grid;place-items:center;">{{ $item->pivot->qty }}×</span>
                  <span style="flex:1;font-size:.93rem;font-weight:500;">{{ $item->name }}</span>
                  <span style="color:#6B6A66;font-weight:600;white-space:nowrap;font-size:.9rem;">@money($item->pivot->qty * $price, $currency, true)</span>
                </div>
                @endif
              @endforeach
            </div>

            <div style="border-top:1px solid #EFEDE8;margin-top:15px;padding-top:13px;display:flex;flex-direction:column;gap:8px;">
              <div style="display:flex;justify-content:space-between;font-size:.88rem;"><span style="color:#8A8983;">{{ __('Sub Total') }}</span><span style="font-weight:600;">@money($order->order_price, $currency, $convert)</span></div>
              @if($order->delivery_method==1)
              <div style="display:flex;justify-content:space-between;font-size:.88rem;"><span style="color:#8A8983;">{{ __('Delivery') }}</span><span style="font-weight:600;">@money($order->delivery_price, $currency, $convert)</span></div>
              @endif
              <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:2px;"><span style="font-weight:700;font-size:1.02rem;">{{ __('TOTAL') }}</span><span style="font-weight:800;font-size:1.2rem;color:var(--brand);">@money($total, $currency, true)</span></div>
            </div>

            {{-- iMenu 2026 — زر "وصلت" لطلبات السيارة --}}
            <div style="margin-top:16px;">
              @include('orders.partials.arrived')
            </div>

            <div style="display:flex;gap:10px;margin-top:16px;">
              @if($showWhatsApp)
              <a href="{{ route('order.success') }}?order={{ $order->id }}&whatsapp=yes" target="_blank" style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;height:46px;border-radius:13px;background:#25D366;color:#fff;font-weight:700;font-size:.92rem;text-decoration:none;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24z"/></svg>
                {{ __('WhatsApp') }}
              </a>
              @endif
              <a href="{{ route('vendor', $order->restorant->subdomain) }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;height:46px;border-radius:13px;background:#fff;border:1.5px solid #E4E2DD;color:#1B1B1A;font-weight:700;font-size:.92rem;text-decoration:none;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"/></svg>
                {{ __('Order again') }}
              </a>
            </div>

          </div>
        </section>
      @empty
        <div style="text-align:center;padding:60px 20px;color:#9A9994;">{{ __('No orders yet') }}</div>
      @endforelse

    </main>
  </div>
</div>

<script>
  document.querySelectorAll('#imenu-guest-orders .go-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
      var body = document.getElementById(btn.dataset.target);
      var chev = btn.querySelector('svg:last-child');
      var hidden = body.style.display === 'none';
      body.style.display = hidden ? '' : 'none';
      if(chev) chev.style.transform = hidden ? 'rotate(180deg)' : 'rotate(0deg)';
    });
  });
</script>
@endsection

