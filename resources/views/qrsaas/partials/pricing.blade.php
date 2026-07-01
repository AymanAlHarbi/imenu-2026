<section id="pricing">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="tag">أسعار شفّافة</div>
      <h2>ابدأ مجاناً، وارتقِ حين تحتاج</h2>
      <p>باقات تناسب كل حجم — من مقهى صغير إلى سلسلة مطاعم. لا رسوم خفية، ويمكنك الإلغاء في أي وقت.</p>
    </div>
    <div class="price-grid">
      @foreach ($plans as $plan)
        @include('qrsaas.partials.plan', ['plan' => $plan, 'featured' => $loop->index === (int) floor((count($plans)-1)/2)])
      @endforeach
    </div>
  </div>
</section>
