FROM php:8.3-fpm-alpine

# Install system dependencies & libraries required for PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    zip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    mysql-client \
    postgresql-client \
    libxml2-dev \
    oniguruma-dev \
    linux-headers \
    autoconf \
    build-base

# Configure & install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        xml \
        intl

# Install Redis extension via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Expose port for artisan serve
EXPOSE 8000

# Default development command
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
