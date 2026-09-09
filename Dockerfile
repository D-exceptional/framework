# ============================================================
# PHP APPLICATION IMAGE
# ============================================================

FROM php:8.3-fpm

# ------------------------------------------------------------
# Environment
# ------------------------------------------------------------

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer

# ------------------------------------------------------------
# System dependencies
# ------------------------------------------------------------

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# ------------------------------------------------------------
# PHP extensions
# ------------------------------------------------------------

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        intl \
        zip \
        exif \
        pcntl \
        bcmath \
        gd

# ------------------------------------------------------------
# Redis PHP extension
# ------------------------------------------------------------

RUN pecl install redis \
    && docker-php-ext-enable redis

# ------------------------------------------------------------
# Composer
# ------------------------------------------------------------

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ------------------------------------------------------------
# Working directory
# ------------------------------------------------------------

WORKDIR /var/www

# ------------------------------------------------------------
# Copy Composer files first
#
# This allows Docker to cache dependency installation
# when application source code changes.
# ------------------------------------------------------------

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ------------------------------------------------------------
# Copy application
# ------------------------------------------------------------

COPY . .

# ------------------------------------------------------------
# Permissions
# ------------------------------------------------------------

RUN chown -R www-data:www-data /var/www

# ------------------------------------------------------------
# PHP-FPM
# ------------------------------------------------------------

EXPOSE 9000

CMD ["php-fpm"]