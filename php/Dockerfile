FROM php:7.3-fpm

RUN apt-get update && apt-get install -y \
    && docker-php-ext-configure mysqli \
    && docker-php-ext-install mysqli

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer