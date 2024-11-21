FROM php:8.3-fpm-alpine3.19

WORKDIR /app

EXPOSE 9000

COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf