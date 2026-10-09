# نشر نظام ASID لعيادة الأسنان على Alwaysdata

هذا الدليل مخصص للمستودع `hossamalsaydy/asid-laravel-project`. التطبيق مبني على Laravel 13، ويحتاج PHP 8.3 أو أحدث، وقاعدة بيانات MySQL/MariaDB. [7] نفّذ الخطوات بالترتيب، ولا تضع كلمات المرور أو ملف `.env` في GitHub.

## إصلاحات الأمان الموجودة في النسخة الحالية

أزيل المسار `/run-seed` الذي كان يستدعي `migrate:fresh`، وأزيل التسجيل العام، واستبدلت كلمة مرور المدير الافتراضية بمتطلب كلمة مرور يحددها مسؤول النظام عند إنشاء الحساب. تُحفظ مرفقات المرضى الجديدة على قرص خاص وتُعرض عبر مسار يتطلب تسجيل الدخول ويتأكد من ارتباط الملف بالمريض.

إذا كان لديك تثبيت أقدم رفع مستندات إلى `storage/app/public`، خذ نسخة احتياطية من قاعدة البيانات والملفات أولاً. يتضمن المستودع أمر `patients:documents-secure` لنقل المرفقات المسجلة إلى التخزين الخاص، وملف `.htaccess` يوقف الوصول المباشر إلى ملفات القرص العام على Apache أثناء الترحيل. بعد نجاح الأمر، أزل رابط `public/storage` القديم إن كان موجوداً؛ خطوات التنفيذ أدناه.

شاشة تسجيل الدخول موجودة ضمن Breeze. لا يحتوي المشروع الآن على كلمة مرور مدير معروفة مسبقاً، ولا يسمح للزوار بإنشاء حسابات.

إذا كنت ترفع تحديثاً إلى قاعدة استُخدمت من قبل، راجع حسابات `users` قبل فتح الموقع. إيقاف التسجيل لا يحذف المستخدمين الحاليين، وSeeder لا يغيّر كلمة مرور مدير موجود؛ حدّث أي كلمة مرور افتراضية قديمة وعطّل الحسابات غير المعروفة بعد حفظ نسخة احتياطية.

## 1. جهّز حساب Alwaysdata والخدمات

1. من لوحة Alwaysdata افتح **Environment → PHP**، واختر إصداراً لا يقل عن **8.3**. المشروع يطلب `php: ^8.3` في `composer.json`، وLaravel 13 يتطلب PHP 8.3 أو أحدث. إصدارات PHP المتاحة قد تختلف حسب الحساب؛ افحص أن الإصدار المطلوب مُفعّل فعلياً. [1] [7]
2. اختر إصدار Node.js حديثاً، مثل 22 أو 24، من إعدادات **Environment → Node.js** إذا كان متاحاً. يستخدم المشروع Vite 8 لبناء ملفات CSS وJavaScript. [5]
3. من **Databases → MySQL** أنشئ قاعدة بيانات ومستخدماً، وامنح المستخدم صلاحية الكتابة اللازمة للتطبيق. دوّن الاسم واسم المستخدم وكلمة المرور واسم الخادم كما تظهر **بالضبط** في اللوحة. Alwaysdata يستخدم MariaDB/MySQL؛ اسم الخادم يكون عادةً بالشكل `mysql-اسم_الحساب.alwaysdata.net` والمنفذ `3306`، لكن اعتمد القيم الظاهرة في حسابك. [2]

## 2. اتصل بالخادم عبر SSH

أنشئ أو تحقّق من مستخدم SSH من **Remote access → SSH/SFTP**. من جهازك افتح الطرفية واتصل بالأمر التالي بعد استبدال العناصر بين الأقواس:

```bash
ssh اسم_مستخدم_SSH@ssh-اسم_حسابك.alwaysdata.net
```

عنوان SSH والمنفذ الافتراضي 22 موضّحان في وثائق Alwaysdata. [3]

يمكنك نسخ المشروع من GitHub باستخدام SSH. إذا كان المستودع خاصاً، أنشئ مفتاح SSH على حساب Alwaysdata، ثم أضف المفتاح العام إلى إعدادات المستودع في GitHub كـ **Deploy key للقراءة فقط**. لا تنسخ المفتاح الخاص إلى GitHub أو إلى المحادثات. إذا كان المستودع عاماً، يمكن استخدام رابط HTTPS بدلاً من ذلك.

```bash
cd ~
git clone git@github.com:hossamalsaydy/asid-laravel-project.git
cd asid-laravel-project
```

