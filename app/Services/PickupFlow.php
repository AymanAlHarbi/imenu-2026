<?php

namespace App\Services;

use App\Notifications\CustomerArrived;
use App\Order;

/**
 * مسار الاستلام — المنطق المشترك بين الويب وواجهة التطبيق.
 *
 * وُجد هذا الملف لسبب واحد: «وصلت» كانت منفَّذة داخل OrderController للويب،
 * وعند فتح النقطة للتطبيق كان البديل نسخ المنطق مرة ثانية. نسختان تفترقان
 * بصمت بعد أول تعديل — وهذا بالضبط ما يحذّر منه العقد في API_REFERENCE.md.
 *
 * قرار ١٧ سبتمبر ٢٠٢٦: طريقتان لا ثلاث — car (من السيارة) · counter (من الكاشير).
 * القيمة window ملغاة وتبقى مقروءة للطلبات القديمة فقط.
 */
class PickupFlow
{
    /** طرق الاستلام النافذة */
    public const METHODS = ['car', 'counter'];

    /** قيم ملغاة تبقى مقروءة للطلبات القديمة */
    public const LEGACY_METHODS = ['window'];

    /** طريقة استلام الطلب، موحّدة: أي قيمة ملغاة تُقرأ counter */
    public static function method(Order $order): string
    {
        $value = (string) $order->getConfig('pickup_method', '');

        if (in_array($value, self::LEGACY_METHODS, true)) {
            return 'counter';
        }

        return in_array($value, self::METHODS, true) ? $value : 'counter';
    }

    public static function isCar(Order $order): bool
    {
        return self::method($order) === 'car';
    }

    /**
     * العميل وصل — يُسجَّل مرة واحدة ولطلبات السيارة فقط،
     * ويُشعَر المقهى وطاقمه.
     *
     * @return bool true إن سُجّل الوصول الآن، false إن لم ينطبق أو كان مسجّلًا
     */
    public static function markArrived(Order $order): bool
    {
        if (! self::isCar($order)) {
            return false;
        }

        if ($order->getConfig('arrived_at', false)) {
            return false;
        }

        $order->setConfig('arrived_at', now()->toDateTimeString());

        self::notifyVendorOfArrival($order);

        return true;
    }

    /** وقت تسجيل الوصول، أو null */
    public static function arrivedAt(Order $order)
    {
        $at = $order->getConfig('arrived_at', false);

        return $at ?: null;
    }

    private static function notifyVendorOfArrival(Order $order): void
    {
        try {
            $order->restorant->user->notify(new CustomerArrived($order));

            foreach ($order->restorant->staff()->get() as $staffMember) {
                $staffMember->notify(new CustomerArrived($order));
            }
        } catch (\Throwable $th) {
            \Log::error('CustomerArrived notify failed: '.$th->getMessage());
        }
    }
}
