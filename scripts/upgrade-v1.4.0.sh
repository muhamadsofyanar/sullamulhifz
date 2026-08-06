#!/usr/bin/env sh
set -eu
cd "$(dirname "$0")/.."

echo "=== Sullamul Hifz v1.4.0 additive upgrade ==="
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class='Database\Seeders\OperationalV140Seeder' --force
php artisan storage:link || true
php artisan optimize
php artisan migrate:status
php artisan route:list --except-vendor >/tmp/sullamul-hifz-v140-routes.txt

echo "Upgrade selesai. Tidak ada db:wipe, migrate:fresh, atau seeder data awal."
echo "=== Sullamul Hifz v1.4.0 ready ==="
