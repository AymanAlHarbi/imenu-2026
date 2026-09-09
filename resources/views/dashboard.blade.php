@extends('layouts.app')
@section('admin_title')
    {{__('Dashboard')}}
@endsection

@section('content')
    @if(auth()->user()->hasRole('owner'))
        @include('layouts.headers.cards.owner')
    @elseif(!auth()->user()->hasRole('driver'))
        @include('layouts.headers.cards.general')
    @else
        @include('layouts.headers.cards.driver')
    @endif

    @if(
        (auth()->user()->hasRole('admin')&&config('app.isft')) ||
        (auth()->user()->hasRole('owner')&&in_array("drivers", config('global.modules',[]))) 
    )
      
        <div class="container-fluid mt--7 mb-8">
            <div class="row">
                <div class="col-xl-12">
                    @include('drivers.map')
                </div>
            </div>
        </div>  
    @endif
    

    @if(!auth()->user()->hasRole('driver'))
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col-xl-12">
                <div class="card shadow">
                    <div class="card-header bg-transparent">
                        <h6 class="text-uppercase text-muted ls-1 mb-1">{{ __('Overview') }}</h6>
                        <h2 class="mb-0">{{ __('Daily sales') }} — {{ __('Last 14 days') }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart"><canvas id="chart-daily" class="chart-canvas"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-xl-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ __('Latest orders') }}</h3>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <tbody>
                            @foreach ($latestOrders as $order)
                                @php
                                    $dashCustomer = $order->client ? $order->client->name : null;
                                    if (!$dashCustomer) {
                                        foreach ($order->getAllConfigs() as $cKey => $cValue) {
                                            if ($cValue && (str_contains(mb_strtolower($cKey), 'name') || str_contains($cKey, 'اسم'))) { $dashCustomer = $cValue; break; }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td><a class="btn badge badge-success badge-pill" href="{{ route('orders.show', $order->id) }}">#{{ $order->id_formated }}</a></td>
                                    <td><span class="font-weight-bold">{{ $dashCustomer ?: __('Guest') }}</span></td>
                                    <td>@money($order->order_price_with_discount, config('settings.cashier_currency'), config('settings.do_convertion'))</td>
                                    <td>@include('orders.partials.laststatus')</td>
                                    <td class="text-muted"><small>{{ $order->created_at->locale(Config::get('app.locale'))->diffForHumans() }}</small></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-transparent"><h3 class="mb-0">{{ __('Top selling items') }} ( 30 {{ __('days') }} )</h3></div>
                    <div class="card-body">
                        @php $maxQty = count($topItems) ? max($topItems->pluck('qty')->toArray()) : 1; @endphp
                        @forelse ($topItems as $item)
                            <div class="d-flex justify-content-between"><span>{{ $item->name }}</span><span class="text-muted">{{ $item->qty }} {{ __('times') }}</span></div>
                            <div class="progress" style="height: 6px; margin: 4px 0 12px;">
                                <div class="progress-bar bg-success" style="width: {{ round($item->qty / max($maxQty, 1) * 100) }}%"></div>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No orders right now!') }}</p>
                        @endforelse
                        <hr class="my-3"/>
                        @php $typeNames = [1 => __('Delivery'), 2 => __('Pickup'), 3 => __('Dine in')]; @endphp
                        @foreach ($orderTypes as $type => $cnt)
                            <span class="badge badge-pill badge-primary">{{ $typeNames[$type] ?? $type }}: {{ $cnt }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var ctx = document.getElementById('chart-daily');
                if (ctx && window.Chart) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @json($dailyLabels),
                            datasets: [{ label: '{{ __('Daily sales') }}', data: @json($dailyValues), backgroundColor: '#7F77DD' }]
                        },
                        options: {
                            legend: { display: false },
                            maintainAspectRatio: false,
                            tooltips: {
                                callbacks: {
                                    afterLabel: function (item, data) {
                                        var counts = @json($dailyCounts);
                                        return '{{ __('Orders') }}: ' + counts[item.index];
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{ ticks: { beginAtZero: true } }],
                                xAxes: [{ barPercentage: 0.7, categoryPercentage: 0.8, maxBarThickness: 40 }]
                            }
                        }
                    });
                }
            });
        </script>
        @if ($doWeHaveExpensesApp)
        <script>
           
            var categoriesLabels = {!! json_encode($expenses['last30daysCostPerGroupLabels']) !!};
            var categoriesValues = {!! json_encode($expenses['last30daysCostPerGroupValues']) !!};

            var vendorsLabels = {!! json_encode($expenses['last30daysCostPerVendorLabels']) !!};
            var vendorsValues = {!! json_encode($expenses['last30daysCostPerVendorValues']) !!};
            
        </script>
        <div class="row mt-5">
            <div class="col-xl-6">
                <div class="card shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase text-muted ls-1 mb-1">{{ __('Expenses') }} ( 30 {{ __('days') }} )</h6>
                                <h2 class="mb-0">{{ __('By category') }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Chart -->
                        @if(count($salesValue)>0)
                            <div class="chart">
                                <canvas id="chart-bycategory" class="chart-canvas"></canvas>
                            </div>
                        @else
                            <p>{{ __('No expenses right now!') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase text-muted ls-1 mb-1">{{ __('Expenses') }} ( 30 {{ __('days') }} )</h6>
                                <h2 class="mb-0">{{ __('By vendor') }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Chart -->
                        @if(count($salesValue)>0)
                            <div class="chart">
                                <canvas id="chart-byvendor" class="chart-canvas"></canvas>
                            </div>
                        @else
                            <p>{{ __('No expenses right now!') }}</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        @endif

        @if(auth()->user()->hasRole('owner')&&config('settings.enable_pricing'))
            @php $dashPlan = auth()->user()->restorant->getPlanAttribute(); @endphp
            <div class="card shadow mt-4">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <span>{{ __('Current Plan') }}: <strong>{{ isset($dashPlan['plan']['name']) ? $dashPlan['plan']['name'] : '' }}</strong></span>
                    <a href="{{ route('plans.current') }}" class="btn btn-sm btn-outline-primary">{{ __('Go to plans') }}</a>
                </div>
            </div>
        @endif
        
        @include('layouts.footers.auth')
    </div>
    @endif
@endsection
@section('topjs')
    <script src="{{ asset('argon') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('argon') }}/vendor/chart.js/dist/Chart.extension.js"></script>
@endsection
@push('js')
  

      

@if(
    (auth()->user()->hasRole('admin')&&config('app.isft')) ||
    (auth()->user()->hasRole('owner')&&in_array("drivers", config('global.modules',[]))) 
)

    <!-- Live orders -->
    <script src="{{ asset('custom') }}/js/liveorders.js"></script>

    <!-- Google Map -->
    <script async defer src= "https://maps.googleapis.com/maps/api/js?libraries=geometry,drawing&callback=initDriverMap&key=<?php echo config('settings.google_maps_api_key'); ?>"> </script>
      
    <script type="text/javascript">
    var map=null;
    var clientsAndDriverMarkers=[];

    function initDriverMap(){
        map = new google.maps.Map(document.getElementById('map_location'), {center: {lat: 40.7128, lng: -74.006}, zoom: 15 });
        getRestorants();
    }

    function getRestorants(){

        var infowindow = new google.maps.InfoWindow(); 

        const image ="/custom/img/pin_restaurant.svg";

        var bounds = new google.maps.LatLngBounds();

        var link='/restaurantslocations';
        axios.get(link).then(function (response) {
            

            response.data.restaurants.forEach(restaurant => {

                    /**
                     *  Restaurant Marker
                     **/
                     var restoMarker=new google.maps.Marker({
                        position: new google.maps.LatLng(parseFloat(restaurant.lat), parseFloat(restaurant.lng)),
                        animation: google.maps.Animation.DROP,
                        map,
                        title: restaurant.name,
                        icon:image,
                        color:"red"
                    });

                    restoMarker.addListener("click", () => {
                        var content="<a href=\"/orders?restorant_id="+restaurant.id+"\"><strong>"+restaurant.name+"</strong></a>";
                        infowindow.setContent(content);
                        infowindow.open({
                            anchor: restoMarker,
                            map,
                            shouldFocus: false,
                        });
                    });
                    bounds.extend(restoMarker.position);
            });

            map.fitBounds(bounds);

            getDriverOrders();
            setInterval(() => {
                getDriverOrders();
            }, 20000);
            
        });
    }
   

    function getDriverOrders(){
           
            var infowindow = new google.maps.InfoWindow(); 

            const image ="/custom/img/pin_driver.svg";

            var link='/driverlocations';

           

            for (let i = 0; i < clientsAndDriverMarkers.length; i++) {
                    clientsAndDriverMarkers[i].setMap(null);
                }
                clientsAndDriverMarkers=[];
            

            axios.get(link).then(function (response) {
                

                
                response.data.drivers.forEach(driver => {
                    
                    
                    if(driver.lat!=null){

                        
                         /**
                     *  Driver Marker
                     **/
                    var driverMarker=new google.maps.Marker({
                        position: new google.maps.LatLng(parseFloat(driver.lat), parseFloat(driver.lng)),
                        map,
                        title: driver.name,
                        icon:image,
                        color:"red"
                    });
                    clientsAndDriverMarkers.push(driverMarker);
                    google.maps.event.addListener(driverMarker, 'click', (function(driverMarker, i) {
                        var content="<a href=\"/orders?driver_id="+driver.id+"\">"+driver.name+"</a>";
                        content+="<br />";
                        content+="Orders: "+driver.driverorders.length;
                        content+="<br />";
                        content+="---------";
                        content+="<br />";
                        driver.driverorders.forEach(order => {
                            content+="Order <a href=\"/orders/"+order.id+"\">#"+order.id+"</a> <a href=\"/orders?restorant_id="+order.restorant_id+"\"><strong>"+order.restorant.name+"</strong></a>";
                            content+="<br />";
                        });
                        content+="---------";
                        content+="<br />";
                        return function() {
                            infowindow.setContent(content);
                            infowindow.open(map, driverMarker);
                        }
                    })(driverMarker, i));
                    

                    /**
                     *  Driver Path
                     **/
                    var driverPathCoordinates=[];
                    driver.paths.forEach(path => {
                        driverPathCoordinates.push({lat: parseFloat(path.lat), lng: parseFloat(path.lng)});
                    });
                    driverPathCoordinates.push({lat: parseFloat(driver.lat), lng: parseFloat(driver.lng)});

                    const driverPath = new google.maps.Polyline({
                        path: driverPathCoordinates,
                        geodesic: true,
                        strokeColor: "#0000FF",
                        strokeOpacity: 1.0,
                        strokeWeight: 2,
                    });
                    driverPath.setMap(map);

                    

                    /**
                     *  Driver orders - if any
                     * */
                     driver.driverorders.forEach(order => {


                        //The restaurant
                        var restaurantMarker=new google.maps.Marker({
                            position: new google.maps.LatLng(parseFloat(order.restorant.lat), parseFloat(order.restorant.lng)),
                            title: order.restorant.name,
                            color:"red"
                        });
                        bounds.extend(restaurantMarker.position);

                        //The Client
                        var clientMarker=new google.maps.Marker({
                            position: new google.maps.LatLng(parseFloat(order.address.lat), parseFloat(order.address.lng)),
                            title: order.address.address,
                            map,
                            icon:"/custom/img/pin_client.svg",
                            color:"red"
                        });
                        bounds.extend(clientMarker.position);
                        clientsAndDriverMarkers.push(clientMarker);

                        google.maps.event.addListener(clientMarker, 'click', (function(clientMarker, i) {
                            var content="Order <a href=\"/orders/"+order.id+"\">#"+order.id+"</a> <a href=\"/orders?restorant_id="+order.restorant_id+"\"><strong>"+order.restorant.name+"</strong></a>";
                            content+="<br />Address <a href=\"/orders?client_id="+order.client_id+"\"><strong>"+order.address.address+"</strong></a>";
                               
                            return function() {
                                infowindow.setContent(content);
                                infowindow.open(map, clientMarker);
                            }
                        })(clientMarker, i));


                        var driverPathToClientCoordinates=[];

                        //Create new paths, to indicate, from driver, to restaurant if order is not picked up
                        if(order.laststatus[0].pivot.status_id<6){
                            
                            //Only if this order is not yet picked up
                            var driverPathToRestaurantCoordinates=[];
                            
                            driverPathToRestaurantCoordinates.push({lat: parseFloat(driver.lat), lng: parseFloat(driver.lng)});
                            driverPathToRestaurantCoordinates.push({lat: parseFloat(order.restorant.lat), lng: parseFloat(order.restorant.lng)});
                            driverPathToClientCoordinates.push({lat: parseFloat(order.restorant.lat), lng: parseFloat(order.restorant.lng)});

                            const driverPathToResto = new google.maps.Polyline({
                                path: driverPathToRestaurantCoordinates,
                                geodesic: true,
                                strokeColor: "#FF6000",
                                strokeOpacity: 1.0,
                                strokeWeight: 2,
                            });
                            driverPathToResto.setMap(map);
                        }else{
                            driverPathToClientCoordinates.push({lat: parseFloat(driver.lat), lng: parseFloat(driver.lng)});
                        }

                       
                            
                           //Complete path to client
                            driverPathToClientCoordinates.push({lat: parseFloat(order.address.lat), lng: parseFloat(order.address.lng)});
   
                           const driverPathToClient = new google.maps.Polyline({
                               path: driverPathToClientCoordinates,
                               geodesic: true,
                               strokeColor: "#FF6000",
                               strokeOpacity: 1.0,
                               strokeWeight: 2,
                           });
                           driverPathToClient.setMap(map);
                        });

                    }

                   
                         
                        

                   


                    
                });

              
                

                
                
            })
            .catch(function (error) {
                
            });
    };
   
    </script>
    @endif
@endpush