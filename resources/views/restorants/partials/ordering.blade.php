@if(!config('settings.makePureSaaS',false)  && !(config('app.isdrive',false)||config('app.issd',false)) )
    @if(config('app.ordering'))
        <h6 class="heading-small text-muted mb-4">{{ __('Orders') }}</h6>

        {{--
            iMenu 2026 — لا حد أدنى للطلب (تعطيل لا حذف).

            settings.pickup_only = true يخفي «الحد الأدنى» ويرسله صفرًا ثابتًا:
            المفهوم قادم من التوصيل، و BaseOrderRepository::validateOrder يرفض أي طلب تحت
            قيمته في كل ما ليس dinein — مقهى تذكرته ١٤ ﷼ وحد أدنى ٢٠ لا تُطلب منه قهوة واحدة.
            الحقل الأصلي باقٍ في فرع @else؛ PICKUP_ONLY=false يعيده.
        --}}
        @if(config('settings.pickup_only'))
            <input type="hidden" name="minimum" value="0">
        @else
            @include('partials.fields',['fields'=>[
                ['required'=>true,'ftype'=>'input','type'=>'number','placeholder'=>"Minimum order",'name'=>'Minimum order', 'additionalInfo'=>'Enter Minimum order value', 'id'=>'minimum', 'value'=>$restorant->minimum],
            ]])
        @endif

        @include('partials.fields',['fields'=>[
            ['required'=>true,'ftype'=>'select','placeholder'=>"",'name'=>'Average order prepare time in minutes', 'id'=>'custom[time_to_prepare_order_in_minutes]','data'=>[0=>0,5=>5,10=>10,15=>15,20=>20,25=>25,30=>30,35=>35,40=>40,45=>45,50=>50,60=>60,90=>90,120=>120],'value'=>$restorant->getConfig('time_to_prepare_order_in_minutes',config('settings.time_to_prepare_order_in_minutes'))],
            ['required'=>true,'ftype'=>'select','placeholder'=>"",'name'=>'Time slots separated in minutes', 'id'=>'custom[delivery_interval_in_minutes]','data'=>[5=>5,10=>10,15=>15,20=>20,25=>25,30=>30,35=>35,40=>40,45=>45,50=>50,60=>60,90=>90,120=>120],'value'=>$restorant->getConfig('delivery_interval_in_minutes',config('settings.delivery_interval_in_minutes'))]
        ]])
    @endif
@endif
