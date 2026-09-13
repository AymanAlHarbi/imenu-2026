<?php

namespace App\Http\Controllers;

use App\Exports\FinancesExport;
use App\Exports\SalesExport;
use App\Order;
use App\Restorant;
use App\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;

class FinanceController extends Controller
{
    private function getResources()
    {
        //iMenu 2026 - هذه القوائم تغذّي فلاتر المدير وحده. تحميلها لصاحب المطعم كان ثلاثة
        //استعلامات ثقيلة (كل المطاعم وكل العملاء وكل السائقين) في كل فتح للصفحة، بلا أي استخدام.
        $loadFilterLists = auth()->user()->hasAnyRole(['admin', 'driver']);

        $restorants = $loadFilterLists ? Restorant::where(['active' => 1])->get() : collect();
        $drivers = $loadFilterLists ? User::role('driver')->where(['active' => 1])->get() : collect();
        $clients = $loadFilterLists ? User::role('client')->where(['active' => 1])->get() : collect();

        $orders = Order::orderBy('created_at', 'desc');

        //Get client's orders
        if (auth()->user()->hasRole('client')) {
            $orders = $orders->where(['client_id' => auth()->user()->id]);
        } elseif (auth()->user()->hasRole('driver')) {
            $orders = $orders->where(['driver_id' => auth()->user()->id]);
        //Get owner's restorant orders
        } elseif (auth()->user()->hasRole('owner')) {
            $orders = $orders->where(['restorant_id' => auth()->user()->restorant->id]);
        }

        //FILTER BT RESTORANT
        if (isset($_GET['restorant_id'])) {
            $orders = $orders->where(['restorant_id' => $_GET['restorant_id']]);
        }
        //If restorant owner, get his restorant orders only
        if (auth()->user()->hasRole('owner')) {
            //Current restorant id
            $restorant_id = auth()->user()->restorant->id;
            $orders = $orders->where(['restorant_id' => $restorant_id]);
        }

        //BY CLIENT
        if (isset($_GET['client_id'])) {
            $orders = $orders->where(['client_id' => $_GET['client_id']]);
        }

        //BY DRIVER
        if (isset($_GET['driver_id'])) {
            $orders = $orders->where(['driver_id' => $_GET['driver_id']]);
        }

        //BY DATE FROM
        if (isset($_GET['fromDate']) && strlen($_GET['fromDate']) > 3) {
            $orders = $orders->whereDate('created_at', '>=', $_GET['fromDate']);
        }

        //BY DATE TO
        if (isset($_GET['toDate']) && strlen($_GET['toDate']) > 3) {
            $orders = $orders->whereDate('created_at', '<=', $_GET['toDate']);
        }

        return ['orders' => $orders, 'restorants' => $restorants, 'drivers' => $drivers, 'clients' => $clients];
    }

    public function adminFinances()
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $resources = $this->getResources();
        $resources['orders'] = $resources['orders']->where('payment_status', 'paid')->whereNotNull('payment_method');

        //With downloaod
        if (isset($_GET['report'])) {
            $items = [];
            foreach ($resources['orders']->get() as $key => $order) {
                $item = [
                    'order_id' => $order->id,
                    'restaurant_name' => $order->restorant->name,
                    'restaurant_id' => $order->restorant_id,
                    'created' => $order->created_at,
                    'last_status' => $order->status->pluck('alias')->last(),
                    'client_name' => $order->client ? $order->client->name : '',
                    'client_id' => $order->client_id,
                    'address' => $order->address ? $order->address->address : '',
                    'address_id' => $order->address_id,
                    'driver_name' => $order->driver ? $order->driver->name : '',
                    'driver_id' => $order->driver_id,
                    'payment_method' => $order->payment_method,
                    'srtipe_payment_id' => $order->srtipe_payment_id,
                    'restaurant_fee' => $order->fee,
                    'order_fee' => $order->fee_value,
                    'restaurant_static_fee' => $order->static_fee,
                    'platform_fee' => $order->fee_value + $order->static_fee,
                    'processor_fee' => $order->payment_processor_fee,
                    'delivery' => $order->delivery_price,
                    'net_price_with_vat' => $order->order_price_with_discount,
                    'discount' => $order->discount,
                    'vat' => $order->vatvalue,
                    'net_price' => $order->order_price_with_discount - $order->vatvalue,
                    'order_total' => $order->delivery_price + $order->order_price_with_discount,
                ];
                array_push($items, $item);
            }

            return Excel::download(new FinancesExport($items), 'finances_'.time().'.xlsx');
        }

