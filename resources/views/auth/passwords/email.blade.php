{{-- =========================================================
  iMenu 2.0 — نسيت كلمة المرور (ثيم "اي منيو")
  بديل لـ resources/views/auth/passwords/email.blade.php
========================================================= --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ckb','ps','sd']) ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ __('Reset password') }} — {{ config('app.name', 'iMenu') }}</title>
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
      <h2>استعادة الوصول لحسابك</h2>
      <p>لا تقلق، يحدث للجميع. أدخل بريدك المسجّل وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.</p>
      <ul class="sc-list">
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> رابط آمن يصل إلى بريدك</li>
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> تستعيد دخولك خلال دقيقة</li>
      </ul>
    </div>
    <div class="sc-foot"><span class="stars">★★★★★</span><span>دعم سريع متى احتجته</span></div>
  </aside>

  <main class="formside">
    <div class="top-row">
      <a class="back" href="{{ route('login') }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg> {{ __('Back to login') }}</a>
    </div>

    <div class="form-card">
      <span class="eyebrow"><span class="dot"></span> {{ __('Reset password') }}</span>
      <h1>{{ __('Forgot password?') }}</h1>
      <p class="sub">أدخل بريدك الإلكتروني المسجّل وسنرسل لك رابط إعادة التعيين.</p>

      @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
      @endif

      <form role="form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="field {{ $errors->has('email') ? 'has-danger' : '' }}">
          <label for="email">{{ __('Email') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
          </div>
          @if ($errors->has('email'))<span class="err">{{ $errors->first('email') }}</span>@endif
        </div>

        <button type="submit" class="btn-submit">{{ __('Send Password Reset Link') }}
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
        </button>
      </form>

      <p class="alt-line">تذكّرت كلمة المرور؟ <a href="{{ route('login') }}">{{ __('Back to login') }}</a></p>
    </div>
  </main>
</div>
</body>
</html>
