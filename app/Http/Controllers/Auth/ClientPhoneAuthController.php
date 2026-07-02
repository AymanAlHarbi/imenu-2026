<?php

namespace App\Http\Controllers\Auth;

use App\Events\NewClient;
use App\Http\Controllers\Controller;
use App\Restorant;
use App\Helpers\SaudiPhone;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ClientPhoneAuthController extends Controller
{
    /** مدة صلاحية رمز التحقق بالدقائق */
    private const OTP_TTL_MINUTES = 10;

        public function show()
    {
        // لو مسجل دخول أصلاً، رجّعه لوجهته
        if (Auth::check()) {
            return redirect($this->afterLoginUrl());
        }

        // بيانات المطعم الذي جاء منه العميل — للشعار واللون وزر الرجوع
        $resto = null;
        $alias = session('last_visited_restaurant_alias');
        if ($alias) {
            $resto = Restorant::where('subdomain', $alias)->first();
        }

        return view('auth.phone_login', [
            'resto'   => $resto,
            'backUrl' => $alias ? route('vendor', ['alias' => $alias]) : url('/'),
        ]);
    }

    /**
     * الخطوة 1: التحقق من الرقم — هل هو عميل موجود أم جديد؟
     */
    public function check(Request $request): JsonResponse
    {
        // حماية من تعداد الأرقام (phone enumeration)
        $ipKey = 'phone-check:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 15)) {
            return response()->json(['status' => false, 'errMsg' => __('Too many attempts. Please try again later.')], 429);
        }
        RateLimiter::hit($ipKey, 60);

        $request->validate(['phone' => ['required', 'string', 'max:20']]);

        $phone = SaudiPhone::normalize($request->phone);
        if (! $phone) {
            return response()->json([
                'status' => false,
                'errMsg' => __('Please enter a valid Saudi mobile number (05XXXXXXXX).'),
            ], 422);
        }

        $user = $this->findUserByPhone($phone);

        // ثغرة أمنية مغلقة: الدخول برقم الجوال فقط مخصص للعملاء.
        // حسابات المالك/الأدمن/السائق/الموظف يجب أن تدخل بالطريقة القياسية.
        if ($user && ! $user->hasRole('client')) {
            return response()->json([
                'status'             => false,
                'use_standard_login' => true,
                'login_url'          => route('login'),
                'errMsg'             => __('This account must sign in with email and password.'),
            ], 403);
        }

        session([
            'pending_phone'   => $phone,
            'pending_user_id' => $user?->id,
        ]);

        return response()->json(['exists' => (bool) $user, 'phone' => $phone]);
    }

    /**
     * الخطوة 2 (للعميل الجديد): إنشاء الحساب ثم إرسال الرمز.
     */
    public function register(Request $request): JsonResponse
    {
        $phone = session('pending_phone');
        if (! $phone) {
            return response()->json(['status' => false, 'errMsg' => __('Please enter your phone number first.')], 422);
        }

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
        ]);

        // حماية من التكرار في حال سباق الطلبات أو تسجيل سابق بصيغة أخرى
        $existing = $this->findUserByPhone($phone);
        if ($existing) {
            if (! $existing->hasRole('client')) {
                return response()->json([
                    'status'             => false,
                    'use_standard_login' => true,
                    'login_url'          => route('login'),
                    'errMsg'             => __('This account must sign in with email and password.'),
                ], 403);
            }
            session(['pending_user_id' => $existing->id]);

            return $this->sendOtpOrSkip($existing);
        }

        $user = User::create([
            'name'      => strip_tags($request->name),
            'email'     => $request->email,
            'phone'     => $phone, // الصيغة الموحدة دائماً +9665XXXXXXXX
            'password'  => null,
            'api_token' => Str::random(80),
        ]);
        $user->assignRole('client');

        session(['pending_user_id' => $user->id]);

        return $this->sendOtpOrSkip($user);
    }

    /**
     * إرسال / إعادة إرسال رمز التحقق (للعميل الموجود أو بعد التسجيل).
     */
    public function sendCode(Request $request): JsonResponse
    {
        $phone = session('pending_phone');
        if (! $phone) {
            return response()->json(['status' => false, 'errMsg' => __('Please enter your phone number first.')], 422);
        }

        $user = $this->findUserByPhone($phone);

        if (! $user) {
            return response()->json(['status' => false, 'errMsg' => __('Phone number not registered.')], 422);
        }

        if (! $user->hasRole('client')) {
            return response()->json([
                'status'             => false,
                'use_standard_login' => true,
                'login_url'          => route('login'),
                'errMsg'             => __('This account must sign in with email and password.'),
            ], 403);
        }

        $key = 'otp-send:'.$phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'status'      => false,
                'retry_after' => $seconds,
                'errMsg'      => __('Please wait before requesting a new code.'),
            ], 429);
        }
        RateLimiter::hit($key, 60);

        session(['pending_user_id' => $user->id]);

        return $this->sendOtpOrSkip($user);
    }

    /**
     * الخطوة الأخيرة: التحقق من الرمز وإتمام الدخول.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:10']]);

        $user = User::find(session('pending_user_id'));
        $key = 'otp-verify:'.optional($user)->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['status' => false, 'errMsg' => __('Too many attempts. Request a new code.')], 429);
        }

        // انتهاء صلاحية الرمز بعد 10 دقائق
        $sentAt = session('otp_sent_at');
        $expired = ! $sentAt || now()->diffInMinutes($sentAt) >= self::OTP_TTL_MINUTES;

        $code = strtr($request->code, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $valid = $user
            && ! $expired
            && $user->verification_code !== null
            && hash_equals((string) $user->verification_code, (string) $code);

        if (! $valid) {
            RateLimiter::hit($key, 300);

            return response()->json([
                'status' => false,
                'errMsg' => $expired
                    ? __('The code has expired. Please request a new one.')
                    : __('The code you provided is wrong.'),
            ], 422);
        }

        RateLimiter::clear($key);

        return $this->completeLogin($user);
    }

    /* ---------------------------------------------------------------- */

    /**
     * البحث عن المستخدم بأي صيغة قديمة مخزنة لنفس الرقم.
     */
    private function findUserByPhone(string $normalized): ?User
    {
        return User::whereIn('phone', SaudiPhone::variants($normalized))->first();
    }

    /**
     * لو التحقق برقم الجوال مفعّل (مزود SMS مربوط) → يرسل الكود وينتظر إدخاله.
     * لو غير مفعّل (وضع التجربة) → يتخطى الكود ويسجّل الدخول مباشرة
     * (متاح للعملاء فقط — تم التحقق من الدور قبل الوصول هنا).
     */
    private function sendOtpOrSkip(User $user): JsonResponse
    {
        if (config('settings.enable_sms_verification')) {
            try {
                $user->callToVerify();
                session(['otp_sent_at' => now()]);
            } catch (\Throwable $e) {
                return response()->json(['status' => false, 'errMsg' => __('Could not send verification code. Please try again later.')], 500);
            }

            return response()->json(['status' => true]);
        }

        // وضع التجربة: لا يوجد مزود SMS مفعّل بعد
        return $this->completeLogin($user, true);
    }

    private function completeLogin(User $user, bool $skipOtp = false): JsonResponse
    {
        // توحيد الرقم المخزن إذا كان بصيغة قديمة
        $normalized = session('pending_phone');
        if ($normalized && $user->phone !== $normalized) {
            $user->phone = $normalized;
        }

        // مسح الرمز بعد الاستخدام حتى لا يُعاد استعماله
        $user->verification_code = null;
        $user->save();

        $user->markPhoneAsVerified();
        Auth::login($user, true);
        session()->forget(['pending_phone', 'pending_user_id', 'otp_sent_at']);

        $lastVendor = session('last_visited_restaurant_alias');
        if ($lastVendor) {
            NewClient::dispatch($user, Restorant::where('subdomain', $lastVendor)->first());
        }

        return response()->json([
            'status'   => true,
            'skip_otp' => $skipOtp,
            'redirect' => $this->afterLoginUrl(),
        ]);
    }

    /**
     * وجهة ما بعد الدخول:
     * 1) الصفحة التي كان يقصدها قبل تحويله للدخول (مثل صفحة السلة)
     * 2) منيو آخر مطعم زاره
     * 3) الرئيسية
     */
    private function afterLoginUrl(): string
    {
        $intended = session()->pull('url.intended');
        if ($intended) {
            return $intended;
        }

        $lastVendor = session('last_visited_restaurant_alias');

        return $lastVendor
            ? route('vendrobyalias', ['alias' => $lastVendor])
            : route('home');
    }
}