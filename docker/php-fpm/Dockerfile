FROM php:8.3-fpm

# Установка необходимых расширений PHP и инструментов
RUN apt-get update && apt-get install -y \
    rsyslog \
    git \
    unzip \
    zip \
    wget \
    default-mysql-client \
    curl \
    nano \
    nodejs \
    libzip-dev \
    npm \
    libpq-dev \
    && docker-php-ext-install exif bcmath mysqli pdo pdo_pgsql pgsql pdo_mysql


# intl
RUN apt-get install -y libicu-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl

# Установка gd
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev && \
docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
docker-php-ext-install gd

# Установка Xdebug extension
RUN pecl install xdebug \
    # Enable xdebug
    && docker-php-ext-enable xdebug

# Установка memcached
RUN apt-get install -y libmemcached-dev zlib1g-dev && \
pecl install memcached \

RUN docker-php-ext-enable memcached

# Установка phalcon
RUN pecl install phalcon-5.6.0 && docker-php-ext-enable phalcon

#zip
RUN pecl install zip && docker-php-ext-enable zip


# Install swool
RUN pecl install swoole && docker-php-ext-enable swoole
RUN apt-get install -y libpcre3-dev software-properties-common


# pcov
RUN pecl install pcov && docker-php-ext-enable pcov

# redis
RUN pecl install redis && docker-php-ext-enable redis

#RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
#USER "1000:1000"

# Установите Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установите зависимости
#COPY . /var/www/app
WORKDIR /var/www/app

#RUN composer install

# Настройте права доступа
#RUN chown -R www-data:www-data /var/www/app && chmod -R 755 /var/www/app

EXPOSE 9000
CMD ["php-fpm"]