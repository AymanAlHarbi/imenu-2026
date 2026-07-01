<!DOCTYPE html>
{{-- =========================================================
  iMenu 2.0 — QR SaaS Landing (ثيم "اي منيو")
  صفحة هبوط بديلة لـ qrsaas/home.blade.php
  - التصميم مدمج بالكامل (CSS داخلي) ولا يعتمد على Argon/Impact
  - الأجزاء الديناميكية مربوطة: الباقات، اللغات، الروابط، صفحات الفوتر، التسجيل
========================================================= --}}
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ckb','ps','sd']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('argonfront') }}/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="{{ asset('argonfront') }}/img/favicon.png" />
    <meta property="og:image" content="{{ config('global.site_logo') }}" />
    <title>{{ config('global.site_name', config('app.name', 'iMenu')) }}</title>

    {{-- Google Analytics --}}
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

    <link rel="manifest" href="/site.webmanifest" />
    <meta name="theme-color" content="#0E2C4A" />

    {{-- خطوط الثيم --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
      :root{
        --bg:#FAF7F1; --surface:#FFFFFF; --cream-deep:#F1EADD;
        --ink:#13202E; --ink-soft:#4E5A68; --line:#E8E1D4;
        --green-900:#0E2C4A; --green-700:#155083; --green-500:#2C82C4;
        --saffron:#F2A33C; --saffron-deep:#E27D26; --coral:#E1604A;
        --radius:18px;
        --shadow-sm:0 1px 2px rgba(16,58,48,.06), 0 4px 14px rgba(16,58,48,.05);
        --shadow-md:0 10px 40px rgba(16,58,48,.10);
        --shadow-lg:0 24px 70px rgba(16,58,48,.16);
        --maxw:1160px;
        --display:"El Messiri", serif;
        --body:"IBM Plex Sans Arabic", system-ui, sans-serif;
      }
      *{box-sizing:border-box;margin:0;padding:0}
      html{scroll-behavior:smooth}
      body{font-family:var(--body);background:var(--bg);color:var(--ink);line-height:1.7;-webkit-font-smoothing:antialiased;overflow-x:hidden}
      h1,h2,h3,.display{font-family:var(--display);line-height:1.25}
      a{color:inherit;text-decoration:none}
      img{max-width:100%;display:block}
      .wrap{max-width:var(--maxw);margin-inline:auto;padding-inline:24px}
      section{padding-block:clamp(56px,8vw,110px)}

      /* Buttons */
      .btn{display:inline-flex;align-items:center;gap:10px;justify-content:center;font-family:var(--body);font-weight:600;font-size:1rem;padding:14px 26px;border-radius:999px;cursor:pointer;border:1.5px solid transparent;transition:transform .18s ease, box-shadow .18s ease, background .18s ease;white-space:nowrap}
      .btn-primary{background:linear-gradient(180deg,#F4AE4A,var(--saffron-deep));color:#2A170A;box-shadow:0 8px 22px rgba(226,125,38,.32)}
      .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(226,125,38,.42)}
      .btn-ghost{background:transparent;border-color:var(--line);color:var(--ink)}
      .btn-ghost:hover{border-color:var(--green-700);color:var(--green-700)}
      .btn-dark{background:var(--green-900);color:#fff}
      .btn-dark:hover{transform:translateY(-2px);background:#0a223a}
      .btn-lg{padding:17px 34px;font-size:1.06rem}

      /* Nav */
      header.nav{position:sticky;top:0;z-index:50;background:rgba(250,247,241,.82);backdrop-filter:blur(12px);border-bottom:1px solid transparent;transition:border-color .3s, box-shadow .3s}
      header.nav.scrolled{border-color:var(--line);box-shadow:0 4px 20px rgba(16,58,48,.05)}
      .nav-inner{display:flex;align-items:center;gap:28px;height:74px}
      .brand{display:flex;align-items:center;gap:10px;font-family:var(--display);font-weight:700;font-size:1.35rem;color:var(--green-900)}
      .brand .mark{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;background:var(--green-900);color:var(--saffron);font-size:1.15rem;font-weight:700;box-shadow:var(--shadow-sm);overflow:hidden}
      .brand .mark img{width:100%;height:100%;object-fit:contain}
      .nav-links{display:flex;gap:26px;margin-inline-start:18px}
      .nav-links a{color:var(--ink-soft);font-weight:500;font-size:.97rem;transition:color .15s}
      .nav-links a:hover{color:var(--green-900)}
      .nav-actions{margin-inline-start:auto;display:flex;align-items:center;gap:14px;position:relative}
      .lang-wrap{position:relative}
      .lang{display:inline-flex;align-items:center;gap:6px;font-size:.9rem;color:var(--ink-soft);font-weight:500;border:1px solid var(--line);padding:7px 13px;border-radius:999px;cursor:pointer;background:none}
      .lang:hover{border-color:var(--green-700);color:var(--green-700)}
      .lang-menu{position:absolute;inset-inline-end:0;top:calc(100% + 8px);background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:var(--shadow-md);padding:6px;min-width:140px;display:none;z-index:60}
      .lang-menu.open{display:block}
      .lang-menu a{display:block;padding:8px 12px;border-radius:8px;font-size:.92rem;color:var(--ink)}
      .lang-menu a:hover{background:var(--cream-deep)}
      .nav-login{font-weight:600;color:var(--ink);font-size:.97rem}
      .nav-login:hover{color:var(--green-700)}
      .menu-toggle{display:none;background:none;border:1px solid var(--line);border-radius:10px;width:42px;height:42px;cursor:pointer}

      /* Hero */
      .hero{position:relative;padding-top:clamp(40px,6vw,72px);overflow:hidden}
      .hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:54px;align-items:center}
      .eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--cream-deep);color:var(--green-700);font-weight:600;font-size:.86rem;padding:7px 15px;border-radius:999px;margin-bottom:22px}
      .eyebrow .dot{width:8px;height:8px;border-radius:50%;background:var(--saffron-deep)}
      .hero h1{font-size:clamp(2.3rem,5vw,3.65rem);font-weight:700;color:var(--green-900);letter-spacing:-.5px}
      .hero h1 .hl{position:relative;color:var(--saffron-deep);white-space:nowrap}
      .hero h1 .hl::after{content:"";position:absolute;inset-inline:-2px;bottom:6px;height:12px;z-index:-1;background:rgba(242,163,60,.28);border-radius:4px}
      .hero p.lead{font-size:clamp(1.05rem,2vw,1.22rem);color:var(--ink-soft);margin:22px 0 12px;max-width:30em}
      .hero .punch{font-weight:600;color:var(--ink);font-size:1.12rem;margin-bottom:26px}
      .hero-cta{display:flex;flex-wrap:wrap;gap:14px;align-items:center}
      .hero-form{margin-top:22px;display:flex;flex-direction:column;gap:12px;max-width:30em}
      .hero-form input{font-family:var(--body);font-size:1rem;padding:13px 16px;border:1.5px solid var(--line);border-radius:14px;background:#fff;color:var(--ink)}
      .hero-form input:focus{outline:none;border-color:var(--green-500)}
      .alert{background:#E7F6EC;border:1px solid #BfE6CC;color:#1F8A5B;padding:12px 16px;border-radius:12px;font-size:.95rem;margin-bottom:6px}
      .micro{display:flex;align-items:center;gap:8px;color:var(--ink-soft);font-size:.92rem;margin-top:18px}
      .micro svg{flex-shrink:0}

      .hero-visual{position:relative;display:grid;place-items:center;min-height:480px}
      .blob{position:absolute;width:430px;height:430px;border-radius:46% 54% 60% 40%/52% 44% 56% 48%;background:radial-gradient(circle at 30% 30%, #2C82C4, var(--green-900));filter:blur(2px);opacity:.96;z-index:0;animation:morph 14s ease-in-out infinite}
      @keyframes morph{0%,100%{border-radius:46% 54% 60% 40%/52% 44% 56% 48%}50%{border-radius:58% 42% 44% 56%/46% 58% 42% 54%}}
      .phone{position:relative;z-index:2;width:268px;height:540px;border-radius:42px;background:#0a223a;padding:12px;box-shadow:var(--shadow-lg);border:1px solid rgba(255,255,255,.08)}
      .phone-screen{height:100%;border-radius:32px;background:var(--bg);overflow:hidden;display:flex;flex-direction:column}
      .ps-head{background:var(--green-900);color:#fff;padding:18px 16px 14px;text-align:center}
      .ps-head .logo{width:34px;height:34px;margin-inline:auto;border-radius:9px;background:var(--saffron);color:var(--green-900);display:grid;place-items:center;font-family:var(--display);font-weight:700;margin-bottom:7px}
      .ps-head b{font-family:var(--display);font-size:1.02rem}
      .ps-head span{display:block;font-size:.7rem;opacity:.7}
      .ps-tabs{display:flex;gap:7px;padding:11px 13px;overflow:hidden}
      .ps-tabs i{font-style:normal;font-size:.66rem;padding:5px 11px;border-radius:999px;background:var(--cream-deep);color:var(--ink-soft);white-space:nowrap}
      .ps-tabs i.on{background:var(--green-900);color:#fff}
      .ps-item{display:flex;gap:10px;padding:10px 13px;align-items:center;border-bottom:1px solid var(--line)}
      .ps-thumb{width:42px;height:42px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,#F4C77A,var(--saffron-deep))}
      .ps-thumb.b{background:linear-gradient(135deg,#7fc4ae,var(--green-700))}
      .ps-thumb.c{background:linear-gradient(135deg,#f0a89b,var(--coral))}
      .ps-meta b{font-size:.74rem;display:block}
      .ps-meta span{font-size:.64rem;color:var(--ink-soft)}
      .ps-price{margin-inline-start:auto;font-size:.72rem;font-weight:700;color:var(--green-700)}
      .qr-tag{position:absolute;z-index:3;bottom:42px;inset-inline-start:-26px;background:#fff;border-radius:16px;padding:13px;box-shadow:var(--shadow-md);display:flex;align-items:center;gap:11px;animation:float 5s ease-in-out infinite}
      @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
      .qr-tag .qr{width:50px;height:50px;border-radius:9px;background:conic-gradient(from 0deg,#13202E 0 25%,#fff 0 50%,#13202E 0 75%,#fff 0)/14px 14px,#13202E;background-size:14px 14px;border:3px solid #13202E}
      .qr-tag small{font-size:.66rem;color:var(--ink-soft);display:block}
      .qr-tag b{font-size:.82rem;color:var(--green-900)}
      .order-tag{position:absolute;z-index:3;top:54px;inset-inline-end:-18px;background:var(--green-900);color:#fff;border-radius:14px;padding:11px 15px;box-shadow:var(--shadow-md);display:flex;align-items:center;gap:9px;font-size:.8rem;font-weight:600;animation:float 5s ease-in-out infinite .8s}
      .order-tag .ping{width:9px;height:9px;border-radius:50%;background:var(--saffron);box-shadow:0 0 0 0 rgba(242,163,60,.6);animation:ping 1.8s infinite}
      @keyframes ping{0%{box-shadow:0 0 0 0 rgba(242,163,60,.6)}70%{box-shadow:0 0 0 10px rgba(242,163,60,0)}100%{box-shadow:0 0 0 0 rgba(242,163,60,0)}}

      /* Trust */
      .trust{background:var(--green-900);color:#fff}
      .trust-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;text-align:center}
      .trust-grid .num{font-family:var(--display);font-size:clamp(1.8rem,4vw,2.6rem);font-weight:700;color:var(--saffron)}
      .trust-grid .lbl{font-size:.92rem;opacity:.82;margin-top:2px}

      /* Section header */
      .sec-head{max-width:640px;margin-inline:auto;text-align:center;margin-bottom:54px}
      .sec-head .tag{color:var(--saffron-deep);font-weight:600;font-size:.92rem;letter-spacing:.5px;margin-bottom:10px}
      .sec-head h2{font-size:clamp(1.8rem,4vw,2.6rem);font-weight:700;color:var(--green-900)}
      .sec-head p{color:var(--ink-soft);margin-top:14px;font-size:1.06rem}

      /* How */
      .steps{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;position:relative}
      .step{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:30px 26px;box-shadow:var(--shadow-sm);position:relative}
      .step .n{font-family:var(--display);font-weight:700;font-size:1.05rem;width:42px;height:42px;border-radius:12px;background:var(--cream-deep);color:var(--green-900);display:grid;place-items:center;margin-bottom:18px}
      .step h3{font-size:1.22rem;color:var(--green-900);margin-bottom:8px}
      .step p{color:var(--ink-soft);font-size:.97rem}

      /* Features */
      .features{background:var(--cream-deep)}
      .feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
      .feat{background:var(--surface);border-radius:var(--radius);padding:30px 26px;border:1px solid var(--line);box-shadow:var(--shadow-sm);transition:transform .2s ease, box-shadow .2s ease}
      .feat:hover{transform:translateY(-5px);box-shadow:var(--shadow-md)}
      .feat .ic{width:52px;height:52px;border-radius:14px;display:grid;place-items:center;margin-bottom:18px;background:linear-gradient(150deg,#fff5e6,#fbe3c2)}
      .feat .ic svg{width:26px;height:26px;stroke:var(--saffron-deep)}
      .feat h3{font-size:1.2rem;color:var(--green-900);margin-bottom:9px}
      .feat p{color:var(--ink-soft);font-size:.96rem;margin-bottom:14px}
      .feat ul{list-style:none;display:flex;flex-direction:column;gap:8px}
      .feat li{display:flex;gap:9px;align-items:flex-start;font-size:.92rem;color:var(--ink)}
      .feat li svg{width:18px;height:18px;flex-shrink:0;margin-top:4px;stroke:var(--green-500)}

      /* Pricing */
      .price-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;align-items:stretch}
      .plan{position:relative;background:var(--surface);border:1px solid var(--line);border-radius:22px;padding:34px 30px;display:flex;flex-direction:column;box-shadow:var(--shadow-sm);transition:transform .2s, box-shadow .2s}
      .plan:hover{transform:translateY(-4px);box-shadow:var(--shadow-md)}
      .plan.featured{border:2px solid var(--saffron);box-shadow:var(--shadow-md);transform:scale(1.02)}
      .plan.featured:hover{transform:scale(1.02) translateY(-4px)}
      .badge-pop{position:absolute;top:-14px;inset-inline-start:50%;transform:translateX(50%);background:var(--saffron-deep);color:#fff;font-size:.8rem;font-weight:600;padding:6px 16px;border-radius:999px;box-shadow:0 6px 16px rgba(226,125,38,.35)}
      .plan .pname{font-family:var(--display);font-weight:700;font-size:1.45rem;color:var(--green-900)}
      .plan .pdesc{color:var(--ink-soft);font-size:.92rem;margin:6px 0 20px;min-height:42px}
      .plan .pprice{display:flex;align-items:baseline;gap:6px;margin-bottom:4px}
      .plan .pprice .cur{font-size:1rem;color:var(--ink-soft);font-weight:600}
      .plan .pprice .amt{font-family:var(--display);font-size:2.7rem;font-weight:700;color:var(--green-900)}
      .plan .pprice .per{color:var(--ink-soft);font-size:.95rem}
      .perf{border:none;border-top:2px dashed var(--line);margin:22px -30px;position:relative}
      .perf::before,.perf::after{content:"";position:absolute;top:-9px;width:18px;height:18px;border-radius:50%;background:var(--bg)}
      .perf::before{inset-inline-start:-9px}
      .perf::after{inset-inline-end:-9px}
      .plan ul{list-style:none;display:flex;flex-direction:column;gap:11px;margin-bottom:26px;flex:1}
      .plan li{display:flex;gap:9px;font-size:.95rem;color:var(--ink)}
      .plan li svg{width:19px;height:19px;flex-shrink:0;margin-top:4px;stroke:var(--green-500)}
      .plan .btn{width:100%}

      /* Testimonials */
      .testimonials{background:var(--green-900);color:#fff}
      .testimonials .sec-head h2{color:#fff}
      .testimonials .sec-head .tag{color:var(--saffron)}
      .testimonials .sec-head p{color:rgba(255,255,255,.72)}
      .t-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:24px}
      .tcard{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius);padding:28px}
      .tcard .stars{color:var(--saffron);font-size:1rem;letter-spacing:3px;margin-bottom:14px}
      .tcard q{display:block;font-size:1.05rem;line-height:1.85;color:#fff;quotes:none}
      .tcard q::before,.tcard q::after{content:none}
      .twho{display:flex;align-items:center;gap:13px;margin-top:20px}
      .tav{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;font-family:var(--display);font-weight:700;font-size:1.05rem;color:#fff;flex-shrink:0}
      .twho b{display:block;font-size:.98rem}
      .twho span{font-size:.85rem;color:rgba(255,255,255,.65)}

      /* Demo */
      .demo-card{background:var(--surface);border-radius:26px;border:1px solid var(--line);box-shadow:var(--shadow-md);display:grid;grid-template-columns:1fr 1fr;gap:0;overflow:hidden}
      .demo-left{padding:clamp(34px,5vw,56px)}
      .demo-left h2{font-size:clamp(1.7rem,3.5vw,2.4rem);color:var(--green-900);margin-bottom:14px}
      .demo-left p{color:var(--ink-soft);font-size:1.05rem;margin-bottom:26px}
      .demo-steps{list-style:none;display:flex;flex-direction:column;gap:14px;margin-bottom:30px}
      .demo-steps li{display:flex;gap:12px;align-items:center;font-weight:500}
      .demo-steps .c{width:30px;height:30px;border-radius:9px;background:var(--cream-deep);color:var(--green-900);display:grid;place-items:center;font-weight:700;flex-shrink:0;font-family:var(--display)}
      .demo-right{background:linear-gradient(150deg,var(--green-700),var(--green-900));display:grid;place-items:center;padding:40px;position:relative}
      .qr-frame{background:#fff;border-radius:22px;padding:26px;text-align:center;box-shadow:var(--shadow-lg)}
      .qr-frame img{width:200px;height:200px;margin-inline:auto;border-radius:12px}
      .qr-big{width:180px;height:180px;margin-inline:auto;border-radius:12px;background:conic-gradient(from 0deg,#13202E 0 25%,#fff 0 50%,#13202E 0 75%,#fff 0)/24px 24px,#13202E;background-size:24px 24px;border:8px solid #fff;outline:3px solid #13202E;outline-offset:-3px}
      .qr-frame p{margin-top:16px;font-size:.92rem;color:var(--ink-soft)}
      .qr-frame .scan{display:inline-flex;gap:7px;align-items:center;color:var(--green-700);font-weight:600;font-size:.9rem;margin-top:4px}

      /* Final CTA */
      .finalcta{text-align:center}
      .finalcta .box{background:linear-gradient(150deg,#155083,var(--green-900));color:#fff;border-radius:28px;padding:clamp(44px,6vw,72px) 24px;box-shadow:var(--shadow-lg);position:relative;overflow:hidden}
      .finalcta .box::before{content:"";position:absolute;width:300px;height:300px;border-radius:50%;background:rgba(242,163,60,.16);top:-120px;inset-inline-start:-80px;filter:blur(10px)}
      .finalcta h2{font-size:clamp(1.9rem,4vw,2.8rem);position:relative}
      .finalcta p{color:rgba(255,255,255,.8);max-width:34em;margin:16px auto 30px;font-size:1.1rem;position:relative}
      .finalcta .micro{justify-content:center;color:rgba(255,255,255,.7)}
      .finalcta .micro svg{stroke:var(--saffron)}

      /* Footer */
      footer{background:var(--bg);border-top:1px solid var(--line);padding-block:54px 28px}
      .foot-grid{display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr;gap:34px;margin-bottom:40px}
      .foot-brand .brand{margin-bottom:14px}
      .foot-brand p{color:var(--ink-soft);font-size:.95rem;max-width:26em}
      .pay{display:flex;gap:10px;margin-top:20px;align-items:center;flex-wrap:wrap}
      .pay span{font-size:.78rem;color:var(--ink-soft)}
      .pay .chip{border:1px solid var(--line);border-radius:7px;padding:4px 9px;font-size:.72rem;font-weight:700;color:var(--ink-soft);background:#fff}
      .foot-col h4{font-size:.92rem;color:var(--green-900);margin-bottom:15px;font-weight:700}
      .foot-col a{display:block;color:var(--ink-soft);font-size:.93rem;margin-bottom:10px;transition:color .15s}
      .foot-col a:hover{color:var(--green-700)}
      .foot-bottom{border-top:1px solid var(--line);padding-top:22px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;color:var(--ink-soft);font-size:.88rem}

      /* Reveal */
      .reveal{opacity:0;transform:translateY(26px);transition:opacity .6s ease, transform .6s ease}
      .reveal.in{opacity:1;transform:none}

      /* Responsive */
      @media(max-width:920px){
        .hero-grid{grid-template-columns:1fr;gap:30px}
        .hero-visual{order:-1;min-height:430px}
        .trust-grid{grid-template-columns:repeat(2,1fr);gap:30px}
        .steps,.feat-grid,.price-grid,.t-grid{grid-template-columns:1fr}
        .plan.featured{transform:none}
        .plan.featured:hover{transform:translateY(-4px)}
        .demo-card{grid-template-columns:1fr}
        .demo-right{order:-1}
        .foot-grid{grid-template-columns:1fr 1fr;gap:26px}
      }
      @media(max-width:680px){
        .nav-links,.nav-login{display:none}
        .menu-toggle{display:grid;place-items:center}
        .nav-actions{gap:9px}
        .foot-grid{grid-template-columns:1fr}
        .feat-grid{gap:18px}
      }
      @media(prefers-reduced-motion:reduce){
        *{animation:none!important}
        .reveal{opacity:1;transform:none;transition:none}
        html{scroll-behavior:auto}
      }
      :focus-visible{outline:3px solid var(--saffron-deep);outline-offset:2px;border-radius:6px}
    </style>
</head>

<body>

    {{-- NAV --}}
    @include('qrsaas.partials.nav')

    {{-- HERO --}}
    @include('qrsaas.partials.hero')

    {{-- TRUST STRIP --}}
    @include('qrsaas.partials.trust')

    {{-- HOW IT WORKS --}}
    @include('qrsaas.partials.how')

    {{-- FEATURES / PRODUCT --}}
    @include('qrsaas.partials.product')

    {{-- PRICING --}}
    @include('qrsaas.partials.pricing')

    {{-- TESTIMONIALS --}}
    @include('qrsaas.partials.testimonials')

    {{-- DEMO --}}
    @include('qrsaas.partials.demo')

    {{-- FINAL CTA --}}
    @include('qrsaas.partials.finalcta')

    {{-- FOOTER --}}
    @include('qrsaas.partials.footer')

    <script>
      // Nav shadow on scroll
      const nav = document.getElementById('nav');
      if (nav) window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 12));
      // Language dropdown
      const langBtn = document.getElementById('langBtn');
      const langMenu = document.getElementById('langMenu');
      if (langBtn && langMenu) {
        langBtn.addEventListener('click', e => { e.stopPropagation(); langMenu.classList.toggle('open'); });
        document.addEventListener('click', () => langMenu.classList.remove('open'));
      }
      // Reveal on scroll
      const io = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
      }, {threshold:0.12});
      document.querySelectorAll('.reveal').forEach(el => io.observe(el));
    </script>
</body>
</html>
