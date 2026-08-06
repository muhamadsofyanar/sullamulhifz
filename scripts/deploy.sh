#!/usr/bin/env sh
set -eu

php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class='Database\Seeders\ProductionSeeder' --force
php artisan storage:link || true
php artisan optimize

echo "Sullamul Hifz deployment tasks completed."
