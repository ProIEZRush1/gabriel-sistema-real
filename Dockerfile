# Client site — Laravel + Vue (Inertia). Assets are pre-built and committed, so the
# image build is just composer (fast); no npm at build time.
FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libsqlite3-dev unzip git \
    && docker-php-ext-install zip bcmath pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN cp .env.production .env \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts \
    && php artisan package:discover --ansi \
    && php artisan key:generate \
    && touch database/database.sqlite \
    && chmod +x docker/start.sh

EXPOSE 8080
CMD ["sh", "docker/start.sh"]
