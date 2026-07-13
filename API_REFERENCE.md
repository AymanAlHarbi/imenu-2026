# مرجع API — iMenu 2026

> مرجع موجّه لمطوّري **التطبيقات المربوطة** (Client / Vendor / Driver / KDS) مع منصة iMenu.
> كل الحقول والمسارات في هذا الملف مستخرجة من الكود الفعلي (`routes/api.php` + `app/Http/Controllers/API/*`).

---

## 1. الأساسيات

| البند | القيمة |
|------|--------|
| **Base URL** | `https://YOUR-DOMAIN/api` |
| **الإصدار المعتمد** | `v2` → كل المسارات تحت `/api/v2/...` (يوجد `/api/...` قديم للسائق فقط) |
| **الصيغة** | JSON في الطلب والرد |
| **المصادقة** | Token (guard `auth:api`, driver = `token`) |

### آلية المصادقة (مهم)
- بعد تسجيل الدخول تحصل على **`api_token`** (سلسلة 80 حرفًا) — احفظه في التطبيق.
- أرسله مع **كل طلب محمي** بإحدى الطريقتين:
  - كـ **query param**: `?api_token=XXXX` (الطريقة المستخدمة داخليًا في عدة نقاط)
  - أو **Bearer header**: `Authorization: Bearer XXXX`
- التوكن **ثابت** لكل مستخدم (ليس منتهي الصلاحية) — يتغيّر فقط عند إعادة الإنشاء.

### الأدوار (Roles)
| الدور | يدخل عبر | ملاحظات |
|------|---------|---------|
| `client` | جوال+OTP أو إيميل أو Google/FB | عميل التطبيق |
| `owner` / `staff` | إيميل + كلمة مرور | صاحب/موظف المطعم (Vendor) |
| `driver` | إيميل/توكن | السائق |

### صيغة الرد الموحّدة
```json
{ "status": true,  "data": [ ... ] }          // نجاح
{ "status": false, "errMsg": "..." }           // خطأ (قد يكون نصًا أو كائن أخطاء)
```
> ⚠️ ملاحظة: كثير من نقاط الفشل تُرجع **HTTP 200** مع `status:false`. اعتمد دائمًا على حقل `status` وليس على كود HTTP فقط. (نقاط OTP الحديثة تُرجع أكواد صحيحة 422/429/403.)

### app_secret
عمليات **التسجيل** (register) تتطلب حقل `app_secret` يطابق `settings.app_secret` في السيرفر — للحماية من التسجيل الآلي. اطلبه من الإدارة.

---

## 2. الإعدادات العامة — Settings

### `GET /api/v2/client/settings`  (وأيضًا `/vendor/settings`, `/driver/settings`)
عام (بدون توكن). يُرجع إعدادات التطبيق:
```json
{
  "data": {
    "app_name": "iMenu",
    "single_mode": false,          // وضع المطعم الواحد
    "single_mode_id": null,
    "multi_city": true,
    "currency": "SAR",
    "currency_sign": "﷼",
    "enable_cod": true,            // الدفع عند الاستلام
    "enable_stripe": false,
    "stripe_publish_key": "...",
    "onesignal_android_app_id": "...",
    "onesignal_ios_app_id": "...",
    "google_api_key": "...",
    "payment_methods": [ { "name": "...", "alias": "..." } ],
    "units": "K"                   // K = كيلومتر
  },
  "status": true
}
```

---

## 3. مصادقة العميل — Client Auth  (`/api/v2/client/auth`)

### أ) الدخول برقم الجوال + OTP (المسار الأساسي للتطبيق)
تدفّق من 3–4 خطوات، **عام بدون توكن**:

| # | الطلب | Body | الرد |
|---|-------|------|------|
| 1 | `POST .../phone/check` | `{ phone }` | `{ status, exists:bool, phone }` — هل الرقم مسجّل؟ |
| 2أ | `POST .../phone/register` | `{ phone, name, email?, app_secret }` | ينشئ عميل جديد ثم يرسل الرمز |
| 2ب | `POST .../phone/sendcode` | `{ phone }` | يرسل/يعيد إرسال الرمز لرقم مسجّل |
| 3 | `POST .../phone/verify` | `{ phone, code, expotoken?, vendor_id? }` | `{ status, token, id, name, email, phone }` |

