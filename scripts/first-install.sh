#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

echo "=== Sullamul Hifz v1.0.0: first installation ==="

if [ "${CONFIRM_DATABASE_WIPE:-}" != "YES" ]; then
    echo "ABORTED: This command deletes every table in the configured database."
    echo "Run it only for a new/empty installation:"
    echo "CONFIRM_DATABASE_WIPE=YES sh scripts/first-install.sh"
    exit 1
fi

if ! grep -q "asgn_submission_recipient_attempt_uq" database/migrations/0001_01_03_000000_create_learning_tables.php; then
    echo "ABORTED: the MySQL index-name hotfix is missing from this release."
    exit 1
fi

php artisan optimize:clear
php artisan db:wipe --force
php artisan migrate --force
php artisan db:seed --class='Database\Seeders\ProductionSeeder' --force
php artisan storage:link || true
php artisan optimize
php artisan migrate:status

echo "=== First installation completed ==="
