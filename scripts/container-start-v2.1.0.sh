#!/usr/bin/env sh
set -eu

cd /var/www/html

echo "=== Sullamul Hifz v2.1.0 Unified Platform startup ==="

# Persistent volume dapat menutupi direktori bawaan image saat container baru dimulai.
mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R unit:unit storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

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

    if [ "${BOOTSTRAP_PRODUCTION:-false}" = "true" ]; then
        echo "BOOTSTRAP_PRODUCTION=true: menyiapkan lembaga dan akun admin awal."
        php artisan db:seed --class=Database\\Seeders\\ProductionSeeder --force
    fi

    # Semua seeder di bawah bersifat idempoten dan tidak menghapus data operasional.
    php artisan db:seed --class=Database\\Seeders\\LaunchTemplateV190Seeder --force
    php artisan db:seed --class=Database\\Seeders\\AcademyLaunchV200Seeder --force
    php artisan db:seed --class=Database\\Seeders\\AcademyLaunchV203Seeder --force
    php artisan db:seed --class=Database\\Seeders\\PlatformFoundationV210Seeder --force
    php artisan sullam:secure-legacy-media || echo "PERINGATAN: sebagian media lama belum berhasil diamankan; periksa log dan jalankan ulang perintah."

    php artisan sullam:verify-academic-core || echo "PERINGATAN: verifikasi fondasi akademik belum sepenuhnya lulus."
    php artisan sullam:verify-quran-learning || echo "PERINGATAN: verifikasi pembelajaran Qur’an belum sepenuhnya lulus."
    php artisan sullam:verify-launch || echo "PERINGATAN: checklist peluncuran belum sepenuhnya lulus."
    php artisan sullam:verify-academy || echo "PERINGATAN: verifikasi Academy belum sepenuhnya lulus."
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
