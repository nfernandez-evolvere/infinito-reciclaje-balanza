#!/usr/bin/env bash
# =============================================================================
#  Deploy blue-green en un host Linux. Lo invoca el workflow de GitHub Actions
#  por SSH:  ./docker/deploy.sh <TAG>
#
#  Flujo:
#    1. pull de la imagen nueva
#    2. migraciones (una vez, deben ser backward-compatible / expand-contract)
#    3. levanta el color INACTIVO con la imagen nueva
#    4. health-check del color nuevo  (si falla → aborta, el viejo sigue sirviendo)
#    5. cutover: reescribe el upstream del edge + nginx -s reload (graceful)
#    6. baja el color viejo y actualiza el worker
#
#  Resultado: el usuario nunca ve downtime. Si el color nuevo no pasa el health
#  check, no hay cutover y el deploy se aborta dejando el color viejo intacto.
# =============================================================================
set -euo pipefail

TAG="${1:?Uso: deploy.sh <TAG>}"
IMAGE="${IMAGE:-ghcr.io/nfernandez-evolvere/infinito-reciclaje-balanza}"
NETWORK="${NETWORK:-balanza-net}"
HEALTH_TIMEOUT="${HEALTH_TIMEOUT:-60}"   # segundos de espera del health-check

# Repo root (este script vive en docker/)
cd "$(dirname "$0")/.."

ACTIVE_FILE="docker/edge/active-upstream.conf"
log() { echo -e "\033[1;36m[deploy]\033[0m $*"; }

port_for() { [ "$1" = "blue" ] && echo 8081 || echo 8082; }

# --- 0) Login a GHCR (si se pasó token) y red compartida ---------------------
if [ -n "${GHCR_TOKEN:-}" ]; then
    echo "$GHCR_TOKEN" | docker login ghcr.io -u "${GHCR_USER:-x}" --password-stdin
fi
docker network inspect "$NETWORK" >/dev/null 2>&1 || docker network create "$NETWORK"

# --- 1) Pull de la imagen nueva ----------------------------------------------
log "Pull $IMAGE:$TAG"
export TAG IMAGE
docker pull "$IMAGE:$TAG"

# --- 2) Determinar color activo e inactivo -----------------------------------
if grep -q "balanza-app-green" "$ACTIVE_FILE" 2>/dev/null; then
    ACTIVE=green; INACTIVE=blue
else
    ACTIVE=blue;  INACTIVE=green
fi
INACTIVE_PORT="$(port_for "$INACTIVE")"
log "Color activo: $ACTIVE  →  desplegando en: $INACTIVE (puerto $INACTIVE_PORT)"

run_color() { # $1=color $2=port  → resto = comando compose
    COLOR="$1" HTTP_PORT="$2" TAG="$TAG" \
        docker compose -p "app-$1" -f compose.prod.yaml "${@:3}"
}

# --- 3) Migraciones — NUNCA automáticas --------------------------------------
# Política de DB (CLAUDE.md): la base es COMPARTIDA entre proyectos. Las
# migraciones son SIEMPRE una acción deliberada del operador; en ningún ambiente
# corren solas. Por defecto el deploy NO toca el schema.
# Para un release que incluya migraciones (ya revisadas y backward-compatible /
# expand-contract), correr el deploy con el opt-in explícito:
#     RUN_MIGRATIONS=true bash docker/deploy.sh <TAG>
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    log "RUN_MIGRATIONS=true → migrate --force (paso deliberado)"
    run_color "$INACTIVE" "$INACTIVE_PORT" run --rm app php artisan migrate --force
else
    log "Migraciones OMITIDAS (RUN_MIGRATIONS≠true). El schema no se toca."
fi

# --- 4) Levantar el color inactivo con la imagen nueva -----------------------
log "Levantando stack $INACTIVE"
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
log "✅ $INACTIVE saludable."

# --- 6) Cutover: conmutar el upstream del edge + reload graceful -------------
log "Cutover del edge → $INACTIVE"
cp "docker/edge/upstream-$INACTIVE.conf" "$ACTIVE_FILE"   # in-place (bind-mount safe)
docker exec balanza-edge nginx -t
docker exec balanza-edge nginx -s reload
log "Tráfico ahora en $INACTIVE."

# --- 7) Bajar el color viejo y actualizar el worker --------------------------
log "Bajando color viejo ($ACTIVE)"
docker compose -p "app-$ACTIVE" -f compose.prod.yaml down || true

log "Actualizando worker a $TAG"
TAG="$TAG" docker compose -p app-worker -f compose.worker.yaml up -d

# --- 8) Limpieza de imágenes huérfanas ---------------------------------------
docker image prune -f >/dev/null 2>&1 || true
log "🎉 Deploy completado: $IMAGE:$TAG activo en $INACTIVE."
