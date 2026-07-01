<section id="demo">
  <div class="wrap">
    <div class="demo-card reveal">
      <div class="demo-left">
        <h2>جرّب قائمة إلكترونية حيّة الآن</h2>
        <p>لا حاجة للتسجيل. شاهد بنفسك كيف يرى عملاؤك قائمتك على هواتفهم.</p>
        <ul class="demo-steps">
          <li><span class="c">١</span> افتح كاميرا هاتفك</li>
          <li><span class="c">٢</span> وجّهها نحو رمز QR المجاور</li>
          <li><span class="c">٣</span> تصفّح القائمة التجريبية فوراً</li>
        </ul>
        <a class="btn btn-primary btn-lg" href="{{ route('newrestaurant.register') }}">افتح القائمة التجريبية</a>
      </div>
      <div class="demo-right">
        <div class="qr-frame">
          {{-- استبدل الصورة برمز QR الحقيقي لقائمتك التجريبية، أو احذف <img> لإظهار الرمز الزخرفي --}}
          @if(file_exists(public_path('impactfront/img/qrdemo.jpg')))
            <img src="{{ asset('impactfront') }}/img/qrdemo.jpg" alt="رمز QR لقائمة تجريبية">
          @else
            <div class="qr-big" role="img" aria-label="رمز QR لقائمة تجريبية"></div>
          @endif
          <p>قائمة تجريبية فورية</p>
          <span class="scan">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#155083" stroke-width="2"><path d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2"/></svg>
            امسح للبدء
          </span>
        </div>
      </div>
    </div>
  </div>
</section>
