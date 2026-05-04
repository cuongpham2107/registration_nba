# Giai đoạn 1: Build PHP dependencies
FROM php:8.4-fpm as vendor

WORKDIR /var/www/html

# Cài đặt system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev

# Cài đặt PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql intl zip pcntl

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Cài đặt dependencies
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

# Giai đoạn 2: Build frontend assets
FROM oven/bun:1.1 as frontend

WORKDIR /var/www/html

# Copy package files
COPY package.json bun.lock ./
# Nếu không có bun.lock thì dùng package.json
# COPY package.json ./

# Cài đặt dependencies bằng bun
RUN bun install

# Copy vendor từ stage trước để Vite có thể resolve Filament assets
COPY --from=vendor /var/www/html/vendor ./vendor

# Copy source code và build
COPY . .
RUN bun run build

# Giai đoạn 3: Final runtime image
FROM php:8.4-fpm

WORKDIR /var/www/html

# Cài đặt system dependencies cho runtime
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    nginx \
    supervisor \
    && apt-get clean && rm -rf /var/www/html/*

# Cài đặt PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql intl zip pcntl

# Copy source code và vendor từ các giai đoạn trước
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/build ./public/build
COPY . .

# Thiết lập quyền truy cập cho storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy cấu hình Nginx và PHP
COPY .docker/nginx/conf.d/app.conf /etc/nginx/sites-available/default
COPY .docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY .docker/supervisor/reverb.conf /etc/supervisor/conf.d/reverb.conf

# Copy entrypoint script
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
