FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends $PHPIZE_DEPS libzip-dev \
 && docker-php-ext-install pdo_mysql \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apt-get purge -y --auto-remove $PHPIZE_DEPS \
 && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY php.ini /usr/local/etc/php/conf.d/zz-lab.ini
