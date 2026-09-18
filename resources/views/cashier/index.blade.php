{{-- شاشة الكاشير — تابلت المقهى · مواصفة imenu-cashier-screen-spec.md --}}
@extends('layouts.empty')

@section('content')
<style>
:root{
  --primary:#FF721C; --bg:#FFF6ED; --card:#FFFFFF; --row:#FFF0E2;
  --ink:#171513; --ink2:#5A4A3F; --muted:#A08977; --faint:#C9B6A6;
  --ready:#2E5C43; --readydot:#A9E0BF; --wait:#D8A15E; --danger:#C0392B;
}
/* خط عربي واحد + أرقام مصفوفة. لا خط أحادي المسافة لاتينيًا — لا يدعم العربية. */
.ck, .ck *{
  font-family:"Segoe UI","Noto Naskh Arabic","Noto Sans Arabic",Tahoma,Arial,sans-serif;
  font-feature-settings:"tnum" 1; font-variant-numeric:tabular-nums;
}
.ck{background:var(--bg); color:var(--ink); min-height:100vh; padding:14px 18px 40px;}

/* ── شريط الحالة: حالة المقهى، لا إجراء على طلب ── */
.ck-bar{display:flex; flex-wrap:wrap; align-items:center; gap:22px;
  background:var(--card); border-radius:14px; padding:14px 18px; margin-bottom:16px;
  box-shadow:0 1px 3px rgba(23,21,19,.08);}
.ck-bar h1{font-size:20px; font-weight:700; margin:0 0 0 0;}
.ck-lbl{display:block; font-size:12px; color:var(--muted); margin-bottom:3px;}
.ck-big{font-size:30px; font-weight:700; line-height:1;}
.ck-slice{min-height:44px; min-width:56px; border:2px solid var(--primary); background:transparent;
  color:var(--primary); border-radius:10px; font-size:17px; font-weight:700; margin-inline-end:6px; cursor:pointer;}
