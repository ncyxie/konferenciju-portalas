FROM php:8.5-apache

RUN docker-php-ext-install pdo_mysql

ARG SHARED_GID

RUN groupadd -g "$SHARED_GID" bendras \
    && usermod -aG bendras www-data
