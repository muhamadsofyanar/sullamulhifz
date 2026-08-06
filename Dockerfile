FROM composer:2.8 AS vendor
WORKDIR /app
COPY . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM unit:1.34.2-php8.4

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
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000
CMD ["unitd", "--no-daemon"]
