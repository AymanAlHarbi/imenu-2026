{{-- =========================================================
  iMenu 2.0 — صفحة تسجيل المطعم (ثيم "اي منيو")
  بديل لـ resources/views/restorants/register.blade.php
  - مستقلة بالكامل (CSS داخلي)، لا تعتمد على Argon/Bootstrap
  - مربوطة: route('newrestaurant.store')، CSRF، الأخطاء، التعبئة من الرابط، reCAPTCHA
========================================================= --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ckb','ps','sd']) ? 'rtl' : 'ltr' }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ __('Register your restaurant') }} — {{ config('app.name', 'iMenu') }}</title>

  @if (config('settings.google_analytics'))
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo config('settings.google_analytics'); ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?php echo config('settings.google_analytics'); ?>');
    </script>
  @endif

  @if (strlen(config('settings.recaptcha_site_key')) > 2)
    {!! htmlScriptTagJsApi([]) !!}
  @endif

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#FAF7F1; --surface:#FFFFFF; --cream-deep:#F1EADD;
      --ink:#13202E; --ink-soft:#4E5A68; --line:#E8E1D4;
      --green-900:#0E2C4A; --green-700:#155083; --green-500:#2C82C4;
      --saffron:#F2A33C; --saffron-deep:#E27D26; --coral:#E1604A;
      --display:"El Messiri", serif;
      --body:"IBM Plex Sans Arabic", system-ui, sans-serif;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:var(--body);background:var(--bg);color:var(--ink);line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh}
    a{color:inherit;text-decoration:none}
    h1,h2,h3{font-family:var(--display);line-height:1.25}
    img{max-width:100%;display:block}

    .page{display:grid;grid-template-columns:1fr 1.05fr;min-height:100vh}

    .showcase{position:relative;background:linear-gradient(160deg,var(--green-700),var(--green-900));color:#fff;padding:clamp(34px,4vw,64px);display:flex;flex-direction:column;justify-content:space-between;overflow:hidden}
    .showcase::before{content:"";position:absolute;width:420px;height:420px;border-radius:50%;background:rgba(242,163,60,.16);top:-140px;inset-inline-end:-120px;filter:blur(8px)}
    .showcase::after{content:"";position:absolute;width:300px;height:300px;border-radius:50%;background:rgba(44,130,196,.22);bottom:-120px;inset-inline-start:-90px;filter:blur(8px)}
    .sc-brand{display:flex;align-items:center;gap:11px;font-family:var(--display);font-weight:700;font-size:1.4rem;position:relative;z-index:2;color:#fff}
    .sc-brand .mark{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:var(--saffron);color:var(--green-900);font-size:1.2rem;font-weight:700;overflow:hidden}
    .sc-brand .mark img{width:100%;height:100%;object-fit:contain}
    .sc-mid{position:relative;z-index:2;margin-block:auto;padding-block:40px}
    .sc-mid h2{font-size:clamp(1.7rem,2.6vw,2.4rem);font-weight:700;margin-bottom:16px;max-width:13em}
    .sc-mid p{color:rgba(255,255,255,.82);font-size:1.08rem;max-width:26em;margin-bottom:30px}
    .sc-list{list-style:none;display:flex;flex-direction:column;gap:16px}
    .sc-list li{display:flex;gap:13px;align-items:flex-start;font-size:1.02rem}
    .sc-list .ck{width:28px;height:28px;border-radius:9px;background:rgba(242,163,60,.18);display:grid;place-items:center;flex-shrink:0;margin-top:2px}
    .sc-list .ck svg{width:16px;height:16px;stroke:var(--saffron)}
    .sc-foot{position:relative;z-index:2;display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.7);font-size:.92rem}
    .sc-foot .stars{color:var(--saffron);letter-spacing:2px}

    .formside{display:flex;flex-direction:column;padding:clamp(28px,3vw,48px) clamp(24px,4vw,72px)}
    .top-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:auto;gap:12px;flex-wrap:wrap}
    .back{display:inline-flex;align-items:center;gap:8px;color:var(--ink-soft);font-weight:500;font-size:.95rem;border:1px solid var(--line);padding:9px 16px;border-radius:999px;transition:.15s}
    .back:hover{border-color:var(--green-700);color:var(--green-700)}
    .have-acc{font-size:.95rem;color:var(--ink-soft)}
    .have-acc a{color:var(--green-700);font-weight:600}

    .form-card{width:100%;max-width:480px;margin-inline:auto;margin-block:auto;padding-block:40px}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--cream-deep);color:var(--green-700);font-weight:600;font-size:.84rem;padding:6px 14px;border-radius:999px;margin-bottom:18px}
    .eyebrow .dot{width:7px;height:7px;border-radius:50%;background:var(--saffron-deep)}
    .form-card h1{font-size:clamp(1.8rem,3vw,2.3rem);color:var(--green-900);margin-bottom:8px}
    .form-card .sub{color:var(--ink-soft);font-size:1.02rem;margin-bottom:30px}

    .alert{background:#E7F6EC;border:1px solid #BFE6CC;color:#1F8A5B;padding:13px 16px;border-radius:12px;font-size:.95rem;margin-bottom:22px}

    .group-label{font-size:.82rem;font-weight:700;color:var(--saffron-deep);letter-spacing:.4px;margin:26px 0 14px;display:flex;align-items:center;gap:10px}
    .group-label::after{content:"";flex:1;height:1px;background:var(--line)}
    .group-label:first-of-type{margin-top:0}

    .field{margin-bottom:18px}
    .field label{display:block;font-size:.92rem;font-weight:600;color:var(--ink);margin-bottom:7px}
    .field .control{position:relative;display:flex;align-items:center}
    .field .control .ic{position:absolute;inset-inline-start:15px;display:grid;place-items:center;pointer-events:none}
    .field .control .ic svg{width:19px;height:19px;stroke:var(--ink-soft)}
    .field input{width:100%;font-family:var(--body);font-size:1rem;color:var(--ink);padding:14px 46px 14px 16px;border:1.5px solid var(--line);border-radius:14px;background:#fff;transition:border-color .15s, box-shadow .15s}
    .field input::placeholder{color:#A9B0B8}
    .field input:focus{outline:none;border-color:var(--green-500);box-shadow:0 0 0 4px rgba(44,130,196,.12)}
    .field.has-danger input{border-color:var(--coral)}
    .field .err{color:var(--coral);font-size:.85rem;margin-top:6px;display:block}

    .recaptcha-row{margin:8px 0 4px;display:flex;justify-content:center}
    .btn-submit{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:10px;font-family:var(--body);font-weight:600;font-size:1.06rem;padding:16px 26px;border-radius:14px;cursor:pointer;border:none;background:linear-gradient(180deg,#F4AE4A,var(--saffron-deep));color:#2A170A;box-shadow:0 8px 22px rgba(226,125,38,.32);transition:transform .18s ease, box-shadow .18s ease;margin-top:10px}
    .btn-submit:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(226,125,38,.42)}
    .terms{text-align:center;color:var(--ink-soft);font-size:.86rem;margin-top:18px}
    .terms a{color:var(--green-700);font-weight:600}
    .form-foot-acc{display:none;text-align:center;margin-top:24px;color:var(--ink-soft);font-size:.95rem}
    .form-foot-acc a{color:var(--green-700);font-weight:600}

    @media(max-width:900px){
      .page{grid-template-columns:1fr}
      .showcase{display:none}
      .have-acc{display:none}
      .form-foot-acc{display:block}
    }
    @media(max-width:520px){ .formside{padding:22px 20px} }
    :focus-visible{outline:3px solid var(--saffron-deep);outline-offset:2px;border-radius:6px}
    .url-control{display:flex;align-items:stretch;border:1.5px solid var(--line);border-radius:14px;overflow:hidden;background:#fff;transition:border-color .15s, box-shadow .15s}
    .url-control:focus-within{border-color:var(--green-500);box-shadow:0 0 0 4px rgba(44,130,196,.12)}
    .url-control .url-prefix{display:flex;align-items:center;padding:0 14px;background:var(--cream-deep);color:var(--ink-soft);font-size:.9rem;white-space:nowrap;border-inline-end:1.5px solid var(--line)}
    .url-control input{border:none !important;box-shadow:none !important;border-radius:0;padding:14px !important;text-align:left}
    .field.has-danger .url-control{border-color:var(--coral)}
    .url-hint{display:block;color:var(--ink-soft);font-size:.85rem;margin-top:7px}
    .url-hint b{color:var(--green-900);font-weight:600}
  </style>
</head>

<body>
<div class="page">

  {{-- SHOWCASE --}}
  <aside class="showcase">
    <a class="sc-brand" href="/">
      <span class="mark">
        @if(config('global.site_logo'))
          <img src="{{ config('global.site_logo') }}" alt="{{ config('app.name') }}">
        @else
          {{ mb_substr(config('app.name','اي منيو'), 0, 1) }}
        @endif
      </span>
      {{ config('app.name', 'اي منيو') }}
    </a>

    <div class="sc-mid">
      <h2>قائمتك الرقمية تبدأ خلال دقائق</h2>
      <p>أنشئ حساب مطعمك مجاناً، صمّم رمز QR، واستقبل الطلبات مباشرة من هواتف عملائك.</p>
      <ul class="sc-list">
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> ابدأ مجاناً — بدون بطاقة ائتمانية</li>
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> قائمة ورمز QR جاهزان فوراً</li>
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> استقبل الطلبات والمدفوعات</li>
        <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></span> تحديث القائمة بنقرة واحدة</li>
      </ul>
    </div>

    <div class="sc-foot">
      <span class="stars">★★★★★</span>
      <span>يثق بنا أصحاب المطاعم والمقاهي</span>
    </div>
  </aside>

  {{-- FORM --}}
  <main class="formside">
    <div class="top-row">
      <a class="back" href="/">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        العودة للرئيسية
      </a>
      <span class="have-acc">لديك حساب؟ <a href="/login">تسجيل الدخول</a></span>
    </div>

    <div class="form-card">
      <span class="eyebrow"><span class="dot"></span> تسجيل مطعم جديد</span>
      <h1>{{ __('Register your restaurant') }}</h1>
      <p class="sub">أدخل بياناتك الأساسية وسننشئ قائمتك الرقمية فوراً.</p>

      @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
      @endif

      <form id="{{ getFormId() }}" method="post" action="{{ route('newrestaurant.store') }}" autocomplete="off">
        @csrf

        <div class="group-label">{{ __('Restaurant information') }}</div>
        <div class="field {{ $errors->has('name') ? 'has-danger' : '' }}">
          <label for="name">{{ __('Restaurant Name') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M3 9l1-5h16l1 5M5 9v11h14V9M9 13h6"/></svg></span>
            <input type="text" id="name" name="name" placeholder="{{ __('Restaurant Name here') }} ..." value="{{ isset($_GET['name']) ? $_GET['name'] : '' }}" required autofocus>
          </div>
          @if ($errors->has('name'))
            <span class="err">{{ $errors->first('name') }}</span>
          @endif
        </div>
        <div class="field {{ $errors->has('subdomain') ? 'has-danger' : '' }}">
          <label for="subdomain">رابط المنيو الإلكتروني</label>
          <div class="control url-control" dir="ltr">
            <span class="url-prefix">i-menu.me/m/</span>
            <input type="text" id="subdomain" name="subdomain" dir="ltr"
                   placeholder="albik"
                   value="{{ old('subdomain') }}"
                   required
                   oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]+/g,'-'); document.getElementById('slugEcho').textContent=this.value||'extra';">
          </div>
          <span class="url-hint">رابط منيو مطعمك سيكون:
            <b dir="ltr">https://i-menu.me/m/<span id="slugEcho">{{ old('subdomain', 'albik') }}</span></b>
          </span>
          @if ($errors->has('subdomain'))
            <span class="err">{{ $errors->first('subdomain') }}</span>
          @endif
        </div>
        <div class="group-label">{{ __('Owner information') }}</div>
        <div class="field {{ $errors->has('name_owner') ? 'has-danger' : '' }}">
          <label for="name_owner">{{ __('Owner Name') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 016-6h4a6 6 0 016 6v1"/></svg></span>
            <input type="text" id="name_owner" name="name_owner" placeholder="{{ __('Owner Name here') }} ..." value="{{ isset($_GET['name']) ? $_GET['name'] : '' }}" required>
          </div>
          @if ($errors->has('name_owner'))
            <span class="err">{{ $errors->first('name_owner') }}</span>
          @endif
        </div>

        <div class="field {{ $errors->has('email_owner') ? 'has-danger' : '' }}">
          <label for="email_owner">{{ __('Owner Email') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
            <input type="email" id="email_owner" name="email_owner" placeholder="{{ __('Owner Email here') }} ..." value="{{ isset($_GET['email']) ? $_GET['email'] : '' }}" required>
          </div>
          @if ($errors->has('email_owner'))
            <span class="err">{{ $errors->first('email_owner') }}</span>
          @endif
        </div>

        <div class="field {{ $errors->has('phone_owner') ? 'has-danger' : '' }}">
          <label for="phone_owner">{{ __('Owner Phone') }}</label>
          <div class="control">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M5 4h4l2 5-3 2a12 12 0 005 5l2-3 5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg></span>
            <input type="text" id="phone_owner" name="phone_owner" placeholder="{{ __('Owner Phone here') }} ..." value="{{ isset($_GET['phone']) ? $_GET['phone'] : '' }}" required>
          </div>
          @if ($errors->has('phone_owner'))
            <span class="err">{{ $errors->first('phone_owner') }}</span>
          @endif
        </div>

        @if (strlen(config('settings.recaptcha_site_key')) > 2)
          {{-- reCAPTCHA مُفعّل: يولّد الحزمة زر الإرسال (نفس سلوك السكربت الأصلي) --}}
          @if ($errors->has('g-recaptcha-response'))
            <span class="err">{{ $errors->first('g-recaptcha-response') }}</span>
          @endif
          {!! htmlFormButton(__('Save'), ['id' => 'thesubmitbtn', 'class' => 'btn-submit']) !!}
        @else
          <button type="submit" id="thesubmitbtn" class="btn-submit">
            {{ __('Save') }}
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        @endif

        <p class="terms">بإنشائك الحساب فأنت توافق على
          <a href="/blog/Terms-and-conditions" target="_blank">الشروط والأحكام</a> و<a href="/blog/Privacy-Policy" target="_blank">سياسة الخصوصية</a>.
        </p>
      </form>

      <p class="form-foot-acc">لديك حساب؟ <a href="/login">تسجيل الدخول</a></p>
    </div>
  </main>

</div>

@if (isset($_GET['name']) && $errors->isEmpty())
<script>
  "use strict";
  document.getElementById("thesubmitbtn").click();
</script>
@endif
</body>
</html>
