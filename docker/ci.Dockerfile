# syntax=docker/dockerfile:1
# CI image. PHPUnit uses sqlite :memory: and does not call Ollama, MySQL, Reverb, or the queue.
# Tests are not executed during the image build, so a cached image cannot hide a new failure.
# The placeholder APP_KEY only lets Laravel boot. It is not a production key.

FROM node:20-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

COPY vite.config.js tailwind.config.js ./
COPY resources ./resources
RUN npm run build \
    && test -f public/build/manifest.json

FROM php:8.4-cli-bookworm AS ci

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libsqlite3-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" bcmath gd pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-interaction --prefer-dist --no-scripts --no-progress

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-interaction --no-scripts \
    && APP_KEY=base64:YWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWE= \
        php artisan package:discover --ansi \
    && mkdir -p \
        /reports \
        storage/app/public \
        storage/framework/views \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/logs \
        bootstrap/cache \
    && touch .env

COPY docker/ci-test.sh /usr/local/bin/ci-test.sh
RUN chmod 755 /usr/local/bin/ci-test.sh

ENV APP_KEY=base64:YWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWE=

CMD ["ci-test.sh"]
