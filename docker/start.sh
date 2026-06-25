#!/usr/bin/env sh
set -e
cd /app
[ -f .env ] || cp .env.production .env
# Ensure a key exists in .env; let Dotenv read it (no config:cache, no export).
grep -q "^APP_KEY=base64:" .env 2>/dev/null || php artisan key:generate --force
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
exec php artisan serve --host 0.0.0.0 --port 8080
