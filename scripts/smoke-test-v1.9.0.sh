#!/usr/bin/env sh
set -eu
cd /var/www/html

echo "=== Sullamul Hifz v1.9.0 smoke test ==="
printf "Release: " && cat RELEASE
php artisan migrate:status
php artisan sullam:verify-academic-core
php artisan sullam:verify-quran-learning
php artisan sullam:verify-launch
php artisan route:list --path=teacher/daily
php artisan route:list --path=admin/launch-readiness
php artisan route:list --path=admin/reports
php artisan route:list --path=latihan-quran
php artisan about --only=environment
