FROM php:7.4

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

