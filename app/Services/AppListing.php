<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * من يظهر في تطبيق العميل.
 *
 * القرار (٩ سبتمبر ٢٠٢٦): المنيو مجاني دائمًا وبلا سعر مُعلن، **والرافعة هي
 * الظهور في التطبيق** لا تسعير المنيو. المقهى الذي يريد «المنيو فقط» يحصل على
 * منيو ورابط وQR، ولا يظهر لعملاء حيّه في التطبيق — فيرون المقهى المجاور
 * ولا يرونه. ندرة حقيقية، تبيع نفسها بلا فاتورة.
 *
 * فالقائمة تُفلتر بالخطة لا بالمدينة وحدها. والفلترة بالـSQL لا بحلقة على
 * كل مقهى — ‏getPlanAttribute()‎ يعدّ الأصناف والطلبات لكل مقهى، ولا يُحتمل وطنيًا.
 */
class AppListing
{
    /** معرّفات المقاهي التي تستحق الظهور في التطبيق */
    public static function vendorIds($cityId = null): array
    {
        //١. الخطط التي تسمح بالطلب — اسم الجدول مفرد `plan` كما في نموذج App\Plans
        $plans = DB::table('plan')
            ->select('id', 'enable_ordering', 'limit_orders', 'period')
            ->get()
            ->keyBy('id');

        $freePlanId = (int) config('settings.free_pricing_id');

        //٢. المقاهي النشطة وخطة كل مالك — الافتراضية لمن لا خطة له
        $query = DB::table('companies')
            ->join('users', 'users.id', '=', 'companies.user_id')
            ->where('companies.active', 1)
            ->whereNull('companies.deleted_at')
            ->select('companies.id as company_id', 'users.id as user_id', 'users.plan_id');

        if ($cityId !== null && $cityId !== 'none') {
            $query->where('companies.city_id', $cityId);
        }

        $rows = $query->get();

        //٣. من انتهى اشتراكه يُعامل بالخطة المجانية — استعلام واحد لا استعلام لكل مقهى
        $expired = self::expiredUserIds($rows->pluck('user_id')->all());

        $candidates = [];
        foreach ($rows as $row) {
            $planId = $row->plan_id ? (int) $row->plan_id : $freePlanId;
            if (isset($expired[(int) $row->user_id])) {
                $planId = $freePlanId;
            }
            $plan = $plans->get($planId);

            //لا خطة معروفة ← نتبع سلوك النظام الافتراضي: الطلب مسموح
            if (! $plan) {
                $candidates[$row->company_id] = null;

                continue;
            }

            if ((int) $plan->enable_ordering !== 1) {
                continue;
            }

            $candidates[$row->company_id] = (int) $plan->limit_orders !== 0 ? $plan : null;
        }

        if (empty($candidates)) {
            return [];
        }

        //٤. من استنفد حد طلبات خطته لا يُعرض — وإلا لمس العميل مقهى ثم فشل طلبه
        $limited = array_filter($candidates, fn ($plan) => $plan !== null);
        if (! empty($limited)) {
            $counts = self::orderCounts(array_keys($limited), $limited);
            foreach ($limited as $companyId => $plan) {
                if (($counts[$companyId] ?? 0) >= (int) $plan->limit_orders) {
                    unset($candidates[$companyId]);
                }
            }
        }

        return array_map('intval', array_keys($candidates));
    }

    /**
     * معرّفات المستخدمين الذين انتهى اشتراكهم — استعلام واحد على جدول configs.
     *
     * @return array<int,bool> مفهرسة بمعرّف المستخدم
     */
    private static function expiredUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if (empty($userIds)) {
            return [];
        }

        $rows = DB::table('configs')
            ->where('key', 'plan_expires_at')
            ->where('model_type', \App\User::class)
            ->whereIn('model_id', $userIds)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->select('model_id', 'value')
            ->get();

        $today = \Carbon\Carbon::now()->toDateString();
        $expired = [];

        foreach ($rows as $row) {
            if (substr((string) $row->value, 0, 10) < $today) {
                $expired[(int) $row->model_id] = true;
            }
        }

        return $expired;
    }

    /**
     * عدد طلبات كل مقهى داخل فترة خطته.
     * استعلامان على الأكثر (شهري وسنوي) لا استعلام لكل مقهى.
     */
    private static function orderCounts(array $companyIds, array $plansByCompany): array
    {
        $byPeriod = [];
        foreach (array_keys(Subscription::periods()) as $periodId) {
            $byPeriod[$periodId] = [];
        }

        foreach ($companyIds as $companyId) {
            $period = (int) $plansByCompany[$companyId]->period;
            if (! isset($byPeriod[$period])) {
                $period = Subscription::MONTHLY;
            }
            $byPeriod[$period][] = $companyId;
        }

        $counts = [];
        foreach ($byPeriod as $period => $ids) {
            if (empty($ids)) {
                continue;
            }
            $since = Subscription::periodStart($period);

            $rows = DB::table('orders')
                ->whereIn('restorant_id', $ids)
                ->whereNull('deleted_at')
                ->where('created_at', '>=', $since)
                ->groupBy('restorant_id')
                ->select('restorant_id', DB::raw('count(*) as cnt'))
                ->get();

            foreach ($rows as $row) {
                $counts[$row->restorant_id] = (int) $row->cnt;
            }
        }

        return $counts;
    }
}
