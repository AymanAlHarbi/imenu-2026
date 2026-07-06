<?php
// تشغيل: php fix-images.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Intervention\Image\Facades\Image;

$dir = public_path('uploads/restorants');
$rules = [
    '_large.jpg'     => 590,   // أقصى عرض
    '_cover.jpg'     => 1600,
    '_medium.jpg'    => 295,
    '_thumbnail.jpg' => 200,
];

$before = 0; $after = 0; $count = 0;
foreach ($rules as $suffix => $maxWidth) {
    foreach (glob($dir.'/*'.$suffix) as $f) {
        $sizeBefore = filesize($f);
        if ($sizeBefore < 50 * 1024) continue; // الصور الصغيرة أصلاً لا تُمس

        $img = Image::make($f);
        if ($img->width() > $maxWidth) {
            $img->resize($maxWidth, null, function ($c) { $c->aspectRatio(); });
        }
        $img->save($f, 75);

        clearstatcache(true, $f);
        $sizeAfter = filesize($f);
        $before += $sizeBefore; $after += $sizeAfter; $count++;
        echo basename($f).': '.round($sizeBefore/1024).'KB -> '.round($sizeAfter/1024)."KB\n";
    }
}
echo "\n=== $count صورة | قبل: ".round($before/1048576, 1).'MB | بعد: '.round($after/1048576, 1)."MB ===\n";