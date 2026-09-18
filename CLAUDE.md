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
| `app/Http/Controllers/` | أهم الكنترولرات: `OrderController`, `RestorantController`, `CartController`, `PlansController`, `ItemsController`, `PaymentController`, `FrontEndController`, `FinanceController`, `CashierController` |
| `modules/` | Whatsi, Floorplan, LoyaltyPoints, Coupons, Staff, Clients, Manager, Cloner, Deliveryqr, Socialplatforms + قوالب Ember / Glow / ElegantTemplate |
| `resources/views/` | قوالب Blade: `frontend`, `orders`, `cart`, `restorants`, `dashboard`, `plans`, `cashier`, `finances`... |
| `routes/` | `web.php` (~400 سطر), `api.php`, `channels.php`, `console.php` |
| `config/` | إعدادات المنصة الخاصة في `config/config.php` و `config/settings.php` |
| `public/imenu/` | أصول واجهات اي منيو الخاصة: `sales/` · `qr/` |

## قواعد سير العمل (مهمة جدًا)
- **التعديلات يطبّقها المستخدم بنفسه** على السيرفر — نقدّم **أدلة/معاينة** فقط، لا تعديلًا مباشرًا على السيرفر.
- **القاعدة الثابتة**: معاينة مرئية **أولًا** → انتظار "اعتمد/عدّل المعاينة" → **ثم** كتابة الدليل.
- **التفعيل التلقائي للمطاعم الجديدة موقوف** — التفعيل يدوي من الإدارة.
- صفحة **cart-checkout** فيها **3 طبقات CSS** على السيرفر؛ التعديلات في **آخر** `imenu-checkout-2026.css` مع رفع رقم الإصدار `?v=`.
- جدول الحالات اسمه **`status`** (مفرد) وليس `statuses`.

## النشر — إلزامي بعد كل `git pull`
```bash
php artisan config:clear && php artisan view:clear && php artisan cache:clear
```
- **⚠️ لا تُنفِّذ `php artisan config:cache` أبدًا** — صفحة الإعدادات تقرأ بـ`env()` مباشرة،
  فستُقرأ القيم فارغة وتُكتب الفوارغ في `.env` عند أول حفظ.
- **`view:clear` ليس اختياريًا عند تغيير الترجمات** — نصوص `lang/ar.json` مخبوزة في كاش
  الواجهات، فبدونه تبقى التسمية القديمة ظاهرة رغم تحديث الملف.

## طريقتا الاستلام — اثنتان (قرار ١٧ سبتمبر ٢٠٢٦)
**«من السيارة» و«من الكاشير».** لا شباك ولا كاونتر.

- القيمة تُخزَّن في `configs` الطلب: `key = pickup_method` · `value = car` أو `counter`.
- **المنطق ثنائي أصلًا** في كل السكربت (`== 'car'` وما عداه) — القرار غيّر التسمية لا المنطق.
- **التسمية من مفتاح واحد:** `From the cashier` في `lang/ar.json`. يستعمله:
  `Order::getExpeditionType()` · `views/cashier/index.blade.php` ·
  `views/finances/partials/pickup_tag.blade.php` · وتطبيق العميل بنصّ مطابق.
- المفتاحان القديمان `From the cafe` و`From the coffee shop` بقيا في `ar.json` مؤشَّرين
  على نفس النص تحسّبًا لأي استدعاء منسي — **لا تُنشئ مفتاحًا ثالثًا**.

## الهوية البصرية — سطحان لا سطح واحد
| السطح | الداكن | الفعل |
|---|---|---|
| **شاشة الكاشير** `/cashier` | `#171513` أسود دافئ | `#F06C1F` |
| الموقع ولوحة الإدارة · `sales.css` · `qr-page.css` | `#16304C` كحلي | `#E8952F` |

شاشة الكاشير تتبع **باليت تطبيق العميل** لا باليت اللوحة: سطح تشغيلي يُلمح من مترين
في ضوء النهار، لا صفحة إدارة تُقرأ. المرجع الكامل: `imenu-brand-identity.md` §8 في
مشروع «i Menu 2.0».

## المشاريع المرتبطة (منظومة iMenu)

المنظومة ثلاثة مجلدات + مشروع معرفة واحد. أي عمل على أحدها قد يمسّ الباقي.

| المسار | الدور | ملاحظات |
|--------|------|---------|
| `C:\GitHub\imenu-2026` | **السكربت** (هذا المجلد) — Laravel 10 | remote: `github.com/AymanAlHarbi/imenu-2026` · فرع `main` |
| `C:\iMenu\clients_app` | **تطبيق العميل** — Expo 57 / RN 0.86.3 / React 19.2.3 | **كان اسمه `vendor_app`** وأُعيدت تسميته ١٧ سبتمبر ٢٠٢٦. محلي بلا remote. فيه `design/` (canvas تصميم) |
| `C:\iMenu\vendor_app_Tablet` | **تطبيق المطعم** لاستقبال الطلبات | `SINGLE_MODE` على المطعم رقم `17` — اكسترا جمبري. مؤجَّل: شاشة الكاشير على الويب `/cashier` تؤدي الغرض |
| مشروع «i Menu 2.0» على claude.ai | الهوية والاستراتيجية | `imenu-brand-identity.md` · `claude/imenu-pickup-strategy.md` · `claude/imenu-client-app-spec.md` · `claude/imenu-cashier-screen-spec.md` |

### العقد الرابط
`API_REFERENCE.md` (في هذا المجلد، ونسخة منه في تطبيق العميل) هو **العقد الملزم** بين
الطرفين. أي تعديل على `routes/api.php` أو `app/Http/Controllers/API/*` يجب أن ينعكس فيه
**وفي التطبيقين**، وإلا انكسر الربط بصمت.

- كلا التطبيقين يتصلان بـ `https://i-menu.me` عبر `config.js` (`domain` + `APP_SECRET`).
- `APP_SECRET` في التطبيقين يجب أن يطابق `settings.app_secret` في لوحة التحكم.
- مسارات التاجر: `routes/api.php` تحت `v2/vendor` (من السطر ~89).

> 🔴 **فجوة قائمة تعطّل شاشات التتبّع في التطبيق:** نقاط `arrived` · `notcollected` ·
> `dispute` وحالة الثقة موجودة في `routes/web.php` **وغائبة عن `v2/client`**.
> هذه أول بند في قائمة تنفيذ الباك إند.

## أوامر مفيدة
```bash
php artisan serve            # تشغيل محلي
php artisan migrate          # الهجرات
php artisan module:list      # قائمة الموديولات وحالتها
npm run dev                  # بناء الأصول (webpack mix)
./vendor/bin/phpunit         # الاختبارات (phpunit.xml موجود)
```

## سجل القرارات
| التاريخ | القرار |
|---|---|
| 2026-09-17 | **توحيد مفردة «من الكاشير»** — مفتاح `From the cashier` بدل أربع تسميات (`lang/ar.json` · `Order::getExpeditionType` · شاشة الكاشير · وسم المبيعات) |
| 2026-09-17 | **الباليت المحايدة في شاشة الكاشير** — `--primary` ← `#F06C1F` · `--danger` ← `#B3261E` (مطابقة `constants/Design.js` في التطبيق) |
