#!/bin/bash
set -e

# Crear archivo SQLite si no existe
touch database/database.sqlite

# Migrar siempre (idempotente)
php artisan migrate --force

# Seedear solo si la base está vacía
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)

if [ "$USER_COUNT" = "0" ]; then
    echo "Base de datos vacía — ejecutando seeds..."
    php artisan db:seed --class=DevSeeder --force
else
    echo "Base de datos ya tiene datos — omitiendo seeds."
fi

# Iniciar servidor
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