ملاحظات:
- الأرقام سعودية بصيغة `05XXXXXXXX` (تُطبَّع داخليًا). أرقام عربية `٠١٢..` مقبولة في `code`.
- إذا كان التحقق بالجوال **معطّلًا** في الإعدادات (وضع تجربة)، يتخطى `verify` ويرجع التوكن مباشرة (`skip_otp:true`).
- **Rate limiting**: check=15/دقيقة للـ IP، sendcode=1/دقيقة للرقم، verify=5 محاولات. عند التجاوز → HTTP 429.
- إذا كان الرقم لحساب owner/admin يرجع `use_standard_login:true` (يجب الدخول بالإيميل).

### ب) الدخول بالإيميل
```
POST /api/v2/client/auth/gettoken   { email, password, expotoken? }
→ { status, token, id, name, email }
```
### ج) التسجيل بالإيميل
```
POST /api/v2/client/auth/register   { name, email, phone, password(min8), app_secret, expotoken? }
→ { status, token, id }
```
### د) دخول اجتماعي
```
POST /api/v2/client/auth/loginfb        { email, fb_id, name? }
POST /api/v2/client/auth/logingoogle    { email, google_id, name? }
```
### هـ) بيانات المستخدم / إلغاء التفعيل (محمي)
```
GET /api/v2/client/auth/data?api_token=XXX        → { name, email, phone }
GET /api/v2/client/auth/deactivate?api_token=XXX
```

> **expotoken**: توكن إشعارات Expo push — أرسله عند الدخول ليستقبل المستخدم الإشعارات.

---

## 4. المطاعم والمنيو — Client Vendor  (`/api/v2/client/vendor`) — عام

| الطلب | الوصف | الرد |
|-------|-------|------|
| `GET .../cities` | قائمة المدن (مع `logo` مطلق) | `{ data:[ city ] }` |
| `GET .../list/{city_id}` | مطاعم مدينة (أو `none` = الكل) | `{ data:[ restaurant ] }` |
| `GET .../{id}/items` | منيو المطعم مقسّمًا حسب الأقسام | `{ data:[ [ item... ] ] }` |
| `GET .../{id}/hours` | ساعات العمل + الحالة (مفتوح/مغلق) + timeslots | `{ data:{ restorant, timeSlots, openingTime, closingTime } }` |
| `GET .../deliveryfee/{res}/{adr}` | رسوم التوصيل لعنوان معيّن | `{ fee, inRadius, address }` |

**بنية عنصر المنيو (item)** يشمل: الحقول الأساسية + `category_name` + `extras[]` + `options[]` + `variants[]` (كل variant له `price` و`optionsList`).

---

## 5. الطلبات — Client Orders  (`/api/v2/client/orders`) — محمي

### `GET /` — طلباتي (آخر 50)
يرجع الطلبات مع العلاقات: `restorant, status, items, address, driver`.

### `POST /` — إنشاء طلب
الحقول المطلوبة (تحقُّق فعلي في `MobileAppOrderRepository`):
```jsonc
{
  "api_token": "XXX",
  "vendor_id": 12,
  "delivery_method": "delivery",   // أو "pickup"
  "payment_method": "cod",         // "cod" | "stripe" | alias بوابة أخرى
  "items": [
    { "id": 34, "qty": 2, "variant": 5|null, "extras": [ ... ] }
  ],
  // حسب delivery_method:
  "address_id": 7,                 // للتوصيل
  // حسب payment_method:
  "stripe_token": "tok_...",       // لو stripe
  // اختيارية:
  "coupon_code": "SALE10",
  "tip": 5,
  "comment": "بدون بصل",
  "customFields": { ... }
}
```
الرد: `{ status, message, id, paymentLink }` — إذا كان الدفع أونلاين، وجّه المستخدم إلى `paymentLink`.

