FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy full app so Composer can run Symfony scripts (generates autoload_runtime.php)
COPY . .

# Must NOT use --no-scripts: Symfony Runtime plugin creates vendor/autoload_runtime.php
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p var/cache var/log \
    && chmod -R 777 var

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENV APP_ENV=prod
ENV APP_DEBUG=0

ENTRYPOINT ["docker-entrypoint.sh"]
