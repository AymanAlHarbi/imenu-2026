{{--
    iMenu 2026 — مطبوعات الكوفي الجاهزة للطباعة.
    صفحة مستقلة (بلا قالب اللوحة) تُفتح من صفحة QR وتُطبع من المتصفح مباشرة (Ctrl+P).
    $template: window | counter | car | cup — والرمز يحمل ?src=<template> لقياس مصدر العميل.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — {{ $vendor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('imenu/qr/qr-print.css') }}?v=1">
    <style>@page { size: {{ $page }}; margin: 0; }</style>
</head>
<body class="t-{{ $template }}" id="iqp" data-config='@json($config)'>

<div class="bar">
    <div class="title">{{ $title }} <small>{{ $size }} · {{ $vendor->name }}</small></div>
    <div class="actions">
        <a class="btn outline" href="{{ route('qr') }}">{{ __('Back') }}</a>
        <button type="button" class="btn primary" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
            <span>{{ __('Print') }}</span>
        </button>
    </div>
</div>

<div class="stage">

@if($template === 'window')
    <div class="sheet">
        <div class="band">
            <span class="c1"></span><span class="c2"></span>
            <div class="cafe">{{ $vendor->name }}</div>
            <h1>{{ __('Next time,') }}<br>{{ __('don\'t wait in line') }}</h1>
        </div>
        <div class="body">
            <p class="lead">{{ __('Order before you arrive, and pick it up ready') }}<br>{{ __('from the window or your car.') }}</p>
            <div class="qr-plate" data-qr></div>
            <div class="scan">
                <b>{{ __('Scan the code with your phone camera') }}</b>
                <span>{{ __('Pay on pickup · no app download') }}</span>
            </div>
            @include('qrsaas.partials.print_foot')
        </div>
    </div>

@elseif($template === 'counter')
    <div class="sheet">
        <div class="top">
            <span class="cafe">{{ $vendor->name }}</span>
            <h1>{{ __('Order now') }}<br>{{ __('and pick up ready') }}</h1>
            <span class="rule"></span>
        </div>
        <div class="qr-plate" data-qr></div>
        <div class="scan">
            <b>{{ __('Scan and browse the menu from your phone') }}</b>
            <span>{{ __('Pay on pickup') }}</span>
        </div>
        @include('qrsaas.partials.print_foot')
    </div>

@elseif($template === 'car')
    <div class="sheet">
        <div class="main">
            <div class="text">
                <span class="chip">{{ __('Car pickup') }}</span>
                <h1>{{ __('From the car?') }}</h1>
                <p class="lead">{{ __('Order here and we bring it to your car.') }}</p>
                <p class="sub">{{ __('Write your car type and color in the order, and wait where you are.') }}</p>
            </div>
            <div class="side">
                <div class="qr-plate" data-qr></div>
                <b>{{ __('Scan the code with your phone camera') }}</b>
            </div>
        </div>
        @include('qrsaas.partials.print_foot')
    </div>

@else
    <div class="sheet">
        <div class="head">
            <b>{{ __('Cup sticker') }}</b>
            <span>{{ __('4×4 cm · 15 per sheet · cut along the dashed line') }}</span>
        </div>
        <div class="grid">
            @for($i = 0; $i < 15; $i++)
                <div class="cell">
                    <div class="qr-plate" data-qr></div>
                    <b>{{ __('Follow the cafe') }}</b>
                    <span>{{ $vendor->name }}</span>
                </div>
            @endfor
        </div>
        @include('qrsaas.partials.print_foot')
    </div>
@endif

</div>

<script src="{{ asset('imenu/qr/qr-code-styling.min.js') }}?v=1.9.2"></script>
<script>
(function () {
    var cfg = JSON.parse(document.getElementById('iqp').getAttribute('data-config'));
    if (typeof QRCodeStyling === 'undefined') { return; }
    var classic = cfg.style === 'classic';
    var logo = cfg.style === 'logo' && cfg.logo;
    var width = cfg.template === 'cup' ? 600 : 1200;
    var o = {
        width: width, height: width, type: 'svg', data: cfg.data, margin: 0,
        qrOptions: { errorCorrectionLevel: logo ? 'H' : 'M' },
        dotsOptions: { color: cfg.c, type: classic ? 'square' : 'dots' },
        cornersSquareOptions: { color: cfg.c2, type: classic ? 'square' : 'extra-rounded' },
        cornersDotOptions: { color: cfg.c2, type: classic ? 'square' : 'dot' },
        backgroundOptions: { color: '#FFFFFF' }
    };
    if (logo) {
        o.image = cfg.logo;
        o.imageOptions = { crossOrigin: 'anonymous', margin: Math.round(width * 0.012), imageSize: 0.2, hideBackgroundDots: true };
    }
    Array.prototype.forEach.call(document.querySelectorAll('[data-qr]'), function (el) {
        new QRCodeStyling(o).append(el);
    });
})();
</script>
</body>
</html>
