#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

echo "=== Sullamul Hifz v1.1.0: regular deployment ==="

if [ -z "${INITIAL_TPA_DATA_KEY:-}" ]; then
    echo "ABORTED: INITIAL_TPA_DATA_KEY belum diisi di Environment Variables."
    exit 1
fi

if [ ! -f database/data/initial_tpa_2026_2027.enc.json ]; then
    echo "ABORTED: file data awal terenkripsi tidak ditemukan."
    exit 1
fi

if ! grep -q "asgn_submission_recipient_attempt_uq" database/migrations/0001_01_03_000000_create_learning_tables.php; then
    echo "ABORTED: the MySQL index-name hotfix is missing from this release."
    exit 1
fi

php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class='Database\Seeders\ProductionSeeder' --force
php artisan storage:link || true
php artisan optimize
php artisan migrate:status
php artisan sullam:verify-installation

echo "=== Regular deployment completed ==="
