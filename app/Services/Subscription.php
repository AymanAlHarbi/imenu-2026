<?php

namespace App\Services;

use App\Plans;
use App\User;
use Carbon\Carbon;

/**
 * إدارة فترات الخطط وانتهاء الاشتراك.
 *
 * الفترات: ١ شهري، ٢ سنوي، ٣ نصف سنوي.
 * القيمتان ١ و٢ محفوظتان مسبقًا في عمود plan.period ولا يجوز تغييرهما.
 *
 * تاريخ الانتهاء يُخزَّن في جدول configs على المستخدم (بلا migration)
 * تحت المفتاح plan_expires_at بصيغة Y-m-d. القيمة الفارغة = بلا انتهاء.
 */
class Subscription
{
    public const MONTHLY = 1;

    public const YEARLY = 2;

    public const SEMIANNUAL = 3;

    private const KEY = 'plan_expires_at';

    /** ذاكرة داخل الطلب الواحد حتى لا يتكرر الاستعلام مع كل نداء mplanid(). */
    private static $cache = [];

    /* ------------------------------------------------------------------ */
    /*  الفترات                                                           */
    /* ------------------------------------------------------------------ */

    /** الفترات المتاحة: المعرّف => [المفتاح في النموذج، الاسم، عدد الأشهر]. */
    public static function periods(): array
    {
        return [
            self::MONTHLY => ['slug' => 'monthly', 'name' => 'Monthly', 'short' => 'm', 'months' => 1],
            self::SEMIANNUAL => ['slug' => 'semiannual', 'name' => 'Semi-annual', 'short' => '6m', 'months' => 6],
            self::YEARLY => ['slug' => 'anually', 'name' => 'Anually', 'short' => 'y', 'months' => 12],
        ];
    }

    private static function period($period): array
    {
        $periods = self::periods();
        $key = (int) $period;

        return isset($periods[$key]) ? $periods[$key] : $periods[self::MONTHLY];
    }

    /** تحويل قيمة راديو النموذج إلى معرّف الفترة. */
    public static function periodFromRequest($value): int
    {
        foreach (self::periods() as $id => $period) {
            if ($period['slug'] === $value) {
                return $id;
            }
        }

        return self::MONTHLY;
    }

    public static function periodSlug($period): string
    {
        return self::period($period)['slug'];
    }

    public static function periodLabel($period): string
    {
        return __(self::period($period)['name']);
    }

    /** اللاحقة المختصرة بجانب السعر. */
    public static function periodShort($period): string
    {
        return __(self::period($period)['short']);
    }

    public static function periodMonths($period): int
    {
        return self::period($period)['months'];
    }

    /**
     * بداية الفترة الحالية — تُستخدم لعدّ الطلبات ضمن حد الخطة.
     * النصف سنوي: ١ يناير أو ١ يوليو.
     */
    public static function periodStart($period): Carbon
    {
        $key = (int) $period;

        if ($key === self::YEARLY) {
            return Carbon::now()->startOfYear();
        }

        if ($key === self::SEMIANNUAL) {
            $now = Carbon::now();

            return $now->month <= 6
                ? Carbon::create($now->year, 1, 1)->startOfDay()
                : Carbon::create($now->year, 7, 1)->startOfDay();
        }

        return Carbon::now()->startOfMonth();
    }

    /* ------------------------------------------------------------------ */
    /*  انتهاء الاشتراك                                                    */
    /* ------------------------------------------------------------------ */

    public static function freePlanId(): int
    {
        return intval(config('settings.free_pricing_id'));
    }

    /** تاريخ انتهاء اشتراك المستخدم، أو null إذا كان بلا انتهاء. */
    public static function expiresAt(User $user)
    {
        if (array_key_exists($user->id, self::$cache)) {
            return self::$cache[$user->id];
        }

        $raw = $user->getConfig(self::KEY, null);
        $date = null;

        if ($raw) {
            try {
                $date = Carbon::parse($raw)->endOfDay();
            } catch (\Exception $e) {
                $date = null;
            }
        }

        self::$cache[$user->id] = $date;

        return $date;
    }

    /** حفظ تاريخ الانتهاء. مرّر null أو نصًا فارغًا لجعل الاشتراك بلا انتهاء. */
    public static function setExpiry(User $user, $date): void
    {
        $value = null;

        if ($date) {
            try {
                $value = Carbon::parse($date)->toDateString();
            } catch (\Exception $e) {
                $value = null;
            }
        }

        $user->setConfig(self::KEY, $value);
        self::$cache[$user->id] = $value ? Carbon::parse($value)->endOfDay() : null;
    }

    public static function isExpired(User $user): bool
    {
        $expiresAt = self::expiresAt($user);

        return $expiresAt !== null && $expiresAt->isPast();
    }

    /**
     * الأيام المتبقية. سالب = منتهٍ منذ كذا يوم. null = بلا انتهاء.
     */
    public static function daysLeft(User $user)
    {
        $expiresAt = self::expiresAt($user);

        if ($expiresAt === null) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($expiresAt->copy()->startOfDay(), false);
    }

    /** تاريخ انتهاء مقترح لخطة: اليوم + طول فترتها. */
    public static function defaultExpiry($plan)
    {
        if (! $plan instanceof Plans) {
            return null;
        }

        return Carbon::now()->addMonths(self::periodMonths($plan->period))->toDateString();
    }

    /** نص جاهز للعرض على المقهى. */
    public static function label(User $user)
    {
        $expiresAt = self::expiresAt($user);

        if ($expiresAt === null) {
            return null;
        }

        $daysLeft = self::daysLeft($user);

        if ($daysLeft < 0) {
            return __('Your subscription expired on').' '.$expiresAt->toDateString();
        }

        return __('Your subscription is valid until').' '.$expiresAt->toDateString().
            ' ('.$daysLeft.' '.__('days left').')';
    }

    /** لون التنبيه المناسب للمدة المتبقية. */
    public static function alertType(User $user): string
    {
        $daysLeft = self::daysLeft($user);

        if ($daysLeft === null) {
            return 'success';
        }

        if ($daysLeft < 0) {
            return 'danger';
        }

        return $daysLeft <= 14 ? 'warning' : 'success';
    }

    /** تفريغ الذاكرة الداخلية — للاختبارات أو بعد تغيير الخطة. */
    public static function forget(User $user): void
    {
        unset(self::$cache[$user->id]);
    }
}
