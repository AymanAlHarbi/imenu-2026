<?php

namespace App\Services;

use App\Restorant;
use Illuminate\Support\Facades\DB;

/**
 * مدة التجهيز — تلقائية مع تجاوز يدوي.
 *
 * القاعدة (مواصفة شاشة الكاشير): المدة **حالة للمقهى لا حقل لكل طلب**.
 * ضغطة واحدة تنطبق على كل الطلبات الداخلة حتى تتغيّر.
 *
 * الافتراضي تلقائي: متوسط (وقت الطلب ← ختم «جاهز») لآخر SAMPLE_SIZE طلبًا
 * في هذا المقهى. فيعمل من أول يوم بلا ضبط، ويتحسّن مع كل طلب.
 */
class PrepTime
{
    /** الشرائح اليدوية المعروضة للمقهى */
    /** الشرائح اليدوية. القيمة ١ تعني «حالًا» — صنف جاهز كقهوة اليوم. */
    public const SLICES = [1, 5, 10, 15, 20];

    /** يُستخدم قبل توفّر عدد كافٍ من الطلبات المقيسة */
    public const DEFAULT_MINUTES = 10;

    /** حجم النافذة المقيسة */
    public const SAMPLE_SIZE = 20;

    /** أقل عدد طلبات مقيسة نثق به */
    public const MIN_SAMPLE = 5;

    /** حدود التعقّل — تحمي الوعد من طلب نُسي مفتوحًا يومًا كاملًا */
    public const MIN_MINUTES = 1;

    public const MAX_MINUTES = 45;

    /** حالة «جاهز» في جدول status */
    private const STATUS_PREPARED = 5;

    /** الشريحة المختارة يدويًا، أو null إن كان الوضع تلقائيًا */
    public static function override(Restorant $vendor): ?int
    {
        $value = (int) $vendor->getConfig('prep_minutes_override', 0);

        return in_array($value, self::SLICES, true) ? $value : null;
    }

    /** ضبط الشريحة اليدوية. تمرير null (أو صفر) يعيد الوضع تلقائيًا. */
    public static function setOverride(Restorant $vendor, $minutes): bool
    {
        $value = in_array((int) $minutes, self::SLICES, true) ? (int) $minutes : 0;

        return $vendor->setConfig('prep_minutes_override', $value);
    }

    public static function isAuto(Restorant $vendor): bool
    {
        return self::override($vendor) === null;
    }

    /**
     * المتوسط المقيس فعليًا، أو null إن لم تكفِ العيّنة.
     * يُقرأ من order_has_status مباشرة — الختم الزمني موجود أصلًا، بلا هجرة.
     */
    public static function measured(Restorant $vendor): ?int
    {
        $rows = DB::table('order_has_status')
            ->join('orders', 'orders.id', '=', 'order_has_status.order_id')
            ->where('orders.restorant_id', $vendor->id)
            ->where('order_has_status.status_id', self::STATUS_PREPARED)
            ->whereNull('orders.deleted_at')
            ->orderByDesc('order_has_status.id')
            ->limit(self::SAMPLE_SIZE)
            ->select('orders.created_at as ordered_at', 'order_has_status.created_at as ready_at')
            ->get();

        $samples = [];
        foreach ($rows as $row) {
            if (! $row->ordered_at || ! $row->ready_at) {
                continue;
            }
            $minutes = (strtotime($row->ready_at) - strtotime($row->ordered_at)) / 60;

            //تجاهل القيم الشاذة: طلب سُجّل جاهزًا قبل إنشائه، أو نُسي مفتوحًا
            if ($minutes < 0 || $minutes > self::MAX_MINUTES * 3) {
                continue;
            }
            $samples[] = $minutes;
        }

        if (count($samples) < self::MIN_SAMPLE) {
            return null;
        }

        return self::round(array_sum($samples) / count($samples));
    }

    /** المدة المعمول بها الآن لهذا المقهى */
    public static function minutes(Restorant $vendor): int
    {
        $override = self::override($vendor);
        if ($override !== null) {
            return $override;
        }

        return self::measured($vendor) ?? self::DEFAULT_MINUTES;
    }

    /**
     * الوعد يُقال بأرقام مستريحة: تقريب لأعلى إلى أقرب ٥ دقائق.
     * إلا تحت الخمس — مقهى يجهّز في دقيقة لا يُقال عنه خمس، فيُقرَّب لأقرب دقيقة.
     */
    private static function round($minutes): int
    {
        $rounded = $minutes < 5 ? (int) ceil($minutes) : (int) (ceil($minutes / 5) * 5);

        return max(self::MIN_MINUTES, min(self::MAX_MINUTES, $rounded));
    }
}
