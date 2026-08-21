# تطبيق ASID المحمول عبر Capacitor

يستخدم هذا المشروع Capacitor لتغليف واجهة Laravel داخل تطبيق Android. لأن واجهة ASID مبنية بقوالب Blade وتُعرض من الخادم، فإن التطبيق المحمول يستخدم **shell محلياً صغيراً** ثم يوجّه WebView إلى خادم Laravel المنشور. لا يتم نسخ صفحات Blade إلى حزمة الهاتف؛ لذلك تبقى التحديثات اليومية على الخادم متاحة للتطبيق مباشرة دون إصدار APK جديد.

## الإعداد

اضبط عنوان الخادم قبل المزامنة:

```bash
export MOBILE_BACKEND_URL="https://clinic.example.com"
npm run build:mobile
```

ينفذ الأمر بناء أصول Laravel، ثم يولّد `mobile/www/runtime-config.js`، ثم ينسخ الأصول إلى المشروع الأصلي عبر `cap sync`.

## إضافة منصة Android

إذا لم يكن مجلد Android موجوداً بعد:

```bash
npx cap add android
```

ثم يمكن فتح المشروع في Android Studio:

```bash
npm run cap:open:android
```

أو تشغيله على جهاز/محاكي متصل:

```bash
npm run cap:run:android
```

يتطلب البناء الأصلي Android Studio وAndroid SDK وJDK متوافقاً مع إصدار Capacitor المستخدم. بيئة Linux الحالية تحتوي على Node.js فقط، لذلك لا يمكن التحقق من APK هنا ما لم تُثبّت أدوات Android الأصلية.

## التحديثات

تعديلات Laravel وBlade وCSS وJavaScript التي تُرفع إلى الخادم تظهر في التطبيق بعد إعادة تحميله، لأن التطبيق يتعامل مباشرة مع خادم Laravel. أما تعديل `capacitor.config.ts` أو `mobile/www/index.html` أو أي إضافة Native فيتطلب إعادة تنفيذ `npm run build:mobile` وإعادة بناء APK.

## ملاحظات أمنية

يجب استخدام HTTPS في الإنتاج وعدم تفعيل `allowMixedContent` أو `cleartext`. كما ينبغي إبقاء `MOBILE_BACKEND_URL` خارج Git وعدم وضع أسرار داخل ملفات التطبيق، لأن كل ما داخل APK قابل للاستخراج. يجب أيضاً اختبار تسجيل الدخول والجلسات ورفع الملفات داخل WebView على Android قبل النشر.
