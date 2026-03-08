FROM webdevops/php-nginx:8.2

WORKDIR /app

# Install required extensions
RUN apt-get update && apt-get install -y unzip libzip-dev libonig-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Ensure cache directories exist
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/views storage/logs

# Copy application files
COPY --chown=www-data:www-data . .

# Install composer and dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction

# Fix permissions
RUN chmod -R 755 /app && chmod -R 775 /app/bootstrap /app/storage

# Configure Nginx
ENV WEB_DOCUMENT_ROOT=/app/public
ENV PHP_DISPLAY_ERRORS=off
ENV PHP_LOG_ERRORS=off

EXPOSE 8080

# Startup
CMD ["php-fpm"]
