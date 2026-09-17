<header class="nav" id="nav">
  <div class="wrap nav-inner">
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

    <nav class="nav-links">
      <a href="#how">كيف يعمل</a>
      <a href="#product">المميزات</a>
      <a href="#pricing">الأسعار</a>
      {{-- iMenu 2026 — يتبع علم الشهادات: بلا القسم يصير الرابط ميتًا --}}
      @if(config('settings.fake_testimonials'))
        <a href="#testimonials">آراء العملاء</a>
      @endif
      <a href="#demo">تجربة حية</a>
    </nav>

    <div class="nav-actions">
      @if(isset($availableLanguages) && count($availableLanguages) > 1)
        <div class="lang-wrap">
          <button class="lang" id="langBtn" type="button">
            @foreach ($availableLanguages as $short => $lang)
              @if(strtolower($short) == strtolower($locale ?? app()->getLocale())){{ strtoupper($short) }}@endif
            @endforeach
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="lang-menu" id="langMenu">
            @foreach ($availableLanguages as $short => $lang)
              <a href="/{{ strtolower($short) }}">{{ __($lang) }}</a>
            @endforeach
          </div>
        </div>
      @endif

      <a class="nav-login" href="/login">
        @auth {{ __('qrlanding.dashboard') }} @endauth
        @guest تسجيل الدخول @endguest
      </a>

      @guest
        <a class="btn btn-primary" href="{{ route('newrestaurant.register') }}">ابدأ مجاناً</a>
      @endguest

      <button class="menu-toggle" aria-label="القائمة" onclick="document.querySelector('.nav-links').style.display='flex'">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#13202E" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>
