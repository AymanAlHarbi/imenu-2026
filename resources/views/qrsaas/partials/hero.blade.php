<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <span class="eyebrow"><span class="dot"></span> المنصة الأشمل لقوائم QR الرقمية</span>
      <h1>حوّل قائمة مطعمك إلى <span class="hl">قائمة إلكترونية ذكية</span></h1>
      <p class="lead">أنشئ قائمة رقمية لمطعمك أو مقهاك، صمّم رمز QR احترافياً، واستقبل الطلبات والمدفوعات مباشرة من هاتف عميلك.</p>
      <p class="punch">هاتف عميلك هو قائمتك الآن.</p>

      @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
      @endif

      @guest
        <form class="hero-form" action="{{ route('newrestaurant.register') }}">
          <input type="text"  name="name"  placeholder="اسمك" required>
          <input type="email" name="email" placeholder="بريدك الإلكتروني" required>
          <input type="text"  name="phone" placeholder="رقم جوالك" required>
          <button class="btn btn-primary btn-lg" type="submit">أنشئ قائمتك الآن</button>
        </form>
      @else
        <div class="hero-cta">
          <a class="btn btn-primary btn-lg" href="/home">لوحة التحكم</a>
          <a class="btn btn-ghost btn-lg" href="#demo">شاهد نموذجاً حياً</a>
        </div>
      @endguest

      <div class="micro">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2C82C4" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
        ابدأ مجاناً — بدون بطاقة ائتمانية، وبدون التزام.
      </div>
    </div>

    <div class="hero-visual">
      <div class="blob"></div>
      <div class="phone">
        <div class="phone-screen">
          <div class="ps-head">
            <div class="logo">م</div>
            <b>مطعم الذواقة</b>
            <span>القائمة الرقمية</span>
          </div>
          <div class="ps-tabs">
            <i class="on">المشاوي</i><i>المقبّلات</i><i>المشروبات</i><i>الحلى</i>
          </div>
          <div class="ps-item"><div class="ps-thumb"></div><div class="ps-meta"><b>مشاوي مشكّلة</b><span>لحم • دجاج • كباب</span></div><div class="ps-price">٦٥ ﷼</div></div>
          <div class="ps-item"><div class="ps-thumb b"></div><div class="ps-meta"><b>سلطة فتوش</b><span>طازجة يومياً</span></div><div class="ps-price">١٨ ﷼</div></div>
          <div class="ps-item"><div class="ps-thumb c"></div><div class="ps-meta"><b>عصير برتقال</b><span>طبيعي ١٠٠٪</span></div><div class="ps-price">١٢ ﷼</div></div>
          <div class="ps-item"><div class="ps-thumb"></div><div class="ps-meta"><b>كنافة بالقشطة</b><span>تُقدّم ساخنة</span></div><div class="ps-price">٢٢ ﷼</div></div>
        </div>
      </div>
      <div class="qr-tag">
        <div class="qr"></div>
        <div><small>امسح للوصول</small><b>قائمة فورية</b></div>
      </div>
      <div class="order-tag"><span class="ping"></span> طلب جديد وصل!</div>
    </div>
  </div>
</section>
