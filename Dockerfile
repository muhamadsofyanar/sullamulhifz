FROM composer:2.8 AS vendor
WORKDIR /app
COPY . .

# composer.lock is strongly recommended. Until it is committed, Composer will
# resolve dependencies during the build. Application bootstrap must therefore
# be safe to execute without runtime-only secrets or a database connection.
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

# Fail fast at image-build time if Laravel cannot bootstrap or routes cannot be
# registered. This catches bootstrap regressions before Coolify swaps containers.
RUN php artisan package:discover --ansi \
    && php artisan route:list --no-ansi > /tmp/sullam-routes.txt

FROM unit:1.34.2-php8.4

LABEL org.opencontainers.image.title="Sullamul Hifz" \
      org.opencontainers.image.version="2.1.1" \
      org.opencontainers.image.description="Platform pembinaan Al-Quran Sullamul Hifz"

RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring intl zip gd exif bcmath opcache \
    && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    'memory_limit=512M' \
    'upload_max_filesize=32M' \
    'post_max_size=34M' \
    'max_execution_time=120' \
    'opcache.enable=1' \
    'opcache.validate_timestamps=0' \
    > /usr/local/etc/php/conf.d/sullamul-hifz.ini

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY unit.json /docker-entrypoint.d/unit.json

RUN mkdir -p \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R unit:unit storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x scripts/*.sh

EXPOSE 8000
CMD ["sh", "scripts/container-start-v2.1.0.sh"]
