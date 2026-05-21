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

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV APP_SECRET=build_time_secret_replace_in_railway_vars
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# Install dependencies first (better layer cache)
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .

# Symfony requires a readable .env at boot; Railway/OS env vars override these defaults
COPY .env.docker .env

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && test -f vendor/autoload_runtime.php \
    && mkdir -p var/cache var/log public/bundles config/jwt \
    && chmod -R 777 var

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
