<!DOCTYPE html>
{{-- =========================================================
  iMenu 2026 — الصفحة الرئيسية v2
  المواصفة: claude/imenu-homepage-v2.md · المصدر: claude/imenu-homepage-v2.html

  التبديل: QR_LANDING=home_v2 في .env (القديمة: QR_LANDING=home)
  الصفحة القديمة home.blade.php وأجزاؤها باقية كما هي — تعطيل لا حذف.

  الرسالة: العمولة صفر وملكية العميل — لا «منيو إلكتروني بـQR».
  الدعوة: «اشترك الآن» (تسجيل ذاتي يعمل من أي مدينة) لا «اطلب زيارة».
  النموذج: منيو مجاني سنة · الطلب مجاني حتى ٥٠ طلبًا · ثم تحويل بنكي.
========================================================= --}}
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ckb','ps','sd']) ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('argonfront') }}/img/apple-icon.png">
  <link rel="icon" type="image/png" href="{{ asset('argonfront') }}/img/favicon.png">
  <meta property="og:image" content="{{ config('global.site_logo') }}">
  <meta name="description" content="عميلك يطلب قبل ما يوصل ويستلم بلا طابور. صفر عمولة، والدفع عندك في المحل.">
  <title>{{ config('global.site_name', config('app.name', 'iMenu')) }}</title>

  @if (config('settings.google_analytics'))
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo config('settings.google_analytics'); ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?php echo config('settings.google_analytics'); ?>');
    </script>
  @endif

  @yield('head')
  @laravelPWA

  <link rel="manifest" href="/site.webmanifest">
  <meta name="theme-color" content="#412A1B">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">

  <style>
:root{
  --bg:#FAF8F5; --surface:#FFFFFF; --cream-2:#F3EDE6; --line:#EFE9E2;
  --espresso:#412A1B; --espresso-deep:#2E1C11; --espresso-soft:#6B5344;
  --orange:#F06C1F; --orange-lt:#F79552; --orange-deep:#C4531A;
  --peach:#F9ECE3; --peach-line:#F0D9C7;
  --ink:#2A211B; --ink-soft:#7A6C61; --ink-faint:#A99C92;
  --green:#2F7D5F; --green-bg:#EAF3EE;
  --display:"El Messiri","IBM Plex Sans Arabic",serif;
  --body:"IBM Plex Sans Arabic","Segoe UI",Tahoma,system-ui,sans-serif;
  --radius:20px;
  --maxw:1120px;
}
*{box-sizing:border-box;margin:0;padding:0}
img{max-width:100%;display:block}
.brand .mark{overflow:hidden}
.brand .mark img{width:100%;height:100%;object-fit:contain}
body{font-family:var(--body);background:var(--bg);color:var(--ink);line-height:1.75;direction:rtl}
h1,h2,h3{font-family:var(--display);line-height:1.25;color:var(--espresso-deep)}
a{color:inherit;text-decoration:none}
.n{font-variant-numeric:tabular-nums}
.wrap{max-width:var(--maxw);margin-inline:auto;padding-inline:28px}
section{padding-block:78px}

/* الأزرار */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;font-weight:700;font-size:15.5px;
  padding:15px 28px;border-radius:999px;border:1.5px solid transparent;white-space:nowrap}
.btn-primary{background:var(--orange);color:#fff;box-shadow:0 8px 22px rgba(240,108,31,.28)}
.btn-dark{background:var(--espresso);color:#fff}
.btn-ghost{background:var(--surface);border-color:var(--espresso);color:var(--espresso)}
.btn-lg{padding:17px 32px;font-size:16.5px}

/* الترويسة */
.nav{position:sticky;top:0;z-index:40;background:rgba(250,248,245,.9);border-bottom:1px solid var(--line)}
.nav-in{display:flex;align-items:center;gap:26px;height:74px}
.brand{display:flex;align-items:center;gap:10px;font-family:var(--display);font-weight:700;font-size:21px;color:var(--espresso-deep)}
.brand .mark{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;background:var(--espresso);color:var(--orange);font-size:18px}
.nav-links{display:flex;gap:24px;margin-inline-start:14px}
.nav-links a{color:var(--ink-soft);font-size:14.5px;font-weight:500}
.nav-act{margin-inline-start:auto;display:flex;align-items:center;gap:12px}
.nav-login{font-size:14.5px;font-weight:600;color:var(--espresso)}

/* البطل */
.hero{padding-block:64px 70px}
.hero-grid{display:grid;grid-template-columns:1.06fr .94fr;gap:48px;align-items:center}
.eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--peach);color:var(--orange-deep);
  font-weight:700;font-size:13.5px;padding:7px 15px;border-radius:999px;margin-bottom:20px}
