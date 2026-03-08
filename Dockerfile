FROM php:8.2-cli

WORKDIR /app

# Install required extensions
RUN apt-get update && apt-get install -y unzip libzip-dev libonig-dev libcurl4-openssl-dev \
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

EXPOSE 8080

# Startup script
RUN echo '#!/bin/bash' > /startup.sh && \
    echo 'echo "Starting ServicoSimples..."' >> /startup.sh && \
    echo 'php artisan migrate --force --no-interaction 2>/dev/null || true' >> /startup.sh && \
    echo 'php artisan config:cache' >> /startup.sh && \
    echo 'php artisan route:cache' >> /startup.sh && \
    echo 'exec php -S 0.0.0.0:8080 -t public public/index.php' >> /startup.sh && \
    chmod +x /startup.sh

CMD ["/startup.sh"]
