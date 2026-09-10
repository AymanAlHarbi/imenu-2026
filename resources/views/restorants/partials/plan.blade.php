<div class="card card-profile bg-secondary shadow">
    <div class="card-header">
        <h5 class="h3 mb-0">{{ __("Subscription plan")}}</h5>
    </div>
    <div class="card-body">
        @php($planUser = $restorant->user)
        @php($planExpiresAt = \App\Services\Subscription::expiresAt($planUser))
        @php($planDaysLeft = \App\Services\Subscription::daysLeft($planUser))

        @if($planExpiresAt && $planDaysLeft < 0)
            <div class="alert alert-danger" role="alert">
                {{ __('Subscription expired. The vendor is on the free plan now.') }}
            </div>
        @elseif($planExpiresAt && $planDaysLeft <= 14)
            <div class="alert alert-warning" role="alert">
                {{ __('days left') }}: {{ $planDaysLeft }}
            </div>
        @endif

        <form method="POST" action="{{ route('update.plan')}}" >
            @csrf
            <input type="hidden" name="user_id" value="{{ $planUser->id }}">
            <input type="hidden" name="restaurant_id" value="{{ $restorant->id }}">
            @include('partials.fields',['fields'=>[
                ['class'=>'col-12', 'ftype'=>'select','name'=>"Current plan",'id'=>"plan_id",'data'=>$plans,'required'=>true,'value'=>$planUser->plan_id?$planUser->plan_id:\App\Services\Subscription::freePlanId()],
                ['class'=>'col-12', 'ftype'=>'input','type'=>'date','step'=>'1','name'=>"Subscription expires at",'id'=>"plan_expires_at",'placeholder'=>"",'required'=>false,'additionalInfo'=>"Leave empty for a subscription without an end date",'value'=>$planExpiresAt?$planExpiresAt->toDateString():null],
            ]])

            <div class="col-12 mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imSetExpiry(1)">+1 {{ __('month') }}</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imSetExpiry(6)">+6 {{ __('months') }}</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imSetExpiry(12)">+12 {{ __('months') }}</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imSetExpiry(0)">{{ __('Clear') }}</button>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    // أزرار سريعة لتحديد تاريخ الانتهاء: اليوم + عدد الأشهر. الصفر يمسح التاريخ.
    function imSetExpiry(months) {
        var field = document.getElementById('plan_expires_at');
        if (!field) { return; }
        if (!months) { field.value = ''; return; }
        var date = new Date();
        var day = date.getDate();
        date.setMonth(date.getMonth() + months);
        // تصحيح تجاوز الشهر (مثال: ٣١ يناير + شهر واحد)
        if (date.getDate() !== day) { date.setDate(0); }
        var pad = function (n) { return (n < 10 ? '0' : '') + n; };
        field.value = date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }
</script>
