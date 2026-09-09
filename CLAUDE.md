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
- **جوال**: تطبيقان منفصلان خارج هذا الريبو — انظر قسم «المشاريع المرتبطة» أدناه.
  ⚠️ ما بداخل `modules/` من ملفات React Native هو **نسخة قالب ميتة** (`yoursite.com`) — لا تُستخدم.
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

## المشاريع المرتبطة (منظومة iMenu)

المنظومة ثلاثة مجلدات + مشروع معرفة واحد. أي عمل على أحدها قد يمسّ الباقي.

| المسار | الدور | ملاحظات |
|--------|------|---------|
| `C:\GitHub\imenu-2026` | **السكربت** (هذا المجلد) — Laravel 10 | remote: `github.com/AymanAlHarbi/imenu-2026` · فرع `main` |
| `C:\iMenu\vendor_app` | **تطبيق العميل** — Expo 54 / RN 0.81 / React 19 | الاسم مضلّل: محتواه تطبيق عميل وليس تاجر. فيه `design/` (canvas تصميم) |
| `C:\iMenu\vendor_app_Tablet` | **تطبيق المطعم** لاستقبال الطلبات | `SINGLE_MODE` على المطعم رقم `17` — اكسترا جمبري |
| مشروع «i Menu 2.0» على claude.ai | الهوية والاستراتيجية | `imenu-brand-identity.md` · `claude/imenu-pickup-strategy.md` · `imenu-home.html` |

### العقد الرابط
`API_REFERENCE.md` (في هذا المجلد، ونسخة منه في تطبيق العميل) هو **العقد الملزم** بين
الطرفين. أي تعديل على `routes/api.php` أو `app/Http/Controllers/API/*` يجب أن ينعكس فيه
**وفي التطبيقين**، وإلا انكسر الربط بصمت.

- كلا التطبيقين يتصلان بـ `https://i-menu.me` عبر `config.js` (`domain` + `APP_SECRET`).
- `APP_SECRET` في التطبيقين يجب أن يطابق `settings.app_secret` في لوحة التحكم.
- مسارات التاجر: `routes/api.php` تحت `v2/vendor` (من السطر ~89).

## أوامر مفيدة
```bash
php artisan serve            # تشغيل محلي
php artisan migrate          # الهجرات
php artisan module:list      # قائمة الموديولات وحالتها
npm run dev                  # بناء الأصول (webpack mix)
./vendor/bin/phpunit         # الاختبارات (phpunit.xml موجود)
```
