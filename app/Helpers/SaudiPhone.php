<?php

namespace App\Helpers;

class SaudiPhone
{
    /**
     * توحيد صيغة رقم الجوال السعودي إلى الصيغة القياسية: +9665XXXXXXXX
     *
     * يقبل: 05XXXXXXXX / 5XXXXXXXX / 9665XXXXXXXX / +9665XXXXXXXX / 009665XXXXXXXX
     * ويقبل الأرقام العربية (٠١٢٣...) والمسافات والشرطات والأقواس.
     *
     * @return string|null الرقم الموحد، أو null إذا كان الرقم غير صالح
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        // تحويل الأرقام العربية إلى إنجليزية
        $raw = strtr($raw, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        // إزالة كل شيء غير الأرقام
        $digits = preg_replace('/\D+/', '', $raw);

        // إزالة البادئات الدولية
        if (str_starts_with($digits, '00966')) {
            $digits = substr($digits, 5);
        } elseif (str_starts_with($digits, '966')) {
            $digits = substr($digits, 3);
        }

        // إزالة الصفر المحلي
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // يجب أن يكون الناتج 5XXXXXXXX (9 أرقام تبدأ بـ 5)
        if (! preg_match('/^5\d{8}$/', $digits)) {
            return null;
        }

        return '+966'.$digits;
    }

    /**
     * الصيغ المحتملة المخزنة سابقاً في قاعدة البيانات لنفس الرقم.
     * تُستخدم عند البحث لتفادي إنشاء حسابات مكررة لعملاء قدامى.
     *
     * @return array<string>
     */
    public static function variants(string $normalized): array
    {
        // $normalized = +9665XXXXXXXX
        $local = substr($normalized, 4); // 5XXXXXXXX

        return array_unique([
            $normalized,          // +9665XXXXXXXX
            '966'.$local,         // 9665XXXXXXXX
            '00966'.$local,       // 009665XXXXXXXX
            '0'.$local,           // 05XXXXXXXX
            $local,               // 5XXXXXXXX
        ]);
    }
}
