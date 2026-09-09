@section('head')
<script>
  window.snaptr && snaptr('track', 'PURCHASE', {
    'price': {{ $order->delivery_price + $order->order_price_with_discount }},
    'currency': '{{ config('settings.cashier_currency') }}',
    'transaction_id': '{{ $order->id }}'
  });
</script>
@endsection




{{--
  صفحة نجاح الطلب — جاهزة للتركيب
  المسار: resources/views/orders/success.blade.php  (route: order.success)
  المتغيّرات المستخدمة (يمرّرها OrderController@success): $order, $showWhatsApp
  لتلوين الهوية: --brand يأخذ لون المطعم تلقائياً من theme_color إن وُجد.
--}}

@extends('layouts.front', ['title' => __('Order'), 'class' => 'imenu-clean-page'])


@section('content')
@php
  $currency = config('settings.cashier_currency');
  $convert  = config('settings.do_convertion');
  $lastStatus = $order->status->pluck('name')->last() ?? 'Just created';
  $brand = method_exists($order->restorant,'getConfig') ? $order->restorant->getConfig('theme_color', '#FA8128') : '#FA8128';
  $restoLink = config('settings.wildcard_domain_ready') ? $order->restorant->getLinkAttribute() : route('vendor', $order->restorant->subdomain);
