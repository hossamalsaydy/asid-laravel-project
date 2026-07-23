# نظام اسيد (ASID) — نظام إدارة عيادة أسنان مصغر

نظام ERP بسيط ومستقر لإدارة عيادة أسنان، مصمم لمستخدم واحد فقط (الطبيب/المدير)،
ومبني ليعمل محلياً أولاً (Offline-First) مع مزامنة اختيارية إلى سيرفر سحابي.

## 1. خطوات التركيب

```bash
composer install
cp .env.example .env
php artisan key:generate

# إعداد قاعدة البيانات (MySQL) في .env ثم:
php artisan migrate
php artisan db:seed              # ينشئ حساب المدير الوحيد admin@asid.local

# رابط تخزين الملفات العامة (ضروري لعرض صور الأشعة/المستندات)
php artisan storage:link
```

بعد أول تسجيل دخول بحساب `admin@asid.local` (كلمة المرور الافتراضية داخل
`DatabaseSeeder.php`)، **يجب تغييرها فوراً**.

## 2. الخط العربي (Tajawal) - Offline

ضع ملفات خط Tajawal (woff2) داخل:
```
public/assets/fonts/tajawal/
```
مع ملف `tajawal.css` يعرّف `@font-face` بمسارات محلية (وليس Google Fonts)،
حتى يعمل النظام بالكامل دون إنترنت.

## 3. هيكل الموديولات المبنية

| الموديول | Model | Controller | المسارات الأساسية |
|---|---|---|---|
| المرضى | `Patient`, `PatientDocument` | `PatientController` | `/patients` |
| المواعيد | `Appointment` | `AppointmentController` | `/appointments` |
| مخطط الأسنان | `DentalTreatment` | `DentalTreatmentController` | `/patients/{id}/dental-chart` |
| المعامل الخارجية | `DentalLab` | `DentalLabController` | `/labs` |
| الفواتير | `Invoice`, `InvoiceItem` | `InvoiceController` | `/invoices` |
| الأقساط | `Installment`, `InstallmentPayment` | `InstallmentController` | `/installments` |
| السندات | `Voucher` | `VoucherController` | `/vouchers` |
| المخزن | `InventoryItem`, `InventoryTransaction` | `InventoryController` | `/inventory` |

## 4. آلية المزامنة (Offline-First Sync)

كل جدول يحتوي `local_id` (UUID) + `is_synced` + `synced_at` عبر
`App\Traits\HasSyncFields` الذي يولّد `local_id` تلقائياً عند الإنشاء.

نقاط نهاية API (`routes/api.php`، محمية بـ `auth:sanctum`):

- **رفع (Push):** `POST /api/sync/{table}/push`
  ```json
  { "records": [ { "local_id": "uuid...", "name": "...", ... } ] }
  ```
  يقوم السيرفر بعمل `updateOrCreate` بالاعتماد على `local_id` (وليس `id`
  التسلسلي، لأنه يختلف بين كل جهاز والسيرفر)، ويردّ بقائمة `local_id` التي
  تمت مزامنتها بنجاح ليعلّمها الجهاز المحلي كمُزامَنة.

- **تحميل (Pull):** `GET /api/sync/{table}/pull?after=2026-07-01T00:00:00Z`
  يعيد كل السجلات التي تغيّرت بعد ذلك التاريخ، مع `server_time` لاستخدامه
  كنقطة بداية في طلب المزامنة التالي.

الجداول المسموح مزامنتها محددة بقائمة بيضاء داخل `SyncController` لمنع أي
وصول لجداول غير مصرح بها.

⚠️ **تنبيه Laravel 11+:** تأكد من تسجيل `routes/api.php` داخل
`bootstrap/app.php`:
```php
->withRouting(
    web: __DIR__.'/routes/web.php',
    api: __DIR__.'/routes/api.php',
    commands: __DIR__.'/routes/console.php',
    health: '/up',
)
```
كما يجب تثبيت Laravel Sanctum (`composer require laravel/sanctum`) لإصدار
Tokens للجهاز المحلي عند طلب المزامنة.

## 5. منع الحجز وقت القيلولة

يُطبَّق عبر `App\Rules\NotDuringNapTime` (مُستخدمة داخل
`StoreAppointmentRequest`)، وتمنع أي وقت موعد بين الساعة 1:00 ظهراً و4:00
عصراً. لتغيير حدود الفترة، عدّل `$napStartHour` / `$napEndHour` داخل الـ Rule.

## 6. وضع الستر (Privacy Mode)

مُفعّل عبر JavaScript في `layouts/admin.blade.php`. أي عنصر في أي واجهة يحمل
`class="sensitive-data"` (أسماء المرضى، الهواتف، صور الأشعة...) يُطمس تلقائياً
عند تفعيل الزر في الشريط العلوي، وتُحفظ الحالة في `localStorage`.

## 7. ما يحتاج استكماله يدوياً

- **نظام المصادقة (Login):** لم يتم بناء شاشة تسجيل الدخول ضمن هذه الحزمة
  لتبسيط النطاق؛ نظراً لأن النظام لمستخدم واحد، يمكن استخدام Laravel
  Breeze/Fortify بأبسط إعداد ممكن (بدون تسجيل حسابات جديدة).
- **Laravel Sanctum:** مطلوب لتفعيل `auth:sanctum` في `routes/api.php`.
- تأكد من وجود جدول `users` القياسي في مشروع Laravel (موجود افتراضياً عند
  إنشاء أي مشروع Laravel جديد).