.ck-slice.on{background:var(--primary); color:#fff;}
.ck-slice.auto{border-color:var(--ink2); color:var(--ink2);}
.ck-slice.auto.on{background:var(--ink2); color:#fff;}
.ck-toggle{min-height:56px; padding:0 20px; border-radius:12px; border:0; cursor:pointer;
  font-size:17px; font-weight:700; color:#fff;}
.ck-toggle.on{background:var(--ready);} .ck-toggle.off{background:var(--faint); color:var(--ink);}

/* ── البطاقة: اللون هو الواجهة، شريط جانبي ٧ بكسل يُقرأ من مترين ── */
.ck-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(330px,1fr)); gap:14px;}
.ck-card{position:relative; background:var(--card); border-radius:14px; padding:16px 18px 16px 24px;
  box-shadow:0 1px 3px rgba(23,21,19,.10); overflow:hidden;}
.ck-card::before{content:""; position:absolute; inset-inline-start:0; top:0; bottom:0; width:7px; background:var(--faint);}
.ck-card.s-new::before{background:var(--primary);}
.ck-card.s-preparing::before{background:var(--wait);}
.ck-card.s-late::before{background:var(--danger);}
.ck-card.s-late{border:2px solid var(--danger);}
.ck-card.s-ready::before{background:var(--ready);}
.ck-card.s-not_collected{opacity:.55;}
.ck-card.s-not_collected::before{background:var(--faint);}

/* العميل وصل: بطاقة منقلبة داكنة، عرض كامل، تتصدّر الشاشة */
.ck-card.s-arrived{grid-column:1/-1; background:var(--ready); color:#fff;}
.ck-card.s-arrived::before{background:var(--readydot); width:9px;}
.ck-card.s-arrived .ck-lbl, .ck-card.s-arrived .ck-sub{color:rgba(255,255,255,.82);}

.ck-id{font-size:26px; font-weight:700; line-height:1.1;}
.ck-sub{font-size:13px; color:var(--muted);}
.ck-count{font-size:34px; font-weight:700; line-height:1;}
.ck-count.late{color:var(--danger);}
.ck-pill{display:inline-block; padding:3px 11px; border-radius:999px; font-size:12px; font-weight:700;}

/* بيانات السيارة — ما يخرج به العامل. لا اسم العميل. */
.ck-car{display:flex; align-items:center; gap:10px; margin:12px 0 4px;
  background:var(--row); border-radius:10px; padding:10px 12px;}
.ck-card.s-arrived .ck-car{background:rgba(255,255,255,.14);}
.ck-swatch{width:34px; height:22px; border-radius:5px; border:1px solid rgba(23,21,19,.22); flex:0 0 auto;}
.ck-plate{font-size:21px; font-weight:700; letter-spacing:.5px;}

/* الأصناف — تُقرأ من مترين: الكمية والاسم كبيران، والتفاصيل تحتهما */
.ck-items{list-style:none; margin:12px 0 0; padding:0;}
.ck-items li{padding:7px 0; border-top:1px solid var(--row);}
.ck-items li:first-child{border-top:0;}
.ck-qty{display:inline-block; min-width:38px; font-size:22px; font-weight:700; color:var(--primary);}
.ck-name{font-size:20px; font-weight:700;}
.ck-opt{display:block; margin-inline-start:38px; font-size:14px; color:var(--ink2);}
.ck-card.s-arrived .ck-items li{border-color:rgba(255,255,255,.22);}
.ck-card.s-arrived .ck-qty{color:var(--readydot);}
.ck-card.s-arrived .ck-opt{color:rgba(255,255,255,.85);}
.ck-note{margin-top:10px; background:var(--row); border-radius:8px; padding:8px 11px; font-size:15px;}
.ck-card.s-arrived .ck-note{background:rgba(255,255,255,.16);}

.ck-head{display:flex; justify-content:space-between; align-items:flex-start; gap:10px;}
.ck-pill.way{background:var(--row); color:var(--ink2); margin-top:4px;}
.ck-card.s-arrived .ck-pill.way{background:rgba(255,255,255,.18); color:#fff;}
.ck-car.big{padding:14px 16px;}
.ck-swatch.big{width:46px; height:30px;}
.ck-carname{font-weight:700; font-size:21px;}
.ck-plate.big{font-size:28px;}
.ck-amount{display:flex; align-items:baseline; gap:10px; margin-top:12px;
  background:var(--row); border-radius:10px; padding:10px 13px;}
.ck-amount .ck-lbl{margin:0;}
.ck-money{font-size:30px; font-weight:700; color:var(--ink);}
.ck-money.paid{color:var(--ready);}
.ck-amount.small{background:transparent; padding:8px 0 0; margin-top:8px; border-top:1px solid var(--row);}
.ck-amount.small .ck-money{font-size:19px; color:var(--ink2);}
.ck-card.s-arrived .ck-amount{background:rgba(255,255,255,.18);}
.ck-card.s-arrived .ck-money{color:#fff;}
.ck-card.s-arrived .ck-money.paid{color:var(--readydot);}
.ck-oneline{margin-top:10px; font-size:16px; color:var(--ink2);}
.ck-card.s-arrived .ck-oneline{color:rgba(255,255,255,.85);}
.ck-act{display:flex; flex-wrap:wrap; gap:8px; margin-top:14px;}
.ck-btn{min-height:56px; padding:0 22px; border:0; border-radius:12px; cursor:pointer;
  font-size:18px; font-weight:700; color:#fff; background:var(--primary);}
.ck-btn.ready{background:var(--ready);}
.ck-btn.huge{min-height:78px; font-size:24px; flex:1;}
.ck-btn.ghost{background:transparent; color:var(--ink2); border:2px solid var(--faint); font-size:16px; padding:0 16px;}
.ck-card.s-arrived .ck-btn.ghost{color:#fff; border-color:rgba(255,255,255,.5);}
.ck-empty{text-align:center; padding:70px 20px; color:var(--muted); font-size:19px;}
form.ck-f{display:inline; margin:0;}
</style>

<div class="ck" dir="rtl">

  {{-- شريط الحالة --}}
  <div class="ck-bar">
    <h1>{{ $vendor->name }}</h1>

    <div>
      <span class="ck-lbl">{{ __('Preparation time now') }}</span>
      @if ($prepMinutes <= 1)
        <span class="ck-big">{{ __('Now') }}</span>
      @else
        <span class="ck-big">{{ $prepMinutes }}<span style="font-size:15px"> {{ __('min') }}</span></span>
      @endif
    </div>

    <form method="POST" action="{{ route('vendor.preptime') }}" class="ck-f">
      @csrf
      @foreach (\App\Services\PrepTime::SLICES as $slice)
        <button class="ck-slice {{ (! $prepIsAuto && $prepMinutes == $slice) ? 'on' : '' }}"
                name="minutes" value="{{ $slice }}">{{ $slice == 1 ? __('Now') : $slice }}</button>
      @endforeach
      <button class="ck-slice auto {{ $prepIsAuto ? 'on' : '' }}" name="minutes" value="0">{{ __('Automatic') }}</button>
    </form>

    <form method="POST" action="{{ route('cashier.togglecar') }}" class="ck-f">
      @csrf
      <span class="ck-lbl">{{ __('Car pickup') }}</span>
      <button class="ck-toggle {{ $carOn ? 'on' : 'off' }}">
        @if ($carOn)
          {{ __('On') }}
        @else
          {{ __('Turn on now') }} · {{ $carMinutesLeft }} {{ __('min') }}
        @endif
      </button>
    </form>

    <div>
      <span class="ck-lbl">{{ __('Active orders') }}</span>
      <span class="ck-big">{{ $activeCount }}</span>
    </div>

    @if ($accuracy !== null)
      <div>
        <span class="ck-lbl">{{ __('Your accuracy') }}</span>
        <span class="ck-big" style="color:{{ $accuracy >= 80 ? 'var(--ready)' : ($accuracy >= 60 ? 'var(--wait)' : 'var(--danger)') }}">{{ $accuracy }}%</span>
      </div>
    @endif
  </div>

  @if (count($cards) === 0)
    <div class="ck-empty">{{ __('No active orders') }}</div>
  @endif

  <div class="ck-grid">
    @foreach ($cards as $card)
      @php
        $o = $card['order'];
        $v = $card['vehicle'];
        $handover = $card['emphasis'] === 'handover';
        $hasCar = $card['is_car'] && ($v['brand'] || $v['color'] || $v['plate']);
      @endphp
      <div class="ck-card s-{{ $card['is_new'] ? 'new' : $card['state'] }}" data-order="{{ $o->id }}">

        <div class="ck-head">
          <div>
            <div class="ck-id">#{{ $o->id_formated }}</div>
            <span class="ck-pill way">{!! $card['is_car'] ? '&#128663; '.__('From my car') : '&#129517; '.__('From the cashier') !!}</span>
            @if ($card['promise_at'])
              <div class="ck-sub">{{ __('Promised') }} {{ $card['promise_at']->format('H:i') }}</div>
            @endif
          </div>

          <div style="text-align:end">
            @if ($card['state'] === 'arrived')
              <span class="ck-pill" style="background:var(--readydot); color:var(--ready)">{{ __('Customer has arrived') }}</span>
            @elseif ($card['state'] === 'late')
              <div class="ck-count late">+{{ $card['minutes_late'] }}</div>
              <div class="ck-sub" style="color:var(--danger)">{{ __('Late') }}</div>
            @elseif ($card['state'] === 'ready')
              <span class="ck-pill" style="background:var(--readydot); color:var(--ready)">{{ __('Ready · waiting') }}</span>
            @elseif ($card['state'] === 'not_collected')
              <span class="ck-pill" style="background:var(--faint); color:var(--ink)">{{ __('Not collected') }}</span>
            @elseif ($card['promise_at'])
              <div class="ck-count" data-until="{{ $card['promise_at']->timestamp }}">&mdash;</div>
              <div class="ck-sub">{{ __('min') }}</div>
            @endif
          </div>
        </div>

        @if ($handover)
          {{-- لحظة التسليم: «لمين أعطيه وكم آخذ؟» — السيارة والمبلغ يتصدّران --}}
          @if ($hasCar)
            <div class="ck-car big">
              <span class="ck-swatch big" style="background:{{ $card['state'] === 'arrived' ? 'rgba(255,255,255,.9)' : '#D9D2CA' }}"></span>
              <div>
                <div class="ck-carname">{{ $v['brand'] ?: __('From my car') }}@if($v['color']) · {{ $v['color'] }}@endif</div>
                @if ($v['plate'])<div class="ck-plate big" dir="ltr">{{ $v['plate'] }}</div>@endif
                @if ($v['spot'])<div class="ck-sub">{{ __('Parking spot') }}: {{ $v['spot'] }}</div>@endif
              </div>
            </div>
          @endif

          <div class="ck-amount">
            @if ($o->payment_status == 'paid')
              <span class="ck-lbl">{{ __('Paid') }}</span>
              <span class="ck-money paid">@money($o->order_price_with_discount, config('settings.cashier_currency'), config('settings.do_convertion'))</span>
            @else
              <span class="ck-lbl">{{ __('Collect') }}</span>
              <span class="ck-money">@money($o->order_price_with_discount, config('settings.cashier_currency'), config('settings.do_convertion'))</span>
            @endif
          </div>

          {{-- الأصناف هنا تأكيد لا تعليمات، فسطر واحد يكفي --}}
          @if ($card['items_line'])
            <div class="ck-oneline">{{ $card['items_line'] }}</div>
          @endif

          @if (strlen(trim($o->comment ?? '')) > 0)
            <div class="ck-note">{{ __('Note') }}: {{ $o->comment }}</div>
          @endif

          {{-- الجوال: يُحتاج في حالة واحدة — جاهز ولا أحد جاء --}}
          @if ($card['phone'])
            <div class="ck-sub" style="margin-top:8px">
              {{ __('Phone') }}: <a href="tel:{{ $card['phone'] }}" dir="ltr" style="color:inherit; text-decoration:underline">{{ $card['phone'] }}</a>
            </div>
          @endif

        @elseif ($card['state'] !== 'not_collected')
          {{-- لحظة التحضير: «وش أصنع؟» — الأصناف تتصدّر، وما عداها يصغر --}}
          <ul class="ck-items">
            @foreach ($o->items as $item)
              @if ($item->pivot->qty > 0)
                <li>
                  <span class="ck-qty">{{ $item->pivot->qty }}×</span>
                  <span class="ck-name">{{ $item->name }}</span>
                  @if (strlen($item->pivot->variant_name) > 1)
                    <span class="ck-opt">{{ str_replace(',', ' · ', $item->pivot->variant_name) }}</span>
                  @endif
                  @if (strlen($item->pivot->extras) > 2)
                    @php $imenuExtras = json_decode($item->pivot->extras) ?: []; @endphp
                    @if (count($imenuExtras))
                      <span class="ck-opt">+ {{ implode(' · ', array_map('strval', $imenuExtras)) }}</span>
                    @endif
                  @endif
                </li>
              @endif
            @endforeach
          </ul>

          @if (strlen(trim($o->comment ?? '')) > 0)
            <div class="ck-note">{{ __('Note') }}: {{ $o->comment }}</div>
          @endif

          <div class="ck-amount small">
            <span class="ck-lbl">{{ $o->payment_status == 'paid' ? __('Paid') : __('Collect') }}</span>
            <span class="ck-money">@money($o->order_price_with_discount, config('settings.cashier_currency'), config('settings.do_convertion'))</span>
          </div>

        @else
          @if ($card['items_line'])
            <div class="ck-oneline">{{ $card['items_line'] }}</div>
          @endif
        @endif

        @if ($card['trust']['key'] !== 'new')
          <span class="ck-pill" style="background:{{ $card['trust']['color'] }}; color:#fff">{{ $card['trust']['text'] }}</span>
        @endif

        {{-- ثلاثة إجراءات فقط --}}
        <div class="ck-act">
          @if ($card['state'] === 'arrived')
            <form method="POST" action="{{ route('cashier.delivered') }}" class="ck-f" style="flex:1">
              @csrf<input type="hidden" name="order_id" value="{{ $o->id }}">
              <button class="ck-btn ready huge">{{ __('Handed over') }}</button>
            </form>

          @elseif ($card['state'] === 'ready')
            <form method="POST" action="{{ route('cashier.delivered') }}" class="ck-f">
              @csrf<input type="hidden" name="order_id" value="{{ $o->id }}">
              <button class="ck-btn ready">{{ __('Handed over') }}</button>
            </form>
            @if ($card['can_report'])
              <form method="POST" action="{{ route('order.notcollected') }}" class="ck-f">
                @csrf<input type="hidden" name="order_id" value="{{ $o->id }}">
                <button class="ck-btn ghost">{{ __('Not collected') }}</button>
              </form>
            @endif

          @elseif ($card['state'] !== 'not_collected')
            <form method="POST" action="{{ route('cashier.ready') }}" class="ck-f">
              @csrf<input type="hidden" name="order_id" value="{{ $o->id }}">
              <button class="ck-btn">{{ __('Ready') }}</button>
            </form>
            @if ($card['state'] === 'late')
              @foreach ([5, 10] as $add)
                <form method="POST" action="{{ route('cashier.delay') }}" class="ck-f">
                  @csrf<input type="hidden" name="order_id" value="{{ $o->id }}">
                  <button class="ck-btn ghost" name="minutes" value="{{ $add }}">+{{ $add }}</button>
                </form>
              @endforeach
            @endif
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>

<script>
(function () {
  // عدّاد تنازلي بالدقائق — يُحدَّث محليًا فلا نحتاج طلبًا للسيرفر لكل ثانية
  function ticks() {
    document.querySelectorAll('[data-until]').forEach(function (el) {
      var left = Math.round((parseInt(el.dataset.until, 10) * 1000 - Date.now()) / 60000);
      el.textContent = left > 0 ? left : 0;
    });
  }
  ticks();
  setInterval(ticks, 20000);

  // رنّة واحدة خفيفة: عند طلب جديد، وعند أول تجاوز. لا تكرار —
  // التنبيه المتكرر يُطفأ، ومعه يُطفأ تنبيه الطلب الجديد.
  function chime() {
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var osc = ctx.createOscillator(), gain = ctx.createGain();
      osc.frequency.value = 880; osc.type = 'sine';
      gain.gain.setValueAtTime(0.0001, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.15, ctx.currentTime + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.45);
      osc.connect(gain); gain.connect(ctx.destination);
      osc.start(); osc.stop(ctx.currentTime + 0.5);
    } catch (e) {}
  }

  function seen(key) {
    try {
      var raw = sessionStorage.getItem(key);
      return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
  }
  function remember(key, ids) {
    try { sessionStorage.setItem(key, JSON.stringify(ids)); } catch (e) {}
  }

  ['new', 'late'].forEach(function (state) {
    var key = 'ck_chimed_' + state;
    var known = seen(key);
    var ids = Array.prototype.map.call(
      document.querySelectorAll('.s-' + state + '[data-order]'),
      function (el) { return el.dataset.order; }
    );
    var fresh = ids.filter(function (id) { return known.indexOf(id) === -1; });
    if (fresh.length) { chime(); }
    remember(key, ids);
  });

  // تحديث دوري — لا حجب للشاشة، والنافذة الحاجبة قد تخفي طلبًا جديدًا داخلًا
  setTimeout(function () { window.location.reload(); }, 20000);
})();
</script>
@endsection
