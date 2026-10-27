FROM php:8.5-apache

RUN docker-php-ext-install pdo_mysql
