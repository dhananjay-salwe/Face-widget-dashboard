#!/bin/sh
set -e

echo "──────────────────────────────────────────"
echo " Face Widget Dashboard — Starting up..."
echo "──────────────────────────────────────────"

# ── Wait for MySQL to be ready ────────────────────────────────────────────────
echo "[1/6] Waiting for database..."
until php -r "
    \$pdo = new PDO(
        'mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}',
        '${DB_USERNAME}',
        '${DB_PASSWORD}'
    );
    echo 'Connected';
" 2>/dev/null; do
    echo "  → DB not ready yet, retrying in 3s..."
    sleep 3
done
echo "  ✓ Database is ready"

# ── Cache config ─────────────────────────────────────────────────────────────
echo "[2/6] Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "  ✓ Caches built"

# ── Run migrations ────────────────────────────────────────────────────────────
echo "[3/6] Running migrations..."
php artisan migrate:fresh --force --no-interaction
echo "  ✓ Migrations done"

# ── Storage link ─────────────────────────────────────────────────────────────
echo "[4/6] Linking storage..."
php artisan storage:link --force 2>/dev/null || true
echo "  ✓ Storage linked"

# ── Permissions ───────────────────────────────────────────────────────────────
echo "[5/6] Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "  ✓ Permissions set"

# ── Start supervisor (nginx + php-fpm) ────────────────────────────────────────
echo "[6/6] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
