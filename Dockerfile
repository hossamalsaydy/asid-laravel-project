FROM richarvey/nginx-php-fpm:php83

# نسخ كود المشروع بالكامل إلى الحاوية
COPY . /var/www/html

# ضبط إعدادات خادم الويب
ENV WEBROOT /var/www/html/public
ENV APP_ENV production

# تثبيت مكتبات وحزم الاعتماد الخاصة بلارافيل
RUN composer install --no-dev --optimize-autoloader

# إنشاء ملف قاعدة بيانات SQLite فارغ في حال اختيارها وضبط صلاحياته
RUN touch /var/www/html/database/database.sqlite && chmod -R 777 /var/www/html/database

# منح الصلاحيات لمجلدات التخزين والكاش
RUN chown -R nw:nw /var/www/html/storage /var/www/html/bootstrap/cache

# أمر تشغيل السيرفر مع عمل الهجرات تلقائياً عند الإقلاع
CMD sh -c "php artisan migrate --force && ./entrypoint.sh"
