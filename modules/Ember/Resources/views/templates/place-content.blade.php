<!-- place-content (Ember: Bold Appetite) -->
<section class="section-place-content">

    <div id="place-menu" class="content-tab expanded">

        @if ($showLanguagesSelector)
            <div class="em-langs">
                @foreach ($restorant->localmenus()->get() as $language)
                    <a href="?lang={{ $language->language }}">{{ $language->languageName }}</a>
                @endforeach
            </div>
        @endif

        @if (isset($showGoogleTranslate)&&$showGoogleTranslate&&!$showLanguagesSelector)
            <div class="em-langs">@include('googletranslate::buttons')</div>
        @endif

        <!-- Sticky category quick-nav -->
        <nav class="em-cats">
            @foreach ( $restorant->categories as $key => $category)
                @if(!$category->items->isEmpty())
                    <a class="em-chip" href="#subsection-{{ $category->id }}">{{ $category->name }}</a>
                @endif
            @endforeach
        </nav>

        <div class="em-content">
            <div class="em-content-main">
                @if(!$restorant->categories->isEmpty())
                    @foreach ( $restorant->categories as $key => $category)
                        @if(!$category->aitems->isEmpty())
                            <h2 id="subsection-{{ $category->id }}" class="em-sectitle">{{ $category->name }}</h2>
                            <div class="em-grid">
                                @foreach ($category->aitems as $item)
                                    <div class="em-card" onClick="setCurrentItemInEmber({{ $item->id }})" style="cursor:pointer;">
                                        <div class="em-thumb" style="@if(strlen($item->mediumm)>5)background-image:url('{{ $item->mediumm }}');@endif">
                                            @if(!(strlen($item->mediumm)>5))<i class="las la-utensils"></i>@endif
                                        </div>
                                        <div class="em-cardbody">
                                            <h3 class="em-name">{{ $item->name }}</h3>
                                            @if(strlen($item->short_description)>1)
                                                <p class="em-desc">{{ $item->short_description }}</p>
                                            @endif
                                            <div class="em-pricerow">
                                                <div class="em-prices">
                                                    @if ($item->discounted_price>0)
                                                        <span class="em-old">@money($item->discounted_price, config('settings.cashier_currency'),config('settings.do_convertion'))</span>
                                                    @endif
                                                    <span class="em-price">@money($item->price, config('settings.cashier_currency'),config('settings.do_convertion'))</span>
                                                </div>
                                                @if ($canDoOrdering)
                                                    <span class="em-add" aria-label="{{ __('Add To Cart') }}"><i class="las la-plus"></i></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            @if ($canDoOrdering)
                <aside class="em-aside">
                    @include('ember::templates.side_cart',['id'=>'cartList','idtotal'=>'totalPrices'])
                </aside>
            @endif
        </div>
    </div>

    <!-- Info tab -->
    <div id="place-info" class="content-tab">
        <div class="em-info">
            <div class="em-infocard">
                <h3><i class="las la-map-marker"></i>{{ __('Address') }}</h3>
                <p><strong style="color:var(--em-ink)">{{ $restorant->address }}</strong></p>
                <p>{{ $restorant->phone }}</p>

                <h3 style="margin-top:18px;"><i class="las la-clock"></i>{{ __('Working Hours') }}</h3>
                <ol class="em-hours">
                    @php
                        $fmtTime = function($t){ return \Carbon\Carbon::createFromFormat('H:i', (string)$t)->locale(config('app.locale'))->translatedFormat('g:i a'); };
                    @endphp
                    @foreach (['sunday','monday','tuesday','wednesday','thursday','friday','saturday'] as $day)
                        @php $hours = $wh[$day] ?? []; @endphp
                        <li>
                            <span class="day {{ $day==$currentDay?'today':'' }}">
                                {{ __(ucfirst($day)) }}
                                @if ($day==$currentDay)<span class="tag">{{ __('Today') }}</span>@endif
                            </span>
                            <span>
                                @forelse ($hours as $timeRange)
                                    {{ $fmtTime($timeRange->start()) }} - {{ $fmtTime($timeRange->end()) }}@if(!$loop->last) <span style="opacity:.6;">و</span> @endif
                                @empty
                                    <span style="color:#e24b4a; font-weight:bold;">{{ __('Closed') }}</span>
                                @endforelse
                            </span>
                        </li>
                    @endforeach
                </ol>

                <div class="em-map">
                    <iframe src="https://maps.google.com/maps?q={{ $restorant->lat }},{{ $restorant->lng }}&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
        </div>
    </div>

</section>

@if ($canDoOrdering)
    <!-- Bottom cart bar (mobile) -->
    <div id="cartButtonHolder" v-cloak>
        <div class="em-bottombar-inner" v-if="counter>0" onClick="openNav()">
            <span class="l"><i class="las la-shopping-cart" style="font-size:18px;"></i>{{ __('Shopping Cart') }}<span class="badge">@{{ counter }}</span></span>
            <span class="r">{{ __('View') }} <i class="las la-angle-left"></i></span>
        </div>
    </div>
@endif
