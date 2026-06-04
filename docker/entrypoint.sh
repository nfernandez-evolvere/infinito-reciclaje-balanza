#!/usr/bin/env bash
# Entrypoint de la imagen. Corre como root, prepara la app y delega en el CMD
# (supervisord web o worker). Idempotente: seguro de re-ejecutar en cada arranque.
set -euo pipefail

cd /app

echo "[entrypoint] Preparando la aplicación… (APP_ENV=${APP_ENV:-production})"

# --- Symlink de storage público (idempotente) -------------------------------
php artisan storage:link --no-interaction 2>/dev/null || true

# --- Migraciones — NUNCA automáticas en ningún ambiente ----------------------
# Política de DB (CLAUDE.md): base COMPARTIDA entre proyectos → las migraciones
# son SIEMPRE deliberadas. AUTO_MIGRATE queda en false en dev y en prod.
# Las migraciones se corren a mano: `php artisan migrate` (o RUN_MIGRATIONS=true
# en docker/deploy.sh para un deploy con schema nuevo).
if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
    echo "[entrypoint] AUTO_MIGRATE=true → php artisan migrate --force"
    php artisan migrate --force
fi

# --- Caches de framework ------------------------------------------------------
# En LOCAL: NO cachear. Laravel lee env y config frescos en cada request.
# Cachear en dev confunde: el cache puede fijar APP_URL u otros valores del
# primer arranque en vez de leer el env real de compose.
# En PRODUCTION: sí cachear (imagen inmutable, env estable, performance crítica).
# route:cache se omite siempre: la app usa rutas con Closure (/, fallback,
# previews de reportes) que no son serializables.
if [ "${APP_ENV:-production}" != "local" ]; then
    echo "[entrypoint] Generando caches de framework (modo ${APP_ENV:-production})…"
    php artisan config:cache
    php artisan view:cache
    php artisan event:cache
else
    # Limpiar cualquier cache vieja que pueda venir en la imagen
    php artisan config:clear 2>/dev/null || true
    php artisan view:clear   2>/dev/null || true
    php artisan event:clear  2>/dev/null || true
    # Habilitar validación de timestamps en opcache: en prod está en 0 (máx perf),
    # pero en dev con bind mount los archivos cambian en disco y opcache debe
    # detectarlo para que PHP pickee los cambios sin reiniciar el contenedor.
    echo "opcache.validate_timestamps=1" > /usr/local/etc/php/conf.d/zz-dev-opcache.ini
    echo "[entrypoint] Modo local — caches omitidas, opcache.validate_timestamps=1."
fi

# Las caches escritas por root deben quedar legibles para www-data (php-fpm)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "[entrypoint] Listo. Arrancando: $*"
exec "$@"