        //CARDS
        $cards = [
            ['title' => 'Orders', 'value' => 0],
            ['title' => 'Total', 'value' => 0, 'isMoney' => true],
            ['title' => 'Platform Fee', 'value' => 0, 'isMoney' => true],
            ['title' => 'Net', 'value' => 0, 'isMoney' => true],

            ['title' => 'Processor fee', 'value' => 0, 'isMoney' => true],
            ['title' => 'Deliveries', 'value' => 0],
            ['title' => 'Delivery income', 'value' => 0, 'isMoney' => true],
            ['title' => 'Platform profit', 'value' => 0, 'isMoney' => true],
        ];
        foreach ($resources['orders']->get() as $key => $order) {
            $cards[0]['value'] += 1;
            $cards[1]['value'] += $order->delivery_price + $order->order_price_with_discount;
            $cards[2]['value'] += $order->fee_value + $order->static_fee;
            $cards[3]['value'] += $order->order_price_with_discount - $order->fee_value - $order->static_fee;

            $cards[4]['value'] += $order->payment_processor_fee;
            $cards[5]['value'] += $order->delivery_method.'' == '1' ? 1 : 0;
            $cards[6]['value'] += $order->delivery_price;
            $cards[7]['value'] += $order->fee_value + $order->static_fee + $order->delivery_price - $order->payment_processor_fee;
        }

        $displayParam = [
            'cards' => $cards,
            'orders' => $resources['orders']->paginate(10),
            'restorants' => $resources['restorants'],
            'drivers' => $resources['drivers'],
            'clients' => $resources['clients'],
            'parameters' => count($_GET) != 0,
            'statuses' => Status::pluck('name', 'id')->toArray(),
        ];

