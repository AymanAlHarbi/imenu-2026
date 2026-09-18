<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Order;
use App\Services\PickupFlow;
use App\Services\Trust;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * نقاط العميل لمسار الاستلام ونظام الثقة — v2/client.
 *
 * كانت هذه الأفعال موجودة على الويب فقط (routes/web.php) فبقيت شاشات
 * التتبّع في التطبيق معطّلة. فُتحت هنا ١٨ سبتمبر ٢٠٢٦.
 *
 * فرق جوهري عن الويب: الويب يوثّق الزائر ببصمة الطلب (md) لأن العميل قد يكون
 * بلا حساب. هنا كل نداء مصادَق بـauth:api، فالتحقق بملكية الطلب مباشرة —
 * أقوى، ولا يحتاج تمرير md من التطبيق.
 *
 * الرد موحّد: {status, message, data} — والأخطاء تعود بـ200 مع status:false،
 * وهو عرف السكربت الذي يعتمد عليه التطبيق (انظر API_REFERENCE.md).
 */
class TrustController extends Controller
{
    /**
     * حالة الاستلام والثقة لطلب واحد — ما تحتاجه شاشات التتبّع الأربع.
     * GET v2/client/orders/{order}/state
     */
    public function orderState(Order $order): JsonResponse
    {
        if (! $this->ownsOrder($order)) {
            return $this->deny();
        }

        return $this->ok($this->stateOf($order));
    }

    /**
     * «وصلت — أنا في الموقف». لطلبات السيارة فقط ومرة واحدة.
     * POST v2/client/orders/arrived  { order_id }
     */
    public function arrived(Request $request): JsonResponse
    {
        $order = Order::find($request->order_id);
        if (! $order || ! $this->ownsOrder($order)) {
            return $this->deny();
        }

        if (! PickupFlow::isCar($order)) {
            return $this->fail(__('This order is picked up from the cashier'));
        }

        $already = (bool) PickupFlow::arrivedAt($order);
        PickupFlow::markArrived($order);

        return $this->ok(
            $this->stateOf($order->fresh()),
            $already ? __('The coffee shop was already notified') : __('The coffee shop has been notified')
        );
    }

    /**
     * اعتراض العميل على بلاغ «لم يُستلم» — حق مكفول في نظام حماية البيانات،
     * لا ميزة اختيارية. يوقف عدّ البلاغ حتى يُحسم.
     * POST v2/client/orders/dispute  { order_id }
     */
    public function dispute(Request $request): JsonResponse
    {
        $order = Order::find($request->order_id);
        if (! $order || ! $this->ownsOrder($order)) {
            return $this->deny();
        }

        if (! Trust::dispute($order)) {
            return $this->fail(__('No open report on this order'));
        }

        return $this->ok(
            $this->stateOf($order->fresh()),
            __('Your objection has been recorded')
        );
    }

    /**
     * حالة ثقة العميل نفسه — لشاشة «حسابي».
     * تتبع قاعدة «للأمام لا للخلف»: تُرجع ما تبقّى للوصول إلى «موثوق»،
     * ولا تُرجع أبدًا عدّاد بلاغات ولا عدد طلبات تراكمي.
     * GET v2/client/trust/me
     */
    public function me(): JsonResponse
    {
        $client = auth()->user();

        $streak = Trust::streak($client);
        $blockedUntil = Trust::blockedUntil($client);

        return $this->ok([
            'state' => Trust::isBlocked($client) ? 'blocked' : (Trust::isTrusted($client) ? 'trusted' : 'normal'),
            'label' => Trust::clientLabel($client),
            //ما تبقّى للوصول إلى «موثوق» — رقم للأمام لا كشف حساب
            'pickups_to_trusted' => max(0, Trust::TRUSTED_STREAK - $streak),
            'trusted_streak_target' => Trust::TRUSTED_STREAK,
            'is_trusted' => Trust::isTrusted($client),
            //الإيقاف يُعرض بالسبب والمدة وحق الاعتراض — متطلب PDPL
            'blocked_until' => $blockedUntil ? $blockedUntil->toDateTimeString() : null,
            'blocked_days_left' => $blockedUntil ? $blockedUntil->diffInDays(now()) + 1 : 0,
            'block_reason' => $blockedUntil ? __('Orders were not collected') : null,
        ]);
    }

    /* ------------------------------------------------------------------
     | داخلي
     |------------------------------------------------------------------ */

    /** شكل الحالة الذي تقرأه شاشات التتبّع */
    private function stateOf(Order $order): array
    {
        $readyAt = Trust::readyAt($order);
        $arrivedAt = PickupFlow::arrivedAt($order);
        $reported = Trust::isReported($order);
        $disputed = Trust::isDisputed($order);

        if ($reported) {
            $state = 'not_collected';
        } elseif (Trust::isDelivered($order)) {
            $state = 'delivered';
        } elseif ($readyAt) {
            $state = 'ready';
        } else {
            $state = 'preparing';
        }

        return [
            'order_id' => $order->id,
            'state' => $state,
            'pickup_method' => PickupFlow::method($order),
            'is_car' => PickupFlow::isCar($order),
            'ready_at' => $readyAt ? $readyAt->toDateTimeString() : null,
            'arrived_at' => $arrivedAt,
            //«وصلت» يظهر للسيارة فقط، ومرة واحدة
            'can_mark_arrived' => PickupFlow::isCar($order) && ! $arrivedAt && $state !== 'delivered',
            'is_reported' => $reported,
            'is_disputed' => $disputed,
            //زر الاعتراض يظهر ما دام هناك بلاغ لم يُعترض عليه
            'can_dispute' => $reported && ! $disputed,
        ];
    }

    private function ownsOrder(Order $order): bool
    {
        $user = auth()->user();

        return $user && $order->client_id && (int) $order->client_id === (int) $user->id;
    }

    private function ok(array $data, string $message = ''): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data]);
    }

    private function fail(string $message): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $message, 'data' => null]);
    }

    private function deny(): JsonResponse
    {
        return $this->fail(__('Order not found'));
    }
}
