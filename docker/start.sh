#!/usr/bin/env sh
set -e
cd /app
[ -f .env ] || cp .env.production .env
# Coolify may inject a fresh .env without a key — generate one if missing.
grep -q "^APP_KEY=base64:" .env 2>/dev/null || php artisan key:generate --force
php artisan config:clear >/dev/null 2>&1 || true
php artisan config:cache >/dev/null 2>&1 || true
exec php artisan serve --host 0.0.0.0 --port 8080
