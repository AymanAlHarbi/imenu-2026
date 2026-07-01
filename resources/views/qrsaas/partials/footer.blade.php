<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <a class="brand" href="/">
          <span class="mark">
            @if(config('global.site_logo'))
              <img src="{{ config('global.site_logo') }}" alt="{{ config('app.name') }}">
            @else
              {{ mb_substr(config('app.name','اي منيو'), 0, 1) }}
            @endif
          </span>
          {{ config('app.name', 'اي منيو') }}
        </a>
        <p>أنشئ قائمة رقمية لمطعمك أو مقهاك، وتواصل أكثر مع عملائك. هاتف عميلك هو قائمتك الآن.</p>
        <div class="pay">
          <span>طرق دفع آمنة:</span>
          <span class="chip">VISA</span>
          <span class="chip">Mastercard</span>
          <span class="chip">mada</span>
          <span class="chip">Stripe</span>
        </div>
      </div>

      <div class="foot-col">
        <h4>المنتج</h4>
        <a href="#product">المميزات</a>
        <a href="#pricing">الأسعار</a>
        <a href="#demo">تجربة حية</a>
        <a href="#how">كيف يعمل</a>
      </div>

      <div class="foot-col">
        <h4>{{ __('qrlanding.my_account') }}</h4>
        <a href="/login">@auth {{ __('qrlanding.dashboard') }} @endauth @guest تسجيل الدخول @endguest</a>
        @guest
          <a href="{{ route('newrestaurant.register') }}">إنشاء حساب</a>
        @endguest
      </div>

      <div class="foot-col">
        <h4>{{ __('qrlanding.helpful_links') }}</h4>
        @if(isset($pages))
          @foreach ($pages as $page)
            <a target="_blank" href="/blog/{{ $page->slug }}">{{ $page->title }}</a>
          @endforeach
        @endif
      </div>
    </div>

    <div class="foot-bottom">
      <span>© {{ config('app.name', 'اي منيو') }} {{ date('Y') }}. {{ __('All rights reserved') }}.</span>
      <span>صُنع بشغف لمطاعم الوطن العربي 🍽️</span>
    </div>
  </div>
</footer>
