<?php

namespace App\Http\Controllers;

use App\Notifications\OrderDelayed;
use App\Notifications\OrderNotification;
use App\Order;
use App\Services\CarPickup;
use App\Services\PrepTime;
use App\Services\Trust;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * شاشة الكاشير — تابلت المقهى.
 *
 * القاعدة الحاكمة (imenu-cashier-screen-spec.md): **ثلاثة إجراءات فقط**
 * جاهز · سُلّم · لم يُستلم. وشريط الحالة أعلى الشاشة ليس زرًا رابعًا —
 * هو حالة المقهى (مدة التجهيز · مفتاح السيارة)، لا إجراء على طلب.
 *
 * الترتيب على الشاشة يتبع **الإلحاح لا وقت الطلب**، وتحديد الحالة يُحسب هنا
 * لا في القالب — فالمنطق يُختبر ويُقرأ، والقالب يعرض.
 */
class CashierController extends Controller
{
    private const STATUS_ACCEPTED = 3;

    private const STATUS_PREPARED = 5;

    private const STATUS_DELIVERED = 7;

    /** الحالات التي تُخرج الطلب من الشاشة */
    private const CLOSED_STATUSES = [7, 8, 9, 11];

    /** ترتيب الإلحاح — الرقم الأصغر يتصدّر */
    private const URGENCY = [
        'arrived' => 0,
        'late' => 1,
        'ready' => 2,
        'preparing' => 3,
        'not_collected' => 4,
    ];

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $orders = Order::where('restorant_id', $vendor->id)
            ->where('created_at', '>=', Carbon::now()->subHours(18))
            ->with(['items', 'status', 'client'])
            ->orderByDesc('id')
            ->get();

        $cards = [];
        foreach ($orders as $order) {
            $statusIds = $order->status->pluck('id')->all();

            //الطلب المغلق يخرج من الشاشة — إلا المسجّل «لم يُستلم» فيبقى باهتًا
            if (count(array_intersect($statusIds, self::CLOSED_STATUSES)) > 0 && ! Trust::isReported($order)) {
                continue;
            }

            $cards[] = $this->card($order, $statusIds);
        }

        usort($cards, function ($a, $b) {
            if ($a['urgency'] !== $b['urgency']) {
                return $a['urgency'] <=> $b['urgency'];
            }

            //داخل المجموعة: الأقدم أولًا — من انتظر أكثر يُخدَم أولًا
            return $a['order']->id <=> $b['order']->id;
        });

