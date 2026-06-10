#!/usr/bin/env bash
# =============================================================================
#  Deploy blue-green en un host Linux. Lo invoca el workflow de GitHub Actions
#  por SSH:  ./docker/deploy.sh <TAG> [ENV_PREFIX]
#
#  ENV_PREFIX: "prod" (default) o "staging"
#
#  Flujo:
#    1. pull de la imagen nueva
#    2. levanta el color INACTIVO con la imagen nueva
#    3. health-check del color nuevo  (si falla → aborta, el viejo sigue sirviendo)
#    4. cutover: reescribe el upstream del edge + nginx -s reload (graceful)
#    5. baja el color viejo y actualiza el worker
#
#  Migraciones: NUNCA se ejecutan acá — la base es compartida entre proyectos,
#  el operador las aplica a mano (docker exec ... php artisan migrate --force).
#
#  Resultado: el usuario nunca ve downtime. Si el color nuevo no pasa el health
#  check, no hay cutover y el deploy se aborta dejando el color viejo intacto.
# =============================================================================
set -euo pipefail

TAG="${1:?Uso: deploy.sh <TAG> [prod|staging]}"
ENV_PREFIX="${2:-prod}"

if [[ "$ENV_PREFIX" != "prod" && "$ENV_PREFIX" != "staging" ]]; then
    echo "Error: ENV_PREFIX debe ser 'prod' o 'staging' (recibido: '${ENV_PREFIX}')" >&2
    exit 1
fi

IMAGE="${IMAGE:-ghcr.io/nfernandez-evolvere/infinito-reciclaje-balanza}"
NETWORK="${NETWORK:-balanza-net}"
HEALTH_TIMEOUT="${HEALTH_TIMEOUT:-60}"

# Repo root (este script vive en docker/)
cd "$(dirname "$0")/.."

ACTIVE_FILE="docker/edge/${ENV_PREFIX}/active-upstream.conf"

log() { echo -e "\033[1;36m[deploy:${ENV_PREFIX}]\033[0m $*"; }

# Puertos por entorno: prod usa 8081/8082, staging usa 8083/8084
if [ "$ENV_PREFIX" = "staging" ]; then
    PORT_BLUE=8083; PORT_GREEN=8084
else
    PORT_BLUE=8081; PORT_GREEN=8082
fi
port_for() { [ "$1" = "blue" ] && echo "$PORT_BLUE" || echo "$PORT_GREEN"; }

# --- 0) Login a GHCR (si se pasó token) y red compartida ---------------------
if [ -n "${GHCR_TOKEN:-}" ]; then
    echo "$GHCR_TOKEN" | docker login ghcr.io -u "${GHCR_USER:-x}" --password-stdin
fi
docker network inspect "$NETWORK" >/dev/null 2>&1 || docker network create "$NETWORK"

# --- 1) Pull de la imagen nueva ----------------------------------------------
log "Pull $IMAGE:$TAG"
export TAG IMAGE ENV_PREFIX
docker pull "$IMAGE:$TAG"

# --- 2) Determinar color activo e inactivo -----------------------------------
if grep -q "balanza-${ENV_PREFIX}-app-green" "$ACTIVE_FILE" 2>/dev/null; then
    ACTIVE=green; INACTIVE=blue
else
    ACTIVE=blue;  INACTIVE=green
fi
INACTIVE_PORT="$(port_for "$INACTIVE")"
log "Color activo: $ACTIVE  →  desplegando en: $INACTIVE (puerto $INACTIVE_PORT)"

run_color() { # $1=color $2=port  → resto = comando compose
    ENV_PREFIX="$ENV_PREFIX" COLOR="$1" HTTP_PORT="$2" TAG="$TAG" \
        docker compose -p "${ENV_PREFIX}-$1" -f compose.prod.yaml "${@:3}"
}

# --- 3) Migraciones — BLOQUEADAS en el deploy --------------------------------
# El deploy NUNCA toca el schema. La base es compartida entre proyectos y las
# migraciones son siempre una acción manual y deliberada del operador:
#
#   ssh usuario@host
#   docker exec balanza-prod-app-blue php artisan migrate --force
#
# No existe ningún flag ni variable de entorno que habilite migraciones aquí.
log "Migraciones no ejecutadas (política: siempre manuales)."

# --- 4) Levantar el color inactivo con la imagen nueva -----------------------
log "Levantando stack ${ENV_PREFIX}-${INACTIVE}"
run_color "$INACTIVE" "$INACTIVE_PORT" up -d

# --- 5) Health-check del color nuevo -----------------------------------------
log "Health-check http://127.0.0.1:$INACTIVE_PORT/up (timeout ${HEALTH_TIMEOUT}s)"
deadline=$(( $(date +%s) + HEALTH_TIMEOUT ))
until curl -fsS "http://127.0.0.1:$INACTIVE_PORT/up" >/dev/null 2>&1; do
    if [ "$(date +%s)" -ge "$deadline" ]; then
        log "❌ El color $INACTIVE no quedó saludable. Abortando (el viejo sigue activo)."
        run_color "$INACTIVE" "$INACTIVE_PORT" logs --tail=50 app || true
        run_color "$INACTIVE" "$INACTIVE_PORT" down || true
        exit 1
    fi
    sleep 2
done
log "✅ ${ENV_PREFIX}/${INACTIVE} saludable."

# --- 6) Cutover: conmutar el upstream del edge + reload graceful -------------
log "Cutover del edge → ${ENV_PREFIX}/${INACTIVE}"
cp "docker/edge/${ENV_PREFIX}/upstream-${INACTIVE}.conf" "$ACTIVE_FILE"
docker exec balanza-edge nginx -t
docker exec balanza-edge nginx -s reload
log "Tráfico ${ENV_PREFIX} ahora en ${INACTIVE}."

# --- 7) Bajar el color viejo y actualizar el worker --------------------------
log "Bajando color viejo (${ENV_PREFIX}/${ACTIVE})"
docker compose -p "${ENV_PREFIX}-${ACTIVE}" -f compose.prod.yaml down || true

log "Actualizando worker de ${ENV_PREFIX} a $TAG"
ENV_PREFIX="$ENV_PREFIX" TAG="$TAG" \
    docker compose -p "${ENV_PREFIX}-worker" -f compose.worker.yaml up -d

# --- 8) Limpieza de imágenes huérfanas ---------------------------------------
docker image prune -f >/dev/null 2>&1 || true
log "🎉 Deploy completado: $IMAGE:$TAG activo en ${ENV_PREFIX}/${INACTIVE}."
