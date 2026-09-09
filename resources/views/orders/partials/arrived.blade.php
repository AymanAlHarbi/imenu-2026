{{-- iMenu 2026 — زر "وصلت" لطلبات الاستلام من السيارة فقط --}}
@if ($order->getConfig('pickup_method', '') == 'car')
    @php
        $arrivedAt = $order->getConfig('arrived_at', '');
        $vehicleParts = array_filter([
            trim($order->getConfig('vehicle_brand', '') . ' ' . $order->getConfig('vehicle_model', '')),
            $order->getConfig('vehicle_color', ''),
            $order->getConfig('vehicle_plate', ''),
        ]);
    @endphp

    @if ($arrivedAt)
        <div style="background:#E6F7EE;border:1.5px solid #9FE1CB;border-radius:15px;padding:15px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;flex:none;border-radius:50%;background:#1D9E75;display:grid;place-items:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;color:#0F6E56;">{{ __('The coffee shop knows you have arrived') }}</div>
                <div style="font-size:.85rem;color:#0F6E56;margin-top:3px;">{{ __('Your order will be brought out shortly') }}</div>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('order.arrived') }}" style="margin:0;">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="md" value="{{ $order->md }}">
            <button type="submit" style="width:100%;display:flex;align-items:center;justify-content:center;gap:9px;height:54px;border-radius:15px;background:var(--brand, #FA8128);color:#fff;font-weight:700;font-size:1.05rem;border:none;cursor:pointer;font-family:inherit;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14M5 17a2 2 0 1 0 4 0M15 17a2 2 0 1 0 4 0M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2A2 2 0 0 1 19.5 8L21 13v4H3v-4z"/></svg>
                {{ __('I have arrived') }}
            </button>
        </form>
        @if (count($vehicleParts) > 0)
            <div style="margin-top:9px;text-align:center;font-size:.82rem;color:#8A8983;">
                {{ implode(' · ', $vehicleParts) }}
            </div>
        @endif
    @endif
@endif