.eyebrow .dot{width:8px;height:8px;border-radius:50%;background:var(--orange)}
.hero h1{font-size:clamp(34px,4.6vw,52px);font-weight:700;letter-spacing:-.3px}
.hero h1 .hl{color:var(--orange-deep);position:relative;white-space:nowrap}
.hero h1 .hl::after{content:"";position:absolute;inset-inline:-3px;bottom:5px;height:12px;background:rgba(240,108,31,.2);border-radius:4px;z-index:-1}
.hero .lead{font-size:18px;color:var(--ink-soft);margin:20px 0 14px;max-width:31em}
.hero .punch{font-family:var(--display);font-weight:700;font-size:21px;color:var(--espresso-deep);margin-bottom:26px}
.hero-cta{display:flex;flex-wrap:wrap;gap:13px}
.micro{display:flex;align-items:center;gap:9px;flex-wrap:wrap;color:var(--ink-soft);font-size:14px;margin-top:18px}
.micro b{color:var(--espresso-deep);font-weight:600}

/* الهاتف */
.hero-vis{position:relative;display:grid;place-items:center;min-height:520px}
.blob{position:absolute;width:400px;height:400px;border-radius:48% 52% 58% 42%/54% 44% 56% 46%;
  background:radial-gradient(circle at 32% 30%,#5A3D28,var(--espresso-deep));z-index:0}
.phone{position:relative;z-index:2;width:262px;border-radius:40px;background:var(--espresso-deep);padding:11px;
  box-shadow:0 28px 70px rgba(46,28,17,.3)}
.screen{border-radius:30px;background:var(--bg);overflow:hidden}
.sc-top{display:flex;align-items:center;justify-content:space-between;padding:14px 13px 10px}
.sc-brand{font-family:var(--display);font-weight:700;font-size:16px;color:var(--espresso-deep)}
.sc-brand i{font-style:normal;color:var(--orange)}
.sc-loc{font-size:9.5px;color:var(--ink-faint)}
.sc-loc b{display:block;font-size:12px;color:var(--espresso-deep)}
.sc-chips{display:flex;gap:5px;padding:0 13px 10px}
.sc-chips i{font-style:normal;font-size:9.5px;padding:4px 10px;border-radius:999px;background:var(--surface);border:1px solid var(--line);color:var(--espresso)}
.sc-chips i.on{background:var(--espresso);color:#fff;border-color:var(--espresso)}
.sc-card{display:flex;gap:9px;margin:0 13px 8px;padding:8px;background:var(--surface);border:1px solid var(--line);border-radius:15px}
.sc-img{width:66px;height:60px;border-radius:12px;flex:none;display:grid;place-items:center;
  font-family:var(--display);font-size:9.5px;font-weight:700;color:#E8D5C0;letter-spacing:.05em}
.sc-meta{flex:1;min-width:0;display:flex;flex-direction:column;gap:3px}
.sc-meta b{font-size:11.5px;color:var(--espresso-deep)}
.sc-meta span{font-size:9px;color:var(--ink-faint)}
.sc-ready{font-size:9px;color:var(--green);font-weight:600}
.sc-tag{display:inline-flex;align-items:center;gap:3px;font-size:8.5px;font-weight:600;background:var(--green-bg);color:var(--green);padding:2px 7px;border-radius:999px;align-self:flex-start}
.sc-bar{display:flex;justify-content:space-around;padding:9px 0 11px;border-top:1px solid var(--line);background:var(--surface)}
.sc-bar span{width:15px;height:15px;border-radius:4px;background:var(--cream-2)}
.sc-bar span.on{background:var(--orange)}
.float{position:absolute;z-index:3;background:var(--surface);border:1px solid var(--line);border-radius:16px;
  padding:12px 14px;box-shadow:0 14px 34px rgba(46,28,17,.16);display:flex;align-items:center;gap:10px}
.float.a{top:44px;inset-inline-end:-6px}
.float.b{bottom:52px;inset-inline-start:-14px}
.float .ic{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;flex:none}
.float small{display:block;font-size:10.5px;color:var(--ink-faint)}
.float b{font-size:13px;color:var(--espresso-deep)}

/* شريط الحقائق */
.facts{background:var(--espresso-deep);color:#fff;padding-block:38px}
.facts-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;text-align:center}
.facts .v{font-family:var(--display);font-size:30px;font-weight:700;color:var(--orange)}
.facts .l{font-size:14px;color:rgba(255,255,255,.72);margin-top:2px}

/* رأس القسم */
.sec-head{max-width:660px;margin-inline:auto;text-align:center;margin-bottom:48px}
.sec-head .tag{color:var(--orange-deep);font-weight:700;font-size:14px;margin-bottom:10px}
.sec-head h2{font-size:clamp(26px,3.4vw,38px);font-weight:700}
.sec-head p{color:var(--ink-soft);margin-top:13px;font-size:16.5px}

/* المقارنة */
.cmp{background:var(--surface);border:1px solid var(--line);border-radius:26px;overflow:hidden;box-shadow:0 1px 2px rgba(65,42,27,.04)}
.cmp table{width:100%;border-collapse:collapse}
.cmp th,.cmp td{padding:18px 22px;text-align:start;border-bottom:1px solid var(--line);font-size:15.5px}
.cmp tr:last-child td{border-bottom:0}
.cmp thead th{font-family:var(--display);font-size:17px;font-weight:700;background:var(--cream-2)}
.cmp thead th.us{background:var(--espresso);color:#fff}
.cmp td.us{background:var(--peach);font-weight:700;color:var(--espresso-deep)}
.cmp td.them{color:var(--ink-soft)}
.cmp td.k{font-weight:600;color:var(--espresso-deep)}

/* الخطوات */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.step{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:28px 24px}
.step .n{width:42px;height:42px;border-radius:13px;background:var(--peach);color:var(--orange-deep);
  display:grid;place-items:center;font-family:var(--display);font-weight:700;font-size:17px;margin-bottom:16px}
.step h3{font-size:19px;margin-bottom:7px}
.step p{color:var(--ink-soft);font-size:15px}

/* المزايا */
.feats{background:var(--cream-2)}
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feat{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:26px 24px}
.feat .ic{width:48px;height:48px;border-radius:14px;background:var(--peach);color:var(--orange-deep);display:grid;place-items:center;margin-bottom:15px}
.feat h3{font-size:18px;margin-bottom:7px}
.feat p{color:var(--ink-soft);font-size:14.5px}

/* التطبيق */
.app-card{background:var(--espresso-deep);border-radius:28px;padding:52px;color:#fff;display:grid;
  grid-template-columns:1fr .8fr;gap:40px;align-items:center;position:relative;overflow:hidden}
.app-card h2{color:#fff;font-size:clamp(24px,3vw,34px)}
.app-card p{color:rgba(255,255,255,.76);font-size:16.5px;margin:14px 0 24px;max-width:30em}
.app-ul{list-style:none;display:flex;flex-direction:column;gap:11px;margin-bottom:26px}
.app-ul li{display:flex;gap:10px;align-items:flex-start;color:rgba(255,255,255,.9);font-size:15px}
.app-ul svg{flex:none;margin-top:5px;color:var(--orange)}
.app-shots{display:flex;gap:14px;justify-content:center}
.shot{width:150px;border-radius:22px;background:var(--bg);padding:8px;box-shadow:0 20px 44px rgba(0,0,0,.3)}
.shot .in{border-radius:16px;overflow:hidden;background:var(--surface)}
.shot .hd{background:var(--espresso);color:#fff;padding:10px;text-align:center;font-family:var(--display);font-size:11px}
.shot .rw{display:flex;gap:6px;padding:7px;border-bottom:1px solid var(--line);align-items:center}
.shot .th{width:30px;height:26px;border-radius:7px;flex:none}
.shot .tx{flex:1}
.shot .tx b{display:block;font-size:8.5px;color:var(--espresso-deep)}
.shot .tx span{font-size:7px;color:var(--ink-faint)}

/* المنيو المجاني */
.plan-card{display:flex;flex-direction:column}
.pnum{font-variant-numeric:tabular-nums;line-height:1.1}
.plan-card ul{margin-top:auto}
.notes-row{margin-top:34px;display:grid;grid-template-columns:1fr 1fr;gap:18px}
.zone{background:var(--peach);border:1px solid var(--peach-line);border-radius:22px;
  padding:26px 28px;display:flex;gap:18px;align-items:flex-start}
.zone .zi{flex:none;width:46px;height:46px;border-radius:14px;display:grid;place-items:center;
  background:var(--surface);color:var(--orange-deep);border:1px solid var(--peach-line)}
.zone h4{font-family:var(--display);font-size:19px;font-weight:700;color:var(--espresso-deep);margin-bottom:7px}
.zone p{color:var(--espresso-soft);font-size:15.5px}
.zone b{color:var(--espresso-deep);font-weight:700}
.free{background:var(--peach);border:1px solid var(--peach-line);border-radius:26px;padding:44px;text-align:center}
.free h2{font-size:clamp(24px,3vw,32px)}
.free p{color:var(--espresso-soft);font-size:16.5px;margin:14px auto 24px;max-width:44em}

/* الأسئلة */
.faq{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.qa{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:24px}
.qa h3{font-size:17px;margin-bottom:8px}
.qa p{color:var(--ink-soft);font-size:14.5px}

/* الدعوة الأخيرة */
.cta-box{background:linear-gradient(150deg,#54381F,var(--espresso-deep));border-radius:28px;
  padding:64px 28px;text-align:center;color:#fff;position:relative;overflow:hidden}
.cta-box h2{color:#fff;font-size:clamp(26px,3.6vw,38px)}
.cta-box p{color:rgba(255,255,255,.8);max-width:36em;margin:15px auto 28px;font-size:17px}
.cta-row{display:flex;gap:13px;justify-content:center;flex-wrap:wrap}
.cta-box .micro{justify-content:center;color:rgba(255,255,255,.68);margin-top:20px}
.cta-box .micro b{color:#fff}

/* التذييل */
footer{border-top:1px solid var(--line);padding-block:48px 24px}
.foot{display:grid;grid-template-columns:1.7fr 1fr 1fr 1fr;gap:32px;margin-bottom:34px}
.foot p{color:var(--ink-soft);font-size:14.5px;max-width:26em;margin-top:12px}
.foot h4{font-size:14px;margin-bottom:13px;color:var(--espresso-deep);font-weight:700}
.foot a{display:block;color:var(--ink-soft);font-size:14px;margin-bottom:9px}
.foot-b{border-top:1px solid var(--line);padding-top:20px;display:flex;justify-content:space-between;
  flex-wrap:wrap;gap:10px;color:var(--ink-faint);font-size:13.5px}

@media(max-width:900px){
  section{padding-block:52px}
  .nav-links,.nav-login{display:none}
  .hero-grid{grid-template-columns:1fr;gap:34px}
  .hero-vis{order:-1;min-height:450px}
  .facts-grid{grid-template-columns:repeat(2,1fr);gap:26px}
  .steps,.feat-grid,.faq{grid-template-columns:1fr}
  .app-card{grid-template-columns:1fr;padding:34px 24px;text-align:center}
  .app-card p{margin-inline:auto}
  .app-ul{text-align:start;max-width:22em;margin-inline:auto}
  .foot{grid-template-columns:1fr 1fr}
  .cmp th,.cmp td{padding:14px 12px;font-size:13.5px}
  .free{padding:32px 22px}
  .cta-box{padding:44px 20px}
  .notes-row{grid-template-columns:1fr;gap:14px}
  .zone{flex-direction:column;gap:14px;padding:24px 20px}
}
@media(max-width:520px){
  .wrap{padding-inline:18px}
  .blob{width:330px;height:330px}
  .hero-vis{min-height:430px}
  .foot{grid-template-columns:1fr}
  .btn{width:100%}
  .hero-cta{flex-direction:column}
}
    /* منتقي اللغة — مأخوذ من الصفحة القديمة ليبقى السلوك واحدًا */
    .lang-wrap{position:relative}
    .lang{display:inline-flex;align-items:center;gap:6px;font-size:14px;color:var(--ink-soft);font-weight:500;
      border:1px solid var(--line);padding:7px 13px;border-radius:999px;cursor:pointer;background:none;font-family:inherit}
    .lang:hover{border-color:var(--espresso);color:var(--espresso)}
    .lang-menu{position:absolute;inset-inline-end:0;top:calc(100% + 8px);background:var(--surface);
      border:1px solid var(--line);border-radius:12px;box-shadow:0 10px 34px rgba(46,28,17,.14);
      padding:6px;min-width:140px;display:none;z-index:60}
    .lang-menu.open{display:block}
    .lang-menu a{display:block;padding:8px 12px;border-radius:8px;font-size:14px;color:var(--ink)}
    .lang-menu a:hover{background:var(--cream-2)}
    @media(max-width:900px){ .lang-wrap{display:none} }
    /* الترويسة لاصقة — بلا هذا السطر يغطّي الشريطُ عنوانَ القسم عند القفز إليه من الروابط */
    section[id]{scroll-margin-top:90px}
    :focus-visible{outline:3px solid var(--orange-deep);outline-offset:2px;border-radius:6px}
    @media(prefers-reduced-motion:reduce){ html{scroll-behavior:auto} }
    html{scroll-behavior:smooth}
  </style>
</head>
<body>
<div dir="rtl"><header class="nav"><div class="wrap nav-in"><a class="brand" href="/"><span class="mark">@if(config('global.site_logo'))<img src="{{ config('global.site_logo') }}" alt="{{ config('app.name') }}">@else{{ mb_substr(config('app.name','اي منيو'), 0, 1) }}@endif</span> {{ config('app.name', 'اي منيو') }}</a><nav class="nav-links"><a href="#why">لماذا اي منيو</a><a href="#how">كيف يعمل</a><a href="#app">للعملاء</a><a href="#pricing">الأسعار</a><a href="#faq">الأسئلة</a></nav><div class="nav-act">@if(isset($availableLanguages) && count($availableLanguages) > 1)
        <div class="lang-wrap">
          <button class="lang" id="langBtn" type="button">@foreach ($availableLanguages as $short => $lang)@if(strtolower($short) == strtolower($locale ?? app()->getLocale())){{ strtoupper($short) }}@endif @endforeach<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></button>
          <div class="lang-menu" id="langMenu">@foreach ($availableLanguages as $short => $lang)<a href="/{{ strtolower($short) }}">{{ __($lang) }}</a>@endforeach</div>
        </div>
      @endif
      <a class="nav-login" href="/login">@auth {{ __('qrlanding.dashboard') }} @endauth @guest دخول المقاهي @endguest</a><a class="btn btn-primary" href="#pricing">اشترك الآن</a></div></div></header><section class="hero"><div class="wrap hero-grid"><div><span class="eyebrow"><span class="dot"></span> لكافيهات ومطاعم الحي</span><h1>عميلك يطلب قبل ما يوصل — ويستلم <span class="hl">بلا طابور</span></h1><p class="lead">اي منيو يجمع كافيهات حيّك في تطبيق واحد. الطلب مسبق، والاستلام من الشباك أو من السيارة، والدفع عندك في المحل.</p><p class="punch">صفر عمولة. وعميلك يبقى عميلك.</p><div class="hero-cta"><a class="btn btn-primary btn-lg" href="#pricing">اشترك الآن</a><a class="btn btn-ghost btn-lg" href="{{ route('newrestaurant.register') }}">سجّل منيوك مجانًا</a></div><div class="micro"><b>لا تدفع شيئًا اليوم</b> · منيوك مجاني سنة كاملة · @if(isset($featured_vendors) && count($featured_vendors))<a href="{{ route('vendor', ['alias' => $featured_vendors->first()->alias]) }}" style="color:var(--orange-deep);font-weight:600;border-bottom:1px solid currentColor">شاهد منيو حيًّا</a>@else<span>تجهيز في يوم واحد</span>@endif</div></div><div class="hero-vis"><span class="blob"></span><div class="phone"><div class="screen"><div class="sc-top"><span class="sc-brand"><i>i</i>Menu</span><span class="sc-loc">موقعك<b>رابغ</b></span></div><div class="sc-chips"><i class="on">الكل</i><i>كافيهات</i><i>شاورما</i><i>برجر</i></div><div class="sc-card"><div class="sc-img" style="background:linear-gradient(160deg,#3A2A20,#1E1512)">RATIO</div><div class="sc-meta"><b>Ratio</b><span>كوفي مختص</span><span class="sc-ready">جاهز خلال <span class="n">8</span> دقائق</span><span class="sc-tag"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16 6.5 10h11L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>من السيارة</span></div></div><div class="sc-card"><div class="sc-img" style="background:linear-gradient(160deg,#33261D,#171110)">BREW</div><div class="sc-meta"><b>Brew</b><span>مختص بالقهوة</span><span class="sc-ready">جاهز خلال <span class="n">7</span> دقائق</span><span class="sc-tag"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16 6.5 10h11L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>من السيارة</span></div></div><div class="sc-card"><div class="sc-img" style="background:linear-gradient(160deg,#4A2E1C,#241509)">شاورما</div><div class="sc-meta"><b>شاورما الشام</b><span>شاورما</span><span class="sc-ready">جاهز خلال <span class="n">12</span> دقائق</span><span class="sc-tag"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16 6.5 10h11L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>من السيارة</span></div></div><div class="sc-bar"><span class="on"></span><span></span><span></span><span></span></div></div></div><div class="float a"><span class="ic" style="background:var(--green-bg);color:var(--green)"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span><div><small>طلب جديد</small><b>جاهز خلال <span class="n">6</span> دقائق</b></div></div><div class="float b"><span class="ic" style="background:var(--peach);color:var(--orange-deep)"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16 6.5 10h11L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg></span><div><small>العميل وصل</small><b>اطلع له للسيارة</b></div></div></div></div></section><section class="facts"><div class="wrap facts-grid"><div><div class="v n">0%</div><div class="l">عمولة على كل طلب</div></div><div><div class="v n">100%</div><div class="l">من قيمة الطلب تصلك</div></div><div><div class="v n">2</div><div class="l">طريقتا استلام: الشباك والسيارة</div></div><div><div class="v n">50</div><div class="l">طلبًا مجانًا قبل أول ريال</div></div></div></section><section id="why"><div class="wrap"><div class="sec-head"><div class="tag">الفرق في جدول</div><h2>لماذا اي منيو بدل تطبيقات التوصيل</h2><p>لسنا منافسًا لها في التوصيل — نحن نحلّ ما لا تحلّه: العميل الذي يمرّ عليك كل يوم.</p></div><div class="cmp"><table><thead><tr><th></th><th class="us">اي منيو</th><th>تطبيقات التوصيل</th></tr></thead><tbody><tr><td class="k">العمولة على كل طلب</td><td class="us n">صفر</td><td class="them n">20–30%</td></tr><tr><td class="k">من يملك العميل</td><td class="us n">عميلك — باسمه ورقمه</td><td class="them n">عميلهم — لا تعرف اسمه</td></tr><tr><td class="k">أين يصل المال</td><td class="us n">عندك في المحل لحظة الاستلام</td><td class="them n">يمرّ بهم ثم يصلك لاحقًا</td></tr><tr><td class="k">بيانات عملائك</td><td class="us n">لك، وتصدّرها متى شئت</td><td class="them n">لا تصلك</td></tr><tr><td class="k">التواصل معهم</td><td class="us n">إشعارات مباشرة باسم كوفيك</td><td class="them n">ممنوع</td></tr></tbody></table></div></div></section><section id="how"><div class="wrap"><div class="sec-head"><div class="tag">ثلاث خطوات</div><h2>من أول زيارة إلى أول طلب</h2></div><div class="steps"><div class="step"><div class="n">1</div><h3>نضيف كوفيك ومنيوك</h3><p>نصوّر منيوك وندخله مجانًا، وتستلم رابطك الخاص ورمز QR ومطبوعات جاهزة للشباك والكاونتر.</p></div><div class="step"><div class="n">2</div><h3>عميلك يطلب قبل ما يوصل</h3><p>من التطبيق أو من رمز QR على شبّاكك. يختار: استلام من الشباك أو من سيارته.</p></div><div class="step"><div class="n">3</div><h3>تجهّزه ويستلمه ويدفع عندك</h3><p>شاشة كاشير بثلاثة أزرار: جاهز · سُلّم · لم يُستلم. والمبلغ يصلك كاملًا في المحل.</p></div></div></div></section><section class="feats"><div class="wrap"><div class="sec-head"><div class="tag">ما تحصل عليه</div><h2>أدوات كوفي الشباك — لا أدوات مطعم كبير</h2><p>كل ميزة هنا وُضعت لأن صاحب كوفي طلبها، لا لأنها تملأ قائمة.</p></div><div class="feat-grid"><div class="feat"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.4"/><path d="M3 20c1.2-3.4 3.6-5 6-5s4.8 1.6 6 5"/><path d="M17 11a3 3 0 1 0-2-5.2"/><path d="M17.5 15c1.9.6 3.2 2 3.8 4"/></svg></div><h3>إلغاء الطابور</h3><p>الطلب المسبق يرفع طاقتك الاستيعابية في وقت الذروة بلا موظف إضافي.</p></div><div class="feat"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 1 1 12 0c0 4 1.5 5.5 1.5 5.5h-15S6 13 6 9z"/><path d="M10 18a2 2 0 0 0 4 0"/></svg></div><h3>عملاؤك لك</h3><p>قاعدة عملاء باسم كوفيك، وإشعارات مباشرة — قناة تسويق يمنعها المنافسون.</p></div><div class="feat"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16 6.5 10h11L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg></div><h3>الاستلام من السيارة</h3><p>عاملك يطلع للسيارة حين يضغط العميل «وصلت». ومفتاح يوقفه لحظيًا وقت الزحمة.</p></div><div class="feat"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM18 18h3v3h-3z"/></svg></div><h3>منيو ورمز QR مجانًا</h3><p>رابط خاص بكوفيك، ورمز بشعارك، ومطبوعات جاهزة للطباعة من المتصفح.</p></div><div class="feat"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg></div><h3>شاشة كاشير بثلاثة أزرار</h3><p>تعمل على أي تابلت أو جوال بالمتصفح. بلا جهاز تشتريه وبلا تدريب.</p></div><div class="feat"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><rect x="6" y="10" width="3" height="7" rx="1"/><rect x="11" y="6" width="3" height="11" rx="1"/><rect x="16" y="13" width="3" height="4" rx="1"/></svg></div><h3>مبيعاتك في شاشة واحدة</h3><p>كم بعت اليوم، ومتوسط الطلب، ومن أين يستلم عملاؤك — من السيارة أم من الشباك.</p></div></div></div></section><section id="app"><div class="wrap"><div class="app-card"><div><h2>وللعميل: تطبيق واحد لكافيهات حيّه</h2><p>يفتحه كل صباح، يضغط «طلبك المعتاد»، ويستلم وهو في سيارته.</p><ul class="app-ul"><li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg><span>الطلب المسبق وإلغاء الانتظار</span></li><li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg><span>الاستلام من السيارة — «وصلت» بضغطة</span></li><li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg><span>الدفع عند الاستلام، بلا بطاقة</span></li><li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg><span>منيوهات كل مقاهي الحي في مكان واحد</span></li></ul>@if(config('global.playstore') || config('global.appstore'))<a class="btn btn-primary" href="{{ config('global.playstore') ?: config('global.appstore') }}" target="_blank" rel="noopener">حمّل التطبيق</a>@else<a class="btn btn-primary" href="{{ route('newrestaurant.register') }}">سجّل كوفيك</a>@endif</div><div class="app-shots"><div class="shot"><div class="in"><div class="hd">كافيهات حيّك</div><div class="rw"><span class="th" style="background:linear-gradient(160deg,#3A2A20,#1E1512)"></span><span class="tx"><b>Ratio</b><span>جاهز خلال 8 دقائق</span></span></div><div class="rw"><span class="th" style="background:linear-gradient(160deg,#33261D,#171110)"></span><span class="tx"><b>Brew</b><span>جاهز خلال 7 دقائق</span></span></div><div class="rw"><span class="th" style="background:linear-gradient(160deg,#2B2521,#141010)"></span><span class="tx"><b>Dose</b><span>جاهز خلال 8 دقائق</span></span></div></div></div><div class="shot"><div class="in"><div class="hd">طلبك المعتاد</div><div class="rw"><span class="th" style="background:linear-gradient(150deg,#EADBCB,#D8BFA4)"></span><span class="tx"><b>لاتيه كبير</b><span>قليل السكر</span></span></div><div class="rw"><span class="th" style="background:linear-gradient(150deg,#F2DFC2,#E3C79B)"></span><span class="tx"><b>كرواسون لوز</b><span>طازج</span></span></div><div class="rw"><span class="th" style="background:linear-gradient(150deg,#E7DCCB,#CFBB9F)"></span><span class="tx"><b>تمر محشي</b><span>بالجوز</span></span></div></div></div></div></div></div></section><section id="pricing"><div class="wrap"><div class="sec-head"><div class="tag">عرض الانتشار</div><h2>لا تدفع شيئًا حتى الطلب الخمسين</h2><p>منيوك مجاني سنة كاملة. والطلب المسبق تجرّبه مجانًا حتى تكتمل أول 50 طلبًا — ثم تحويل بنكي واحد. بلا عمولة ولا رسوم لكل طلب.</p></div><div class="steps"><div class="step plan-card" style="padding:32px 28px;position:relative;"><h3 style="font-size:21px;margin-bottom:6px">منيو مجاني</h3><p style="color:var(--ink-soft);font-size:14.5px;min-height:44px">عرض الانتشار: منيوك ورمزك ومطبوعاتك سنة بلا مقابل.</p><div style="display:flex;align-items:baseline;gap:7px;margin:14px 0 18px;"><span class="pnum" style="font-family:var(--display);font-size:40px;font-weight:700;color:var(--espresso-deep)">0</span><span style="color:var(--ink-soft);font-weight:600">﷼ لسنة كاملة</span></div><ul style="list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:24px"><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>منيو إلكتروني برابط خاص بك</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>رمز QR بشعارك</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>مطبوعات جاهزة للطباعة</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>يعمل في كل مدن المملكة</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>سنة كاملة من يوم تسجيلك</li></ul><a class="btn btn-ghost" style="width:100%" href="{{ route('newrestaurant.register') }}">سجّل مجانًا</a></div><div class="step plan-card" style="padding:32px 28px;position:relative;"><h3 style="font-size:21px;margin-bottom:6px">نصف سنوي</h3><p style="color:var(--ink-soft);font-size:14.5px;min-height:44px">للتجربة قبل الالتزام السنوي.</p><div style="display:flex;align-items:baseline;gap:7px;margin:14px 0 18px;"><span class="pnum" style="font-family:var(--display);font-size:40px;font-weight:700;color:var(--espresso-deep)">900</span><span style="color:var(--ink-soft);font-weight:600">﷼ كل 6 أشهر</span></div><ul style="list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:24px"><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>كل ما في المنيو المجاني</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>الطلب المسبق والاستلام من السيارة</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>الظهور في تطبيق العميل</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>شاشة الكاشير وتقرير المبيعات</li></ul><a class="btn btn-dark" style="width:100%" href="{{ route('newrestaurant.register') }}">اشترك</a></div><div class="step plan-card" style="border:2px solid var(--orange);padding:32px 28px;position:relative;"><span style="position:absolute;top:-14px;inset-inline:0;margin-inline:auto;width:max-content;background:var(--orange);color:#fff;font-size:13px;font-weight:700;padding:5px 15px;border-radius:999px;white-space:nowrap;">الأكثر اختيارًا</span><h3 style="font-size:21px;margin-bottom:6px">سنوي</h3><p style="color:var(--ink-soft);font-size:14.5px;min-height:44px">الأوفر — شهران مجانًا مقارنةً بالنصف سنوي.</p><div style="display:flex;align-items:baseline;gap:7px;margin:14px 0 18px;"><span class="pnum" style="font-family:var(--display);font-size:40px;font-weight:700;color:var(--espresso-deep)">1500</span><span style="color:var(--ink-soft);font-weight:600">﷼ في السنة</span></div><ul style="list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:24px"><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>كل ما في النصف سنوي</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>لا تدفع حتى الطلب الخمسين</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>أولوية في الدعم</li><li style="display:flex;gap:9px;font-size:14.5px"><span style="color:var(--green);flex:none"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>لا تجديد تلقائي — أنت من يقرّر</li></ul><a class="btn btn-primary" style="width:100%" href="{{ route('newrestaurant.register') }}">اشترك الآن</a></div></div><p style="text-align:center;color:var(--ink-soft);font-size:14.5px;margin-top:26px">الأسعار شاملة ضريبة القيمة المضافة · الدفع بتحويل بنكي · لا تجديد تلقائي.</p><div class="notes-row"><div class="zone"><span class="zi"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10 12 4l9 6"/><path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8"/><path d="M3 20h18"/></svg></span><div><h4>لا تدفع شيئًا اليوم — والدفع تحويل بنكي</h4><p>تسجّل وتستقبل الطلبات مجانًا. عند اكتمال <b>أول 50 طلبًا</b> نرسل لك الفاتورة، تحوّل المبلغ بنكيًا ويُفعَّل اشتراكك في نفس اليوم. <b>لا بطاقات ولا خصم تلقائي</b> — التجديد بتحويل جديد منك وحدك.</p></div></div><div class="zone"><span class="zi"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg></span><div><h4>كوفيك خارج الأحياء المفعَّلة؟ سجّل اليوم</h4><p>المنيو والرمز والمطبوعات تعمل في <b>كل مدن المملكة من اليوم الأول</b>. أما الطلب المسبق فنفتحه حيًّا بعد حي، ونُشعرك حين نصل حيّك. تسجيلك اليوم هو ما يحدّد الحي التالي.</p></div></div></div></div></section><section id="faq"><div class="wrap"><div class="sec-head"><div class="tag">أسئلة متكررة</div><h2>قبل ما تسأل</h2></div><div class="faq"><div class="qa"><h3>كم تكلفة الاشتراك؟</h3><p>منيوك مجاني سنة كاملة من يوم تسجيلك. أما الطلب المسبق فاشتراك سنوي 1500 ﷼ أو نصف سنوي 900 ﷼ — بلا عمولة ولا رسوم لكل طلب، ولا يبدأ إلا بعد أول 50 طلبًا.</p></div><div class="qa"><h3>متى وكيف أدفع؟</h3><p>لا تدفع شيئًا عند التسجيل. عند اكتمال أول 50 طلبًا نرسل لك الفاتورة، وتحوّل المبلغ بنكيًا فيُفعَّل اشتراكك في نفس اليوم. لا بطاقات ولا خصم تلقائي.</p></div><div class="qa"><h3>وبعد السنة المجانية للمنيو؟</h3><p>نُشعرك قبل انتهائها بوقت كافٍ، ولا يُغلق منيوك فجأة ولا تُحذف أصنافك. تقرّر حينها الاستمرار أو التوقف، ولا يُسحب منك شيء دون إشعار مسبق.</p></div><div class="qa"><h3>كوفيي خارج الرياض — أقدر أسجّل؟</h3><p>نعم، ومن أي مدينة. منيوك ورمزك يعملان فورًا في كل المملكة. والطلب المسبق نفتحه حيًّا بعد حي، ونُشعرك حين نصل حيّك.</p></div><div class="qa"><h3>هل أنتم تطبيق توصيل؟</h3><p>لا. لا يوجد توصيل ولا سائقون. العميل يطلب مسبقًا ويستلم بنفسه — من الشباك أو من سيارته.</p></div><div class="qa"><h3>كيف يدفع العميل؟</h3><p>عندك في المحل، نقدًا أو بالشبكة. لا يمرّ مال العملاء بنا إطلاقًا، ولا نأخذ نسبة منه.</p></div><div class="qa"><h3>هل أحتاج جهازًا جديدًا؟</h3><p>لا. شاشة الكاشير تعمل بالمتصفح على أي تابلت أو جوال عندك.</p></div><div class="qa"><h3>وإذا طلب العميل ولم يستلم؟</h3><p>النظام يسجّلها، ويحدّ من تكرارها مع حق اعتراض للعميل — حمايةً لك بلا ظلم لأحد.</p></div><div class="qa"><h3>هل بياناتي وبيانات عملائي لي؟</h3><p>نعم. قاعدة عملائك ملكك، وتصدّرها متى شئت.</p></div><div class="qa"><h3>أقدر أوقف الاشتراك؟</h3><p>في أي وقت، ولا يوجد تجديد تلقائي أصلًا — التجديد لا يتم إلا بتحويل جديد منك.</p></div></div></div></section><section style="padding-top:0"><div class="wrap"><div class="cta-box"><h2>سجّل كوفيك اليوم</h2><p>التسجيل من أي مدينة وفي دقائق — منيوك ورمزك ومطبوعاتك جاهزة فورًا ومجانًا سنة كاملة، والطلب المسبق تشغّله متى وصلنا حيّك.</p><div class="cta-row"><a class="btn btn-primary btn-lg" href="{{ route('newrestaurant.register') }}">اشترك الآن</a>@if(config('settings.sales_whatsapp'))<a class="btn btn-ghost btn-lg" target="_blank" rel="noopener" href="https://wa.me/{{ config('settings.sales_whatsapp') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12a8 8 0 1 1-3.2-6.4"/><path d="M4 20l1.4-3.6"/><path d="M9 11h6M9 14h4"/></svg> كلّمنا واتساب</a>@endif</div><div class="micro"><b>منيوك مجاني سنة كاملة</b> · بلا عقد · ولا تدفع ريالًا قبل الطلب <b class="n">50</b></div></div></div></section><footer><div class="wrap"><div class="foot"><div><a class="brand" href="/"><span class="mark">@if(config('global.site_logo'))<img src="{{ config('global.site_logo') }}" alt="{{ config('app.name') }}">@else{{ mb_substr(config('app.name','اي منيو'), 0, 1) }}@endif</span> {{ config('app.name', 'اي منيو') }}</a><p>كافيهات حيّك في تطبيق واحد. الطلب مسبق، والاستلام من الشباك أو السيارة، والدفع عندك. صفر عمولة.</p></div><div><h4>للمقاهي</h4><a href="{{ route('newrestaurant.register') }}">سجّل كوفيك</a><a href="#pricing">الأسعار</a><a href="/login">دخول لوحة التحكم</a><a href="#how">كيف يعمل</a></div><div><h4>للعملاء</h4>@if(config('global.playstore'))<a href="{{ config('global.playstore') }}" target="_blank" rel="noopener">حمّل التطبيق</a>@endif @if(config('global.appstore'))<a href="{{ config('global.appstore') }}" target="_blank" rel="noopener">تطبيق آيفون</a>@endif<a href="#app">قريبًا في حيّك</a></div><div><h4>روابط</h4>@if(isset($pages))@foreach ($pages as $page)<a target="_blank" href="/blog/{{ $page->slug }}">{{ $page->title }}</a>@endforeach @endif</div></div><div class="foot-b"><span>© {{ config('app.name', 'اي منيو') }} <span class="n">{{ date('Y') }}</span>. {{ __('All rights reserved') }}.</span><span>صُنع في المملكة العربية السعودية</span></div></div></footer></div>
<script>
  // منتقي اللغة
  var langBtn  = document.getElementById('langBtn');
  var langMenu = document.getElementById('langMenu');
  if (langBtn && langMenu) {
    langBtn.addEventListener('click', function (e) { e.stopPropagation(); langMenu.classList.toggle('open'); });
    document.addEventListener('click', function () { langMenu.classList.remove('open'); });
  }
</script>
</body>
</html>
