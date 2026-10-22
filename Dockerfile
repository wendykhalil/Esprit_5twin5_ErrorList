# syntax=docker/dockerfile:1
# SolarShare — PHP 8.4 runtime. Frontend assets are compiled in a separate stage.
# No .env file and no secret are copied into the image.

FROM node:20-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

COPY . .

ARG VITE_REVERB_APP_KEY=
ARG VITE_REVERB_HOST=localhost
ARG VITE_REVERB_PORT=8081
ARG VITE_REVERB_SCHEME=http

ENV VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}

RUN npm run build \
    && test -f public/build/manifest.json

FROM php:8.4-cli-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libicu-dev \
        libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        sockets \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN { \
        echo "upload_max_filesize=8M"; \
        echo "post_max_size=8M"; \
        echo "memory_limit=512M"; \
        echo "opcache.enable=1"; \
        echo "opcache.enable_cli=1"; \
        echo "opcache.validate_timestamps=0"; \
    } > /usr/local/etc/php/conf.d/solarshare.ini

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --prefer-dist \
        --no-autoloader

COPY . .
COPY --from=assets /app/public/build ./public/build

# Discover packages at build time. This placeholder is not stored as an image ENV.
RUN composer dump-autoload --optimize --no-dev --no-interaction --no-scripts \
    && APP_KEY=base64:YWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWE= \
        php artisan package:discover --ansi \
    && mkdir -p \
        storage/app/public \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage \
    && chown -R www-data:www-data storage bootstrap/cache public/storage \
    && chmod -R ug+rwx storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod 755 /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
