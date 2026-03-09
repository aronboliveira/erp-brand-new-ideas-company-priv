###############################################################################
# ERP Nova Prestech — PHP-FPM application image
# Build:  docker compose build app
# Run:    docker compose up -d
###############################################################################

# ── Stage 1: frontend assets ────────────────────────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /build
COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts
COPY webpack.mix.js tailwind.config.js ./
COPY resources/ resources/
COPY public/ public/
RUN npm run production || true

# ── Stage 2: PHP application ────────────────────────────────────────────────
FROM php:8.3-fpm

LABEL maintainer="Nova Prestech <suporte@prestech.com.br>"

# System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libonig-dev libxml2-dev zip unzip libzip-dev libicu-dev \
        libcurl4-openssl-dev supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath exif gd intl mbstring pcntl pdo_mysql xml zip opcache \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/*

# OPcache tuning
RUN { \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

# Install PHP dependencies (cached layer)
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --no-interaction --no-scripts --optimize-autoloader

# Application source
COPY . .

# Frontend assets from stage 1
COPY --from=frontend /build/public/css/ public/css/
COPY --from=frontend /build/public/js/  public/js/

# Post-install
RUN composer dump-autoload --optimize \
    && php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache  || true

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p storage/logs storage/framework/{cache,sessions,views}

# Health check
HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD php-fpm-healthcheck || curl -f http://localhost:9000/ || exit 1

EXPOSE 9000
CMD ["php-fpm"]