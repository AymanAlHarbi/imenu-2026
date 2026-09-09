<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // لو جاء من تصفح منيو مطعم (عميل) → صفحة تسجيل الجوال الجديدة
        if (session()->has('last_visited_restaurant_alias')) {
            return route('client.phone.show');
        }

        // غير ذلك (owner / staff / driver / admin) → صفحة تسجيل الدخول القديمة
        return route('login');
    }
}