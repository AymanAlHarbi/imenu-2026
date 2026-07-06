<?php

namespace App\Http\Controllers\API\Client;

use App\Events\NewClient;
use App\Helpers\SaudiPhone;
use App\Http\Controllers\Controller;
use App\Restorant;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * دخول العميل برقم الجوال + OTP — نسخة API بدون جلسات، خاصة بتطبيق الجوال.
 *
 * نفس منطق ClientPhoneAuthController المستخدم في الويب، لكن:
 *  - لا يعتمد على session: الرقم يُرسل مع كل طلب
 *  - عند نجاح التحقق يرجع api_token بدل Auth::login
 *
 * المسارات (تُضاف في routes/api.php):
 *  POST /api/v2/client/auth/phone/check     {phone}
 *  POST /api/v2/client/auth/phone/register  {phone, name, app_secret}
 *  POST /api/v2/client/auth/phone/sendcode  {phone}
 *  POST /api/v2/client/auth/phone/verify    {phone, code, expotoken?}
 */
class PhoneAuthController extends Controller
{
    /**
     * علامة إصدار الملف — تظهر في رد check للتأكد أن آخر نسخة مرفوعة فعلاً
     * (OPcache أو رفع ناقص قد يُبقي النسخة القديمة شغالة).
     */
    private const VERSION = 'authentica-1';

    /**
     * الخطوة 1: هل الرقم مسجل كعميل أم جديد؟
     */
    public function check(Request $request): JsonResponse
    {
        $ipKey = 'api-phone-check:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 15)) {
            return response()->json(['status' => false, 'errMsg' => __('Too many attempts. Please try again later.')], 429);
        }
        RateLimiter::hit($ipKey, 60);

        $phone = SaudiPhone::normalize($request->phone);
        if (! $phone) {
            return response()->json([
                'status' => false,
                'errMsg' => __('Please enter a valid Saudi mobile number (05XXXXXXXX).'),
            ], 422);
        }

        $user = $this->findUserByPhone($phone);

        // الدخول برقم الجوال للعملاء فقط — حسابات المالك/الأدمن تدخل بالإيميل
        if ($user && ! $user->hasRole('client')) {
            return response()->json([
                'status' => false,
                'use_standard_login' => true,
                'errMsg' => __('This account must sign in with email and password.'),
            ], 403);
        }

