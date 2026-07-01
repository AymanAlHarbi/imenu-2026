{{-- =========================================================
  iMenu 2.0 — صفحة محتوى (شروط / خصوصية / أي صفحة blog) — ثيم "اي منيو"
  بديل لـ resources/views/pages/show.blade.php
  يستقبل: $page (title, content) — يُستخدم لمسارَي show و blog/{slug}
========================================================= --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ckb','ps','sd']) ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ $page->title }} — {{ config('app.name', 'iMenu') }}</title>
  @if (config('settings.google_analytics'))
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo config('settings.google_analytics'); ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo config('settings.google_analytics'); ?>');</script>
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#FAF7F1; --surface:#FFFFFF; --cream-deep:#F1EADD;
      --ink:#13202E; --ink-soft:#4E5A68; --line:#E8E1D4;
      --green-900:#0E2C4A; --green-700:#155083; --green-500:#2C82C4;
      --saffron:#F2A33C; --saffron-deep:#E27D26;
      --display:"El Messiri", serif;
      --body:"IBM Plex Sans Arabic", system-ui, sans-serif;
      --maxw:880px;
      --shadow-sm:0 1px 2px rgba(16,58,48,.06), 0 4px 14px rgba(16,58,48,.05);
      --shadow-md:0 10px 40px rgba(16,58,48,.10);
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:var(--body);background:var(--bg);color:var(--ink);line-height:1.85;-webkit-font-smoothing:antialiased}
    a{color:inherit;text-decoration:none}
    h1,h2,h3,h4{font-family:var(--display);line-height:1.3}
    img{max-width:100%;display:block}
    .wrap{max-width:var(--maxw);margin-inline:auto;padding-inline:24px}

    header.nav{position:sticky;top:0;z-index:50;background:rgba(250,247,241,.85);backdrop-filter:blur(12px);border-bottom:1px solid var(--line)}
    .nav-inner{max-width:1160px;margin-inline:auto;padding-inline:24px;display:flex;align-items:center;gap:20px;height:70px}
    .brand{display:flex;align-items:center;gap:10px;font-family:var(--display);font-weight:700;font-size:1.3rem;color:var(--green-900)}
    .brand .mark{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;background:var(--green-900);color:var(--saffron);font-weight:700;box-shadow:var(--shadow-sm);overflow:hidden}
    .brand .mark img{width:100%;height:100%;object-fit:contain}
    .nav-actions{margin-inline-start:auto;display:flex;align-items:center;gap:12px}
    .nav-login{font-weight:600;color:var(--ink);font-size:.95rem}
    .nav-login:hover{color:var(--green-700)}
    .btn{display:inline-flex;align-items:center;gap:8px;font-family:var(--body);font-weight:600;font-size:.95rem;padding:11px 20px;border-radius:999px;border:1.5px solid transparent;transition:.18s;cursor:pointer}
    .btn-primary{background:linear-gradient(180deg,#F4AE4A,var(--saffron-deep));color:#2A170A;box-shadow:0 8px 22px rgba(226,125,38,.3)}
    .btn-primary:hover{transform:translateY(-2px)}

    .page-head{position:relative;background:linear-gradient(160deg,var(--green-700),var(--green-900));color:#fff;padding-block:clamp(48px,7vw,84px) clamp(70px,9vw,120px);overflow:hidden}
    .page-head::before{content:"";position:absolute;width:360px;height:360px;border-radius:50%;background:rgba(242,163,60,.16);top:-130px;inset-inline-end:-90px;filter:blur(8px)}
    .page-head::after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;background:rgba(44,130,196,.22);bottom:-120px;inset-inline-start:-70px;filter:blur(8px)}
    .crumb{position:relative;z-index:2;display:flex;align-items:center;gap:9px;font-size:.9rem;color:rgba(255,255,255,.72);margin-bottom:16px}
    .crumb a:hover{color:#fff}
    .crumb svg{width:14px;height:14px;opacity:.6}
    .page-head .eyebrow{position:relative;z-index:2;display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);color:#fff;font-weight:600;font-size:.84rem;padding:6px 14px;border-radius:999px;margin-bottom:16px}
    .page-head .eyebrow .dot{width:7px;height:7px;border-radius:50%;background:var(--saffron)}
    .page-head h1{position:relative;z-index:2;font-size:clamp(2rem,4.5vw,3rem);font-weight:700;letter-spacing:-.5px}

    .content-shell{margin-top:clamp(-58px,-7vw,-80px);position:relative;z-index:3;padding-bottom:70px}
    .content-card{background:var(--surface);border:1px solid var(--line);border-radius:22px;box-shadow:var(--shadow-md);padding:clamp(30px,5vw,64px)}

    .prose{color:var(--ink);font-size:1.06rem}
    .prose > *:first-child{margin-top:0}
    .prose h1,.prose h2,.prose h3,.prose h4{color:var(--green-900);margin:34px 0 14px;line-height:1.35}
    .prose h1{font-size:1.7rem}
    .prose h2{font-size:1.55rem;padding-bottom:12px;border-bottom:2px solid var(--cream-deep)}
    .prose h3{font-size:1.25rem}
    .prose h4{font-size:1.1rem}
    .prose p{margin-bottom:18px;color:var(--ink-soft)}
    .prose a{color:var(--green-700);font-weight:600;text-decoration:underline;text-underline-offset:3px}
    .prose a:hover{color:var(--saffron-deep)}
    .prose strong,.prose b{color:var(--ink);font-weight:700}
    .prose ul,.prose ol{margin:0 0 20px;padding-inline-start:26px;color:var(--ink-soft)}
    .prose li{margin-bottom:9px}
    .prose ul li::marker{color:var(--saffron-deep)}
    .prose ol li::marker{color:var(--green-700);font-weight:700}
    .prose blockquote{border-inline-start:4px solid var(--saffron);background:var(--cream-deep);padding:16px 22px;border-radius:0 12px 12px 0;margin:0 0 20px;color:var(--ink)}
    .prose hr{border:none;border-top:1px solid var(--line);margin:30px 0}
    .prose table{width:100%;border-collapse:collapse;margin-bottom:20px;font-size:.98rem}
    .prose th,.prose td{border:1px solid var(--line);padding:11px 14px;text-align:start}
    .prose th{background:var(--cream-deep);color:var(--green-900);font-weight:700}
    .prose img{border-radius:14px;margin:14px 0}
    .prose code{background:var(--cream-deep);padding:2px 7px;border-radius:6px;font-size:.92em}

    footer{background:var(--green-900);color:#fff;padding-block:46px 28px}
    .foot-inner{max-width:1160px;margin-inline:auto;padding-inline:24px}
    .foot-top{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;margin-bottom:26px}
    .foot-top .brand{color:#fff}
    .foot-top .brand .mark{background:var(--saffron);color:var(--green-900)}
    .foot-links{display:flex;gap:22px;flex-wrap:wrap}
    .foot-links a{color:rgba(255,255,255,.78);font-size:.94rem}
    .foot-links a:hover{color:#fff}
    .foot-bottom{border-top:1px solid rgba(255,255,255,.12);padding-top:20px;color:rgba(255,255,255,.6);font-size:.88rem;text-align:center}

    @media(max-width:600px){
      .nav-login{display:none}
      .foot-top{flex-direction:column;align-items:flex-start}
    }
    :focus-visible{outline:3px solid var(--saffron);outline-offset:2px;border-radius:6px}
  </style>
</head>
<body>

<header class="nav">
  <div class="nav-inner">
    <a class="brand" href="/">
      <span class="mark">@if(config('global.site_logo'))<img src="{{ config('global.site_logo') }}" alt="{{ config('app.name') }}">@else{{ mb_substr(config('app.name','اي منيو'),0,1) }}@endif</span>
      {{ config('app.name', 'اي منيو') }}
    </a>
    <div class="nav-actions">
      <a class="nav-login" href="/login">@auth لوحة التحكم @endauth @guest تسجيل الدخول @endguest</a>
      @guest
        <a class="btn btn-primary" href="{{ route('newrestaurant.register') }}">ابدأ مجاناً</a>
      @endguest
    </div>
  </div>
</header>

<section class="page-head">
  <div class="wrap">
    <div class="crumb">
      <a href="/">الرئيسية</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      <span>{{ $page->title }}</span>
    </div>
    <span class="eyebrow"><span class="dot"></span> مركز المعلومات</span>
    <h1>{{ $page->title }}</h1>
  </div>
</section>

<div class="wrap content-shell">
  <article class="content-card">
    <div class="prose">
      {!! $page->content !!}
    </div>
  </article>
</div>

<footer>
  <div class="foot-inner">
    <div class="foot-top">
      <a class="brand" href="/">
        <span class="mark">@if(config('global.site_logo'))<img src="{{ config('global.site_logo') }}" alt="{{ config('app.name') }}">@else{{ mb_substr(config('app.name','اي منيو'),0,1) }}@endif</span>
        {{ config('app.name', 'اي منيو') }}
      </a>
      <div class="foot-links">
        <a href="/blog/Terms-and-conditions">الشروط والأحكام</a>
        <a href="/blog/Privacy-Policy">سياسة الخصوصية</a>
        <a href="/login">تسجيل الدخول</a>
      </div>
    </div>
    <div class="foot-bottom">© {{ config('app.name', 'اي منيو') }} {{ date('Y') }}. {{ __('All rights reserved') }}.</div>
  </div>
</footer>

</body>
</html>
