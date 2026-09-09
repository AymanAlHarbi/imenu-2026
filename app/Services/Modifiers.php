<?php

namespace App\Services;

use App\Items;

/**
 * مجموعات المُحدِّدات — خيارات الصنف بفروق أسعار جمعية.
 *
 * لماذا لا variants: الـvariants صفّ سعر لكل **توليفة**، فعددها حاصل ضرب.
 * الحجم(٢) × نوع البن(٢) × حار/بارد(٢) = ٨ أسعار لصنف واحد، ومقهى فيه
 * ٣٠ صنفًا يُدخل ٢٤٠ سعرًا يدويًا ويغيّرها كلها عند أي زيادة.
 * هنا: السعر = سعر الصنف + مجموع فروق ما اختاره العميل. ينمو جمعيًا.
 *
 * التخزين: JSON واحد في جدول configs على الصنف — بلا هجرة قاعدة بيانات.
 * البنية تُقرأ وتُكتب ككل دائمًا، فلا حاجة لجدولين وعلاقات.
 *
 * شكل البيانات:
 * [
 *   {
 *     "name": "الحجم", "required": true, "multiple": false, "min": 1, "max": 1,
 *     "options": [ {"name":"صغير","delta":0}, {"name":"كبير","delta":3} ]
 *   }
 * ]
 */
class Modifiers
{
    private const KEY = 'modifier_groups';

    /** حد أعلى يحمي من إدخال عبثي */
    public const MAX_GROUPS = 8;

    public const MAX_OPTIONS = 20;

    /* ------------------------------------------------------------------
     | قراءة وكتابة
     |------------------------------------------------------------------ */

    public static function groups(Items $item): array
    {
        $raw = $item->getConfig(self::KEY, '');
        if (! $raw) {
            return [];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
    }

    public static function hasGroups(Items $item): bool
    {
        return count(self::groups($item)) > 0;
    }

    /** يحفظ البنية بعد تنقيتها — لا يُحفظ إلا ما هو صالح */
    public static function save(Items $item, $groups): bool
    {
        return $item->setConfig(self::KEY, json_encode(self::sanitize($groups), JSON_UNESCAPED_UNICODE));
    }

    /**
     * تنقية الإدخال: أسماء غير فارغة، فروق رقمية، حدود متّسقة.
     * مجموعة بلا اسم أو بلا قيم تُحذف — لا تُحفظ بنية نصف مكتملة.
     */
    public static function sanitize($groups): array
    {
        if (! is_array($groups)) {
            return [];
        }

        $clean = [];
        foreach (array_slice($groups, 0, self::MAX_GROUPS) as $group) {
            if (! is_array($group)) {
                continue;
            }

            $name = trim(strip_tags($group['name'] ?? ''));
            $rawOptions = is_array($group['options'] ?? null) ? $group['options'] : [];

            $options = [];
            foreach (array_slice($rawOptions, 0, self::MAX_OPTIONS) as $option) {
                if (! is_array($option)) {
                    continue;
                }
                $optionName = trim(strip_tags($option['name'] ?? ''));
                if ($optionName === '') {
                    continue;
                }
                $options[] = [
                    'name' => $optionName,
                    'delta' => round((float) ($option['delta'] ?? 0), 2),
                ];
            }

            if ($name === '' || count($options) === 0) {
                continue;
            }

            $multiple = (bool) ($group['multiple'] ?? false);
            $required = (bool) ($group['required'] ?? false);

            //الحدود لا معنى لها إلا مع التعدد
            if ($multiple) {
                $min = max(0, (int) ($group['min'] ?? 0));
                $max = max(0, (int) ($group['max'] ?? 0));
                if ($required && $min < 1) {
                    $min = 1;
                }
                if ($max !== 0 && $max < $min) {
                    $max = $min;
                }
                if ($max > count($options)) {
                    $max = count($options);
                }
            } else {
                $min = $required ? 1 : 0;
                $max = 1;
            }

            $clean[] = [
                'name' => $name,
                'required' => $required,
                'multiple' => $multiple,
                'min' => $min,
                'max' => $max,
                'options' => $options,
            ];
        }

        return $clean;
    }

    /* ------------------------------------------------------------------
     | التحقق والتسعير
     |------------------------------------------------------------------ */

    /**
     * $selected: مصفوفة مفاتيحها رقم المجموعة، وقيمها مصفوفة أرقام الخيارات.
     * تُرجع رسالة الخطأ الأولى، أو null إن كان الاختيار صحيحًا.
     */
    public static function validate(Items $item, $selected): ?string
    {
        $groups = self::groups($item);
        if (count($groups) === 0) {
            return null;
        }

        $selected = is_array($selected) ? $selected : [];

        foreach ($groups as $gi => $group) {
            $picked = self::pickedIndexes($group, $selected[$gi] ?? []);
            $count = count($picked);

            if ($group['required'] && $count < max(1, (int) $group['min'])) {
                return __('Please choose').': '.$group['name'];
            }
            if (! $group['multiple'] && $count > 1) {
                return __('Only one choice is allowed in').': '.$group['name'];
            }
            if ($group['multiple'] && (int) $group['max'] > 0 && $count > (int) $group['max']) {
                return __('Too many choices in').': '.$group['name'];
            }
        }

        return null;
    }

    /**
     * يُرجع ['delta' => مجموع الفروق, 'labels' => أسماء ما اختير مع فرقه].
     * الأسماء تُخزَّن نصًّا في الطلب، فتظهر في شاشة الكاشير والفاتورة بلا تعديل.
     */
    public static function apply(Items $item, $selected): array
    {
        $groups = self::groups($item);
        $selected = is_array($selected) ? $selected : [];

        $delta = 0.0;
        $labels = [];

        foreach ($groups as $gi => $group) {
            foreach (self::pickedIndexes($group, $selected[$gi] ?? []) as $oi) {
                $option = $group['options'][$oi];
                $delta += (float) $option['delta'];
                $labels[] = $group['name'].': '.$option['name'].(
                    (float) $option['delta'] > 0
                        ? ' + '.money($option['delta'], config('settings.cashier_currency'), config('settings.do_convertion'))
                        : ''
                );
            }
        }

        return ['delta' => round($delta, 2), 'labels' => $labels];
    }

    /** أرقام خيارات موجودة فعلًا في المجموعة، بلا تكرار */
    private static function pickedIndexes(array $group, $raw): array
    {
        $raw = is_array($raw) ? $raw : [$raw];
        $valid = [];
        foreach ($raw as $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $index = (int) $value;
            if (isset($group['options'][$index]) && ! in_array($index, $valid, true)) {
                $valid[] = $index;
            }
        }

        return $valid;
    }
}
