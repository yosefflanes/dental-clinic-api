FROM php:8.5-apache

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2dismod mpm_event; a2dismod mpm_worker; true
RUN a2enmod mpm_prefork
RUN a2enmod rewrite

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN cat <<'EOF' > /usr/local/bin/entrypoint.sh
#!/bin/bash
set -e

if [ -n "$PORT" ]; then
    sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf
fi

# Generate APP_KEY otomatis kalau env APP_KEY kosong/belum di-set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Cache config, route, view untuk performa (aman di-skip jika env belum lengkap saat build)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec apache2-foreground
EOF
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]