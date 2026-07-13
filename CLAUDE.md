# CLAUDE.md — مرجع مشروع iMenu 2026

> ملف مرجعي دائم يُحمّل تلقائيًا كل جلسة. يبقى مختصرًا ومركّزًا.
> المصدر التفصيلي الإضافي: ذاكرة المشروع في `~/.claude/projects/C--GitHub-imenu-2026/memory/`.

---

## نظرة عامة
- **iMenu 2026**: منتج **SaaS** للمطاعم والكافيهات — منيو إلكتروني (**QR**) + استقبال **طلبات أونلاين**، يُباع باشتراك **شهري/سنوي**.
- مبني على قالب **FoodTiger / Mobidonia**.
- المالك يتواصل **بالعربية**.

## التقنيات (Stack)
- **Laravel 10** — **PHP 8.1+**
- نظام موديولات `akaunting/laravel-module` في `modules/`
- **اشتراكات**: Laravel Cashier + Mollie + Paystack
- **صور**: Intervention/Image + Spatie Image
- **أذونات**: Spatie Permission
- **جوال**: تطبيق React Native / Expo موجود داخل `modules/`
- **واجهة**: Blade + Argon preset + webpack mix

## بنية المشروع (خريطة سريعة)
| المسار | المحتوى |
|--------|---------|
| `app/` | موديلات في الجذر: `Restorant`, `Items`, `Categories`, `Order`, `Plans`, `User`, `Status`... + `app/Models/` |
| `app/Http/Controllers/` | أهم الكنترولرات: `OrderController`, `RestorantController`, `CartController`, `PlansController`, `ItemsController`, `PaymentController`, `FrontEndController` |
| `modules/` | Whatsi, Floorplan, LoyaltyPoints, Coupons, Staff, Clients, Manager, Cloner, Deliveryqr, Socialplatforms + قوالب Ember / Glow / ElegantTemplate |
| `resources/views/` | قوالب Blade: `frontend`, `orders`, `cart`, `restorants`, `dashboard`, `plans`... |
| `routes/` | `web.php` (~400 سطر), `api.php`, `channels.php`, `console.php` |
| `config/` | إعدادات المنصة الخاصة في `config/config.php` و `config/settings.php` |

## قواعد سير العمل (مهمة جدًا)
- **التعديلات يطبّقها المستخدم بنفسه** على السيرفر — نقدّم **أدلة/معاينة** فقط، لا تعديلًا مباشرًا على السيرفر.
- **القاعدة الثابتة**: معاينة مرئية **أولًا** → انتظار "اعتمد/عدّل المعاينة" → **ثم** كتابة الدليل.
- **التفعيل التلقائي للمطاعم الجديدة موقوف** — التفعيل يدوي من الإدارة.
- صفحة **cart-checkout** فيها **3 طبقات CSS** على السيرفر؛ التعديلات في **آخر** `imenu-checkout-2026.css` مع رفع رقم الإصدار `?v=`.
- جدول الحالات اسمه **`status`** (مفرد) وليس `statuses`.

## أوامر مفيدة
```bash
php artisan serve            # تشغيل محلي
php artisan migrate          # الهجرات
php artisan module:list      # قائمة الموديولات وحالتها
npm run dev                  # بناء الأصول (webpack mix)
./vendor/bin/phpunit         # الاختبارات (phpunit.xml موجود)
```