إذا كنت قد رفعت تعديلات الأمان إلى فرع آخر، استنسخ ذلك الفرع أو بدّل إليه قبل إكمال التثبيت.

## 3. تحقّق من PHP وComposer وابنِ الواجهة

نفّذ:

```bash
php -v
composer --version
node -v
npm -v
```

تأكد أن PHP هو 8.3 أو أحدث. إذا ظهر إصدار أقدم، ارجع إلى إعداد PHP في لوحة Alwaysdata، ثم افتح جلسة SSH جديدة وأعد التحقق. يحتاج Laravel أيضاً إلى امتدادات PHP المعتادة مثل PDO و`pdo_mysql` وOpenSSL وMbstring وFileinfo؛ وثائق Laravel تعرض القائمة كاملة.

ثبّت اعتماديات الإنتاج وابنِ ملفات Vite:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

إذا لم يكن Node/npm متاحاً لحسابك، ابنِ الواجهة على جهازك باستخدام `npm ci && npm run build` ثم ارفع مجلد `public/build` الناتج إلى المسار نفسه في الخادم. لا ترفع مجلد `node_modules`.

## 4. أنشئ ملف الإنتاج `.env`

من مجلد المشروع:

```bash
cp .env.example .env
nano .env
```

لا تترك قيم المثال كما هي. اضبط قاعدة البيانات والعنوان وإعدادات الإنتاج، وأنشئ مفتاحاً جديداً للتطبيق. حدّد أيضاً `ADMIN_PASSWORD` فريداً لا يقل عن 16 محرفاً قبل إنشاء حساب المدير، ولا تضعه في GitHub:

```dotenv
APP_NAME="ASID Dental Clinic"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://اسم-النطاق-الخاص-بك
ADMIN_EMAIL=admin@asid.local
ADMIN_PASSWORD=ضع-كلمة-مرور-قوية-وفريدة-هنا

DB_CONNECTION=mysql
DB_HOST=mysql-اسم-حسابك.alwaysdata.net
DB_PORT=3306
DB_DATABASE=اسم-قاعدة-البيانات-من-اللوحة
DB_USERNAME=اسم-مستخدم-قاعدة-البيانات
DB_PASSWORD="كلمة-مرور-قاعدة-البيانات"

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_SECURE_COOKIE=true
```

استخدم الأسماء الحقيقية التي تعرضها لوحة Alwaysdata؛ قد تحتوي أسماء القاعدة والمستخدم على بادئة مرتبطة بحسابك. أبقِ `APP_DEBUG=false` في الإنتاج. `SESSION_SECURE_COOKIE=true` مناسب عند استخدام HTTPS.

ولّد مفتاحاً جديداً **مرة واحدة فقط** بعد حفظ الملف:

```bash
php artisan key:generate --force
```

لا تعِد توليد المفتاح عند كل تحديث، لأن تغييره قد يبطل الجلسات ويفكّك البيانات المشفّرة السابقة. لا ترسل محتوى `.env` لأحد ولا ترفعه إلى GitHub.

## 5. أنشئ جداول قاعدة البيانات وحساب المدير

قبل إنشاء المدير، اضبط `ADMIN_PASSWORD` في `.env` إلى كلمة فريدة لا تقل عن 16 محرفاً. ثم نفّذ على قاعدة Alwaysdata الجديدة:

```bash
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
```

بعد إنشاء الحساب، احذف قيمة `ADMIN_PASSWORD` من `.env` ثم نفّذ:

```bash
php artisan config:clear
```

لا تستخدم `migrate:fresh` على قاعدة تحتوي بيانات مهمة؛ فهو يحذف الجداول والبيانات. لا يغيّر Seeder كلمة مرور حساب مدير موجود، ولا يضع كلمة مرور افتراضية.

للتحديثات المستقبلية، خذ نسخة احتياطية من قاعدة البيانات أولاً، ثم استخدم `php artisan migrate --force` فقط بعد مراجعة ملفات الترحيل. لا تشغّل البذور في كل تحديث ما لم تكن تعرف أثرها.

## 6. أنشئ موقع PHP واضبط النطاق

بعد استنساخ المشروع، من لوحة Alwaysdata افتح **Web → Sites → Add a site**:

1. اختر النوع **PHP**.
2. أضف عنوان النطاق الذي ستستخدمه. يجب إضافة كل اسم تريد أن يعمل، مثل `example.com` و`www.example.com` كلٌّ على حدة.
3. اجعل **Root directory / المسار** يشير إلى مجلد `public` داخل المشروع، وليس إلى مجلد المشروع كله. مثال:
   ```text
   /home/اسم_حسابك/asid-laravel-project/public
   ```
