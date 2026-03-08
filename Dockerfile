FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libmariadb-dev \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --chown=www-data:www-data . /app

WORKDIR /app

# Create cache directories
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/views storage/logs \
    && chmod -R 775 bootstrap/cache storage

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

RUN php artisan config:cache

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
