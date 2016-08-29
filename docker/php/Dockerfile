FROM php:fpm

MAINTAINER Nathanaël CHERRIER <dev@nathanaelcherrier.com>

RUN apt-get update && apt-get install -y \
    git \
    unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN apt-get autoclean && apt-get autoremove

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable opcache
