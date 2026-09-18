<?php

namespace App\Services;

use App\Order;
use App\Restorant;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * نظام الثقة — الطلب غير المستلَم.
 *
 * القواعد المعتمدة (imenu-pickup-strategy.md):
 *  1. البلاغ لا يُقبل إلا بعد تعليم «جاهز» بختم زمني، وضمن نافذة ساعتين.
 *  2. ٣ استلامات ناجحة متتالية ← «موثوق».
 *  3. ٣ بلاغات صحيحة / ٩٠ يومًا ← حظر شهر + إشعار بالسبب + حق تظلّم.
 *  4. للعميل زر اعتراض — الاعتراض يوقف عدّ البلاغ حتى يُحسم.
 *  5. المقهى مُقاس أيضًا: الفرق بين الوعد وختم «جاهز» = درجة دقته.
 *
 * الشرط البنيوي: الطلب الذي لم يُعلَّم «جاهز» لا يمكن أن يُحسب على العميل —
 * لأن canReport تشترط الختم. قاعدة يفرضها البناء لا نص يسهل خرقه.
 *
 * كل شيء في جدول configs عبر HasConfig — بلا هجرة قاعدة بيانات.
 */
class Trust
{
    /** نافذة البلاغ بعد «جاهز» */
    public const REPORT_WINDOW_HOURS = 2;

    /** استلامات متتالية ← موثوق */
    public const TRUSTED_STREAK = 3;

    /** عدد البلاغات الصحيحة الذي يوجب الحظر */
    public const BLOCK_REPORTS = 3;

    /** نافذة عدّ البلاغات */
    public const BLOCK_WINDOW_DAYS = 90;

    /** مدة الحظر */
    public const BLOCK_DAYS = 30;

    private const STATUS_PREPARED = 5;

    private const STATUS_DELIVERED = 7;

    /* ------------------------------------------------------------------
     | ختم «جاهز»
     |------------------------------------------------------------------ */

    /** وقت تعليم الطلب جاهزًا، أو null إن لم يُعلَّم */
    public static function readyAt(Order $order): ?Carbon
    {
        $stamp = DB::table('order_has_status')
            ->where('order_id', $order->id)
            ->where('status_id', self::STATUS_PREPARED)
            ->orderBy('id')
            ->value('created_at');

        return $stamp ? Carbon::parse($stamp) : null;
    }

    public static function isDelivered(Order $order): bool
    {
        return DB::table('order_has_status')
            ->where('order_id', $order->id)
            ->where('status_id', self::STATUS_DELIVERED)
            ->exists();
    }

    /* ------------------------------------------------------------------
     | البلاغ
     |------------------------------------------------------------------ */

    /**
     * هل يجوز للمقهى الإبلاغ عن هذا الطلب؟
     * سبب الرفض يعود نصًّا في $reason ليُعرض للمقهى بدل زر ميت.
     */
    public static function canReport(Order $order, &$reason = null): bool
    {
        if ($order->getConfig('not_collected_at', false)) {
            $reason = __('Already reported');

            return false;
        }

        if (self::isDelivered($order)) {
            $reason = __('This order was handed over');

            return false;
        }

        $readyAt = self::readyAt($order);
        if ($readyAt === null) {
            $reason = __('Mark the order ready first');

            return false;
        }

        if ($readyAt->diffInMinutes(Carbon::now()) > self::REPORT_WINDOW_HOURS * 60) {
            $reason = __('The reporting window has closed');

            return false;
        }

        return true;
    }

    /** تسجيل البلاغ. يُرجع false إن لم تتحقق الشروط. */
    public static function report(Order $order, $byUserId): bool
    {
        if (! self::canReport($order)) {
            return false;
        }

        $order->setMultipleConfig([
            'not_collected_at' => Carbon::now()->toDateTimeString(),
            'not_collected_by' => $byUserId,
        ]);

        //الاستلامات المتتالية تنقطع فورًا — والحظر يُراجع بعد نافذة الاعتراض
        $client = $order->client_id ? User::find($order->client_id) : null;
        if ($client) {
            $client->setConfig('pickup_streak', 0);
            self::reviewBlock($client);
        }

        return true;
    }

    /** اعتراض العميل — يوقف عدّ هذا البلاغ حتى يُحسم */
    public static function dispute(Order $order): bool
    {
        if (! $order->getConfig('not_collected_at', false)) {
            return false;
        }
        if ($order->getConfig('dispute_at', false)) {
            return false;
        }

        $order->setConfig('dispute_at', Carbon::now()->toDateTimeString());

        $client = $order->client_id ? User::find($order->client_id) : null;
        if ($client) {
            self::reviewBlock($client);
        }

        return true;
    }

    public static function isReported(Order $order): bool
    {
        return (bool) $order->getConfig('not_collected_at', false);
    }

    public static function isDisputed(Order $order): bool
    {
        return (bool) $order->getConfig('dispute_at', false);
    }

    /* ------------------------------------------------------------------
     | العميل
     |------------------------------------------------------------------ */

