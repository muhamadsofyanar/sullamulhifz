#!/usr/bin/env sh
set -eu
cd /var/www/html

echo "=== Sullamul Hifz v2.0.0 smoke test ==="
printf "Release: " && cat RELEASE
php artisan migrate:status
php artisan sullam:verify-academic-core
php artisan sullam:verify-quran-learning
php artisan sullam:verify-launch
php artisan sullam:verify-academy
php artisan route:list --path=academy/belajar
php artisan route:list --path=admin/academy
php artisan route:list --path=teacher/academy
php artisan route:list --path=latihan-quran
php artisan route:list --path=guardian/child
php artisan about --only=environment

echo "Pemeriksaan file PWA..."
test -s public/manifest.webmanifest
test -s public/service-worker.js
test -s public/js/app.js
test -s public/css/app.css

echo "OK: struktur v2.0.0, Academy, Quran Learning, dan PWA tersedia."