4. اختر PHP 8.3 أو أحدث للموقع أو للحساب، وفق إعدادات لوحة التحكم. يتطلب تعريف الموقع اختيار نوعه وإضافة عناوينه في إعدادات **Web → Sites**. [1] [4]
5. فعّل HTTPS/SSL للنطاق، وتأكد أن `APP_URL` في `.env` يبدأ بـ `https://`.

Laravel يجب أن يُقدَّم من `public/index.php`. لا تجعل جذر الموقع هو `/home/اسم_حسابك/asid-laravel-project`؛ هذا قد يكشف ملفات الإعدادات. [6] وإذا كان DNS لدى مزود آخر، أضف سجلات DNS المطلوبة لدى ذلك المزود، ثم انتظر انتشارها. [4]

## 7. أذونات الملفات وتحسين الإنتاج

يحتاج Laravel إلى الكتابة في `storage` و`bootstrap/cache`. [6] من مجلد المشروع نفّذ:

```bash
chmod -R u+rwX storage bootstrap/cache
php artisan optimize:clear
php artisan optimize
```

لا تستخدم `chmod -R 777`. إذا كنت تحدّث تثبيتاً قديماً يحوي ملفات مرضى في التخزين العام، شغّل الأمر التالي بعد أخذ النسخة الاحتياطية. فهو ينقل الملفات المرتبطة بسجلات المستندات، ويتحقق من نسخها قبل حذف النسخ العامة:

```bash
php artisan patients:documents-secure
if [ -L public/storage ]; then rm public/storage; fi
```

لا تنشئ `public/storage` لهذا الأرشيف. الرفع الجديد يستخدم التخزين الخاص تلقائياً.

## 8. اختبر الموقع قبل إدخال بيانات حقيقية

افتح `https://اسم-النطاق-الخاص-بك/up`؛ يفترض أن يعيد Laravel استجابة سليمة. ثم اختبر صفحة الدخول، تسجيل الدخول، عرض لوحة التحكم، إنشاء سجل تجريبي، ورفع ملف وهمي. تأكد أن `/register` غير متاح وأن `/run-seed` غير موجود. راجع سجلات التطبيق في `storage/logs/laravel.log` وسجلات الموقع من لوحة Alwaysdata عند ظهور خطأ 500.

قبل إدخال بيانات حقيقية، تأكد من نجاح نقل أي ملفات قديمة، وأن رابط `public/storage` أزيل، وأن تسجيل الدخول يعمل عبر HTTPS. اختبر أيضاً أن الطلب غير المسجل إلى رابط مستند يُعاد إلى صفحة الدخول.

## 9. طريقة نشر تحديث جديد

بعد دفع التغييرات إلى GitHub، اتصل عبر SSH ثم نفّذ:

```bash
cd ~/asid-laravel-project
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan patients:documents-secure
if [ -L public/storage ]; then rm public/storage; fi
php artisan optimize:clear
php artisan optimize
```

إذا كان التحديث يتضمن تغييراً في ملفات الواجهة فقط، قد تحتاج إلى بناء Vite دون ترحيل قاعدة البيانات. راجع سجل التغييرات ونسخة قاعدة البيانات قبل تنفيذ أي ترحيل على الإنتاج. كما أن README يذكر أن مزامنة تطبيق Android تحتاج استكمال نقاط API وتثبيت Laravel Sanctum؛ لا تفترض أن المزامنة جاهزة لمجرد نشر موقع Laravel.

## مراجع رسمية

[1]: https://help.alwaysdata.com/en/docs/web-hosting/languages/php/configuration/ "Alwaysdata — Configuring PHP"
[2]: https://help.alwaysdata.com/en/docs/web-hosting/databases/mariadb/ "Alwaysdata — MariaDB / MySQL"
[3]: https://help.alwaysdata.com/en/docs/web-hosting/remote-access/ssh/ "Alwaysdata — SSH"
[4]: https://help.alwaysdata.com/en/docs/web-hosting/sites/add-a-site/ "Alwaysdata — Adding a Site"
[5]: https://help.alwaysdata.com/en/docs/web-hosting/languages/nodejs/ "Alwaysdata — Node.js"
[6]: https://laravel.com/framework/docs/deployment "Laravel 13 — Deployment"
[7]: https://laravel.com/framework/docs/releases "Laravel 13 — Release Notes"
