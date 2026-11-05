FROM php:8.4-apache

# gd for avatar uploads, pdo_mysql for the database
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Development settings: errors go to the container log (the app also hides them from visitors).
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
