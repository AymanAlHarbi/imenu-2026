<?php

namespace App\Services;

use App\Restorant;
use Carbon\Carbon;

/**
 * مفتاح «الاستلام من السيارة» — تشغيل/إيقاف لحظي من شاشة الكاشير.
 *
 * القواعد الأربع من مواصفة شاشة الكاشير، وكلها لازمة:
 *  1. العميل يراه **قبل** الطلب — الخيار يظهر مطفأً في المنيو، لا يُرفض بعد الاختيار.
 *  2. **يعود تلقائيًا** بعد ساعتين. بدون هذا سيُنسى مطفأً وتُفقد الميزة بلا أن يدري أحد.
 *  3. **الطلبات القائمة تُحترم** — الإطفاء يمنع الجديد فقط.
 *  4. غير مربوط بمدة التحضير ربطًا صلبًا — الفصل اليدوي يظل ممكنًا.
 *
 * التخزين: مفتاح واحد على المقهى يحمل **وقت العودة**، لا حالة تشغيل/إيقاف.
 * هكذا تصير العودة التلقائية خاصية للبيانات لا مهمة مجدولة يمكن أن تتعطّل.
 */
class CarPickup
{
    /** مدة الإيقاف قبل العودة التلقائية */
    public const OFF_HOURS = 2;

    private const KEY = 'car_pickup_off_until';

    /** وقت العودة التلقائية، أو null إن كان المفتاح مشتغلًا */
    public static function offUntil(?Restorant $vendor): ?Carbon
    {
        if (! $vendor) {
            return null;
        }

        $until = $vendor->getConfig(self::KEY, false);
        if (! $until) {
            return null;
        }

        $until = Carbon::parse($until);

        return $until->isFuture() ? $until : null;
    }

    public static function isOn(?Restorant $vendor): bool
    {
        return self::offUntil($vendor) === null;
    }

    /** الدقائق المتبقية حتى العودة التلقائية — تُعرض كعدّاد ظاهر */
    public static function minutesLeft(?Restorant $vendor): int
    {
        $until = self::offUntil($vendor);

        return $until ? max(0, Carbon::now()->diffInMinutes($until)) : 0;
    }

    public static function turnOff(Restorant $vendor): bool
    {
        return $vendor->setConfig(self::KEY, Carbon::now()->addHours(self::OFF_HOURS)->toDateTimeString());
    }

    /** «تشغيل الآن» — يلغي الإيقاف قبل انتهاء الساعتين */
    public static function turnOn(Restorant $vendor): bool
    {
        return $vendor->setConfig(self::KEY, '');
    }

    public static function toggle(Restorant $vendor): bool
    {
        return self::isOn($vendor) ? self::turnOff($vendor) : self::turnOn($vendor);
    }
}
