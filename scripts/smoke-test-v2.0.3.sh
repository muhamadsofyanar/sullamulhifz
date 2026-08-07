#!/usr/bin/env sh
set -eu
cd /var/www/html

echo "=== Smoke Test v2.0.3 ==="
echo -n "Release: " && cat RELEASE
php artisan route:list --path=admin/academy >/dev/null
php artisan route:list --path=academy >/dev/null
php artisan sullam:verify-academy
php -l app/Http/Controllers/Admin/AcademyController.php >/dev/null
php -l database/seeders/AcademyLaunchV203Seeder.php >/dev/null
[ -f public/css/app-v203.css ]
grep -q 'youtube-nocookie.com/embed' resources/views/academy/lesson.blade.php
grep -q 'x6AVimGaykM' database/seeders/AcademyLaunchV203Seeder.php
echo "OK: route Academy, admin Academy, video embed, dan aset v2.0.3 tersedia."
