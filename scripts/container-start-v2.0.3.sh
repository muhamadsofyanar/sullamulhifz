#!/usr/bin/env sh
set -eu

cd /var/www/html

echo "=== Sullamul Hifz v2.0.3 Academy Experience & Video startup ==="

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

    php artisan db:seed --class=Database\\Seeders\\LaunchTemplateV190Seeder --force
    php artisan db:seed --class=Database\\Seeders\\AcademyLaunchV200Seeder --force
    php artisan db:seed --class=Database\\Seeders\\AcademyLaunchV203Seeder --force
    php artisan sullam:verify-academic-core
    php artisan sullam:verify-quran-learning
    php artisan sullam:verify-launch
    php artisan sullam:verify-academy
    php artisan storage:link || true
    php artisan config:cache
    php artisan view:cache
else
    echo "AUTO_MIGRATE=false: migration otomatis dilewati."
fi

if [ "${QURAN_AUDIO_AUTO_SYNC:-true}" = "true" ]; then
    echo "Sinkronisasi Quran Learning dijalankan di latar belakang."
    (
        sleep "${QURAN_AUDIO_SYNC_DELAY:-8}"
        php artisan sullam:ensure-quran-audio || echo "PERINGATAN: audio belum 100%; ulangi dari menu Pustaka Qur’an."
    ) &
fi

echo "Menjalankan NGINX Unit..."
exec /usr/local/bin/docker-entrypoint.sh unitd --no-daemon
