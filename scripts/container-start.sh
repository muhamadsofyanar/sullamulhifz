#!/usr/bin/env sh
set -eu

cd /var/www/html

release="$(tr -d '\r\n' < RELEASE 2>/dev/null || true)"
echo "=== Sullamul Hifz ${release:-unknown} container startup ==="

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

    # Seeder berikut idempoten dan tidak menghapus data operasional.
    php artisan db:seed --class=Database\\Seeders\\LaunchTemplateV190Seeder --force
    php artisan db:seed --class=Database\\Seeders\\AcademyLaunchV200Seeder --force
    php artisan db:seed --class=Database\\Seeders\\AcademyLaunchV203Seeder --force
    php artisan db:seed --class=Database\\Seeders\\PlatformFoundationV210Seeder --force
    php artisan db:seed --class=Database\\Seeders\\AcademyExpansionV220Seeder --force
    php artisan db:seed --class=Database\\Seeders\\IntegratedLearningEcosystemV230Seeder --force
    php artisan db:seed --class=Database\\Seeders\\FullQuranEngineV240Seeder --force
    php artisan db:seed --class=Database\\Seeders\\TahfizhLearningEngineV250Seeder --force
    php artisan db:seed --class=Database\\Seeders\\QuranJourneyV260Seeder --force
    php artisan sullam:secure-legacy-media || echo "PERINGATAN: sebagian media lama belum berhasil diamankan; periksa log dan jalankan ulang perintah."

    php artisan sullam:verify-academic-core || echo "PERINGATAN: verifikasi fondasi akademik belum sepenuhnya lulus."
    php artisan sullam:verify-quran-learning || echo "PERINGATAN: verifikasi pembelajaran Qur’an belum sepenuhnya lulus."
    php artisan sullam:verify-tahfizh || echo "PERINGATAN: Tahfizh Learning Engine belum sepenuhnya siap untuk validasi."
    php artisan sullam:verify-quran-journey || echo "PERINGATAN: Qur’an Journey belum sepenuhnya siap untuk validasi."
    php artisan sullam:verify-launch || echo "PERINGATAN: checklist peluncuran belum sepenuhnya lulus."
    php artisan sullam:verify-academy || echo "PERINGATAN: verifikasi Academy belum sepenuhnya lulus."
    php artisan sullam:verify-ecosystem || echo "PERINGATAN: verifikasi ekosistem v2.3 belum sepenuhnya lulus."
    php artisan sullam:roadmap-status || echo "PERINGATAN: status roadmap belum dapat dihitung."
    php artisan storage:link || true
    php artisan config:cache
    php artisan view:cache
else
    echo "AUTO_MIGRATE=false: migration otomatis dilewati."
fi

if [ "${MUSHAF_LINE_AUTO_SYNC:-true}" = "true" ]; then
    echo "Sinkronisasi Mushaf Line 604 halaman dijalankan di latar belakang (resume-safe)."
    (
        sleep "${MUSHAF_LINE_SYNC_DELAY:-12}"
        php artisan sullam:ensure-quran-corpus || echo "PERINGATAN: korpus Full Qur’an belum 100%; Mushaf Line tetap akan mencoba sinkronisasi layout."
        php artisan sullam:ensure-mushaf-lines || echo "PERINGATAN: Mushaf Line belum 604/604; sinkronisasi akan dilanjutkan pada restart atau perintah manual."
        php artisan sullam:roadmap-status || true
    ) &
fi

if [ "${QURAN_AUDIO_AUTO_SYNC:-true}" = "true" ]; then
    echo "Sinkronisasi Full Qur’an dijalankan di latar belakang: korpus lebih dahulu, lalu dua qari."
    (
        sleep "${QURAN_AUDIO_SYNC_DELAY:-8}"
        php artisan sullam:ensure-quran-corpus || echo "PERINGATAN: korpus Full Qur’an belum 100%; akan dicoba lagi pada restart atau dari Pustaka Qur’an."
        php artisan sullam:sync-quran-divisions || echo "PERINGATAN: pembagian Juz/Hizb/Rubu‘/Manzil belum sepenuhnya tersinkron."
        php artisan sullam:ensure-quran-audio || echo "PERINGATAN: audio Full Qur’an belum 100%; sinkronisasi bersifat resume-safe dan dapat dilanjutkan."
        php artisan sullam:roadmap-status || true
    ) &
fi

echo "Menjalankan NGINX Unit..."
exec /usr/local/bin/docker-entrypoint.sh unitd --no-daemon
