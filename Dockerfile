FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libmariadb-dev \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip pdo pdo_mysql

RUN a2enmod rewrite

RUN mkdir -p /var/www/html/public \
    && chown -R www-data:www-data /var/www

COPY --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

CMD ["apache2-foreground"]
