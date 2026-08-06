#!/usr/bin/env sh
set -eu
cd /var/www/html

echo "=== Sullamul Hifz v1.6.1 smoke test ==="
cat RELEASE
php artisan migrate:status
php artisan sullam:verify-academic-core
php artisan sullam:verify-quran-learning
php artisan route:list --path=latihan-quran
php artisan route:list --path=admin/quran-library
php artisan about --only=environment
