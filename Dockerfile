FROM php:8.5-apache

RUN docker-php-ext-install pdo_mysql

ARG SHARED_GID=0

RUN if [ "$SHARED_GID" != "0" ]; then \
        groupadd -g "$SHARED_GID" bendras && \
        usermod -aG bendras www-data; \
    fi