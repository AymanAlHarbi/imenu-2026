{{-- iMenu 2026 — وعد الجاهزية للعميل: ساعة لا مدة، و«جاهز ٤:٣٥» أوضح من «بعد ١٥ دقيقة» --}}
@php
    $imenuPromise = $order->getConfig('ready_promise_at', false);
    $imenuReported = \App\Services\Trust::isReported($order);
    $imenuDisputed = \App\Services\Trust::isDisputed($order);
@endphp

@if ($imenuPromise && ! $imenuReported)
    <div class="card shadow-sm mb-3" style="border-left:4px solid #FF721C">
        <div class="card-body py-3 text-center">
            <span class="text-muted text-sm d-block">{{ __('Your order will be ready at') }}</span>
            <span class="h1 mb-0" style="font-variant-numeric: tabular-nums; color:#171513">
                {{ \Carbon\Carbon::parse($imenuPromise)->format('H:i') }}
            </span>
        </div>
    </div>
@endif

@if ($imenuReported)
    <div class="card shadow-sm mb-3" style="border-left:4px solid #C0392B">
        <div class="card-body py-3">
            <h5 class="mb-1">{{ __('Order not collected') }}</h5>
            <p class="text-muted text-sm mb-3">
                {{ __('The coffee shop marked your order as not collected. If this is a mistake, you can object from your orders page.') }}
            </p>

            @if ($imenuDisputed)
                <span class="badge badge-pill badge-secondary">{{ __('Your objection has been recorded') }}</span>
            @else
                <form method="POST" action="{{ route('order.dispute') }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <input type="hidden" name="md" value="{{ $order->md }}">
                    <button type="submit" class="btn btn-outline-danger" style="min-height:44px">
                        {{ __('I did collect it — object') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
@endif
