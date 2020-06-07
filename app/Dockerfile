FROM php:7.3-apache

RUN docker-php-ext-install mysqli

RUN apt-get update -y && apt-get install -y libpng-dev

RUN docker-php-ext-install gd


COPY ./src /var/www/html/

