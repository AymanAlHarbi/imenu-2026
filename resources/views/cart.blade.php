@extends('layouts.front', ['class' => 'imenu-checkout-enhanced'])
@section('head')
    <link type="text/css" href="{{ asset('custom') }}/css/imenu-checkout.css" rel="stylesheet">
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('custom/css/checkout-enhance.css') }}">
<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap');
:root{ --es-tang:#FA8128; --es-tang-d:#E06A12; --es-teal:#48AAAD; --es-ink:#030303; }

/* فرض خط Cairo على كل شيء ما عدا الأيقونات */
body, body *:not(i):not(svg):not(path):not([class*="la-"]):not([class*="fa-"]):not([class*="icon"]){
  font-family:'Cairo','Segoe UI',system-ui,sans-serif !important;
}
h1,h2,h3,h4,h5,h6{ font-weight:700 !important; color:var(--es-ink); }

/* استبدال أزرق Argon أينما ظهر */
.btn-primary, .btn-primary:not(:disabled):not(.disabled),
.bg-primary, .bg-gradient-primary, .badge-primary{
  background:var(--es-tang) !important; background-image:none !important;
  border-color:var(--es-tang) !important; color:#fff !important;
}
.btn-primary:hover,.btn-primary:focus,.btn-primary:active{ background:var(--es-tang-d) !important; border-color:var(--es-tang-d) !important; }
.btn{ border-radius:12px !important; font-weight:700 !important; box-shadow:none !important; }
.btn-outline-primary{ color:var(--es-teal) !important; border-color:var(--es-teal) !important; background:#fff !important; }
.btn-outline-primary:hover,.btn-outline-primary.active,.btn-group-toggle .btn.active{
  background:var(--es-tang) !important; border-color:var(--es-tang) !important; color:#fff !important;
}
.text-primary{ color:var(--es-tang) !important; }
.custom-control-input:checked ~ .custom-control-label::before{ background-color:var(--es-tang) !important; border-color:var(--es-tang) !important; }
.form-control{ border-radius:10px !important; }
.form-control:focus{ border-color:var(--es-teal) !important; box-shadow:0 0 0 3px rgba(72,170,173,.15) !important; }
a{ color:var(--es-tang); } a:hover{ color:var(--es-tang-d); }
</style>

    <section class="section-profile-cover section-shaped my--1 d-none d-md-none d-lg-block d-lx-block">
        <!-- Circles background -->
        <img class="bg-image " src="{{ config('global.restorant_details_cover_image') }}" style="width: 100%;">
        <!-- SVG separator -->
        <div class="separator separator-bottom separator-skew">

        </div>
    </section>
    <section class="section bg-secondary imenu-rd">

      <div class="container">


        @include('notify::components.notify')

          <div class="row">

            <!-- Left part -->
            <div class="col-md-7">

              <!-- List of items -->
              @include('cart.items')

                <form id="order-form" role="form" method="post" action="{{route('order.store')}}" autocomplete="off" enctype="multipart/form-data">
                @csrf
                @if(!config('settings.social_mode'))

                    @if (config('app.isft')&&count($timeSlots)>0)
                    <!-- FOOD TIGER -->
                        <!-- Delivery method -->
                        @if($restorant->can_pickup == 1)
                            @if($restorant->can_deliver == 1)
                              @include('cart.delivery')
                            @endif
                        @endif

                        <!-- Delivery time slot -->
                        @include('cart.time')

                        <!-- Delivery address -->
                        <div id='addressBox'>
                            @include('cart.address')
                        </div>

                        <!-- Custom Fields -->
                        @include('cart.customfields')

                        <!-- Comment -->
                        @include('cart.comment')
                    @elseif(config('app.isag'))  
                        @if(count($timeSlots)>0)
                            <!-- Delivery method -->
                            @include('cart.delivery')

                            <!-- Delivery time slot -->
                            @include('cart.time')

                            <!-- Custom Fields  -->
                            @include('cart.customfields')

                            <!-- Delivery adress -->
                            @include('cart.newaddress')

                            <!-- Client informations -->
                            @include('cart.newclient')

                            <!-- Comment -->
                            @include('cart.comment')
                        @endif

                    @elseif(config('app.isqrsaas')&&count($timeSlots)>0)

                      <!-- QRSAAS -->
                      
                      <!-- DINE IN OR TAKEAWAY -->
                      @if (config('settings.enable_pickup'))
                      
                          <!-- iMenu 2026 — استلام من الشباك أو من السيارة فقط -->
                          @include('cart.localorder.pickupmethod')
                          
                      @endif
                      <!-- LOCAL ORDERING -->
                      @include('cart.localorder.table')

                      <!-- Local Order Phone -->
                      @include('cart.localorder.phone')

                      <!-- Custom Fields -->
                      @include('cart.customfields')

                      <!-- Comment -->
                      @include('cart.comment')
                        

                    @endif
                @else
                    <!-- Social MODE -->

                    @if(count($timeSlots)>0)
                        <!-- Delivery method -->
                        @include('cart.delivery')

                        <!-- Delivery time slot -->
                        @include('cart.time')

                        <!-- Custom Fields  -->
                        @include('cart.customfields')

                        <!-- Delivery adress -->
                        @include('cart.newaddress')

                        <!-- Client informations -->
                        @include('cart.newclient')

                        <!-- Comment -->
                        @include('cart.comment')
                    @endif
                @endif

              <!-- Restaurant -->
             <!--  @include('cart.restaurant')-->
            </div>


          <!-- Right Part -->
          <div class="col-md-5">

            @if (count($timeSlots)>0)
                <!-- Payment -->
                @include('cart.payment')
            @else
                <!-- Closed restaurant -->
                @include('cart.closed')
            @endif


          </div>
        </div>


    </div>
    @include('clients.modals')
  </section>
@endsection
@section('js')

  <script async defer src= "https://maps.googleapis.com/maps/api/js?key=<?php echo config('settings.google_maps_api_key'); ?>&callback=initAddressMap"></script>
  <!-- Stripe -->
  <script src="https://js.stripe.com/v3/"></script>
  <script>
    "use strict";
    var RESTORANT = <?php echo json_encode($restorant) ?>;
    var STRIPE_KEY="{{ config('settings.stripe_key') }}";
    var ENABLE_STRIPE="{{ config('settings.enable_stripe') }}";
    var SYSTEM_IS_QR="{{ config('app.isqrexact') }}";
    var SYSTEM_IS_WP="{{ config('app.iswp') }}";
    var initialOrderType = 'delivery';
    if(RESTORANT.can_deliver == 1 && RESTORANT.can_pickup == 0){
        initialOrderType = 'delivery';
    }else if(RESTORANT.can_deliver == 0 && RESTORANT.can_pickup == 1){
        initialOrderType = 'pickup';
    }
  </script>
  <script src="{{ asset('custom') }}/js/checkout.js"></script>
@endsection

