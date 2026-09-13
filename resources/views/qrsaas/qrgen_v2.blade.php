{{--
    iMenu 2026 — صفحة رمز QR ومطبوعات الكوفي (الإصدار الثاني).
    تُعرض حين settings.qr_page_v2 = true. الصفحة الأصلية (qrgen.blade.php + React) باقية
    كما هي؛ QR_PAGE_V2=false في .env يعيدها. المولّد محلي (public/imenu/qr) — لا خدمة خارجية.
    المعاينة المعتمدة: Artifact «صفحة QR ومطبوعات الكوفي» — ١٣ سبتمبر ٢٠٢٦.
--}}
@extends('layouts.app', ['title' => __('QR')])

@section('head')
    <link rel="stylesheet" href="{{ asset('imenu/qr/qr-page.css') }}?v=1">
@endsection

@section('content')
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8"></div>
<div class="container-fluid mt--7">
  <div class="card shadow" style="border-radius: 22px; background: #FAF6EF; border: 0;">
    <div class="card-body" style="padding: 32px;">

<div id="iq" class="iq" data-config='@json($config)'>

  {{-- الترويسة --}}
  <div class="iq-head">
    <div>
      <h1>{{ __('QR code and cafe prints') }}</h1>
      <p>{{ __('The code opens the menu of') }} <strong>{{ $vendor->name }}</strong>. {{ __('Pick its shape and color, download it in any format, or print ready-made materials with your name and code.') }}</p>
    </div>
    <div class="iq-link">
      <span class="url">{{ preg_replace('#^https?://#', '', $url) }}</span>
      <button type="button" class="iq-copy" id="iq-copy">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
        <span>{{ __('Copy') }}</span>
      </button>
    </div>
  </div>

  {{-- البطاقة الأولى: الرمز --}}
  <div class="iq-card iq-gen">
    <div class="iq-controls">

      <div class="iq-section">
        <div class="iq-label">{{ __('Shape') }}</div>
        <div class="iq-styles">
          <button type="button" class="iq-style" data-style="classic">
            <span class="tick"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
            <div class="thumb" data-qr="classic" data-w="200"></div>
            <span class="name">{{ __('Classic') }}</span>
          </button>
          <button type="button" class="iq-style" data-style="dots">
            <span class="tick"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
            <div class="thumb" data-qr="dots" data-w="200"></div>
            <span class="name">{{ __('Dots') }}</span>
          </button>
          @if($hasLogo)
          <button type="button" class="iq-style" data-style="logo">
            <span class="tick"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
            <div class="thumb" data-qr="logo" data-w="200"></div>
            <span class="name">{{ __('With cafe logo') }}</span>
          </button>
          @endif
        </div>
        @unless($hasLogo)
          <div class="iq-hint">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            <span>{{ __('Upload a cafe logo from the restaurant settings to unlock the code with your logo in the middle.') }}</span>
          </div>
        @endunless
      </div>

      <div class="iq-section">
        <div class="iq-label">{{ __('Color') }}</div>
        <div class="iq-swatches">
          <button type="button" class="iq-swatch" data-preset="navy"><span class="dot" style="background:#16304C"></span><span>{{ __('Navy') }}</span></button>
          <button type="button" class="iq-swatch" data-preset="orange"><span class="dot" style="background:#E8952F"></span><span>{{ __('Orange') }}</span></button>
          <button type="button" class="iq-swatch" data-preset="black"><span class="dot" style="background:#111111"></span><span>{{ __('Black') }}</span></button>
          <button type="button" class="iq-swatch" data-preset="dual"><span class="dot" style="background:linear-gradient(135deg,#16304C 50%,#E8952F 50%)"></span><span>{{ __('Navy and orange') }}</span></button>
          <label class="iq-swatch" data-preset="custom" style="position: relative;">
            <span class="dot custom"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
            <span>{{ __('Custom') }}</span>
            <input type="color" value="#16304C">
          </label>
        </div>
        <div class="iq-hint" id="iq-color-hint" data-icon='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>'>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
          <span>{{ __('Light colors are rejected automatically — the code must be readable on glass under the sun.') }}</span>
        </div>
      </div>

    </div>

    <div class="iq-preview">
      <div class="iq-plate" data-qr="current" data-w="640"></div>
      <div class="iq-url">{{ $url }}</div>
      <div class="iq-actions">
        <button type="button" class="iq-btn primary" data-download="png">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
          <span>{{ __('PNG for print') }}</span>
        </button>
        <button type="button" class="iq-btn outline" data-download="svg">SVG</button>
        <button type="button" class="iq-btn outline" data-download="jpeg">JPG</button>
      </div>
      <div class="iq-note">{{ __('PNG at 3000 px — enough for an A3 poster without artifacts') }}</div>
    </div>
  </div>

  {{-- البطاقة الثانية: المطبوعات --}}
  <div class="iq-section" style="gap: 16px;">
    <div class="iq-templates-head">
      <div>
        <h2>{{ __('Ready-to-print materials') }}</h2>
        <p>{{ __('Your cafe name and code are inside. Print them from the browser — no software, no designer.') }}</p>
      </div>
    </div>

    <div class="iq-templates">

      <div class="iq-tpl">
        <div class="art">
          <div class="band"><small>{{ $vendor->name }}</small><b>{{ __('Next time, don\'t wait in line') }}</b></div>
          <div class="mid"><div class="mini" data-qr="dots" data-w="72" data-src="window"></div></div>
        </div>
        <div class="meta">
          <div class="row"><b>{{ __('Window sticker') }}</b><span>A5</span></div>
          <div class="msg">«{{ __('Next time, don\'t wait in line') }}»</div>
        </div>
        <div class="foot">
          <a class="iq-btn outline sm" target="_blank" rel="noopener" data-print="window" href="{{ route('qr.print', 'window') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
            <span>{{ __('Print') }}</span>
          </a>
          <span class="iq-src">?src=window</span>
        </div>
      </div>

      <div class="iq-tpl">
        <div class="art">
          <div class="mid col">
            <div class="h" style="font-size: 15px;">{{ __('Order now, pick up ready') }}</div>
            <span class="rule"></span>
            <div class="mini" data-qr="dots" data-w="72" data-src="counter"></div>
          </div>
        </div>
        <div class="meta">
          <div class="row"><b>{{ __('Counter card') }}</b><span>A6</span></div>
          <div class="msg">«{{ __('Order now, pick up ready') }}»</div>
        </div>
        <div class="foot">
          <a class="iq-btn outline sm" target="_blank" rel="noopener" data-print="counter" href="{{ route('qr.print', 'counter') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
            <span>{{ __('Print') }}</span>
          </a>
          <span class="iq-src">?src=counter</span>
        </div>
      </div>

      <div class="iq-tpl">
        <div class="art">
          <div class="mid row">
            <div style="display:flex;flex-direction:column;gap:6px;">
              <span class="chip">{{ __('Car pickup') }}</span>
              <div class="h" style="font-size: 20px; text-align: start;">{{ __('From the car?') }}</div>
              <div class="sub">{{ __('Order here and we bring it to you') }}</div>
            </div>
            <div class="mini" data-qr="dots" data-w="72" data-src="car"></div>
          </div>
        </div>
        <div class="meta">
          <div class="row"><b>{{ __('Car sign') }}</b><span>A5 {{ __('Landscape') }}</span></div>
          <div class="msg">«{{ __('From the car? Order here') }}»</div>
        </div>
        <div class="foot">
          <a class="iq-btn outline sm" target="_blank" rel="noopener" data-print="car" href="{{ route('qr.print', 'car') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
            <span>{{ __('Print') }}</span>
          </a>
          <span class="iq-src">?src=car</span>
        </div>
      </div>

      <div class="iq-tpl">
        <div class="art sheet">
          @for($i = 0; $i < 6; $i++)
            <div class="cell"><div data-qr="classic" data-w="30" data-src="cup"></div><span>{{ __('Follow the cafe') }}</span></div>
          @endfor
        </div>
        <div class="meta">
          <div class="row"><b>{{ __('Cup sticker') }}</b><span>{{ __('4×4 cm · 15 pieces') }}</span></div>
          <div class="msg">«{{ __('Follow the cafe') }}»</div>
        </div>
        <div class="foot">
          <a class="iq-btn outline sm" target="_blank" rel="noopener" data-print="cup" href="{{ route('qr.print', 'cup') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
            <span>{{ __('Print') }}</span>
          </a>
          <span class="iq-src">?src=cup</span>
        </div>
      </div>

    </div>
  </div>

  {{-- تنبيه اللاحقة --}}
  <div class="iq-callout">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
    <span>{{ __('Every print carries the same link with a different suffix') }} (<code>?src=</code>). {{ __('Nothing changes for the customer — but after a month you know which spot actually brings customers: the window, the counter, the cup, or Google Maps.') }}</span>
  </div>

</div>

    </div>
  </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('imenu/qr/qr-code-styling.min.js') }}?v=1.9.2"></script>
    <script src="{{ asset('imenu/qr/qr-page.js') }}?v=1"></script>
@endsection
