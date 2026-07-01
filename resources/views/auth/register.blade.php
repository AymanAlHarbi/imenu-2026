{{-- =========================================================
  iMenu 2.0 — إنشاء حساب عميل (ثيم "اي منيو")
  بديل لـ resources/views/auth/register.blade.php
========================================================= --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ckb','ps','sd']) ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ __('Create account') }} — {{ config('app.name', 'iMenu') }}</title>
  @if (strlen(config('settings.recaptcha_site_key')) > 2)
    {!! htmlScriptTagJsApi([]) !!}
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
      <h2>أنشئ حسابك كعميل</h2>
      <p>سجّل لتتبّع طلباتك، تحفظ عناوينك المفضّلة، وتطلب أسرع في المرة القادمة.</p>
      <ul class="sc-list">
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> تتبّع طلباتك الحالية والسابقة</li>
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> طلب أسرع بحفظ بياناتك</li>
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> عروض ومكافآت حصرية</li>
      </ul>
    </div>
    <div class="sc-foot"><span class="stars">★★★★★</span><span>تجربة طلب أسهل وأسرع</span></div>
  </aside>

  <main class="formside">
    <div class="top-row">
      <a class="back" href="{{ route('login') }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg> {{ __('Back to login') }}</a>
      <span class="have-acc">لديك حساب؟ <a href="{{ route('login') }}">{{ __('Sign in') }}</a></span>
    </div>

    <div class="form-card">
      <span class="eyebrow"><span class="dot"></span> {{ __('Create account') }}</span>
      <h1>أنشئ حسابك</h1>
      <p class="sub">دقيقة واحدة وتبدأ الطلب بسهولة.</p>

      <form id="{{ getFormId() }}" role="form" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field {{ $errors->has('name') ? 'has-danger' : '' }}">
          <label for="name">{{ __('Name') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 016-6h4a6 6 0 016 6v1"/></svg></span>
            <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" required autofocus>
          </div>
          @if ($errors->has('name'))<span class="err">{{ $errors->first('name') }}</span>@endif
        </div>

        <div class="field {{ $errors->has('email') ? 'has-danger' : '' }}">
          <label for="email">{{ __('Email') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
          </div>
          @if ($errors->has('email'))<span class="err">{{ $errors->first('email') }}</span>@endif
        </div>

        <div class="field {{ $errors->has('phone') ? 'has-danger' : '' }}">
          <label for="phone">{{ __('Phone') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M5 4h4l2 5-3 2a12 12 0 005 5l2-3 5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg></span>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="05XXXXXXXX" required>
          </div>
          @if ($errors->has('phone'))<span class="err">{{ $errors->first('phone') }}</span>@endif
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

        <div class="field">
          <label for="password_confirmation">{{ __('Confirm Password') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg></span>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required>
          </div>
        </div>

        @if(config('settings.enable_birth_date_on_register'))
          <div class="field {{ $errors->has('birth_date') ? 'has-danger' : '' }}">
            <label for="birth_date">{{ __('Date of Birth') }}</label>
            <div class="control">
              <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg></span>
              <input id="birth_date" type="date" name="birth_date" required>
            </div>
            @if ($errors->has('birth_date'))<span class="err">{{ $errors->first('birth_date') }}</span>@endif
          </div>
        @endif

        <label class="check terms-check">
          <input type="checkbox" name="termsCheckBox" id="termsCheckBox">
          <span class="box"><svg viewBox="0 0 24 24" fill="none" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></span>
          <span>{{ __('i_agree_to') }}
            <a href="{{ config('settings.link_to_ts') }}" target="_blank">{{ __('terms_of_service') }}</a> {{ __('and') }}
            <a href="{{ config('settings.link_to_pr') }}" target="_blank">{{ __('privacy_policy') }}</a>.
          </span>
        </label>

        @if (strlen(config('settings.recaptcha_site_key')) > 2)
          @if ($errors->has('g-recaptcha-response'))
            <span class="err">{{ $errors->first('g-recaptcha-response') }}</span>
          @endif
          {!! htmlFormButton(__('Create account'), ['id' => 'thesubmitbtn', 'class' => 'btn-submit', 'disabled' => 'true']) !!}
        @else
          <button disabled id="thesubmitbtn" type="submit" class="btn-submit">{{ __('Create account') }}
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        @endif
      </form>

      <p class="alt-line">لديك حساب بالفعل؟ <a href="{{ route('login') }}">{{ __('Sign in') }}</a></p>
    </div>
  </main>
</div>

<script>
  function imTogglePass(id){var el=document.getElementById(id);el.type=el.type==='password'?'text':'password';}
  // تفعيل زر الإنشاء عند الموافقة على الشروط (نفس سلوك السكربت الأصلي)
  (function(){
    var cb=document.getElementById('termsCheckBox');
    var btn=document.getElementById('thesubmitbtn');
    if(cb&&btn){cb.addEventListener('change',function(){btn.disabled=!cb.checked;});}
  })();
</script>
</body>
</html>
