<!-- section-place-content -->
<section class='section section-place-content bg-gray-100  '>
    <div class='packer w-full  md:px-20'>
        <div class='package '>
            @if ($canDoOrdering)
                <div id="theCartBottomButton" style="background: linear-gradient(87deg, #ffffff 0, #ffffff 100%) !important; z-index: 9999; width: 60px; height:60px; position: fixed; bottom: 15px; right: 15px;" onClick="openNav()" class="close-mobile-menu circle callOutShoppingButtonBottom icon icon-shape bg-gradient-red text-white rounded-circle shadow mb-4" >
                    <lottie-player src="https://assets10.lottiefiles.com/packages/lf20_atunf5kv.json" background="transparent" speed="0.5" style="width: 60px; height:60px;" loop autoplay></lottie-player>
                </div>
            @endif
           
            <div class='content'>
                
                <!-- tab menu -->
                <div id='place-menu' class='holder-left {{  !$canDoOrdering?"fullHolder":""  }} content-tab expanded'>
                    <div class='content-left w-35'>
                        
                        <!-- Languages -->
                        @if ($showLanguagesSelector)
                            <div class='categories px-4 md-px-0 mb-10'>
                                <div class=' text-lg  text-slate-700 font-bold mb-4'>{{__('Languages')}}</div>
                                <nav>
                                    @foreach ($restorant->localmenus()->get() as $language)
                                        
                                            <div class='item'>
                                                <a href='?lang={{ $language->language }}'
                                                    class="font-light leading-6 text-left text-black"
                                                    style="list-style: outside none none;"
                                                    >{{$language->languageName}}</a>
                                                    <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-200">
                                            
                                            </div>
                                        
                                    @endforeach

                                    
                                </nav>
                            </div>
                        @endif

                        @if (isset($showGoogleTranslate)&&$showGoogleTranslate&&!$showLanguagesSelector)
                            <div class='categories px-4 md-px-0 mb-10'>
                                <div class=' text-lg  text-slate-700 font-bold mb-4'>{{__('Translation')}}</div>
                                @include('googletranslate::buttons')
                            </div>
                        @endif


                        <div class='categories px-4 md-px-0'>
                            <div class=' text-lg  text-slate-700 font-bold mb-4'>{{__('Categories')}}</div>
                            
                            <nav>

                            @foreach ( $restorant->categories as $key => $category)
                                @if(!$category->items->isEmpty())
                                    <div class='item'>

                                        <a href='#subsection-<?php echo $category->id; ?>'
                                        class="font-light leading-6 text-left text-black"
                                        style="list-style: outside none none;"
                                        >{{ $category->name}}</a>
                                        <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-200">
                                    </div>
                                @endif
                            @endforeach

                               
                            </nav>


                        </div>

                        

                        
                        


                    </div>
                    <div class='content-center -mt-10'>

                        
                 

                        @if(!$restorant->categories->isEmpty())
                            @foreach ( $restorant->categories as $key => $category)
                            <h1 id='subsection-<?php echo $category->id; ?>' class="mt-10 text-lg ml-6 text-slate-700 font-bold">{{ $category->name }}</h1>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 p-6 flex flex-grow">
                               
                                
                                @foreach ($category->aitems as $item)
                                <a href='javascript:;' onClick="setCurrentItemInGlow({{ $item->id }})"
                                    class="grid relative gap-2 justify-between items-center p-4 m-0 align-baseline bg-white p-3 shadow-lg hover:shadow-xl rounded-lg "
                                    style="text-decoration: none; transition: all 0.3s ease-in-out 0s; outline: none; grid-template-rows: 1fr; grid-template-columns: minmax(100px, 1fr) 100px;">
                                    <div class="p-0 m-0 w-full align-baseline border-0">
                                        <h2 class="text-slate-700 font-bold">{{ $item->name }}</h2>
                                        <p class="mt-1 text-sm text-slate-400">{{ $item->short_description }}</p>
                                        @if ($item->discounted_price>0)
                                            <span style="text-decoration: line-through;" class="mt-1 text-sm text-slate-500 font-bold">@money($item->discounted_price, config('settings.cashier_currency'),config('settings.do_convertion'))</span>
                                        @endif
                                                       
                                        <span class="mt-1 text-sm text-slate-500 font-bold">@money($item->price, config('settings.cashier_currency'),config('settings.do_convertion'))</span>
                            
                                    </div>
                                    @if (strlen($item->logom)>5)
                                    <picture class="block overflow-hidden relative right-0 m-0 w-24 rounded-lg border border-solid border-zinc-100"
                                        style='max-height: 100px; min-width: 100px; content: ""; padding-bottom: 96%;'>
                                        <source srcset="{{$item->logom }}" media="(min-width: 569px)" class="text-neutral-700" />
                                        <img loading="lazy" src="{{ $item->logom }}"
                                            class="block object-cover absolute p-0 m-0 w-full max-w-full h-full align-middle border-0 border-none" />
                                    </picture>
                            
                                    @endif
                            
                                </a>
                            
                            
                                @endforeach
                            
                            
                               
                            
                            </div>

                    

                            @endforeach
                        @endif




                       
                    </div>
                </div>

                <!-- tab info -->
                <div id='place-info' class='holder-left {{  !$canDoOrdering?"fullHolder":""  }} content-tab'>
                    <div class='full-width'>
                        <div class='box-infos bg-white p-3 shadow-lg hover:shadow-xl rounded-lg mr-10'>
                            <div class='head'>
                                <h3><i class="las la-map-marker"></i>{{ __('Address') }}</h3>
                                <div class='info'>
                                    <p><strong>{{ $restorant->address }}</strong></p>
                                    <p>{{ $restorant->phone }}</p>
                                </div>
                            </div>
                            <div class='content'>
                                <div class='schedule-map'>
                                    <div class='schedule'>
                                        <h4 class="mt-4 font-bold">{{ __('Working Hours') }}</h4>
                                        <ol class='items'>
                                            @foreach ($wh as $day=>$hours)
                                                <li>
                                                    @if ($day==$currentDay)
                                                        <div class='day'>{{ __(ucfirst($day))}}
                                                            <span class='tag'>
                                                                {{ __('Today') }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class='day'>{{ __(ucfirst($day))}} </div>
                                                    @endif
                                                    @foreach ($hours as $timeRange)
                                                        <div class='hours'>{{ $timeRange->start() }} - {{ $timeRange->end() }} </div>
                                                    @endforeach
                                                    
                                                </li>
                                            @endforeach
                                            
                                        </ol>
                                        
                                    </div>
                                    <div class="map">
                                        <iframe src="https://maps.google.com/maps?q={{ $restorant->lat }},{{ $restorant->lng }}&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Language selector -->
                <div id='place-languages' class='holder-left {{  !$canDoOrdering?"fullHolder":""  }} content-tab'>
                    <div class='full-width'>
                        <div class='box-infos bg-white p-3 shadow-lg hover:shadow-xl rounded-lg mr-10'>
                            <div class='head'>
                                <h3><i class="las la-map-marker"></i>{{ __('Address') }}</h3>
                                <div class='info'>
                                    <p><strong>{{ $restorant->address }}</strong></p>
                                    <p>{{ $restorant->phone }}</p>
                                </div>
                            </div>
                            <div class='content'>
                                <div class='schedule-map'>
                                    <div class='schedule'>
                                        <h4 class="mt-4 font-bold">{{ __('Working Hours') }}</h4>
                                        <ol class='items'>
                                            @foreach ($wh as $day=>$hours)
                                                <li>
                                                    @if ($day==$currentDay)
                                                        <div class='day'>{{ __(ucfirst($day))}}
                                                            <span class='tag'>
                                                                {{ __('Today') }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class='day'>{{ __(ucfirst($day))}} </div>
                                                    @endif
                                                    @foreach ($hours as $timeRange)
                                                        <div class='hours'>{{ $timeRange->start() }} - {{ $timeRange->end() }} </div>
                                                    @endforeach
                                                    
                                                </li>
                                            @endforeach
                                            
                                        </ol>
                                        
                                    </div>
                                    <div class="map">
                                        <iframe src="https://maps.google.com/maps?q={{ $restorant->lat }},{{ $restorant->lng }}&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($canDoOrdering)
                    <div class='holder-right' style="width:400px">
                        <!-- New cart -->
                            @include('glow::templates.side_cart',['id'=>'cartList','idtotal'=>'totalPrices'])
                        <!-- End New cart -->
                    </div>
                @endif
                
            </div>
        </div>
    </div>

   
           

</section>