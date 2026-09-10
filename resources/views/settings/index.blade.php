@extends('layouts.app', ['title' => __('Settings')])

@section('content')
<div class="header bg-gradient-primary pb-7 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-12 col-12 text-right">
                @if($hasDemoRestaurants)
                    <a href="{{ route('admin.restaurants.removedemo') }}" class="btn btn-sm btn-danger">{{ __('Remove demo data') }}</a>
                @endif
                <a href="{{ route('systemstatus') }}" class="btn btn-sm btn-outline-light">{{ __('settings_system_status') }}</a>
                <a href="{{ route('admin.regenerate.sitemap') }}" class="btn btn-sm btn-outline-light">{{ __('Regenerate sitemap') }}</a>
            </div>
          </div>
        </div>
    </div>
</div>
<div class="container-fluid mt--7">
    @if ($showMultiLanguageMigration)
        @include('settings.multilanguagemenus')
    @endif
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card bg-secondary shadow">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-md-5 col-12">
                            <h3 class="mb-0">{{ __('Settings Management') }}</h3>
                        </div>
                        <div class="col-md-7 col-12">
                            <input type="search" id="im-settings-search" class="form-control form-control-sm" autocomplete="off"
                                   placeholder="{{ __('Search in settings') }}...">
                            <small class="text-muted" id="im-search-count"></small>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('results'))
                        {{-- كان print_r خامًا بلا تهريب --}}
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <pre class="mb-0" style="white-space:pre-wrap">{{ print_r(session('results'), true) }}</pre>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                        <form id="settings" method="post" action="{{ route('settings.update', $settings->id) }}" autocomplete="off" enctype="multipart/form-data">
                            @csrf
                            @method('put')

                            <div class="nav-wrapper">
                                <ul class="nav nav-pills nav-fill flex-column flex-md-row" id="tabs-icons-text" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link mb-sm-3 mb-md-0 active" id="tab-siteinfo-btn" data-toggle="tab" href="#tab-siteinfo" role="tab" aria-controls="tab-siteinfo" aria-selected="true"><i class="ni ni-bullet-list-67 mr-2"></i>{{ __ ('Site Info') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link mb-sm-3 mb-md-0" id="tab-images-btn" data-toggle="tab" href="#tab-images" role="tab" aria-controls="tab-images" aria-selected="false"><i class="ni ni-image mr-2"></i>{{ __ ('Images') }}</a>
                                    </li>

                                    {{-- كل تبويب مولَّد كان يحمل نفس المعرّف tabs-icons-text-2-tab --}}
                                    @foreach ($envConfigs as $groupConfig)
                                        <li class="nav-item">
                                            <a class="nav-link mb-sm-3 mb-md-0" id="tab-{{ $groupConfig['slug'] }}-btn" data-toggle="tab" href="#{{ $groupConfig['slug'] }}" role="tab" aria-controls="{{ $groupConfig['slug'] }}" aria-selected="false"><i class="{{ $groupConfig['icon'] }}"></i> {{ __ ($groupConfig['name']) }}</a>
                                        </li>
                                    @endforeach

                                    <li class="nav-item">
                                        <a class="nav-link mb-sm-3 mb-md-0" id="tab-cssjs-btn" data-toggle="tab" href="#cssjs" role="tab" aria-controls="cssjs" aria-selected="false"><i class="ni ni-palette mr-2"></i>{{ __ ('CSS & JS') }}</a>
                                    </li>
                                </ul>
                            </div>
                            <br/>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="tab-siteinfo" role="tabpanel" aria-labelledby="tab-siteinfo-btn">
                                        @include('partials.input',['id'=>'site_name','name'=>'Site Name','placeholder'=>'Site Name here ...','value'=>$settings->site_name, 'required'=>true])
                                        @include('partials.input',['id'=>'site_description','name'=>'Site Description','placeholder'=>'Site Description here ...','value'=>$settings->description, 'required'=>true])
                                        @if(config('app.isft'))
                                            @include('partials.input',['id'=>'header_title','name'=>'Header Title','placeholder'=>'Header Title here ...','value'=>$settings->header_title, 'required'=>true])
                                            @include('partials.input',['id'=>'header_subtitle','name'=>'Header Subtitle','placeholder'=>'Header Subtitle here ...','value'=>$settings->header_subtitle, 'required'=>true])
                                            <br/>
                                            @include('partials.input',['id'=>'delivery','name'=>'Delivery cost - fixed','placeholder'=>'Fixed delivery','value'=>$settings->delivery, 'required'=>false])
                                        @endif

                                        {{-- روابط المتاجر خرجت من كتلة isft: تطبيق العميل يحتاجها في كل الأوضاع --}}
                                        <h6 class="heading-small text-muted mb-4">{{ __('Mobile App') }}</h6>
                                        @include('partials.input',['id'=>'mobile_info_title','name'=>'Info Title','placeholder'=>'Info Title text here ...','value'=>$settings->mobile_info_title, 'required'=>false])
                                        @include('partials.input',['id'=>'mobile_info_subtitle','name'=>'Info Subtitle','placeholder'=>'Info Subtitle text here ...','value'=>$settings->mobile_info_subtitle, 'required'=>false])
                                        @include('partials.input',['id'=>'playstore','name'=>'Play Store','placeholder'=>'Play Store link here ...','value'=>$settings->playstore, 'required'=>false, 'dir'=>'ltr'])
                                        @include('partials.input',['id'=>'appstore','name'=>'App Store','placeholder'=>'App Store link here ...','value'=>$settings->appstore, 'required'=>false, 'dir'=>'ltr'])

                                        <h6 class="heading-small text-muted mb-4">{{ __('Social Links') }}</h6>
                                        @include('partials.input',['id'=>'facebook','name'=>'Facebook','placeholder'=>'Facebook link here ...','value'=>$settings->facebook, 'required'=>false, 'dir'=>'ltr'])
                                        @include('partials.input',['id'=>'instagram','name'=>'Instagram','placeholder'=>'Instagram link here ...','value'=>$settings->instagram, 'required'=>false, 'dir'=>'ltr'])
                                        <br/>
                                    </div>

                                    <div class="tab-pane fade" id="tab-images" role="tabpanel" aria-labelledby="tab-images-btn">
                                        <div class="row">
                                            <?php
                                                $images=[
                                                    ['name'=>'site_logo','label'=>__('Site Logo'),'value'=>config('global.site_logo'),'style'=>'width: 200px;'],
                                                    ['name'=>'restorant_details_image','help'=>"590x400px",'label'=>__('Restaurant Default Image'),'value'=>config('global.restorant_details_image'),'style'=>'width: 200px;'],
                                                    ['name'=>'restorant_details_cover_image','label'=>__('Restaurant Details Cover Image'),'value'=>config('global.restorant_details_cover_image'),'style'=>'width: 200px;'],
                                                    ['help'=>"256,256px",'name'=>'favicons','label'=>__('Favicon'),'value'=>'/apple-touch-icon.png','style'=>'width: 120px; height: 120px;']
                                                 ];

                                                if(config('app.isft')){
                                                    array_splice($images, 1, 0, [['name'=>'search','label'=>__('Search Cover'),'value'=>config('global.search'),'style'=>'width: 200px;']] );
                                                }

                                                if(config('settings.is_whatsapp_ordering_mode')){
                                                    array_splice($images, 1, 0, [['name'=>'site_logo_dark','label'=>__('Site Logo Dark'),'value'=>config('global.site_logo_dark'),'style'=>'width: 200px;']] );
                                                    if(config('app.isqrexact')){
                                                        array_push($images,['help'=>"512x512px",'name'=>'qrdemo','label'=>__('Front QR'),'value'=>'/impactfront/img/qrdemo.jpg','style'=>'width: 120px; height: 120px;']);
                                                    }
                                                    array_push($images,['help'=>"",'name'=>'wphomehero','label'=>__('Hero image'),'value'=>'/social/img/wpordering.svg','style'=>'width: 200px; height: 120px;']);

                                                }

                                                if(config('settings.is_pos_cloud_mode')){
                                                    array_splice($images, 1, 0, [['name'=>'site_logo_dark','label'=>__('Site Logo Dark'),'value'=>config('global.site_logo_dark'),'style'=>'width: 200px;']] );
                                                    array_push($images,['help'=>"",'name'=>'poshomehero','label'=>__('Hero image'),'value'=>'/soft/img/poshero.jpeg','style'=>'width: 200px; height: 120px;']);
                                                }

                                                if(config('app.isqrexact')){
                                                    array_splice($images, 1, 0, [['name'=>'site_logo_dark','label'=>__('Site Logo Dark'),'value'=>config('global.site_logo_dark'),'style'=>'width: 200px;']] );

                                                    array_push($images,['help'=>"512x512px",'name'=>'qrdemo','label'=>__('Front QR'),'value'=>'/impactfront/img/qrdemo.jpg','style'=>'width: 120px; height: 120px;']);

                                                    array_push($images,['help'=>"600x600px", 'name'=>'ftimig0','label'=>__('Flayer image'),'value'=>'/impactfront/img/flayer.png','style'=>'width: 200px; height: 200px;']);
                                                    array_push($images,['help'=>"600x467px",'name'=>'ftimig1','label'=>__('Feature image #1'),'value'=>'/impactfront/img/menubuilder.jpg','style'=>'width: 180px; height: 130px;']);
                                                    array_push($images,['help'=>"600x467px",'name'=>'ftimig2','label'=>__('Feature image #2'),'value'=>'/impactfront/img/qr_image_builder.jpg','style'=>'width: 180px; height: 130px;']);
                                                    array_push($images,['help'=>"600x467px",'name'=>'ftimig3','label'=>__('Feature image #3'),'value'=>'/impactfront/img/mobile_pwa.jpg','style'=>'width: 180px; height: 130px;']);

                                                    array_push($images,['help'=>"600x467px",'name'=>'ftimig4','label'=>__('Feature image #4'),'value'=>'/impactfront/img/localorders.jpg','style'=>'width: 180px; height: 130px;']);
                                                    array_push($images,['help'=>"600x467px",'name'=>'ftimig5','label'=>__('Feature image #5'),'value'=>'/impactfront/img/payments.jpg','style'=>'width: 180px; height: 130px;']);
                                                    array_push($images,['help'=>"600x467px",'name'=>'ftimig6','label'=>__('Feature image #6'),'value'=>'/impactfront/img/customerlog.jpg','style'=>'width: 180px; height: 130px;']);
                                                }
                                            ?>
                                            @foreach ($images as $image)
                                                <div class="col-md-4">
                                                    @include('partials.images',$image)
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- ترتيب المحتوى صار مطابقًا لترتيب الأزرار --}}
                                    @foreach ($envConfigs as $groupConfig)
                                        <div class="tab-pane fade" id="{{ $groupConfig['slug'] }}" role="tabpanel" aria-labelledby="tab-{{ $groupConfig['slug'] }}-btn">
                                            <div class="">
                                                @include('partials.fields',['fields'=>$groupConfig['fields']])
                                                @if ($groupConfig['slug']=="setup")
                                                    @include('partials.fields',['fields'=>[
                                                    ['separator'=>"Custom fields on order", 'additionalInfo'=>'Please check docs on how to define the custom fields on order.', 'name'=>'Custom fileds in JSON format','required'=>false,'placeholder'=>'', 'id'=>'order_fields', 'ftype'=>'textarea','type'=>"textarea",'dir'=>'ltr','editclass'=>'im-code','spellcheck'=>'false','value'=>$settings->order_fields]
                                                    ]
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- الكود لاتيني دائمًا: dir=ltr وخط أحادي المسافة، وإلا صار غير مقروء في واجهة عربية --}}
                                    <div class="tab-pane fade" id="cssjs" role="tabpanel" aria-labelledby="tab-cssjs-btn">
                                        @include('partials.textarea',['id'=>'jsfront','name'=>'JavaScript - Frontend','placeholder'=>'JavaScript - Frontend','value'=>$jsfront, 'required'=>false, 'dir'=>'ltr','editclass'=>'im-code','spellcheck'=>'false','rows'=>8])
                                        @include('partials.textarea',['id'=>'jsfrontmenu','name'=>'JavaScript - Menu','placeholder'=>'JavaScript - Menu','value'=>$jsfrontmenu, 'required'=>false, 'dir'=>'ltr','editclass'=>'im-code','spellcheck'=>'false','rows'=>8])
                                        @include('partials.textarea',['id'=>'jsback','name'=>'JavaScript - Backend','placeholder'=>'JavaScript - Backend','value'=>$jsback, 'required'=>false, 'dir'=>'ltr','editclass'=>'im-code','spellcheck'=>'false','rows'=>8])
                                        @include('partials.textarea',['id'=>'cssfront','name'=>'CSS - Frontend','placeholder'=>'CSS - Frontend','value'=>$cssfront, 'required'=>false, 'dir'=>'ltr','editclass'=>'im-code','spellcheck'=>'false','rows'=>8])
                                        @include('partials.textarea',['id'=>'cssfrontmenu','name'=>'CSS - Menu','placeholder'=>'CSS - Menu','value'=>$cssfrontmenu, 'required'=>false, 'dir'=>'ltr','editclass'=>'im-code','spellcheck'=>'false','rows'=>8])
                                        @include('partials.textarea',['id'=>'cssback','name'=>'CSS - Backend','placeholder'=>'CSS - Backend','value'=>$cssback, 'required'=>false, 'dir'=>'ltr','editclass'=>'im-code','spellcheck'=>'false','rows'=>8])
                                    </div>

                            </div>

                        {{-- شريط حفظ ثابت: الصفحة ٦ تبويبات وزر واحد أسفلها، فيظن المستخدم أن كل تبويب يُحفظ وحده --}}
                        <div class="im-savebar">
                            <span class="im-savebar-note">{{ __('Saving applies to all tabs at once') }}</span>
                            <button type="submit" class="btn btn-success">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<br/><br/>
