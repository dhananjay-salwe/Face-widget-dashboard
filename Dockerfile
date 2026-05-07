# =============================================================================
# Stage 1 — Composer: install PHP dependencies
# =============================================================================
FROM composer:2.7 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# =============================================================================
# Stage 2 — Node: build frontend assets with Vite
# =============================================================================
FROM node:20-alpine AS node

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --silent

COPY . .
COPY --from=composer /app/vendor ./vendor

RUN npm run build

# =============================================================================
# Stage 3 — Production image (PHP-FPM + Nginx + Supervisor)
# =============================================================================
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="face-widget-dashboard"
LABEL description="Production image for Face Widget Dashboard (Laravel)"

# ── System packages ───────────────────────────────────────────────────────────
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        intl \
        opcache \
        pcntl \
        bcmath

# ── PHP configuration ─────────────────────────────────────────────────────────
COPY docker/php/php.ini      /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini  /usr/local/etc/php/conf.d/opcache.ini

# ── Nginx configuration ───────────────────────────────────────────────────────
COPY docker/nginx/nginx.conf    /etc/nginx/nginx.conf
COPY docker/nginx/default.conf  /etc/nginx/http.d/default.conf

# ── Supervisor configuration ──────────────────────────────────────────────────
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# ── Application source ────────────────────────────────────────────────────────
WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=composer --chown=www-data:www-data /app/vendor           ./vendor
COPY --from=node     --chown=www-data:www-data /app/public/build     ./public/build

# ── Directory permissions ─────────────────────────────────────────────────────
RUN mkdir -p \
        storage/logs \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Entrypoint ────────────────────────────────────────────────────────────────
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
