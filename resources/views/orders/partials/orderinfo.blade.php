
<div class="card-body">
    @include('partials.flash') 
    @if ($order->restorant)
        <h6 class="heading-small text-muted mb-4">{{ __('Restaurant information') }}</h6>
        <div class="pl-lg-4">
            <h3>{{ $order->restorant->name }}</h3>
            <h4>{{ $order->restorant->address }}</h4>
            <h4>{{ $order->restorant->phone }}</h4>
            <h4>{{ $order->restorant->user->name.", ".$order->restorant->user->email }}</h4>
        </div>
        <hr class="my-4" />
    @endif
    
    
 
     @if (config('app.isft')&&$order->client)
         <h6 class="heading-small text-muted mb-4">{{ __('Client Information') }}</h6>
         <div class="pl-lg-4">
             <h3>{{ $order->client?$order->client->name:"" }}</h3>
             <h4>{{ $order->client?$order->client->email:"" }}</h4>
             <h4>{{ $order->address?$order->address->address:"" }}</h4>
 
             @if(!empty($order->address->apartment))
                 <h4>{{ __("Apartment number") }}: {{ $order->address->apartment }}</h4>
             @endif
             @if(!empty($order->address->entry))
                 <h4>{{ __("Entry number") }}: {{ $order->address->entry }}</h4>
             @endif
             @if(!empty($order->address->floor))
                 <h4>{{ __("Floor") }}: {{ $order->address->floor }}</h4>
             @endif
             @if(!empty($order->address->intercom))
                 <h4>{{ __("Intercom") }}: {{ $order->address->intercom }}</h4>
             @endif
             @if($order->client&&!empty($order->client->phone))
             <br/>
             <h4>{{ __('Contact')}}: {{ $order->client->phone }}</h4>
             @endif
         </div>
         <hr class="my-4" />
     @else
         @if ($order->table)
             <h6 class="heading-small text-muted mb-4">{{ __('Table Information') }}</h6>
             <div class="pl-lg-4">
                 
                     <h3>{{ __('Table:')." ".$order->table->name }}</h3>
                     @if ($order->table->restoarea)
                         <h4>{{ __('Area:')." ".$order->table->restoarea->name }}</h4>
                     @endif
                 
                 
             </div>
             <hr class="my-4" />
         @endif
     @endif
     
 
 
    <?php 
        $currency=config('settings.cashier_currency');
        $convert=config('settings.do_convertion');
    ?>

    @if ($order->driver)
        @hasrole('admin|owner|staff')
            <h6 class="heading-small text-muted mb-4">{{ __('Driver') }}</h6>
            <p><a href="/drivers/{{ $order->driver->id}}/edit">{{ $order->driver->name }}</a></p>
            <hr class="my-4" />
        @endhasanyrole
    @endif
     @if(count($order->items)>0)
     <h6 class="heading-small text-muted mb-4">{{ __('Order') }}</h6>
     
     <ul id="order-items">
         @foreach($order->items as $item)
             <?php 
                 $theItemPrice= ($item->pivot->variant_price?$item->pivot->variant_price:$item->price);
             ?>
            @if ( $item->pivot->qty>0)
            <li><h4>{{ $item->pivot->qty." X ".$item->name }} -  @money($theItemPrice, $currency,$convert)  =  ( @money( $item->pivot->qty*$theItemPrice, $currency,true) )
                 
                @if($item->pivot->vatvalue>0))
                    <span class="small">-- {{ __('VAT').' '.$item->pivot->vat."%: "}} ( @money( $item->pivot->vatvalue, $currency,$convert) )</span>
                @endif
                 @hasrole('admin|owner|staff')
                    <?php $lasStatusId=$order->status->pluck('id')->last(); ?>
                    @if ($lasStatusId!=7&&$lasStatusId!=11)
                        <span class="small">
                            <button 
                            data-toggle="modal" 
                            data-target="#modal-order-item-count" 
                            type="button" 
                            onclick="$('#item_qty').val('{{$item->pivot->qty}}'); $('#pivot_id').val('{{$item->pivot->id}}');   $('#order_id').val('{{$order->id}}');"
                            class="btn btn-outline-danger btn-sm">
                                <span class="btn-inner--icon">
                                    <i class="ni ni-ruler-pencil"></i>
                                </span>
                            </button>
                        </span>
                    @endif
                 @endif
             </h4>
                 @if (strlen($item->pivot->variant_name)>2)
                     <br />
                     <table class="table align-items-center">
                         <thead class="thead-light">
                             <tr>
                                 @foreach ($item->options as $option)
                                     <th>{{ $option->name }}</th>
                                 @endforeach
 
 
                             </tr>
                         </thead>
                         <tbody class="list">
                             <tr>
                                 @foreach (explode(",",$item->pivot->variant_name) as $optionValue)
                                     <td>{{ $optionValue }}</td>
                                 @endforeach
                             </tr>
                         </tbody>
                     </table>
                 @endif
 
                 @if (strlen($item->pivot->extras)>2)
                     <br /><span>{{ __('Extras') }}</span><br />
                     <ul>
                         @foreach(json_decode($item->pivot->extras) as $extra)
                             <li> {{  $extra }}</li>
                         @endforeach
                     </ul><br />
                 @endif
                 <br />
             </li>
            @else
                <li>
                    {{ __('Removed') }}
                    <h4 class="text-muted">{{$item->name }} -  @money($theItemPrice, $currency,$convert) 
                 
                        @if($item->pivot->vatvalue>0))
                            <span class="small">-- {{ __('VAT ').$item->pivot->vat."%: "}} ( @money( $item->pivot->vatvalue, $currency,$convert) )</span>
                        @endif
                    </h4>
                    <br />
                </li>
            @endif
             
         @endforeach
     </ul>
     @endif
     @if(!empty($order->whatsapp_address))
        <br/>
        <h4>{{ __('Address') }}: {{ $order->whatsapp_address }}</h4>
     @endif
     @if(!empty($order->comment))
        <br/>
        <h4>{{ __('Comment') }}: {{ $order->comment }}</h4>
     @endif
     @if(strlen($order->phone)>2)
        <h4>{{ __('Phone') }}: <span dir="ltr">{{ \App\Helpers\SaudiPhone::local($order->phone) }}</span></h4>
     @endif
     <br />
     @if(!empty($order->time_to_prepare))
     <br/>
     <h4>{{ __('Time to prepare') }}: {{ $order->time_to_prepare ." " .__('minutes')}}</h4>
     <br/>
     @endif
     {{-- iMenu 2026 — صفوف مضغوطة بدل مكدّس عناوين.
          كانت كل سطر <h4> بهامش عنوان، فتحوّلت الصفحة إلى نصوص متباعدة
          بلا تراتب: المجموع والهاتف واللون كلها بحجم واحد. --}}
     <div class="im-kv">
        <div><span>{{ __("NET") }}</span><b>@money( $order->order_price-$order->vatvalue, $currency ,true)</b></div>
        <div><span>{{ __("VAT") }}</span><b>@money( $order->vatvalue, $currency,$convert)</b></div>
        <div><span>{{ __("Sub Total") }}</span><b>@money( $order->order_price, $currency,$convert)</b></div>
        @if($order->delivery_method==1)
            <div><span>{{ __("Delivery") }}</span><b>@money( $order->delivery_price, $currency,$convert)</b></div>
        @endif
        @if ($order->discount>0)
            <div><span>{{ __("Discount") }}</span><b>@money( $order->discount, $currency,$convert)</b></div>
            <div><span>{{ __("Coupon code") }}</span><b>{{ $order->coupon }}</b></div>
        @endif
        @if ($order->tip>0)
            <div><span>{{ __("Tip") }}</span><b>@money( $order->tip, $currency,$convert)</b></div>
        @endif
        @if(!empty($order->time_to_prepare))
            <div><span>{{ __("Time to prepare") }}</span><b>{{ $order->time_to_prepare." ".__('minutes') }}</b></div>
        @endif
     </div>

     <div class="im-total">
        <span>{{ __("TOTAL") }}</span>
        <b>@money( $order->delivery_price+$order->order_price_with_discount, $currency,true)</b>
     </div>

     <div class="im-kv">
        <div><span>{{ __("Payment method") }}</span><b>{{ __(strtoupper($order->payment_method)) }}</b></div>
        <div><span>{{ __("Payment status") }}</span><b>{{ __(ucfirst($order->payment_status)) }}</b></div>

        {{-- طريقة الاستلام تُقرأ من pickup_method لا من getExpeditionType العامة --}}
        @if(config('app.isft') || config('app.iswp'))
            <div><span>{{ __("Delivery method") }}</span><b>{{ $order->getExpeditionType() }}</b></div>
        @else
            <div><span>{{ __("Pickup method") }}</span><b>{{ $order->getConfig('pickup_method','') == 'car' ? __('From my car') : __('From the coffee shop') }}</b></div>
        @endif

        {{-- الوعد وختم الوصول: معلومتان مفيدتان، بمسمّى مفهوم لا بمفتاح خام --}}
        @if ($order->getConfig('ready_promise_at', false))
            <div><span>{{ __("Promised") }}</span><b style="font-variant-numeric:tabular-nums">{{ \Carbon\Carbon::parse($order->getConfig('ready_promise_at'))->format('H:i') }}</b></div>
        @endif
        @if ($order->getConfig('arrived_at', false))
            <div><span>{{ __("Customer has arrived") }}</span><b style="font-variant-numeric:tabular-nums">{{ \Carbon\Carbon::parse($order->getConfig('arrived_at'))->format('H:i') }}</b></div>
        @endif

        {{-- الفترة الزمنية تُخفى إن كانت فارغة، وكانت تظهر كعنوان بلا قيمة --}}
        @if (strlen(trim($order->time_formated ?? '')) > 0 && $order->delivery_method != 3)
            <div><span>{{ __("Time slot") }}</span><b>{{ $order->time_formated }}</b></div>
        @endif
     </div>

     @if(isset($custom_data)&&count($custom_data)>0)
        <div class="im-kv-title">{{ __(config('settings.label_on_custom_fields')) }}</div>
        <div class="im-kv">
            @foreach ($custom_data as $keyCutom => $itemValue)
                <div>
                    <span>{{ __("custom.".$keyCutom) }}</span>
                    <b>{{ $keyCutom == 'pickup_method' ? __('custom.pickup_'.$itemValue) : $itemValue }}</b>
                </div>
            @endforeach
        </div>
     @endif

     
 
 
 </div>