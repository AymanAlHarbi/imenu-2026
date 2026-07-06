<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * خدمة Authentica لإرسال رموز التحقق (OTP) والتحقق منها.
 *
 * القاعدة: https://api.authentica.sa
 * المصادقة: هيدر X-Authorization بمفتاح API
 *
 * قناة الإرسال المعتمدة: SMS محلي فقط (1 نقطة لكل رسالة).
 *
 * ملاحظة: Authentica هي من تولّد الرمز وتتحقق منه — لا نخزّن أي رمز محلياً.
 */
class Authentica
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('settings.authentica_base_url', 'https://api.authentica.sa'), '/');
        $this->apiKey = (string) config('settings.authentica_api_key');
    }

    /**
     * إرسال رمز التحقق عبر SMS.
     *
     * @param  string  $phone  رقم بصيغة E.164 مثل +9665XXXXXXXX
     *
     * @throws \RuntimeException إذا فشل الإرسال
     */
    public function sendOtp(string $phone): void
    {
        try {
            $response = $this->client()->post('/api/v2/send-otp', [
                'method' => 'sms',
                'phone'  => $phone,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Authentica send-otp request failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Authentica: OTP send failed for '.$phone, 0, $e);
        }

        if (! $response->successful()) {
            Log::warning('Authentica send-otp rejected', [
                'phone'  => $phone,
                'status' => $response->status(),
                'body'   => mb_substr((string) $response->body(), 0, 300),
            ]);

            throw new \RuntimeException('Authentica: OTP send rejected for '.$phone);
        }

        Log::info('Authentica: OTP sent via sms', ['phone' => $phone]);
    }

    /**
     * التحقق من الرمز الذي أدخله العميل.
     */
    public function verifyOtp(string $phone, string $otp): bool
    {
        try {
            $response = $this->client()->post('/api/v2/verify-otp', [
                'phone' => $phone,
                'otp'   => $otp,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Authentica verify-otp request failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        $data = $response->json() ?? [];

        // نتعامل بمرونة مع صيغة الرد، لكن أي غموض = رفض (الأمان أولاً)
        foreach (['verified', 'status', 'success'] as $key) {
            if (array_key_exists($key, $data)) {
                return $data[$key] === true;
            }
        }

        // رد 200 بدون مفتاح صريح — نعتبره نجاحاً فقط إذا لم توجد رسالة خطأ
        return empty($data['error']) && empty($data['errors']);
    }

    /* ---------------------------------------------------------------- */

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(15)
            ->acceptJson()
            ->asJson()
            ->withHeaders(['X-Authorization' => $this->apiKey]);
    }
}