        return view('cashier.index', [
            'vendor' => $vendor,
            'cards' => $cards,
            'activeCount' => count(array_filter($cards, fn ($c) => $c['state'] !== 'not_collected')),
            'prepMinutes' => PrepTime::minutes($vendor),
            'prepIsAuto' => PrepTime::isAuto($vendor),
            'accuracy' => Trust::vendorAccuracy($vendor),
            'carOn' => CarPickup::isOn($vendor),
            'carMinutesLeft' => CarPickup::minutesLeft($vendor),
        ]);
    }

    /** حالة البطاقة ولونها وأزرارها — كل ما يحتاجه القالب، محسوبًا مرة واحدة */
    private function card(Order $order, array $statusIds): array
    {
        $isPrepared = in_array(self::STATUS_PREPARED, $statusIds);
        $isDelivered = in_array(self::STATUS_DELIVERED, $statusIds);
        $arrivedAt = $order->getConfig('arrived_at', false);
        $promise = $order->getConfig('ready_promise_at', false);
        $promiseAt = $promise ? Carbon::parse($promise) : null;
        $isCar = $order->getConfig('pickup_method', '') === 'car';

        if (Trust::isReported($order)) {
            $state = 'not_collected';
        } elseif ($arrivedAt && ! $isDelivered) {
            $state = 'arrived';
        } elseif ($isPrepared) {
            $state = 'ready';
        } elseif ($promiseAt && $promiseAt->isPast()) {
            $state = 'late';
        } else {
            $state = 'preparing';
        }

        //«جديد» حالة بصرية للدقيقة الأولى فقط، ثم تهدأ تلقائيًا
        $isNew = $state === 'preparing' && $order->created_at->diffInSeconds(Carbon::now()) < 60;

        $reportReason = null;
        $canReport = Trust::canReport($order, $reportReason);

        return [
            'order' => $order,
            'state' => $state,
            'urgency' => self::URGENCY[$state],
            'is_new' => $isNew,
            'is_car' => $isCar,
            'promise_at' => $promiseAt,
            'minutes_late' => ($state === 'late' && $promiseAt) ? $promiseAt->diffInMinutes(Carbon::now()) : 0,
            'can_report' => $canReport,
            'report_reason' => $reportReason,
            'trust' => Trust::clientLabel($order->client),
            'vehicle' => [
                'brand' => trim($order->getConfig('vehicle_brand', '').' '.$order->getConfig('vehicle_model', '')),
                'color' => $order->getConfig('vehicle_color', ''),
                'plate' => $order->getConfig('vehicle_plate', ''),
                'spot' => $order->getConfig('parking_spot', ''),
            ],
        ];
    }

    /** الإجراء الأول: جاهز */
    public function ready(Request $request)
    {
        $order = $this->order($request);
        $statusIds = $order->status->pluck('id')->all();

        //سلسلة الحالات تبقى متّسقة وإن لم يضغط أحد «قبول» — الشاشة ثلاثة أزرار
        if (! in_array(self::STATUS_ACCEPTED, $statusIds)) {
            $order->status()->attach([self::STATUS_ACCEPTED => ['comment' => 'Cashier screen', 'user_id' => auth()->user()->id]]);
        }

        if (! in_array(self::STATUS_PREPARED, $statusIds)) {
            $order->status()->attach([self::STATUS_PREPARED => ['comment' => 'Cashier screen', 'user_id' => auth()->user()->id]]);

            $this->notifyClient($order, self::STATUS_PREPARED);
        }

        return redirect()->route('cashier.index');
    }

    /** الإجراء الثاني: سُلّم */
    public function delivered(Request $request)
    {
        $order = $this->order($request);
        $statusIds = $order->status->pluck('id')->all();

        if (in_array(self::STATUS_DELIVERED, $statusIds)) {
            return redirect()->route('cashier.index');
        }

        //لا يُسلَّم طلب لم يُعلَّم جاهزًا — الختم شرط نظام الثقة
        if (! in_array(self::STATUS_PREPARED, $statusIds)) {
            $order->status()->attach([self::STATUS_PREPARED => ['comment' => 'Cashier screen', 'user_id' => auth()->user()->id]]);
        }

        $order->status()->attach([self::STATUS_DELIVERED => ['comment' => 'Cashier screen', 'user_id' => auth()->user()->id]]);

        if ($order->payment_status != 'paid') {
            $order->payment_method = $order->payment_method ?: 'cod';
            $order->payment_status = 'paid';
            $order->update();
        }

        Trust::onDelivered($order);

        return redirect()->route('cashier.index');
    }

    /**
     * التأخير: +٥ أو +١٠ — بلا نافذة حاجبة.
     * الطريق الوحيد إلى «جاهز» يظل الزر الأصلي، فالتمديد لا يُنهي الطلب.
     */
    public function delay(Request $request)
    {
        $order = $this->order($request);

        $add = (int) $request->minutes;
        if (! in_array($add, [5, 10], true)) {
            return redirect()->route('cashier.index');
        }

        $current = $order->getConfig('ready_promise_at', false);
        $base = $current ? Carbon::parse($current) : Carbon::now();

        //التمديد من الآن إن كان الوعد قد فات — وإلا صار التمديد بلا أثر محسوس
        if ($base->isPast()) {
            $base = Carbon::now();
        }

        $newPromise = $base->copy()->addMinutes($add);
        $order->setConfig('ready_promise_at', $newPromise->toDateTimeString());

        //يُبلَّغ العميل تلقائيًا ويحدّث عدّاده
        try {
            if ($order->client) {
                $order->client->notify(new OrderDelayed($order, $newPromise->format('H:i')));
            }
        } catch (\Throwable $th) {
            \Log::error('OrderDelayed notify failed: '.$th->getMessage());
        }

        return redirect()->route('cashier.index');
    }

    /** مفتاح الاستلام من السيارة — لحظي، والعودة التلقائية خاصية للبيانات */
    public function toggleCar(Request $request)
    {
        CarPickup::toggle($this->vendor());

        return redirect()->route('cashier.index');
    }

    /* ------------------------------------------------------------------ */

    private function vendor()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $vendor = $user->hasRole('staff')
            ? \App\Restorant::find($user->restaurant_id)
            : $user->restorant;

        abort_unless($vendor, 403, 'No restaurant for this user');

        return $vendor;
    }

    private function order(Request $request): Order
    {
        $order = Order::findOrFail($request->order_id);
        abort_unless($order->restorant_id == $this->vendor()->id, 403);

        return $order;
    }

    private function notifyClient(Order $order, $statusId): void
    {
        try {
            if ($order->client) {
                $order->client->notify(new OrderNotification($order, $statusId));
            }
        } catch (\Throwable $th) {
            \Log::error('Cashier notifyClient failed: '.$th->getMessage());
        }
    }
}
