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

COPY . .

# Prod APP_ENV avoids dev bundles during cache:clear in post-install-cmd.
# Symfony Runtime plugin generates vendor/autoload_runtime.php on install.
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p var/cache var/log \
    && chmod -R 777 var

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