> الطلب يُنشأ بالحالة الابتدائية (status id **1** = تم الإنشاء). إن كان `order_approve_directly` مفعّلًا يُضاف status **2** تلقائيًا.

---

## 6. العناوين — Client Addresses  (`/api/v2/client/addresses`) — محمي

| الطلب | Body | الوصف |
|-------|------|-------|
| `GET /?api_token=XXX` | — | عناويني |
| `GET /fees/{restaurant_id}?api_token=XXX` | — | عناويني + رسوم التوصيل لكل عنوان |
| `POST /` | `{ api_token, address, lat, lng, apartment?, intercom?, floor?, entry? }` | إضافة عنوان |
| `POST /delete` | `{ id }` (محمي بالتوكن) | تعطيل عنوان |

---

## 7. الإشعارات — Client Notifications — محمي
```
GET /api/v2/client/notifications   → { data: [ notification... ] }
```

---

## 8. تطبيق المطعم — Vendor  (`/api/v2/vendor`)

### مصادقة (`/auth`)
```
POST /gettoken   { email, password, expotoken? }   // owner/staff فقط
POST /register   { vendor_name, name, email, phone, password, app_secret }
GET  /data?api_token=XXX        GET /deactivate?api_token=XXX
```
> في نسخة FoodTiger التسجيل يُنشئ مطعمًا **غير مفعّل** بانتظار تفعيل يدوي من الإدارة.

### الطلبات (`/orders`, محمي)
```
GET  /                                              // طلبات المطعم
GET  /order/{order}                                 // تفاصيل طلب
GET  /earnings                                      // الأرباح
GET  /updateorderstatus/{order}/{status}            // تغيير حالة الطلب
GET  /updateorderlocation/{order}/{lat}/{lng}
GET  /acceptorder/{order}     GET /rejectorder/{order}
```

---

## 9. تطبيق السائق — Driver  (`/api/v2/driver`)

### مصادقة (`/auth`)
```
POST /gettoken     POST /register
GET  /data   GET /deactivate   GET /driveronline   GET /drveroffline   (محمي)
```
### الطلبات (`/orders`, محمي)
```
GET  /                                   GET /order/{order}
GET  /with_latlng/{lat}/{lng}            GET /earnings
GET  /updateorderdeliveryprice/{order}/{deliveryprice}
GET  /updateorderstatus/{order}/{status}
GET  /updateorderlocation/{order}/{lat}/{lng}
GET  /acceptorder/{order}   GET /rejectorder/{order}
```
> يوجد أيضًا API قديم (V1) للسائق تحت `/api/...` مباشرة — يُفضّل استخدام V2.

---

## 10. شاشة المطبخ — KDS  (`/api/v2/kds/orders`) — محمي
```
GET /{finished}                                       // 0=جارية 1=منتهية
GET /finishItem/{orderid}/{itemId}/{isDBtypeOrder}
GET /unfinishItem/{orderid}/{itemId}/{isDBtypeOrder}
GET /finishOrder/{orderid}/{isDBtypeOrder}
GET /unfinishOrder/{orderid}/{isDBtypeOrder}
```

---

## 11. ملاحظات للمطوّر
- **حالات الطلب** تُدار عبر جدول علاقة (status id ↔ order). أهمها: `1` = تم الإنشاء، `2` = مقبول/معتمد.
- **العملة والوحدات** من `/settings` — لا تُثبّتها في التطبيق.
- **الإشعارات**: OneSignal (app ids في settings) + Expo push token (`expotoken`).
- **الخرائط**: مفتاح Google من `/settings.google_api_key`.
- **وضع المطعم الواحد** (`single_mode`): إن كان `true` تخطّى شاشة اختيار المدينة/المطعم واستخدم `single_mode_id`.

---

*آخر تحديث: 2026-07-11 — مستخرج من الكود الفعلي. عند تعديل `routes/api.php` أو كنترولرات `API/` حدّث هذا الملف.*