@endphp
<div dir="rtl" id="imenu-success" style="--brand: {{ $brand ?: '#FA8128' }}; --teal:#48AAAD; min-height:100vh; background:#ECEAE6; display:flex; justify-content:center; font-family:'Cairo',system-ui,sans-serif; color:#1B1B1A;">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
  <div style="width:100%; max-width:480px; background:#F7F6F4; min-height:100vh; display:flex; flex-direction:column; box-shadow:0 0 60px rgba(20,20,18,.08);">

    <header style="position:sticky; top:0; z-index:20; background:rgba(247,246,244,.92); backdrop-filter:blur(12px); border-bottom:1px solid #E9E7E2; padding:11px 16px; display:flex; align-items:center; gap:11px;">
      <div style="width:40px;height:40px;flex:none;border-radius:12px;background:var(--brand);color:#fff;display:grid;place-items:center;font-weight:700;font-size:1.15rem;">{{ mb_substr($order->restorant->name,0,1) }}</div>
      <div style="display:flex;flex-direction:column;line-height:1.3;min-width:0;flex:1;">
        <span style="font-weight:700;font-size:1.05rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $order->restorant->name }}</span>
        <span style="font-size:.8rem;color:#8A8983;">{{ __('Order Confirmation') }}</span>
      </div>
      <a href="{{ $restoLink }}" aria-label="{{ __('Close') }}" style="width:40px;height:40px;flex:none;background:#fff;border:1px solid #E9E7E2;border-radius:12px;display:grid;place-items:center;color:#1B1B1A;text-decoration:none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </a>
    </header>

    <main style="flex:1; padding:30px 16px 40px; display:flex; flex-direction:column; gap:14px;">

      <div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding:8px 0 6px;">
        <div style="width:84px;height:84px;border-radius:50%;background:var(--brand);display:grid;place-items:center;margin-bottom:22px;box-shadow:0 14px 30px color-mix(in oklab, var(--brand) 34%, transparent), 0 0 0 9px color-mix(in oklab, var(--brand) 11%, #F7F6F4);">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
        </div>
        <div style="font-weight:700;font-size:1.7rem;">{{ __("You're all set!") }}</div>
        <div style="color:#7A7973;font-size:.97rem;margin-top:9px;line-height:1.75;max-width:330px;">{{ __('Your order is created. You will be notified for further information.') }}</div>
        <div style="display:flex;align-items:center;gap:8px;margin-top:16px;background:#fff;border:1px solid #E9E7E2;border-radius:999px;padding:8px 16px;">
          <span style="font-size:.86rem;color:#8A8983;">{{ __('Order') }}</span>
          <span style="font-weight:800;color:var(--brand);direction:ltr;font-size:1rem;">#{{ $order->id_formated }}</span>
        </div>
      </div>

      <div style="background:color-mix(in oklab, var(--brand) 8%, #fff);border:1px solid color-mix(in oklab, var(--brand) 18%, #fff);border-radius:16px;padding:14px 16px;display:flex;align-items:center;gap:13px;">
        <div style="width:42px;height:42px;flex:none;border-radius:11px;background:#fff;color:var(--brand);display:grid;place-items:center;box-shadow:0 2px 8px rgba(20,20,18,.06);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </div>
        <div style="flex:1;">
          <div style="font-size:.82rem;color:#8A8983;">{{ __('Order status') }}</div>
          <div style="font-weight:700;font-size:1.05rem;">{{ __($lastStatus) }}</div>
        </div>
      </div>

      <section style="background:#fff;border:1px solid #E9E7E2;border-radius:18px;padding:16px;box-shadow:0 1px 2px rgba(20,20,18,.04);">
        <div style="font-size:1.02rem;font-weight:700;margin-bottom:14px;">{{ __('Order') }}</div>

        @if($order->table)
          <div style="display:flex;justify-content:space-between;font-size:.93rem;margin-bottom:12px;"><span style="color:#8A8983;">{{ __('Table:') }}</span><span style="font-weight:600;">{{ $order->table->name }}</span></div>
        @endif
        @if($order->address)
          <div style="display:flex;justify-content:space-between;gap:10px;font-size:.93rem;margin-bottom:12px;"><span style="color:#8A8983;flex:none;">{{ __('Address') }}</span><span style="font-weight:600;text-align:left;">{{ $order->address->address }}</span></div>
        @endif

        <div style="border-top:1px dashed #E4E2DD;padding-top:13px;display:flex;flex-direction:column;gap:13px;">
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

        <div style="border-top:1px solid #EFEDE8;margin-top:15px;padding-top:14px;display:flex;flex-direction:column;gap:9px;">
          <div style="display:flex;justify-content:space-between;font-size:.9rem;"><span style="color:#8A8983;">{{ __('Sub Total') }}</span><span style="font-weight:600;">@money($order->order_price, $currency, $convert)</span></div>
          @if($order->delivery_method==1)
            <div style="display:flex;justify-content:space-between;font-size:.9rem;"><span style="color:#8A8983;">{{ __('Delivery') }}</span><span style="font-weight:600;">@money($order->delivery_price, $currency, $convert)</span></div>
          @endif
          @if($order->discount>0)
            <div style="display:flex;justify-content:space-between;font-size:.9rem;"><span style="color:#8A8983;">{{ __('Discount') }}</span><span style="font-weight:600;">@money($order->discount, $currency, $convert)</span></div>
          @endif
          <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:3px;"><span style="font-weight:700;font-size:1.05rem;">{{ __('TOTAL') }}</span><span style="font-weight:800;font-size:1.28rem;color:var(--brand);">@money($order->delivery_price + $order->order_price_with_discount, $currency, true)</span></div>
          <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-top:4px;"><span style="color:#8A8983;">{{ __('Payment method') }}</span><span style="font-weight:600;">{{ __(strtoupper($order->payment_method)) }}</span></div>
        </div>
      </section>

      {{-- iMenu 2026 — زر "وصلت" لطلبات السيارة --}}
      @include('orders.partials.readytime')
      @include('orders.partials.arrived')

      @if($showWhatsApp)
        <a href="?order={{ request('order') }}&whatsapp=yes" target="_blank" style="display:flex;align-items:center;justify-content:center;gap:9px;height:52px;border-radius:15px;background:#25D366;color:#fff;font-weight:700;font-size:1rem;text-decoration:none;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24z"/></svg>
          {{ __('Send order on WhatsApp') }}
        </a>
      @endif

      <div style="display:flex;flex-direction:column;gap:11px;margin-top:4px;">
        <a href="{{ $restoLink }}" style="display:flex;align-items:center;justify-content:center;gap:9px;height:54px;border-radius:15px;background:var(--brand);color:#fff;font-weight:700;font-size:1.05rem;text-decoration:none;box-shadow:0 10px 24px color-mix(in oklab, var(--brand) 34%, transparent);">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8M5 10v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9"/></svg>
          {{ __('Go back to restaurant') }}
        </a>
        @if(config('app.isqrsaas'))
        <a href="{{ route('guest.orders') }}" style="display:flex;align-items:center;justify-content:center;gap:9px;height:52px;border-radius:15px;background:#fff;border:1.5px solid #E4E2DD;color:#1B1B1A;font-weight:700;font-size:1rem;text-decoration:none;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l4 4v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg>
          {{ __('My Orders') }}
        </a>
        @endif
      </div>

    </main>
  </div>
</div>

@isset($whatsappurl)
<script>var w=window.open('{{ $whatsappurl }}','_blank'); if(w){w.location;}</script>
@endisset
@endsection
