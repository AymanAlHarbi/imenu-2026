<?php

use App\Helpers\SaudiPhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1) جعل كلمة المرور اختيارية (العملاء يسجلون برقم الجوال فقط بدون كلمة مرور)
     * 2) توحيد صيغة أرقام الجوال المخزنة سابقاً إلى +9665XXXXXXXX
     *    مع تخطي أي رقم يؤدي توحيده إلى تعارض مع رقم موجود.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        // توحيد الأرقام القديمة (للعملاء وغيرهم) بشكل آمن
        DB::table('users')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $u) {
                    $normalized = SaudiPhone::normalize($u->phone);

                    // رقم غير سعودي/غير صالح → نتركه كما هو
                    if (! $normalized || $normalized === $u->phone) {
                        continue;
                    }

                    // لا نوحد إذا كان سيتعارض مع مستخدم آخر يحمل الصيغة الموحدة
                    $conflict = DB::table('users')
                        ->where('phone', $normalized)
                        ->where('id', '!=', $u->id)
                        ->exists();

                    if (! $conflict) {
                        DB::table('users')->where('id', $u->id)->update(['phone' => $normalized]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
