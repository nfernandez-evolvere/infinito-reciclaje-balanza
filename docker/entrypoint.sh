#!/usr/bin/env bash
# Entrypoint de la imagen. Corre como root, prepara la app y delega en el CMD
# (supervisord web o worker). Idempotente: seguro de re-ejecutar en cada arranque.
set -euo pipefail

cd /app

echo "[entrypoint] Preparando la aplicación… (APP_ENV=${APP_ENV:-production})"

# --- Symlink de storage público (idempotente) -------------------------------
php artisan storage:link --no-interaction 2>/dev/null || true

# --- Migraciones — BLOQUEADAS en el entrypoint --------------------------------
# El contenedor NUNCA migra al arrancar. La base es compartida entre proyectos
# y las migraciones son siempre una acción manual del operador:
#
#   docker exec <contenedor> php artisan migrate --force
#
# No existe ninguna variable de entorno que habilite migraciones aquí.

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
