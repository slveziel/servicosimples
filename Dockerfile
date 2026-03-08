FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libmariadb-dev \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip pdo pdo_mysql

RUN a2enmod rewrite

# Listen on port 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# Configure Apache for Laravel
RUN echo "Listen 8080" > /etc/apache2/sites-available/000-default.conf \
    && echo "<VirtualHost *:8080>" >> /etc/apache2/sites-available/000-default.conf \
    && echo "    DocumentRoot /var/www/html/public" >> /etc/apache2/sites-available/000-default.conf \
    && echo "    <Directory /var/www/html/public>" >> /etc/apache2/sites-available/000-default.conf \
    && echo "        Options Indexes FollowSymLinks" >> /etc/apache2/sites-available/000-default.conf \
    && echo "        AllowOverride All" >> /etc/apache2/sites-available/000-default.conf \
    && echo "        Require all granted" >> /etc/apache2/sites-available/000-default.conf \
    && echo "    </Directory>" >> /etc/apache2/sites-available/000-default.conf \
    && echo "</VirtualHost>" >> /etc/apache2/sites-available/000-default.conf

RUN mkdir -p /var/www/html/public \
    && chown -R www-data:www-data /var/www

COPY --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

CMD ["apache2-foreground"]
