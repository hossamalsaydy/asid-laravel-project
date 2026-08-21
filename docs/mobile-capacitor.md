# تطبيق ASID المحمول عبر Capacitor

يستخدم هذا المشروع Capacitor لتغليف واجهة Laravel داخل تطبيق Android. لأن واجهة ASID مبنية بقوالب Blade وتُعرض من الخادم، فإن التطبيق المحمول يستخدم **shell محلياً صغيراً** ثم يوجّه WebView إلى خادم Laravel المنشور. لا يتم نسخ صفحات Blade إلى حزمة الهاتف؛ لذلك تبقى التحديثات اليومية على الخادم متاحة للتطبيق مباشرة دون إصدار APK جديد.

## الإعداد المحلي

اضبط عنوان الخادم قبل المزامنة:

```bash
export MOBILE_BACKEND_URL="https://clinic.example.com"
npm install
npm run build:mobile
```

ينفذ الأمر بناء أصول Laravel، ثم يولّد `mobile/www/runtime-config.js`، ثم ينسخ الأصول إلى مشروع Android عبر `cap sync`.

إذا لم يكن مجلد Android موجوداً بعد:

```bash
npx cap add android
```

ثم يمكن فتح المشروع في Android Studio أو تشغيله على جهاز/محاكي:

```bash
npm run cap:open:android
npm run cap:run:android
```

## البناء التلقائي ورفع التحديثات

يحتوي المشروع على workflow في `.github/workflows/android-apk.yml`. يتم تشغيله تلقائياً عند رفع commit إلى `master`، كما يمكن تشغيله يدوياً من GitHub Actions. يقوم workflow بتثبيت Node.js وJDK وAndroid SDK، وبناء أصول الويب، ومزامنة Capacitor، ثم إنشاء `app-debug.apk` ورفعه كـ Artifact باسم `asid-clinic-debug-apk`.

يجب إنشاء Repository Variable باسم `MOBILE_BACKEND_URL` في إعدادات GitHub للمستودع، وقيمته عنوان HTTPS العام لتطبيق Laravel. لا تضع عنواناً خاصاً أو بيانات اعتماد داخل Git أو APK.

## التحديثات

تعديلات Laravel وBlade وCSS وJavaScript التي تُرفع إلى الخادم تظهر في التطبيق بعد إعادة تحميله، لأن التطبيق يتعامل مباشرة مع خادم Laravel. أما تعديل `capacitor.config.ts` أو `mobile/www/index.html` أو أي إضافة Native فيتطلب إعادة تنفيذ البناء وإعادة بناء APK.

## متطلبات Android

يتطلب البناء الأصلي Android Studio أو Android SDK وJDK 21 متوافقاً مع إصدار Capacitor المستخدم. تم تجهيز المشروع بإعدادات `compileSdkVersion` و`targetSdkVersion` على API 36. يمكن بناء APK محلياً عبر `android/gradlew assembleDebug` بعد ضبط Android SDK، أو الاعتماد على GitHub Actions.

## iOS

تم تجهيز التكامل الحالي لمنصة Android لأن بيئة التنفيذ الحالية Linux. لإضافة iOS على جهاز macOS، ثبّت `@capacitor/ios` ثم نفّذ `npx cap add ios` و`npx cap sync ios`، وبعدها افتح المشروع في Xcode. بناء وتوقيع iOS يتطلب macOS وXcode وحساب Apple مناسباً.

## ملاحظات أمنية

يجب استخدام HTTPS في الإنتاج وعدم تفعيل `allowMixedContent` أو `cleartext`. كما ينبغي إبقاء `MOBILE_BACKEND_URL` خارج Git وعدم وضع أسرار داخل ملفات التطبيق، لأن كل ما داخل APK قابل للاستخراج. يجب أيضاً اختبار تسجيل الدخول والجلسات ورفع الملفات داخل WebView على Android قبل النشر.
