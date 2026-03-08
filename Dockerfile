FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-zip \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-xml \
    nginx \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP
RUN sed -i 's/;listen.owner = www-data/listen.owner = www-data/' /etc/php/8.1/fpm/pool.d/www.conf \
    && sed -i 's/;listen.group = www-data/listen.group = www-data/' /etc/php/8.1/fpm/pool.d/www.conf

# Configure Nginx
RUN echo "server { listen 8080; root /app/public; index index.php index.html; location / { try_files \$uri \$uri/ /index.php?\$query_string; } location ~ \.php$ { fastcgi_pass unix:/run/php/php8.1-fpm.sock; fastcgi_index index.php; fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name; include fastcgi_params; } }" > /etc/nginx/sites-available/default

WORKDIR /app

COPY --chown=www-data:www-data . .

RUN mkdir -p /app/bootstrap/cache /app/storage/framework/cache /app/storage/framework/views /app/storage/logs \
    && chmod -R 775 /app/bootstrap /app/storage \
    && chmod -R 755 /app/public

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080

CMD service php8.1-fpm start && nginx -g 'daemon off;'