    /** استلام ناجح — يُنادى عند تعليم الطلب «سُلّم» */
    public static function onDelivered(Order $order): void
    {
        if (! $order->client_id) {
            return;
        }

        $client = User::find($order->client_id);
        if (! $client) {
            return;
        }

        $client->setMultipleConfig([
            'pickup_streak' => ((int) $client->getConfig('pickup_streak', 0)) + 1,
            'pickup_total' => ((int) $client->getConfig('pickup_total', 0)) + 1,
        ]);
    }

    public static function streak(?User $client): int
    {
        return $client ? (int) $client->getConfig('pickup_streak', 0) : 0;
    }

    public static function isTrusted(?User $client): bool
    {
        return self::streak($client) >= self::TRUSTED_STREAK;
    }

    /**
     * البلاغات الصحيحة في النافذة — الصحيح هو ما لم يُعترض عليه.
     * تُحسب من الطلبات نفسها لا من عدّاد، فلا تنحرف عن الحقيقة.
     */
    public static function validReports(User $client): int
    {
        $since = Carbon::now()->subDays(self::BLOCK_WINDOW_DAYS);

        $orderIds = DB::table('orders')
            ->where('client_id', $client->id)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since->copy()->subDays(2))
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return 0;
        }

        $reported = DB::table('configs')
            ->where('model_type', \App\Order::class)
            ->whereIn('model_id', $orderIds)
            ->where('key', 'not_collected_at')
            ->pluck('value', 'model_id');

        $disputed = DB::table('configs')
            ->where('model_type', \App\Order::class)
            ->whereIn('model_id', $orderIds)
            ->where('key', 'dispute_at')
            ->pluck('model_id')
            ->all();

        $count = 0;
        foreach ($reported as $orderId => $reportedAt) {
            if (in_array($orderId, $disputed)) {
                continue;
            }
            if (Carbon::parse($reportedAt)->lt($since)) {
                continue;
            }
            $count++;
        }

        return $count;
    }

    /** يُعيد حساب الحظر بعد كل بلاغ أو اعتراض */
    public static function reviewBlock(User $client): void
    {
        if (self::validReports($client) >= self::BLOCK_REPORTS) {
            if (! self::blockedUntil($client)) {
                $client->setConfig('blocked_until', Carbon::now()->addDays(self::BLOCK_DAYS)->toDateTimeString());
            }
        }
    }

    public static function blockedUntil(?User $client): ?Carbon
    {
        if (! $client) {
            return null;
        }
        $until = $client->getConfig('blocked_until', false);
        if (! $until) {
            return null;
        }
        $until = Carbon::parse($until);

        return $until->isFuture() ? $until : null;
    }

    public static function isBlocked(?User $client): bool
    {
        return self::blockedUntil($client) !== null;
    }

    /** تسمية الواجهة — «لم يُستلم» لا «بلاغ» */
    public static function clientLabel(?User $client): array
    {
        if (self::isBlocked($client)) {
            return ['key' => 'blocked', 'text' => __('Ordering paused'), 'color' => '#B3261E'];
        }
        if (self::isTrusted($client)) {
            return ['key' => 'trusted', 'text' => __('Trusted customer'), 'color' => '#2E5C43'];
        }

        return ['key' => 'new', 'text' => __('New customer'), 'color' => '#A08977'];
    }

    /* ------------------------------------------------------------------
     | المقهى — الالتزام طرفان
     |------------------------------------------------------------------ */

    /**
     * درجة دقة المقهى: نسبة الطلبات التي وُفِّي فيها بالوعد.
     * الوعد محفوظ على الطلب لحظة إنشائه (ready_promise_at).
     * تُرجع null إن لم تكفِ العيّنة — لا نُصدر درجة من طلبين.
     */
    public static function vendorAccuracy(Restorant $vendor, $sample = 20): ?int
    {
        $orderIds = DB::table('orders')
            ->where('restorant_id', $vendor->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->limit($sample * 2)
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return null;
        }

        $promises = DB::table('configs')
            ->where('model_type', \App\Order::class)
            ->whereIn('model_id', $orderIds)
            ->where('key', 'ready_promise_at')
            ->pluck('value', 'model_id');

        if ($promises->isEmpty()) {
            return null;
        }

        $stamps = DB::table('order_has_status')
            ->whereIn('order_id', $promises->keys()->all())
            ->where('status_id', self::STATUS_PREPARED)
            ->orderBy('id')
            ->pluck('created_at', 'order_id');

        $onTime = $total = 0;
        foreach ($promises as $orderId => $promise) {
            if (! isset($stamps[$orderId])) {
                continue;
            }
            $total++;
            if (Carbon::parse($stamps[$orderId])->lte(Carbon::parse($promise))) {
                $onTime++;
            }
            if ($total >= $sample) {
                break;
            }
        }

        if ($total < 5) {
            return null;
        }

        return (int) round($onTime * 100 / $total);
    }
}
