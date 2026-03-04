#!/bin/sh
set -e

# Đảm bảo quyền truy cập cho storage và bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Chạy migrations nếu cần (bỏ comment nếu muốn tự động chạy)
# php artisan migrate --force

# Clear and Recache
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache

# Khởi động Supervisor (để quản lý Reverb)
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# Khởi động PHP-FPM
exec php-fpm
