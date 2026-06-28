#!/usr/bin/env sh
# Resilient boot: ALWAYS serve on 8080. DB path comes from the injected DB_DATABASE (a persistent
# volume), so client data survives redeploys. migrate/seed are best-effort and never crash the app.
cd /app
[ -f .env ] || cp .env.production .env 2>/dev/null || true
grep -q "^APP_KEY=base64:" .env 2>/dev/null || php artisan key:generate --force 2>/dev/null || true
export APP_KEY="$(grep '^APP_KEY=' .env | head -1 | cut -d '=' -f2-)"
DBF="${DB_DATABASE:-/app/database/database.sqlite}"
mkdir -p "$(dirname "$DBF")" storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache 2>/dev/null || true
[ -f "$DBF" ] || touch "$DBF"
php artisan config:clear 2>/dev/null || true
( i=0; while [ $i -lt 40 ]; do
    php artisan migrate --force 2>/dev/null && php artisan db:seed --force 2>/dev/null && break
    i=$((i+1)); sleep 3
  done ) &
exec php artisan serve --host 0.0.0.0 --port 8080
