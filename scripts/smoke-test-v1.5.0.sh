#!/usr/bin/env sh
set -eu

cd /var/www/html

PUBLIC_URL="${PUBLIC_SITE_URL:-https://sullamulhifz.or.id}"
PORTAL_URL="${PORTAL_BASE_URL:-https://app.sullamulhifz.or.id}"

echo "=== Smoke test Sullamul Hifz v1.5.0 ==="
php artisan migrate:status
php artisan sullam:verify-academic-core
php artisan about | grep -E "Environment|Laravel|PHP" || true

curl -fsS "${PUBLIC_URL}/up" >/dev/null
curl -fsS "${PUBLIC_URL}/" >/dev/null
curl -fsS "${PORTAL_URL}/login" >/dev/null

echo "Smoke test dasar berhasil. Lanjutkan uji login admin, guru, dan wali melalui browser."
