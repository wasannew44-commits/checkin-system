FROM php:8.2-apache

# เปิด mod_rewrite
RUN a2enmod rewrite

# ติดตั้ง mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# ตั้งค่า Apache ให้ใช้ PORT จาก Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf

# คัดลอกไฟล์โปรเจกต์
COPY . /var/www/html/

# สิทธิ์โฟลเดอร์
RUN chown -R www-data:www-data /var/www/html

EXPOSE ${PORT}
