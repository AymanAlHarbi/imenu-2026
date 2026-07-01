{{-- باقة واحدة — بيانات من $plan: name, description, price, period (1=شهري), features (مفصولة بفواصل) --}}
<div class="plan reveal {{ ($featured ?? false) ? 'featured' : '' }}">
  @if($featured ?? false)
    <div class="badge-pop">الأكثر اختياراً</div>
  @endif
  <div class="pname">{{ __($plan['name']) }}</div>
  <div class="pdesc">{{ __($plan['description']) }}</div>
  <div class="pprice">
    <span class="cur">{{ config('settings.cashier_currency') }}</span>
    <span class="amt">{{ $plan['price'] }}</span>
    <span class="per">/ {{ ($plan['period'] == 1) ? __('qrlanding.month') : __('qrlanding.year') }}</span>
  </div>
  <hr class="perf">
  <ul>
    @foreach (explode(',', $plan['features']) as $feature)
      <li><svg viewBox="0 0 24 24" fill="none" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg> {{ __(trim($feature)) }}</li>
    @endforeach
  </ul>
  <a class="btn {{ ($featured ?? false) ? 'btn-primary' : 'btn-ghost' }}" href="{{ route('newrestaurant.register') }}">
    {{ __('qrlanding.join_now') }}
  </a>
</div>
