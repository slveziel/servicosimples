FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libmariadb-dev \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip pdo pdo_mysql

RUN a2enmod rewrite

# Copy only public folder contents to document root
COPY --chown=www-data:www-data public/ /var/www/html/

# Set permissions
RUN chmod -R 755 /var/www/html

EXPOSE 8080

CMD ["apache2-foreground"]
