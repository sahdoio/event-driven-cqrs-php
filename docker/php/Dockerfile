FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    $PHPIZE_DEPS \
    openssl-dev \
    libzip-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql sockets \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY ../../api /var/www

# Install dependencies
RUN if [ -f composer.json ]; then composer install --no-interaction --optimize-autoloader; fi

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
