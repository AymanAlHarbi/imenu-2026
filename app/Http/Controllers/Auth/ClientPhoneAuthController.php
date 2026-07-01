<?php

namespace App\Http\Controllers\Auth;

use App\Events\NewClient;
use App\Http\Controllers\Controller;
use App\Restorant;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ClientPhoneAuthController extends Controller
{
    public function show()
    {
        return view('auth.phone_login');
    }

    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
        ]);

        $exists = User::where('phone', $request->phone)->exists();
        session(['pending_phone' => $request->phone]);

        return response()->json(['exists' => $exists]);
    }

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

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $phone,
            'password'  => null,
            'api_token' => Str::random(80),
        ]);
        $user->assignRole('client');

        session(['pending_user_id' => $user->id]);

        return $this->sendOtpOrSkip($user);
    }

    public function sendCode(Request $request): JsonResponse
    {
        $phone = session('pending_phone');
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return response()->json(['status' => false, 'errMsg' => __('Phone number not registered.')], 422);
        }

        $key = 'otp-send:'.$phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return response()->json(['status' => false, 'errMsg' => __('Please wait before requesting a new code.')], 429);
        }
        RateLimiter::hit($key, 60);

        session(['pending_user_id' => $user->id]);

        return $this->sendOtpOrSkip($user);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = User::find(session('pending_user_id'));
        $key = 'otp-verify:'.optional($user)->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['status' => false, 'errMsg' => __('Too many attempts. Request a new code.')], 429);
        }

        if (! $user || $user->verification_code !== $request->code) {
            RateLimiter::hit($key, 300);
            return response()->json(['status' => false, 'errMsg' => __('The code you provided is wrong.')], 422);
        }

        RateLimiter::clear($key);

        return $this->completeLogin($user);
    }

    /**
     * لو التحقق برقم الجوال مفعّل (Twilio مربوط) → يرسل الكود فعلياً وينتظر إدخاله.
     * لو غير مفعّل (وضع التجربة الحالي) → يتخطى الكود ويسجّل الدخول مباشرة.
     */
    private function sendOtpOrSkip(User $user): JsonResponse
    {
        if (config('settings.enable_sms_verification')) {
            try {
                $user->callToVerify();
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
        $user->markPhoneAsVerified();
        Auth::login($user, true);
        session()->forget(['pending_phone', 'pending_user_id']);

        $lastVendor = session('last_visited_restaurant_alias');
        if ($lastVendor) {
            NewClient::dispatch($user, Restorant::where('subdomain', $lastVendor)->first());
        }

        return response()->json([
            'status'   => true,
            'skip_otp' => $skipOtp,
            'redirect' => $lastVendor ? route('vendrobyalias', ['alias' => $lastVendor]) : route('home'),
        ]);
    }
}