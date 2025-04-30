# เลือกรูปภาพ PHP ที่ใช้
FROM php:8.0-apache

# ตั้งค่า directory สำหรับงาน
WORKDIR /var/www/html

# คัดลอกไฟล์ทั้งหมดจาก local ไปยัง Docker image
COPY . .

# ติดตั้ง dependencies (ถ้ามี)
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev

# ติดตั้ง extensions ที่จำเป็น
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# เปิดพอร์ตที่ใช้ในการทำงานของ Apache
EXPOSE 80
