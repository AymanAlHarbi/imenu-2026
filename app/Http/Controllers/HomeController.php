<?php

namespace App\Http\Controllers;

use Akaunting\Module\Facade as Module;
use App\Items;
use App\Order;
use App\Restorant;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use Modules\Expenses\Models\Expenses;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function driverInfo()
    {
        $driver = auth()->user();

        //Today paid orders
        $today = Order::where(['driver_id' => $driver->id])->where('payment_status', 'paid')->where('created_at', '>=', Carbon::today());

        //Week paid orders
        $week = Order::where(['driver_id' => $driver->id])->where('payment_status', 'paid')->where('created_at', '>=', Carbon::now()->startOfWeek());

        //This month paid orders
        $month = Order::where(['driver_id' => $driver->id])->where('payment_status', 'paid')->where('created_at', '>=', Carbon::now()->startOfMonth());

        //Previous month paid orders
        $previousmonth = Order::where(['driver_id' => $driver->id])->where('payment_status', 'paid')->where('created_at', '>=', Carbon::now()->subMonth(1)->startOfMonth())->where('created_at', '<', Carbon::now()->subMonth(1)->endOfMonth());

        //This user driver_percent_from_deliver
        $driver_percent_from_deliver = intval(auth()->user()->getConfig('driver_percent_from_deliver', config('settings.driver_percent_from_deliver'))) / 100;

        $earnings = [
            'today' => [
                'orders' => $today->count(),
                'earning' => $today->sum('delivery_price') * $driver_percent_from_deliver,
                'icon' => 'bg-gradient-red',
            ],
            'week' => [
                'orders' => $week->count(),
                'earning' => $week->sum('delivery_price') * $driver_percent_from_deliver,
                'icon' => 'bg-gradient-orange',
            ],
            'month' => [
                'orders' => $month->count(),
                'earning' => $month->sum('delivery_price') * $driver_percent_from_deliver,
                'icon' => 'bg-gradient-green',
            ],
            'previous' => [
                'orders' => $previousmonth->count(),
                'earning' => $previousmonth->sum('delivery_price') * $driver_percent_from_deliver,
                'icon' => 'bg-gradient-info',
            ],
        ];

        return view('dashboard', [
            'earnings' => $earnings,
        ]);
    }

    public function pureSaaSIndex($lang = null)
    {
        $locale = Cookie::get('lang') ? Cookie::get('lang') : config('settings.app_locale');
        if ($lang != null) {
            //this is language route
            $locale = $lang;
        }
        if ($locale != 'android-chrome-256x256.png') {
            App::setLocale(strtolower($locale));
            session(['applocale_change' => strtolower($locale)]);
        }

        $dataToDisplay = [];
        foreach (config('global.modulesWithDashboardInfo') as $moduleWithDashboardInfo) {
            $generatedClass = Module::get($moduleWithDashboardInfo)->get('nameSpace')."\Http\Controllers\DashboardController";
            $dataToDisplay[$moduleWithDashboardInfo] = (new $generatedClass())->index();
        }
        //dd($dataToDisplay);

        $response = new \Illuminate\Http\Response(view('dashboard_pure', $dataToDisplay));
        $response->withCookie(cookie('lang', $locale, 120));
        App::setLocale(strtolower($locale));

        return $response;
    }

    /**
     * Show the application dashboard.
     */
    public function index($lang = null)
    {
        if (config('settings.makePureSaaS', false) || (auth()->user()->hasRole('admin') && Module::has('pureadmindash'))) {
            return $this->pureSaaSIndex($lang);
        }

        $locale = Cookie::get('lang') ? Cookie::get('lang') : config('settings.app_locale');
        if ($lang != null) {
            //this is language route
            $locale = $lang;

        }
        if ($locale != 'android-chrome-256x256.png') {
            App::setLocale(strtolower($locale));
            session(['applocale_change' => strtolower($locale)]);
        }

        if (auth()->user()->hasRole('owner') || auth()->user()->hasRole('staff')) {
            \App\Services\ConfChanger::switchCurrency(auth()->user()->restorant);
        }

        $last30days = Carbon::now()->subDays(30);

        //Driver
        if (auth()->user()->hasRole('driver')) {
            return $this->driverInfo();
        } elseif (auth()->user()->hasRole('client')) {
            //Redirect to pure client dashboard
            return $this->pureSaaSIndex($lang);
        } elseif (auth()->user()->hasRole('admin') && config('app.isft')) {
            //Admin in FT
            $last30daysDeliveryFee = Order::all()->where('created_at', '>', $last30days)->where('payment_status', 'paid')->sum('delivery_price');
            $last30daysStaticFee = Order::all()->where('created_at', '>', $last30days)->where('payment_status', 'paid')->sum('static_fee');
            $last30daysDynamicFee = Order::all()->where('created_at', '>', $last30days)->where('payment_status', 'paid')->sum('fee_value');
            $last30daysTotalFee = DB::table('orders')
                ->select(DB::raw('SUM(delivery_price + static_fee + fee_value) AS sumValue'))
                ->where('created_at', '>', $last30days)
                ->where('payment_status', 'paid')
                ->value('sumValue');
        } else {
            $last30daysDeliveryFee = 0;
            $last30daysStaticFee = 0;
            $last30daysDynamicFee = 0;
            $last30daysTotalFee = 0;
        }

        $doWeHaveExpensesApp = false; // Be default for other don't enable expenses

        if (auth()->user()->hasRole('staff')) {
            if (in_array('poscloud', config('global.modules', []))) {
                //Redirect to POS
                return redirect()->route('poscloud.index');
            } else {
                //Redirect to Orders
                return redirect()->route('orders.index');
            }

        }
        if (auth()->user()->hasRole('manager')) {
            return redirect()->route('admin.restaurants.index');
        }
        if (isset($_GET['page'])) {

        } elseif (! config('app.ordering')) {
            if (auth()->user()->hasRole('owner')) {
                return redirect()->route('admin.restaurants.edit', auth()->user()->restorant->id);
            } elseif (auth()->user()->hasRole('admin')) {
                return redirect()->route('admin.restaurants.index');
            }
        }

        $expenses = [
            'costValue' => [],
        ];

        $months = [
            1 => __('Jan'),
            2 => __('Feb'),
            3 => __('Mar'),
            4 => __('Apr'),
            5 => __('May'),
            6 => __('Jun'),
            7 => __('Jul'),
            8 => __('Aug'),
            9 => __('Sep'),
            10 => __('Oct'),
            11 => __('Nov'),
            12 => __('Dec'),
        ];

        $notRejected = function ($q) {
            $q->whereIn('status.id', [8, 9]);
        };
        $last30daysOrders = Order::where('created_at', '>', $last30days)->whereDoesntHave('status', $notRejected)->count();
        $last30daysOrdersValue = Order::where('created_at', '>', $last30days)
            ->whereDoesntHave('status', $notRejected)
            ->select(DB::raw('ROUND(SUM(order_price+delivery_price),2) as order_price'), DB::raw('SUM(delivery_price + static_fee + fee_value) AS total_fee'), DB::raw('SUM(delivery_price) AS total_delivery'), DB::raw('SUM(static_fee) AS total_static_fee'), DB::raw('SUM(fee_value) AS total_fee_value'))
            ->first()->toArray();

        $sevenMonthsDate = Carbon::now()->subMonths(6)->startOfMonth();
        $salesValueRaw = Order::where('created_at', '>', $sevenMonthsDate)
            ->where('payment_status', 'paid')
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at), MONTH(created_at)'), 'asc')
            ->select(DB::raw('count(id) as totalPerMonth'), DB::raw('ROUND(SUM(order_price + delivery_price),2) AS sumValue'), DB::raw('MONTH(created_at) month'))
            ->get()->toArray();
        $monthsIds = array_map(function ($o) {
            return $o['month'];
        }, $salesValueRaw);
        $salesValue = array_combine($monthsIds, $salesValueRaw);
        foreach ($salesValue as $key => &$sale) {
            $sale['monthName'] = $months[$key];
        }

        //Expenses  - Owner only
        if (auth()->user()->hasRole('owner') && Module::has('expenses')) {
            $doWeHaveExpensesApp = true;
            $last30daysCostValue = Expenses::where([['created_at', '>', $last30days]])->sum('amount');

            $expensesValueRaw = Expenses::where('created_at', '>', $sevenMonthsDate)
                ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
                ->orderBy(DB::raw('YEAR(date), MONTH(date)'), 'asc')
                ->select(DB::raw('SUM(amount) AS costValue'), DB::raw('MONTH(date) month'))
                ->get()->toArray();

            $monthsIds = array_map(function ($o) {
                return $o['month'];
            }, $expensesValueRaw);
            $costValue = array_combine($monthsIds, $expensesValueRaw);
            foreach ($costValue as $monthKey => $cost) {
                if (isset($salesValue[$monthKey])) {
                    $salesValue[$monthKey]['costValue'] = $cost['costValue'];
                }
            }

            //Cost per group
            $last30daysCostPerGroup = Expenses::where([['created_at', '>', $last30days]])
                ->groupBy('expenses_category_id')
                ->select('id', 'expenses_category_id', DB::raw('SUM(amount) AS amount'))->get();
            $last30daysCostPerGroupLabels = [];
            $last30daysCostPerGroupValues = [];
            foreach ($last30daysCostPerGroup as $key => $category) {
                array_push($last30daysCostPerGroupLabels, $category->category->name);
                array_push($last30daysCostPerGroupValues, $category->amount);
            }

            //Cost per vedor
            $last30daysCostPerVendor = Expenses::where([['created_at', '>', $last30days]])
                ->groupBy('expenses_vendor_id')
                ->select('id', 'expenses_vendor_id', DB::raw('SUM(amount) AS amount'))->get();

            $last30daysCostPerVendorLabels = [];
            $last30daysCostPerVendorValues = [];
            foreach ($last30daysCostPerVendor as $key => $vendor) {
                array_push($last30daysCostPerVendorLabels, $vendor->vendor->name);
                array_push($last30daysCostPerVendorValues, $vendor->amount);
            }

            $expenses = [
                'last30daysCostValue' => $last30daysCostValue,
                'costValue' => $costValue,
                'last30daysCostPerGroupLabels' => $last30daysCostPerGroupLabels,
                'last30daysCostPerGroupValues' => $last30daysCostPerGroupValues,
                'last30daysCostPerVendorLabels' => $last30daysCostPerVendorLabels,
                'last30daysCostPerVendorValues' => $last30daysCostPerVendorValues,
            ];
        }

        $monthList = [];
        foreach ($salesValue as $key => $salerecord) {
            array_push($monthList, $salerecord['monthName']);
        }

        $availableLanguagesENV = config('settings.front_languages');
        $exploded = explode(',', $availableLanguagesENV);
        $availableLanguages = [];
        for ($i = 0; $i < count($exploded); $i += 2) {
            $availableLanguages[$exploded[$i]] = $exploded[$i + 1];
        }

        $countItems = 0;
        if (auth()->user()->hasRole('admin')) {
            $countItems = Restorant::count();
        }
        if (auth()->user()->hasRole('owner')) {
            if (auth()->user()->restorant && auth()->user()->restorant->categories) {
                $countItems = Items::whereIn('category_id', auth()->user()->restorant->categories->pluck('id')->toArray())->whereNull('deleted_at')->count();
            }
        }
                //== Dashboard extras ==
        $todayStart = Carbon::today();
        $todayOrdersCount = Order::where('created_at', '>=', $todayStart)->whereDoesntHave('status', $notRejected)->count();
        $todaySalesValue = Order::where('created_at', '>=', $todayStart)->whereDoesntHave('status', $notRejected)->sum(DB::raw('order_price + delivery_price'));
        $yesterdayOrdersCount = Order::whereBetween('created_at', [Carbon::yesterday(), $todayStart])->whereDoesntHave('status', $notRejected)->count();
        $yesterdaySalesValue = Order::whereBetween('created_at', [Carbon::yesterday(), $todayStart])->whereDoesntHave('status', $notRejected)->sum(DB::raw('order_price + delivery_price'));

        $sales30 = is_numeric($last30daysOrdersValue['order_price']) ? $last30daysOrdersValue['order_price'] : 0;
        $avgOrderValue = $last30daysOrders > 0 ? round($sales30 / $last30daysOrders, 2) : 0;

        $totalOrdersAllTime = Order::count();
        $allViewsCount = auth()->user()->hasRole('owner') ? auth()->user()->restorant->views : Restorant::sum('views');
        $conversionRate = $allViewsCount > 0 ? round($totalOrdersAllTime / $allViewsCount * 100, 1) : 0;

        $dailyRaw = Order::where('created_at', '>=', Carbon::today()->subDays(13))
            ->whereDoesntHave('status', $notRejected)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('ROUND(SUM(order_price + delivery_price),2) as total'), DB::raw('COUNT(id) as cnt'))
            ->get()->keyBy('day');
        $dailyLabels = [];
        $dailyValues = [];
        $dailyCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $k = $d->format('Y-m-d');
            $dailyLabels[] = $d->locale(config('app.locale'))->isoFormat('D MMM');
            $dailyValues[] = isset($dailyRaw[$k]) ? (float) $dailyRaw[$k]->total : 0;
            $dailyCounts[] = isset($dailyRaw[$k]) ? (int) $dailyRaw[$k]->cnt : 0;
        }

        $latestOrders = Order::orderBy('id', 'desc')->limit(5)->get();

        $topItemsQuery = DB::table('order_has_items')
            ->join('orders', 'orders.id', '=', 'order_has_items.order_id')
            ->join('items', 'items.id', '=', 'order_has_items.item_id')
            ->where('orders.created_at', '>', $last30days)
            ->whereNull('orders.deleted_at');
        if (session('restaurant_id')) {
            $topItemsQuery->where('orders.restorant_id', session('restaurant_id'));
        }
        $topItems = $topItemsQuery->groupBy('order_has_items.item_id', 'items.name')
            ->orderByDesc(DB::raw('SUM(order_has_items.qty)'))
            ->select('items.name', DB::raw('SUM(order_has_items.qty) as qty'))
            ->limit(5)->get();

        $orderTypes = Order::where('created_at', '>', $last30days)
            ->groupBy('delivery_method')
            ->select('delivery_method', DB::raw('count(id) as cnt'))
            ->pluck('cnt', 'delivery_method');

        $dataToDisplay = [
            'availableLanguages' => $availableLanguages,
            'locale' => $locale,
            'expenses' => $expenses,
            'doWeHaveExpensesApp' => $doWeHaveExpensesApp,
            'last30daysOrders' => $last30daysOrders,
            'last30daysOrdersValue' => $last30daysOrdersValue,
            'allViews' => auth()->user()->hasRole('owner') ? auth()->user()->restorant->views : Restorant::sum('views'),
            'salesValue' => $salesValue,
            'monthLabels' => $monthList,
            'countItems' => $countItems,
            'todayOrdersCount' => $todayOrdersCount,
            'todaySalesValue' => $todaySalesValue,
            'yesterdayOrdersCount' => $yesterdayOrdersCount,
            'yesterdaySalesValue' => $yesterdaySalesValue,
            'avgOrderValue' => $avgOrderValue,
            'conversionRate' => $conversionRate,
            'dailyLabels' => $dailyLabels,
            'dailyValues' => $dailyValues,
            'dailyCounts' => $dailyCounts,
            'latestOrders' => $latestOrders,
            'topItems' => $topItems,
            'orderTypes' => $orderTypes,
            'last30daysDeliveryFee' => $last30daysDeliveryFee,
            'last30daysStaticFee' => $last30daysStaticFee,
            'last30daysDynamicFee' => $last30daysDynamicFee,
            'last30daysTotalFee' => $last30daysTotalFee,
        ];

        $response = new \Illuminate\Http\Response(view('dashboard', $dataToDisplay));
        $response->withCookie(cookie('lang', $locale, 120));
        App::setLocale(strtolower($locale));

        return $response;
    }
}