        return view('finances.index', $displayParam);
    }

    public function ownerFinances()
    {
        if (! auth()->user()->hasRole('owner')) {
            abort(403, 'Unauthorized action.');
        }

        //Find this owner restaurant
        $restaurant = auth()->user()->restorant;

        //Change currency
        \App\Services\ConfChanger::switchCurrency($restaurant);

        // iMenu 2026 - صفحة «المبيعات» (استلام فقط، صفر عمولة).
        // الصفحة الأصلية أسفل باقية كما هي؛ PICKUP_ONLY=false يعيدها بكل بطاقاتها.
        if (config('settings.pickup_only')) {
            return $this->ownerSales($restaurant);
        }

        //Check if Owner has completed
        $stripe_details_submitted = __('No');
        if (auth()->user()->stripe_account) {
            //Set our key
            Stripe::setApiKey(config('settings.stripe_secret'));

            $stripe_details_submitted = Account::retrieve(
                auth()->user()->stripe_account, []
            )->details_submitted ? __('Yes') : __('No');
        }

        $resources = $this->getResources();

        $resources['orders'] = $resources['orders']->whereNotNull('payment_method')->where('payment_status', 'paid');

        //With downloaod
        if (isset($_GET['report'])) {
            $items = [];
            foreach ($resources['orders']->get() as $key => $order) {
                $item = [
                    'order_id' => $order->id,
                    'restaurant_name' => $order->restorant->name,
                    'restaurant_id' => $order->restorant_id,
                    'created' => $order->created_at,
                    'last_status' => $order->status->pluck('alias')->last(),
                    'client_name' => $order->client ? $order->client->name : '',
                    'client_id' => $order->client_id,
                    'address' => $order->address ? $order->address->address : '',
                    'address_id' => $order->address_id,
                    'driver_name' => $order->driver ? $order->driver->name : '',
                    'driver_id' => $order->driver_id,
                    'payment_method' => $order->payment_method,
                    'srtipe_payment_id' => $order->srtipe_payment_id,
                    'restaurant_fee' => $order->fee,
                    'order_fee' => $order->fee_value,
                    'restaurant_static_fee' => $order->static_fee,
                    'platform_fee' => $order->fee_value + $order->static_fee,
                    'processor_fee' => $order->payment_processor_fee,
                    'delivery' => $order->delivery_price,
                    'net_price_with_vat' => $order->order_price_with_discount,
                    'vat' => $order->vatvalue,
                    'net_price' => $order->order_price_with_discount - $order->vatvalue,
                    'order_total' => $order->delivery_price + $order->order_price_with_discount,
                    'discount' => $order->discount,
                ];
                array_push($items, $item);
            }

            return Excel::download(new FinancesExport($items), 'finances_'.time().'.xlsx');
        }

        //CARDS
        $cards = [
            ['title' => 'Orders', 'value' => 0],
            ['title' => 'Total', 'value' => 0, 'isMoney' => true],
            ['title' => 'Platform Fee', 'value' => 0, 'isMoney' => true],
            ['title' => 'Net inc. Vat', 'value' => 0, 'isMoney' => true],

            ['title' => 'VAT', 'value' => 0, 'isMoney' => true],
            ['title' => 'Net', 'value' => 0, 'isMoney' => true],
            ['title' => 'Deliveries', 'value' => 0],
            ['title' => 'Delivery cost', 'value' => 0, 'isMoney' => true],
        ];
        foreach ($resources['orders']->get() as $key => $order) {
            $cards[0]['value'] += 1;
            $cards[1]['value'] += $order->delivery_price + $order->order_price_with_discount;
            $cards[2]['value'] += $order->fee_value + $order->static_fee;
            $cards[3]['value'] += $order->order_price_with_discount - $order->fee_value - $order->static_fee;

            $cards[4]['value'] += $order->vatvalue;
            $cards[5]['value'] += $order->order_price_with_discount - $order->vatvalue - $order->fee_value - $order->static_fee;
            $cards[6]['value'] += $order->delivery_method.'' == '1' ? 1 : 0;
            $cards[7]['value'] += $order->delivery_price;
        }

        $displayParam = [
            'cards' => $cards,
            'orders' => $resources['orders']->paginate(10),
            'restorants' => $resources['restorants'],
            'drivers' => $resources['drivers'],
            'clients' => $resources['clients'],
            'parameters' => count($_GET) != 0,
            'stripe_details_submitted' => $stripe_details_submitted,
            'showFeeTerms' => true,
            'showStripeConnect' => true,
            'restaurant' => $restaurant,
            'weHaveStripeConnect' => env('ENABLE_STRIPE_CONNECT', false),
            'statuses' => Status::pluck('name', 'id')->toArray(),
        ];

        return view('finances.index', $displayParam);
    }

    /**
     * iMenu 2026 - الفترة المختارة.
     * ?range=today|yesterday|week|month|lastmonth|custom — والافتراضي هذا الشهر،
     * فلا تُحمَّل كل طلبات المقهى منذ التأسيس في كل فتح للصفحة.
     */
    private function resolveRange(): array
    {
        $today = Carbon::today();
        $range = request()->query('range');
        $from = (string) request()->query('fromDate');
        $to = (string) request()->query('toDate');

        if (! $range && (strlen($from) > 3 || strlen($to) > 3)) {
            $range = 'custom';
        }

        $parse = function ($value, $fallback) {
            try {
                return strlen((string) $value) > 3 ? Carbon::parse($value)->startOfDay() : $fallback;
            } catch (\Exception $e) {
                return $fallback;
            }
        };

        switch ($range) {
            case 'today':
                return [$today->copy(), $today->copy(), 'today'];
            case 'yesterday':
                return [$today->copy()->subDay(), $today->copy()->subDay(), 'yesterday'];
            case 'week':
                //الأسبوع يبدأ الأحد في السوق السعودي
                return [$today->copy()->startOfWeek(Carbon::SUNDAY), $today->copy(), 'week'];
            case 'lastmonth':
                $month = $today->copy()->subMonthNoOverflow();

                return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth(), 'lastmonth'];
            case 'custom':
                return [
                    $parse($from, $today->copy()->startOfMonth()),
                    $parse($to, $today->copy()),
                    'custom',
                ];
            default:
                return [$today->copy()->startOfMonth(), $today->copy(), 'month'];
        }
    }

    /**
     * iMenu 2026 - صفحة المبيعات لصاحب الكوفي.
     * لا رسوم منصة ولا معالج دفع ولا توصيل ولا سائقين — الأسئلة الثلاثة فقط:
     * كم طلبًا؟ كم بِعت؟ وأين يستلم عملاؤك؟
     */
    private function ownerSales($restaurant)
    {
        [$from, $to, $range] = $this->resolveRange();

        $base = Order::where('restorant_id', $restaurant->id)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString());

        //المستلَمة والمدفوعة — شاشة الكاشير تضبط payment_status عند «سُلّم»
        $paid = (clone $base)->where('payment_status', 'paid')->whereNotNull('payment_method');

        //جولة واحدة على طلبات الفترة: العدد والمبيعات والضريبة
        $count = 0;
        $sales = 0;
        $vat = 0;
        $paidIds = [];
        foreach ((clone $paid)->get() as $order) {
            $count++;
            $sales += $order->order_price_with_discount;
            $vat += $order->vatvalue ?: 0;
            $paidIds[] = $order->id;
        }

        //طريقة الاستلام محفوظة في configs الطلب - استعلام واحد لا استعلام لكل طلب
        $fromCar = count($paidIds) ? DB::table('configs')
            ->where('model_type', Order::class)
            ->whereIn('model_id', $paidIds)
            ->where('key', 'pickup_method')
            ->where('value', 'car')
            ->count() : 0;

        //«لم يُستلم» - على كل طلبات الفترة لا المدفوعة فقط، والمعترَض عليه لا يُعدّ
        $notCollected = 0;
        $allIds = (clone $base)->pluck('id');
        if ($allIds->isNotEmpty()) {
            $reported = DB::table('configs')
                ->where('model_type', Order::class)
                ->whereIn('model_id', $allIds)
                ->where('key', 'not_collected_at')
                ->pluck('model_id');

            if ($reported->isNotEmpty()) {
                $disputed = DB::table('configs')
                    ->where('model_type', Order::class)
                    ->whereIn('model_id', $reported)
                    ->where('key', 'dispute_at')
                    ->pluck('model_id')
                    ->all();

                $notCollected = $reported->reject(fn ($id) => in_array($id, $disputed))->count();
            }
        }

        $withVat = $vat > 0;

        //تنزيل التقرير
        if (request()->has('report')) {
            $rows = [];
            $exportPickup = count($paidIds) ? DB::table('configs')
                ->where('model_type', Order::class)
                ->whereIn('model_id', $paidIds)
                ->where('key', 'pickup_method')
                ->pluck('value', 'model_id') : collect();

            foreach ((clone $paid)->with('items')->orderBy('id', 'desc')->get() as $order) {
                $row = [
                    $order->id_formated,
                    $order->created_at->format('Y-m-d'),
                    $order->created_at->format('H:i'),
                    ($exportPickup[$order->id] ?? '') == 'car' ? __('From my car') : __('Pickup'),
                    $order->items->sum('pivot.qty'),
                ];

                if ($withVat) {
                    $row[] = $order->vatvalue ?: 0;
                    $row[] = $order->order_price_with_discount - ($order->vatvalue ?: 0);
                }

                $row[] = $order->order_price_with_discount;
                array_push($rows, $row);
            }

            return Excel::download(new SalesExport($rows, $withVat), 'sales_'.$from->format('Y-m-d').'_'.$to->format('Y-m-d').'.xlsx');
        }

        $orders = (clone $paid)->with('items')->orderBy('id', 'desc')->paginate(10);

        $pickupMethods = $orders->count() ? DB::table('configs')
            ->where('model_type', Order::class)
            ->whereIn('model_id', $orders->pluck('id'))
            ->where('key', 'pickup_method')
            ->pluck('value', 'model_id') : collect();

        return view('finances.sales', [
            'restaurant' => $restaurant,
            'orders' => $orders,
            'pickupMethods' => $pickupMethods,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'withVat' => $withVat,
            'stats' => [
                'count' => $count,
                'sales' => $sales,
                'average' => $count > 0 ? $sales / $count : 0,
                'vat' => $vat,
                'net' => $sales - $vat,
                'fromCar' => $fromCar,
                'fromShop' => max(0, $count - $fromCar),
                'notCollected' => $notCollected,
                'notCollectedPercent' => $count > 0 ? round($notCollected / $count * 100, 1) : 0,
            ],
        ]);
    }

    public function connect(): RedirectResponse
    {

        //Set our key
        Stripe::setApiKey(config('settings.stripe_secret'));

        if (! auth()->user()->stripe_account) {
            //Create account for client
            $account_id = Account::create([
                'type' => 'standard',
            ])->id;

            //Save this id in user object
            auth()->user()->stripe_account = $account_id;
            auth()->user()->update();
        } else {
            $account_id = auth()->user()->stripe_account;
        }

        //Set account
        $account_links = AccountLink::create([
            'account' => $account_id,
            'refresh_url' => route('finances.owner'),
            'return_url' => route('finances.owner'),
            'type' => 'account_onboarding',
        ]);

        return redirect()->away($account_links->url);
    }
}
