# خطة ربط تطبيق التاجر بمنصة iMenu 2026

> **حالة الخطة:** بانتظار الاعتماد — لم يُنفَّذ أي تعديل بعد.
> تاريخ الفحص: 2026-09-06

---

## 1. الوضع الحالي (نتيجة الفحص)

### ما هو سليم
- الباك إند `C:\GitHub\imenu-2026` منظّم: Laravel 10، فيه `CLAUDE.md` و `API_REFERENCE.md` موثّق جيداً.
- مسارات التاجر موجودة وفعّالة في `routes/api.php` تحت `v2/vendor` (سطر 89):
  `auth` · `settings` · `orders` (updateorderstatus, acceptorder, rejectorder, updateorderlocation)
- التطبيق فيه `services/vendor_api.js` يستدعي نفس هذه النقاط — **الطرفان متوافقان تقنياً**.

### المشاكل المرصودة

| # | المشكلة | الأثر | الخطورة |
|---|---------|------|---------|
| 1 | ملفات React Native مفكوكة داخل `modules/`: `App.js`, `config.js`, `app.json`, `package.json`, `yarn.lock`, `babel.config.js`, `metro.config.js`, `index.js`, `screens/`, `components/`, `navigation/`, `constants/`, `services/`, `store/`, `functions/`, `assets/`, `.expo/`, `.expo-shared/`, `1_docs/` | يربك `php artisan module:list` والـ autoload | **عالية** |
| 2 | `modules/__MACOSX/` — نفايات فك ضغط (ElegantTemplate, Floorplan, Pureadmindash, Socialplatforms, Themeswitcher, Whatsi) | فوضى + احتمال قراءة خاطئة | متوسطة |
| 3 | `modules/modules/LoyaltyPoints` — موديول متداخل بالغلط | الموديول قد لا يُحمَّل | متوسطة |
| 4 | خمس نسخ متطابقة من التطبيق: `source\repos\vendor_app` · `imenu-2026\modules\` · `ملفاتي\iMenu\vendor_app` + `(1)` + `(2)` | لا مصدر وحيد للحقيقة | **عالية** |
| 5 | كل النسخ = القالب الخام. `config.js` فيه `yoursite.com`, `"Appp name"`, `APP_SECRET="app_secret"`, `GOOGLE_API_KEY="YOUR-GOOGLE_API_KEY"`, `currency="USD"` | **التطبيق غير موصول بـ iMenu إطلاقاً** | **عالية** |
| 6 | `npm run makevendor` ينسخ `navigation/Screensvendor.js` — الملف غير موجود | الأمر يفشل | متوسطة |
| 7 | مجلد التطبيق ليس فيه git | لا تراجع عن الأخطاء | عالية |

> **ملاحظة:** تم التحقق بـ `diff -rq` أن نسخة `modules/` ونسخة `source\repos\vendor_app` متطابقة تماماً — لا يوجد عمل ضائع عند الاستغناء عن إحداهما.

---

## 2. الخطة المقترحة

### المرحلة أ — تنظيف `modules/` (لا حذف)
تُنقل الملفات الدخيلة إلى `_extracted_app/` خارج `modules/`، ولا يُحذف شيء:

```
imenu-2026/modules/{ملفات RN}  →  imenu-2026/_extracted_app/
imenu-2026/modules/__MACOSX/    →  imenu-2026/_extracted_app/__MACOSX/
imenu-2026/modules/modules/LoyaltyPoints → يُدمج مع modules/LoyaltyPoints بعد المقارنة
```

يبقى في `modules/` الموديولات الحقيقية فقط:
Clients · Cloner · Coupons · Deliveryqr · ElegantTemplate · Ember · Floorplan · Glow ·
LoyaltyPoints · Manager · Pureadmindash · Socialplatforms · Staff · Whatsi

**التحقق بعدها:** `php artisan module:list` يجب أن يعرض ١٤ موديول بلا أخطاء.

### المرحلة ب — بيت واحد للتطبيق
```
C:\GitHub\imenu-2026-app\      ← المصدر الوحيد (جنب الباك إند)
```
- المصدر: `source\repos\vendor_app` (الأحدث والأنظف).
- `git init` + commit أولي **قبل** أي تعديل.
- النسخ الأخرى تُنقل إلى `ملفاتي\iMenu\_archive_2026-09\` ولا تُحذف.

### المرحلة ج — الربط الفعلي
1. **`config.js`** — يُضبط على قيم iMenu الحقيقية:

   | المفتاح | القيمة الحالية | تحتاج |
   |---|---|---|
   | `domain` | `https://yoursite.com` | دومين iMenu الفعلي |
   | `APP_NAME` | `Appp name` | اسم التطبيق |
   | `APP_SECRET` | `app_secret` | يطابق `settings.app_secret` في السيرفر |
   | `currency` / `currencySign` | `USD` / `$` | `SAR` / `﷼` |
   | `GOOGLE_API_KEY` | placeholder | المفتاح الفعلي |
   | `LOGO` | رابط foodtiger | شعار iMenu |

2. **`CLAUDE.md` في مجلد التطبيق** — يشير إلى `API_REFERENCE.md` في الباك إند كعقد ملزم:
   - المصادقة: `api_token` (٨٠ حرف) عبر query param أو `Authorization: Bearer`
   - الرد الموحّد: `{status:true,data:[...]}` — **الاعتماد على `status` لا على كود HTTP**
   - دور التاجر: `owner` / `staff` — دخول بإيميل + كلمة مرور

3. **`API_REFERENCE.md` في الباك إند** — تُضاف سطر يشير إلى مجلد التطبيق كمستهلك للعقد.

4. مطابقة `services/vendor_api.js` مع مسارات `routes/api.php` سطر ٨٩–١٣٠ والإبلاغ عن أي فرق.

### المرحلة د — إصلاحات صغيرة
- إنشاء `navigation/Screensvendor.js` أو تعديل سكربت `makevendor`.
- حذف سكربتات `zip*` من `package.json` (تشير إلى OneDrive مطوّر القالب الأصلي).

---

## 3. ما ينتج بعد التنفيذ

```
C:\GitHub\
├── imenu-2026\          ← الباك إند (Laravel) — CLAUDE.md + API_REFERENCE.md
└── imenu-2026-app\      ← التطبيق (Expo) — CLAUDE.md يشير إلى API_REFERENCE.md
```

مجلدان مربوطان بعقد واحد موثّق. أي محادثة تُفتح على أي منهما تعرف الطرف الآخر،
فلا يُعاد الشرح، ولا ينفصل التصميم عن الكود.

---

## 4. قرارات مطلوبة منك

1. الموافقة على المرحلة أ (تنظيف `modules/` بالنقل لا الحذف)؟
2. اسم ومكان بيت التطبيق — هل `C:\GitHub\imenu-2026-app` مناسب؟
3. قيم `config.js` الحقيقية (الدومين، `app_secret`، مفتاح Google).
4. هل التنفيذ يتم مني مباشرة على الملفات المحلية، أم أقدّم أوامر تنفّذها بنفسك
   (حسب القاعدة المذكورة في `CLAUDE.md`)؟