        return response()->json([
            'status' => true,
            'exists' => (bool) $user,
            'phone' => $phone,
            'ver' => self::VERSION,
        ]);
    }

    /**
     * الخطوة 2 (عميل جديد): إنشاء الحساب ثم إرسال الرمز.
     */
    public function register(Request $request): JsonResponse
    {
        // نفس حماية app_secret المستخدمة في التسجيل بالإيميل
        if (config('settings.app_secret') == null || config('settings.app_secret').'' != $request->app_secret) {
            return response()->json([
                'status' => false,
                'errMsg' => __('App secret is incorrectly set'),
            ], 403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
        ]);

        $phone = SaudiPhone::normalize($request->phone);
        if (! $phone) {
            return response()->json([
                'status' => false,
                'errMsg' => __('Please enter a valid Saudi mobile number (05XXXXXXXX).'),
            ], 422);
        }

        $existing = $this->findUserByPhone($phone);
        if ($existing) {
            if (! $existing->hasRole('client')) {
                return response()->json([
                    'status' => false,
                    'use_standard_login' => true,
                    'errMsg' => __('This account must sign in with email and password.'),
                ], 403);
            }

            // الحساب موجود أصلاً → أرسل الرمز مباشرة
            return $this->sendOtpOrSkip($existing, $request);
        }

        $user = User::create([
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'phone' => $phone,
            'password' => null,
            'api_token' => Str::random(80),
        ]);
        $user->assignRole('client');

        return $this->sendOtpOrSkip($user, $request);
    }

    /**
     * إرسال / إعادة إرسال رمز التحقق.
     */
    public function sendCode(Request $request): JsonResponse
    {
        $phone = SaudiPhone::normalize($request->phone);
        if (! $phone) {
            return response()->json(['status' => false, 'errMsg' => __('Please enter a valid Saudi mobile number (05XXXXXXXX).')], 422);
        }

        $user = $this->findUserByPhone($phone);
        if (! $user) {
            return response()->json(['status' => false, 'errMsg' => __('Phone number not registered.')], 422);
        }
        if (! $user->hasRole('client')) {
            return response()->json([
                'status' => false,
                'use_standard_login' => true,
                'errMsg' => __('This account must sign in with email and password.'),
            ], 403);
        }

        $key = 'api-otp-send:'.$phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return response()->json([
                'status' => false,
                'retry_after' => RateLimiter::availableIn($key),
                'errMsg' => __('Please wait before requesting a new code.'),
            ], 429);
        }
        RateLimiter::hit($key, 60);

        return $this->sendOtpOrSkip($user, $request);
    }

    /**
     * الخطوة الأخيرة: التحقق من الرمز → إرجاع api_token.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:10']]);

        $phone = SaudiPhone::normalize($request->phone);
        $user = $phone ? $this->findUserByPhone($phone) : null;

        $key = 'api-otp-verify:'.($user ? $user->id : $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['status' => false, 'errMsg' => __('Too many attempts. Request a new code.')], 429);
        }

        $code = strtr($request->code, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        // Authentica تولّد الرمز وتتحقق منه وتدير صلاحيته لديها —
        // لا يوجد تخزين محلي للرمز (verification_code لم يعد مستخدماً).
        // نتحقق برقم المستخدم المخزّن لأنه نفس الرقم الذي أُرسل له الرمز.
        $valid = $user
            && app(\App\Services\Authentica::class)->verifyOtp((string) $user->phone, (string) $code);

        if (! $valid) {
            RateLimiter::hit($key, 300);

            return response()->json([
                'status' => false,
                'errMsg' => __('The code you provided is wrong.'),
            ], 422);
        }

        RateLimiter::clear($key);

        return $this->completeLogin($user, $request);
    }

    /* ---------------------------------------------------------------- */

    private function findUserByPhone(string $normalized): ?User
    {
        return User::whereIn('phone', SaudiPhone::variants($normalized))->first();
    }

    /**
     * لو التحقق بالجوال مفعّل → يرسل الرمز وينتظر verify.
     * لو غير مفعّل (وضع التجربة) → يتخطى الرمز ويرجع التوكن مباشرة.
     */
    private function sendOtpOrSkip(User $user, Request $request): JsonResponse
    {
        if (config('settings.enable_sms_verification')) {
            try {
                // callToVerify يستدعي خدمة Authentica (ترسل SMS وتحتفظ بالرمز لديها)
                $user->callToVerify();
            } catch (\Throwable $e) {
                return response()->json(['status' => false, 'errMsg' => __('Could not send verification code. Please try again later.')], 500);
            }

            return response()->json(['status' => true, 'otp_sent' => true]);
        }

        return $this->completeLogin($user, $request, true);
    }

    private function completeLogin(User $user, Request $request, bool $skipOtp = false): JsonResponse
    {
        // توحيد صيغة الرقم المخزن + مسح الرمز حتى لا يُعاد استخدامه
        $normalized = SaudiPhone::normalize($request->phone);
        if ($normalized && $user->phone !== $normalized) {
            $user->phone = $normalized;
        }
        $user->verification_code = null;

        // أول دخول عبر التطبيق قد لا يملك توكن (حسابات قديمة)
        if (empty($user->api_token)) {
            $user->api_token = Str::random(80);
        }
        $user->save();
        $user->markPhoneAsVerified();

        if ($request->filled('expotoken')) {
            $user->setExpoToken($request->expotoken);
        }

        // ربط العميل بالمطعم (وضع المطعم الواحد)
        if ($request->filled('vendor_id')) {
            $resto = Restorant::find($request->vendor_id);
            if ($resto) {
                NewClient::dispatch($user, $resto);
            }
        }

        return response()->json([
            'status' => true,
            'skip_otp' => $skipOtp,
            'token' => $user->api_token,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }
}
