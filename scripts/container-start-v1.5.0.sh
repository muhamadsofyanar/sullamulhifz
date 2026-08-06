#!/usr/bin/env sh
set -eu

cd /var/www/html

echo "=== Sullamul Hifz v1.5.0 container startup ==="

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    attempt=1
    max_attempts="${DB_WAIT_ATTEMPTS:-30}"

    php artisan optimize:clear

    until php artisan migrate --force; do
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "ERROR: migration belum berhasil setelah ${max_attempts} percobaan."
            exit 1
        fi
        echo "Database belum siap atau migration belum berhasil... (${attempt}/${max_attempts})"
        attempt=$((attempt + 1))
        sleep 2
    done

    php artisan sullam:verify-academic-core
    php artisan storage:link || true
    php artisan config:cache
    php artisan view:cache
else
    echo "AUTO_MIGRATE=false: migration otomatis dilewati."
fi

echo "Menjalankan NGINX Unit..."
exec /usr/local/bin/docker-entrypoint.sh unitd --no-daemon
