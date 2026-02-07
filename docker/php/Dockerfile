FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git unzip libpng-dev libxml2-dev libzip-dev icu-dev oniguruma-dev \
    libpq-dev libmemcached-dev zlib-dev linux-headers \
    nodejs npm $PHPIZE_DEPS

RUN docker-php-ext-install pdo_mysql pdo_pgsql intl zip bcmath gd mbstring

# Установка Redis, Memcached, Xdebug и Swoole
RUN pecl install redis memcached xdebug \
    && docker-php-ext-enable redis memcached xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем проект
COPY . .

# Права доступа для Yii2 Advanced
RUN chmod -R 777 frontend/runtime frontend/web/assets \
    && chmod -R 777 backend/runtime backend/web/assets

CMD ["php-fpm"]