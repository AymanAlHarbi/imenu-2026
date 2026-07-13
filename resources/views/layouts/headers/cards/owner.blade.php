<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row">
                <div class="col-xl col-md-6 mb-2">
                    <div class="card card-stats h-100">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Today orders') }}</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $todayOrdersCount }}</span>
                            <p class="mt-1 mb-0 text-sm text-muted">{{ __('Yesterday') }}: {{ $yesterdayOrdersCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-2">
                    <div class="card card-stats h-100">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Today sales') }}</h5>
                            <span class="h2 font-weight-bold mb-0">@money(is_numeric($todaySalesValue) ? $todaySalesValue : 0, config('settings.cashier_currency'), config('settings.do_convertion'))</span>
                            <p class="mt-1 mb-0 text-sm text-muted">{{ __('Yesterday') }}: @money(is_numeric($yesterdaySalesValue) ? $yesterdaySalesValue : 0, config('settings.cashier_currency'), config('settings.do_convertion'))</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-2">
                    <div class="card card-stats h-100">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Sales Volume') }} ( 30 {{ __('days') }} )</h5>
                            <span class="h2 font-weight-bold mb-0">@money(is_numeric($last30daysOrdersValue['order_price']) ? $last30daysOrdersValue['order_price'] : 0, config('settings.cashier_currency'), config('settings.do_convertion'))</span>
                            <p class="mt-1 mb-0 text-sm text-muted">{{ $last30daysOrders }} {{ __('Orders') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-2">
                    <div class="card card-stats h-100">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Average order value') }}</h5>
                            <span class="h2 font-weight-bold mb-0">@money($avgOrderValue, config('settings.cashier_currency'), config('settings.do_convertion'))</span>
                            <p class="mt-1 mb-0 text-sm text-muted">( 30 {{ __('days') }} )</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-2">
                    <div class="card card-stats h-100">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Views') }}</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $allViews }}</span>
                            <p class="mt-1 mb-0 text-sm text-info">{{ __('Conversion rate') }}: {{ $conversionRate }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>