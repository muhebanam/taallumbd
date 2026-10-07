# Stage 1: Build Frontend Assets with Node
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install --legacy-peer-deps && npm install @rollup/rollup-linux-x64-musl --no-save
COPY . .
# Limit Node memory to avoid OOM on Render free tier (512 MB RAM)
RUN NODE_OPTIONS="--max_old_space_size=384" npm run build

# Stage 2: PHP 8.4 + Nginx Runtime
FROM php:8.4-fpm-alpine

# Install system dependencies & Nginx
RUN apk add --no-cache \
    nginx \
    curl \
    netcat-openbsd \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libzip-dev \
    mysql-client \
    postgresql-dev \
    linux-headers \
    autoconf \
    build-base

# Install PHP extensions required for Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml \
    opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Copy OPcache configuration
COPY docker/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy compiled frontend assets from node_builder stage
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Install PHP production dependencies safely without scripts
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-req=php

# Copy Nginx config (replace root config to avoid Alpine's conf.d include issue)
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create storage directories and set permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/app/public \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
