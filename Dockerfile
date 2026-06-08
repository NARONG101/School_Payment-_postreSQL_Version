# ============================================================
# Stage 1: Node — build frontend assets
# ============================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ============================================================
# Stage 2: Composer — install PHP dependencies
# ============================================================
FROM composer:2.8 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
COPY artisan ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

# ============================================================
# Stage 3: Production image
# ============================================================
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="Student Payment System"

# ── System dependencies ──────────────────────────────────────
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    sqlite-dev \
    libxml2-dev \
    postgresql-dev \
    zip \
    unzip \
    shadow

# ── PHP extensions ───────────────────────────────────────────
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        intl \
        mbstring \
        xml \
        opcache \
        pcntl \
        bcmath

# ── PHP production config ────────────────────────────────────
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/php.ini        "$PHP_INI_DIR/conf.d/99-app.ini"
COPY docker/php/opcache.ini    "$PHP_INI_DIR/conf.d/10-opcache.ini"
COPY docker/php/www.conf       /usr/local/etc/php-fpm.d/www.conf

# ── Nginx config ─────────────────────────────────────────────
COPY docker/nginx/nginx.conf        /etc/nginx/nginx.conf
COPY docker/nginx/default.conf      /etc/nginx/http.d/default.conf

# ── Supervisor config ────────────────────────────────────────
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ── App user ─────────────────────────────────────────────────
RUN addgroup -g 1001 -S appgroup \
    && adduser  -u 1001 -S appuser -G appgroup

WORKDIR /var/www/html

# ── Copy application ─────────────────────────────────────────
COPY --chown=appuser:appgroup . .
COPY --chown=appuser:appgroup --from=composer /app/vendor ./vendor
COPY --chown=appuser:appgroup --from=frontend /app/public/build ./public/build

# ── Storage & cache directories ──────────────────────────────
RUN mkdir -p \
        storage/app/public/students/photos \
        storage/app/public/payments/photos \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R appuser:appgroup storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Entrypoint ───────────────────────────────────────────────
COPY --chmod=755 docker/entrypoint.sh /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
