#!/usr/bin/env sh
# Resilient boot: ALWAYS end up serving on 8080. Migrations/seed are best-effort and never crash
# the container (so a slow/unready DB can't take the app down — it self-heals on the retry loop).
cd /app
[ -f .env ] || cp .env.production .env 2>/dev/null || true
grep -q "^APP_KEY=base64:" .env 2>/dev/null || php artisan key:generate --force 2>/dev/null || true
export APP_KEY="$(grep '^APP_KEY=' .env | head -1 | cut -d '=' -f2-)"
mkdir -p database storage/framework/{cache,sessions,views} bootstrap/cache 2>/dev/null || true
[ -f database/database.sqlite ] || touch database/database.sqlite
php artisan config:clear 2>/dev/null || true
# migrate + seed in the background with retries; the server starts immediately regardless.
( i=0; while [ $i -lt 30 ]; do
    php artisan migrate --force 2>/dev/null && php artisan db:seed --force 2>/dev/null && break
    i=$((i+1)); sleep 3
  done ) &
exec php artisan serve --host 0.0.0.0 --port 8080
