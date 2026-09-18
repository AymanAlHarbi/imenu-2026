<?php

namespace App\Console\Commands;

use App\Hours;
use Illuminate\Console\Command;

/**
 * تصحيح ساعات العمل الموروثة من القالب (09:00–17:00) للمقاهي المسجَّلة سلفًا.
 *
 * الخلل: `RestorantController::store` كان ينشئ كل مقهى جديد بسبعة أيام
 * من 09:00 إلى 17:00، فيظهر «مغلق» للعميل معظم اليوم ويُمنع الطلب.
 * أُصلح الافتراضي في 17 سبتمبر 2026، وهذا الأمر يعالج ما أُنشئ قبله.
 *
 * **لا يلمس إلا الورديات المطابقة للافتراضي القديم في أيامها السبعة كلها.**
 * أي مقهى عدّل ساعاته بنفسه — ولو ليوم واحد — يبقى كما هو. الصمت هنا مقصود:
 * سحب إعداد اختاره صاحب المقهى أسوأ من ترك إعداد لم يختره.
 *
 *   php artisan imenu:fix-default-hours          عرض ما سيتغيّر فقط
 *   php artisan imenu:fix-default-hours --apply  التنفيذ
 */
class FixDefaultHours extends Command
{
    protected $signature = 'imenu:fix-default-hours {--apply : نفّذ التعديل فعلاً بدل عرضه}';

    protected $description = 'تصحيح ساعات العمل الافتراضية القديمة (09:00-17:00) إلى الافتراضي الجديد';

    /** الافتراضي القديم بصيغتيه — 24 ساعة وAM/PM */
    private const OLD_FROM = ['09:00', '09:00:00', '9:00 AM', '9:00AM', '09:00 AM'];

    private const OLD_TO = ['17:00', '17:00:00', '5:00 PM', '5:00PM', '05:00 PM'];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $from = config('settings.default_hours_from', '06:00');
        $to = config('settings.default_hours_to', '23:59');

        if (config('settings.time_format') == 'AM/PM') {
            $from = date('g:i A', strtotime($from));
            $to = date('g:i A', strtotime($to));
        }

        $this->line('الافتراضي الجديد: '.$from.' – '.$to);
        $this->newLine();

        $touched = 0;
        $skipped = 0;

        foreach (Hours::with('restorant')->get() as $shift) {
            if (! $this->isOldDefault($shift)) {
                $skipped++;

                continue;
            }

            $name = $shift->restorant ? $shift->restorant->name : ('#'.$shift->restorant_id);
            $this->line(($apply ? '✔ ' : '• ').$name.'  (وردية '.$shift->id.')');

            if ($apply) {
                for ($day = 0; $day < 7; $day++) {
                    $shift->{$day.'_from'} = $from;
                    $shift->{$day.'_to'} = $to;
                }
                $shift->save();
            }

            $touched++;
        }

        $this->newLine();

        if ($touched === 0) {
            $this->info('لا توجد ورديات على الافتراضي القديم. لا شيء للتغيير.');

            return self::SUCCESS;
        }

        if ($apply) {
            $this->info('حُدِّثت '.$touched.' وردية. وتُركت '.$skipped.' وردية معدّلة يدويًا كما هي.');
        } else {
            $this->warn('عرض فقط — لم يُكتب شيء. للتنفيذ: php artisan imenu:fix-default-hours --apply');
            $this->line('ستُحدَّث '.$touched.' وردية، وتُترك '.$skipped.' كما هي.');
        }

        return self::SUCCESS;
    }

    /** الوردية على الافتراضي القديم إن كانت أيامها السبعة كلها 09:00–17:00 */
    private function isOldDefault(Hours $shift): bool
    {
        for ($day = 0; $day < 7; $day++) {
            $from = trim((string) $shift->{$day.'_from'});
            $to = trim((string) $shift->{$day.'_to'});

            if (! in_array($from, self::OLD_FROM, true) || ! in_array($to, self::OLD_TO, true)) {
                return false;
            }
        }

        return true;
    }
}
