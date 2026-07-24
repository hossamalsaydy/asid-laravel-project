FROM serversideup/php:8.3-fpm-nginx

# نسخ كود المشروع بالكامل إلى الحاوية وضبط الملكية للمستخدم الافتراضي
COPY --chown=www-data:www-data . /var/www/html

# ضبط إعدادات خادم البيئة للإنتاج والتشغيل التلقائي
ENV AUTORUN_ENABLED=true
ENV APP_ENV=production
ENV APP_DEBUG=false

# تثبيت مكتبات وحزم الاعتماد الخاصة بلارافيل
RUN composer install --no-dev --optimize-autoloader

# إنشاء ملف قاعدة بيانات SQLite وضبط الصلاحيات الكاملة لمجلدات الكاش والتخزين
RUN touch /var/www/html/database/database.sqlite && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# الأمر الرسمي والافتراضي لتشغيل خادم الويب Nginx و PHP معاً
# تشغيل أمر التهجير وحقن البيانات التجريبية معاً عند الإقلاع
CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && /init"]