</div>
@endsection
@section('js')
    <script>
        // كان يستبدل script في كل textarea بما فيها حقول JSON، فيفسدها.
        // الآن على حقول الكود الستة فقط، وهي التي يعكسها المتحكم عند الحفظ.
        var imCodeFields = ['jsfront', 'jsfrontmenu', 'jsback', 'cssfront', 'cssfrontmenu', 'cssback'];
        $('#settings').submit(function () {
            imCodeFields.forEach(function (id) {
                var el = document.getElementById(id);
                if (el) { el.value = el.value.replace(/script/g, 'tagscript'); }
            });
        });

        // بحث بين الإعدادات: يتنقل بين التبويبات نيابة عن المستخدم
        (function () {
            var box = document.getElementById('im-settings-search');
            var count = document.getElementById('im-search-count');
            if (!box) { return; }

            function groups() {
                return Array.prototype.slice.call(document.querySelectorAll('#myTabContent .form-group'));
            }

            box.addEventListener('input', function () {
                var q = box.value.trim().toLowerCase();
                var panes = document.querySelectorAll('#myTabContent .tab-pane');

                if (!q) {
                    groups().forEach(function (g) { g.style.display = ''; });
                    Array.prototype.forEach.call(panes, function (p) { p.classList.remove('im-search-open'); });
                    count.textContent = '';
                    return;
                }

                var hits = 0;
                Array.prototype.forEach.call(panes, function (pane) {
                    var paneHits = 0;
                    Array.prototype.forEach.call(pane.querySelectorAll('.form-group'), function (g) {
                        var text = (g.textContent + ' ' + (g.id || '')).toLowerCase();
                        var match = text.indexOf(q) !== -1;
                        g.style.display = match ? '' : 'none';
                        if (match) { paneHits++; }
                    });
                    hits += paneHits;
                    pane.classList.add('im-search-open');
                    if (!paneHits) { pane.classList.remove('im-search-open'); }
                });

                count.textContent = hits + ' ' + @json(__('results'));
            });
        })();
    </script>
@endsection
