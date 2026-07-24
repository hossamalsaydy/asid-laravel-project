FROM serversideup/php:8.3-fpm-nginx

# نسخ كود المشروع بالكامل إلى الحاوية
COPY --chown=www-data:www-data . /var/www/html

# ضبط إعدادات خادم البيئة للإنتاج
ENV AUTORUN_ENABLED=true
ENV APP_ENV=production

# تثبيت مكتبات وحزم الاعتماد الخاصة بلارافيل
RUN composer install --no-dev --optimize-autoloader

# إنشاء ملف قاعدة بيانات SQLite فارغ وضبط صلاحياته للمستخدم الجديد
RUN touch /var/www/html/database/database.sqlite && chmod -R 777 /var/www/html/database

# تشغيل أوامر التهجير عند الإقلاع
CMD ["/init"]


