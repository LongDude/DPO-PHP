FROM php:8.4-apache

RUN apt-get update && apt-get install -y libpq-dev git unzip \
    && docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY composer.json composer.lock ./
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


RUN composer install --no-dev --no-autoloader --no-scripts --no-interaction

RUN mkdir -p /var/cache /var/log \
&& chown -R www-data:www-data /var \
&& chmod -R 777 /var \
&& mkdir -p /var/sessions \
&& chmod -R 777 /var/sessions


COPY . .
EXPOSE 80
CMD ["apache2-foreground"]
