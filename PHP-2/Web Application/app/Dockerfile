FROM php:8.2-apache

RUN apt-get update \ 
    && apt-get install -y libpq-dev msmtp msmtp-mta \
    && docker-php-ext-install pdo pgsql pdo_pgsql

COPY ./msmtprc /etc/msmtprc