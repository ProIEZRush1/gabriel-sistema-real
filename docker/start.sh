#!/usr/bin/env sh
set -e
cd /app
[ -f .env ] || cp .env.production .env
# Ensure a valid key in .env, export it (overrides any empty injected env var),
# then cache config so `php artisan serve` request handlers read the baked key.
grep -q "^APP_KEY=base64:" .env 2>/dev/null || php artisan key:generate --force
export APP_KEY="$(grep '^APP_KEY=' .env | head -1 | cut -d '=' -f2-)"
php artisan config:cache

# Ejecuta migraciones y siembra (idempotente) antes de servir.
[ -f database/database.sqlite ] || touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host 0.0.0.0 --port 8080
