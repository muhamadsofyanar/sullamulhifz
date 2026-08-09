# @phase 4.4 Multi-tenant release packaging
# @phase 4.4.1 Blade Communication Template Hotfix
# @phase 4.4.2 Blade Compilation & Release Docs Hotfix
FROM composer:2.8 AS vendor
WORKDIR /app
COPY . .

# composer.lock is strongly recommended. If it is not present in a source
# snapshot, Composer will resolve dependencies during the build. Application
# bootstrap must therefore be safe without runtime-only secrets or a database.
# Coolify/BuildKit can occasionally resolve Packagist over an unreachable IPv6 path.
# Force Composer downloads to IPv4 and retry transient outbound failures before
# failing the image build. This keeps production deploys resilient without
# changing the host network configuration.
RUN set -eu; \
    export COMPOSER_ALLOW_SUPERUSER=1; \
    export COMPOSER_IPRESOLVE=4; \
    export COMPOSER_MAX_PARALLEL_HTTP=6; \
    attempt=1; \
    while [ "$attempt" -le 4 ]; do \
        echo "Composer install attempt ${attempt}/4 (IPv4)..."; \
        if composer install \
            --no-dev \
            --no-interaction \
            --no-progress \
            --prefer-dist \
            --optimize-autoloader; then \
            break; \
        fi; \
        if [ "$attempt" -eq 4 ]; then \
            echo "Composer install failed after 4 attempts." >&2; \
            exit 1; \
        fi; \
        sleep $((attempt * 5)); \
        attempt=$((attempt + 1)); \
    done

# Fail fast at image-build time if Laravel cannot bootstrap or routes cannot be
# registered. This catches bootstrap regressions before Coolify swaps containers.
RUN php artisan package:discover --ansi \
    && php artisan route:list --no-ansi > /tmp/sullam-routes.txt \
    && php artisan view:cache \
    && for file in storage/framework/views/*.php; do php -l "$file" || exit 1; done \
    && php artisan view:clear

FROM unit:1.34.2-php8.4

LABEL org.opencontainers.image.title="Sullamul Hifz" \
      org.opencontainers.image.version="4.4.2" \
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
CMD ["sh", "scripts/container-start.sh"]
