#!/usr/bin/env sh
set -eu
cd /var/www/html
printf 'Release: '
cat RELEASE
php artisan optimize:clear >/dev/null
php artisan route:list --path=latihan-quran
php artisan route:list --path=academy
php artisan sullam:verify-academy
php artisan sullam:verify-quran-learning
printf '\nOK: v2.0.1 routes dan verifier tersedia.\n'
