#!/usr/bin/env bash
# Entrypoint de la imagen. Corre como root, prepara la app y delega en el CMD
# (supervisord web o worker). Idempotente: seguro de re-ejecutar en cada arranque.
set -euo pipefail

cd /app

echo "[entrypoint] Preparando la aplicación…"

# --- Symlink de storage público (idempotente) -------------------------------
php artisan storage:link --no-interaction 2>/dev/null || true

# --- Migraciones — NUNCA automáticas en ningún ambiente ----------------------
# Política de DB (CLAUDE.md): base COMPARTIDA entre proyectos → las migraciones
# son SIEMPRE deliberadas. AUTO_MIGRATE queda en false en dev y en prod; este
# bloque solo tendría sentido con una base descartable (no es el caso). Las
# migraciones se corren a mano: `php artisan migrate` (o RUN_MIGRATIONS=true en
# docker/deploy.sh para un deploy con schema nuevo).
if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
    echo "[entrypoint] AUTO_MIGRATE=true → php artisan migrate --force"
    php artisan migrate --force
fi

# --- Caches de framework ------------------------------------------------------
# route:cache se OMITE a propósito: la app usa rutas con Closure (/, fallback,
# previews de reportes) que no son serializables. config/view/event sí se cachean.
php artisan config:cache
php artisan view:cache
php artisan event:cache

# Las caches escritas por root deben quedar legibles para www-data (php-fpm)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "[entrypoint] Listo. Arrancando: $*"
exec "$@"
