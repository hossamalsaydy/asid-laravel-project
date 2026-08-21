

import Alpine from 'alpinejs';
import { Capacitor } from '@capacitor/core';

window.Alpine = Alpine;
window.Capacitor = Capacitor;

// يتيح للقوالب أو الإضافات التمييز بين تشغيل الواجهة داخل Capacitor أو المتصفح.
document.documentElement.dataset.nativeApp = Capacitor.isNativePlatform() ? 'true' : 'false';

Alpine.start();
