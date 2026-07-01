{{-- =========================================================
  iMenu 2.0 — تسجيل الدخول (ثيم "اي منيو")
  بديل لـ resources/views/auth/login.blade.php
========================================================= --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ckb','ps','sd']) ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ __('Sign in') }} — {{ config('app.name', 'iMenu') }}</title>
  @if (config('settings.google_analytics'))
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo config('settings.google_analytics'); ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo config('settings.google_analytics'); ?>');</script>
  @endif
  @include('auth.partials.imenu_styles')
</head>
<body>
<div class="page">

  <aside class="showcase">
    <a class="sc-brand" href="/">
      <span class="mark">@if(config('global.site_logo'))<img src="{{ config('global.site_logo') }}" alt="{{ config('app.name') }}">@else{{ mb_substr(config('app.name','اي منيو'),0,1) }}@endif</span>
      {{ config('app.name', 'اي منيو') }}
    </a>
    <div class="sc-mid">
      <h2>أهلاً بعودتك 👋</h2>
      <p>سجّل دخولك لإدارة قائمتك الرقمية، متابعة الطلبات، وتحديث الأصناف لحظياً.</p>
      <ul class="sc-list">
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> لوحة تحكم واحدة لكل شيء</li>
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> إشعارات فورية بالطلبات الجديدة</li>
      </ul>
    </div>
    <div class="sc-foot"><span class="stars">★★★★★</span><span>يثق بنا أصحاب المطاعم والمقاهي</span></div>
  </aside>

  <main class="formside">
    <div class="top-row">
      <a class="back" href="/"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg> العودة للرئيسية</a>
      @if(config('app.isft') || isset($_GET['showCreate']))
        <span class="have-acc">جديد هنا؟ <a href="{{ route('register') }}">أنشئ حساباً</a></span>
      @endif
    </div>

    <div class="form-card">
      <span class="eyebrow"><span class="dot"></span> تسجيل الدخول</span>
      <h1>ادخل إلى حسابك</h1>
      <p class="sub">أدخل بريدك وكلمة المرور للمتابعة.</p>

      @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
      @endif

      {{-- بيانات تجريبية (تظهر فقط في وضع العرض التوضيحي) --}}
      @if(config('settings.is_show_credentials', false))
        <script>
          function loginAs(email){document.getElementById("email").value=email;document.getElementById("password").value="secret";document.getElementById("loginForm").submit();}
        </script>
        <div class="demo-box">
          <div class="dh">دخول سريع للتجربة</div>
          <div class="demo-grid">
            <button type="button" onclick="loginAs('admin@example.com')">Admin</button>
            <button type="button" onclick="loginAs('owner@example.com')">Owner</button>
            @if (config('app.isft'))
              <button type="button" onclick="loginAs('driver@example.com')">Driver</button>
              <button type="button" onclick="loginAs('client@example.com')">Client</button>
            @endif
            @if (config('app.issd'))
              <button type="button" onclick="loginAs('driver@example.com')">Driver</button>
            @endif
            @if (config('settings.is_pos_cloud_mode'))
              <button type="button" onclick="loginAs('staff@example.com')">Staff</button>
            @endif
          </div>
        </div>
      @endif

      {{-- الدخول الاجتماعي --}}
      @if((config('app.isft') || isset($_GET['showCreate'])) && (strlen(config('settings.google_client_id'))>3 || strlen(config('settings.facebook_client_id'))>3))
        <div class="social-row">
          @if (strlen(config('settings.google_client_id'))>3)
            <a href="{{ route('google.login') }}" class="social-btn">
              <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.5 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.9a5 5 0 01-2.2 3.3v2.7h3.6c2.1-1.9 3.2-4.7 3.2-7.8z"/><path fill="#34A853" d="M12 23c2.9 0 5.4-1 7.2-2.6l-3.6-2.7c-1 .7-2.3 1.1-3.6 1.1-2.8 0-5.1-1.9-6-4.4H2.3v2.8A11 11 0 0012 23z"/><path fill="#FBBC05" d="M6 14.4a6.6 6.6 0 010-4.2V7.4H2.3a11 11 0 000 9.8z"/><path fill="#EA4335" d="M12 5.5c1.6 0 3 .5 4.1 1.6l3-3A11 11 0 002.3 7.4L6 10.2C6.9 7.7 9.2 5.5 12 5.5z"/></svg>
              Google
            </a>
          @endif
          @if (strlen(config('settings.facebook_client_id'))>3)
            <a href="{{ route('facebook.login') }}" class="social-btn">
              <svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12a12 12 0 10-13.9 11.9v-8.4H7v-3.5h3.1V9.4c0-3 1.8-4.7 4.6-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v2.2h3.4l-.5 3.5h-2.9v8.4A12 12 0 0024 12z"/></svg>
              Facebook
            </a>
          @endif
        </div>
        <div class="divider">{{ __('Email') }}</div>
      @endif

      <form id="loginForm" role="form" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field {{ $errors->has('email') ? 'has-danger' : '' }}">
          <label for="email">{{ __('Email') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
          </div>
          @if ($errors->has('email'))<span class="err">{{ $errors->first('email') }}</span>@endif
        </div>

        <div class="field {{ $errors->has('password') ? 'has-danger' : '' }}">
          <label for="password">{{ __('Password') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg></span>
            <input id="password" type="password" name="password" placeholder="••••••••" required>
            <button type="button" class="toggle-pass" onclick="imTogglePass('password')" aria-label="إظهار كلمة المرور">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          @if ($errors->has('password'))<span class="err">{{ $errors->first('password') }}</span>@endif
        </div>

        <div class="row-between">
          <label class="check">
            <input type="checkbox" name="remember" id="customCheckLogin" {{ old('remember') ? 'checked' : '' }}>
            <span class="box"><svg viewBox="0 0 24 24" fill="none" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></span>
            {{ __('Remember me') }}
          </label>
          @if (Route::has('password.request'))
            <a class="link-soft" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
          @endif
        </div>

        <button type="submit" class="btn-submit">{{ __('Sign in') }}
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </form>

      @if(config('app.isft') || isset($_GET['showCreate']))
        <p class="alt-line">{{ __('Create new account') }}؟ <a href="{{ route('register') }}">{{ __('Create new account') }}</a></p>
      @endif
    </div>
  </main>
</div>

<script>
  function imTogglePass(id){var el=document.getElementById(id);el.type=el.type==='password'?'text':'password';}
</script>
</body>
</html>
