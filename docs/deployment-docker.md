# Despliegue con Docker — Multi-stage, Blue-Green y CI/CD

Documentación de la infraestructura de contenedores del sistema de Balanza:
cómo se construye la imagen, cómo se corre en desarrollo, y cómo se despliega a
producción **sin downtime** mediante un esquema blue-green automatizado con
GitHub Actions.

---

## Tabla de contenidos

1. [Decisiones de arquitectura](#1-decisiones-de-arquitectura)
2. [Inventario de archivos](#2-inventario-de-archivos)
3. [La imagen — Dockerfile multi-stage](#3-la-imagen--dockerfile-multi-stage)
4. [Modelo de procesos (supervisord)](#4-modelo-de-procesos-supervisord)
5. [Desarrollo local](#5-desarrollo-local)
   - [compose.dev.yaml — con hot-reload](#composedevelopmentyaml--desarrollo-con-hot-reload)
   - [compose.prod-local.yaml — probar imagen de producción localmente](#composeprod-localyaml--probar-la-imagen-de-producción-localmente)
6. [Topología — dos entornos, un solo host](#6-topología--dos-entornos-un-solo-host)
7. [Los dos nginx (el de la app y el edge)](#7-los-dos-nginx-el-de-la-app-y-el-edge)
8. [El script de deploy](#8-el-script-de-deploy)
9. [CI/CD con GitHub Actions](#9-cicd-con-github-actions)
10. [Setup inicial del servidor (runbook)](#10-setup-inicial-del-servidor-runbook)
    - [Fase 0 — Repo y GHCR](#fase-0--preparar-el-repo-y-poblar-ghcr)
    - [Fase 1 — Clave SSH y Secrets](#fase-1--clave-ssh-y-github-secrets)
    - [Fase 2 — Bootstrap de la VPS](#fase-2--bootstrap-de-la-vps)
    - [Fase 3 — Primer deploy](#fase-3--primer-deploy)
    - [Fase 4 — Deploys posteriores](#fase-4--deploys-posteriores-automáticos)
11. [Secrets y variables de entorno](#11-secrets-y-variables-de-entorno)
12. [Gotchas y troubleshooting](#12-gotchas-y-troubleshooting)
13. [Cómo extender (TLS, escalado, rollback)](#13-cómo-extender-tls-escalado-rollback)

---

## 1. Decisiones de arquitectura

| Tema | Decisión | Por qué |
|------|----------|---------|
| Servidor web prod | **nginx + php-fpm** en la imagen (supervisord) | Estándar, sin refactor de la app (vs Octane/FrankenPHP) |
| Base de datos | **SQL Server externo** (host Windows en dev, server compartido en prod) | La DB es compartida entre proyectos; nunca se containeriza. Obliga a incluir `pdo_sqlsrv` |
| Topología prod | **Un host Linux + reverse proxy** | Escala del proyecto; blue-green por swap de upstream |
| CI/CD | **GitHub Actions → GHCR → SSH** | El repo ya está en GitHub; GHCR es gratis para el repo |
| Cola + scheduler | **Un único worker** fuera del blue-green | Evita doble-scheduling de reportes/alertas contra la DB compartida |
| Generación de PDF | **Browsershot** (Chromium + Node en runtime) | Es lo que ya usa `app/Services/PdfService.php` |

---

## 2. Inventario de archivos

```
.
├── Dockerfile                     # imagen multi-stage de producción
├── .dockerignore                  # qué NO entra al contexto de build
├── .env.docker.example            # plantilla de .env.prod (copiar en el server)
├── .env.staging.example           # plantilla de .env.staging (copiar en el server)
├── .env.edge.example              # plantilla de .env.edge: dominios del edge (copiar en el server)
│
├── compose.dev.yaml               # desarrollo: bind mount + Vite dev server + SQL Server del host
├── compose.prod-local.yaml        # testear imagen de producción localmente (sin bind mounts, APP_ENV=production)
├── compose.prod.yaml              # prod/staging: stack WEB de un color — parametrizado por ENV_PREFIX/COLOR/HTTP_PORT/TAG
├── compose.worker.yaml            # prod/staging: cola + scheduler — parametrizado por ENV_PREFIX/TAG
├── compose.edge.yaml              # nginx router persistente (único, maneja ambos entornos)
│
├── docker/
│   ├── entrypoint.sh              # prepara la app y arranca el CMD
│   ├── deploy.sh                  # orquesta el swap blue-green: deploy.sh <TAG> [prod|staging]
│   ├── nginx/default.conf         # nginx de la APP (horneado en la imagen)
│   ├── php/php.ini                # ajustes PHP de producción
│   ├── php/opcache.ini            # OPcache (validate_timestamps=0)
│   ├── php-fpm/www.conf           # pool php-fpm (listen 127.0.0.1:9000)
│   ├── supervisor/web.conf        # grupo WEB: nginx + php-fpm
│   ├── supervisor/worker.conf     # grupo WORKER: queue + scheduler
│   └── edge/
│       ├── default.conf.template  # nginx EDGE: dos server{} (prod + staging) — server_name = ${APP_DOMAIN}/${STAGE_DOMAIN}
│       ├── prod/
│       │   ├── upstream-blue.conf   # upstream balanza_prod_app → balanza-prod-app-blue
│       │   ├── upstream-green.conf  # upstream balanza_prod_app → balanza-prod-app-green
│       │   └── active-upstream.conf # upstream activo de prod (lo reescribe deploy.sh)
│       └── staging/
│           ├── upstream-blue.conf   # upstream balanza_staging_app → balanza-staging-app-blue
│           ├── upstream-green.conf  # upstream balanza_staging_app → balanza-staging-app-green
│           └── active-upstream.conf # upstream activo de staging (lo reescribe deploy.sh)
│
└── .github/workflows/
    └── deploy.yml                 # build/push GHCR + deploy SSH (main→prod, staging→staging)
                                   # (el gate pint+larastan+tests corre local en pre-push, no en CI)
```

---

## 3. La imagen — Dockerfile multi-stage

Tres etapas. Las dependencias y el toolchain de build **no llegan al runtime**.

```
┌─ Stage 1: composer-deps (composer:2) ──────────────────────────┐
│  composer install --no-dev --no-scripts --no-autoloader        │
│  → COPY . . → dump-autoload --optimize --no-scripts            │
│  Sale: /app/vendor                                              │
└────────────────────────────────────────────────────────────────┘
┌─ Stage 2: asset-build (node:22-bookworm) ──────────────────────┐
│  npm ci → COPY . . → npm run build                            │
│  Sale: /app/public/build  (assets versionados de Vite)         │
└────────────────────────────────────────────────────────────────┘
┌─ Stage 3: runtime (php:8.4-fpm-bookworm) ──────────────────────┐
│  • nginx, supervisor, Chromium, fonts-liberation               │
│  • Node 22 + puppeteer@25  (Browsershot)                       │
│  • msodbcsql18 + pecl sqlsrv/pdo_sqlsrv   ← driver SQL Server   │
│  • ext PHP: gd zip opcache pcntl bcmath intl                    │
│  • COPY vendor (stage 1) + public/build (stage 2) + código     │
│  • HEALTHCHECK curl /up · EXPOSE 8080 · ENTRYPOINT             │
└────────────────────────────────────────────────────────────────┘
```

**Por qué multi-stage:** la imagen final no incluye Vite, Tailwind, `node_modules`
de build ni las dev-deps de Composer. Las capas de dependencias se cachean por
`composer.lock` / `package-lock.json`, separadas del código → rebuilds rápidos.

> **El fix más importante:** el Dockerfile anterior instalaba `pdo_mysql`/`pdo_pgsql`
> pero **no** `pdo_sqlsrv`, siendo que el proyecto usa SQL Server. Era un bug latente:
> la imagen nunca habría conectado a la base. Ahora se instala vía `msodbcsql18` +
> `pecl install sqlsrv pdo_sqlsrv`.

### Decisiones internas

- **`--no-scripts` en `dump-autoload`** (stage 1): evita disparar `post-autoload-dump`
  (`artisan package:discover`), que bootearía Laravel en la imagen `composer:2` sin
  extensiones. El package discovery ocurre en runtime, vía `entrypoint.sh`.
- **Tailwind v4 escanea blade/PHP**: por eso el stage 2 hace `COPY . .` completo antes
  de `npm run build` (si no, faltarían clases usadas en `app/` o `resources/views`).
- **`route:cache` NO se ejecuta**: la app tiene rutas con Closure (`/`, `Route::fallback`,
  los previews de reportes) que no son serializables. Se cachea config/view/event.

---

## 4. Modelo de procesos (supervisord)

La imagen trae **dos grupos de supervisord**, seleccionables por el `CMD`:

| Grupo | Archivo | Procesos | Lo usa |
|-------|---------|----------|--------|
| `web` | `docker/supervisor/web.conf` | nginx (`:8080`) + php-fpm (`:9000`) | contenedores blue/green |
| `worker` | `docker/supervisor/worker.conf` | `queue:work` + `schedule:work` | el worker único |

```
CMD por defecto = supervisord -c /etc/supervisor/conf.d/web.conf   (grupo web)
El worker lo sobreescribe:  command: [..., worker.conf, ...]
```

> El **scheduler** corre dentro del grupo worker. El deploy anterior no lo corría,
> así que los reportes programados (cada 15 min) y la detección de alertas diaria
> (`routes/console.php`) **no se disparaban** en contenedores. Ahora sí.

### Socket de supervisord y `supervisorctl`

Ambos archivos de conf (`web.conf` y `worker.conf`) incluyen las secciones necesarias
para que `supervisorctl status` funcione desde dentro del contenedor:

```ini
[unix_http_server]
file = /run/supervisor.sock
chmod = 0700

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[supervisorctl]
serverurl = unix:///run/supervisor.sock
```

Sin estas secciones, `supervisorctl` devuelve `Error: .ini file does not include
supervisorctl section` y no puede consultar el estado de los procesos.

```bash
# Verificar procesos dentro del contenedor web o worker:
docker exec <contenedor> supervisorctl status
```

### HEALTHCHECK y contenedores worker

El `HEALTHCHECK` de la imagen hace `curl http://127.0.0.1:8080/up`. Ese endpoint solo
existe cuando nginx está corriendo (grupo `web`). El contenedor worker no corre nginx,
así que el check siempre fallaría y Docker lo marcaría `unhealthy`.

**Solución:** todos los servicios `worker` en los compose files tienen:

```yaml
healthcheck:
  disable: true
```

Esto no afecta al funcionamiento del worker — solo evita que Docker lo marque
incorrectamente. Para verificar que `queue:work` y `schedule:work` están corriendo:

```bash
docker exec <contenedor-worker> supervisorctl status
# o directamente:
docker exec <contenedor-worker> ps aux | grep artisan
```

### `entrypoint.sh` (corre como root antes del CMD)

1. `php artisan storage:link` (idempotente).
2. `config:cache` + `view:cache` + `event:cache`.
3. `chown` de caches a `www-data` y `exec "$@"`.

> **Migraciones — siempre manuales, sin excepción.** La base es compartida entre
> proyectos, así que aplicar el schema es **siempre** una acción deliberada del operador:
> `docker exec <contenedor> php artisan migrate --force`. El entrypoint y el deploy
> **no tienen ningún mecanismo** que ejecute migraciones. Ver [sección 8](#8-el-script-de-deploy).

---

## 5. Desarrollo local

### `compose.dev.yaml` — desarrollo con hot-reload

Corre la imagen de producción con **bind mount del código fuente** y un servicio
**Vite** dedicado para HMR (recarga automática del browser al cambiar archivos).

```powershell
# Primera vez o cuando cambian deps/assets:
docker compose -f compose.dev.yaml up --build

# Día a día (solo cambios de código PHP/Blade):
docker compose -f compose.dev.yaml up

# App → http://localhost:8000
# Vite HMR → http://localhost:5173 (el browser se conecta solo)
```

Tres servicios: `app` (web, `APP_DEBUG=true`), `worker` (cola + scheduler) y `vite`
(Node 22 + `npm run dev` con polling de filesystem para Docker Desktop en Windows).

El bind mount monta todo el código en `/app`. Tres volúmenes anónimos preservan los
artefactos de la imagen sobre el bind mount: `vendor/` (Composer sin dev-deps),
`public/build/` (assets de Vite de producción) y `bootstrap/cache/` (evita que el
`packages.php` del host —con providers de require-dev— rompa el contenedor).

> **Prerrequisito (una sola vez):** habilitar **TCP/IP** en SQLEXPRESS, fijarle el
> **puerto 1433 estático** y abrir el firewall. Por eso el compose setea
> `DB_HOST=host.docker.internal`, `DB_PORT=1433`, `DB_ENCRYPT=no`,
> `DB_TRUST_SERVER_CERTIFICATE=yes`.
>
> Conectar por **nombre de instancia** (`host\SQLEXPRESS`, `DB_PORT` vacío) también es
> posible —el driver ODBC lo soporta vía **SQL Server Browser** (UDP 1434)— pero desde
> un contenedor es frágil: requiere Browser corriendo y alcanzable por UDP 1434, más el
> puerto TCP dinámico (que cambia al reiniciar la instancia) abierto en el firewall.
> Fijar el puerto a 1433 evita esos tres puntos y es el camino reproducible recomendado,
> no el único. (En el dev nativo `DB_PORT=` vacío funciona porque PHP corre en el mismo
> Windows que SQL Server, donde la resolución por nombre es trivial.)

#### Vite y auto-refresh en Docker Desktop (Windows)

WSL2 no propaga eventos `inotify` al filesystem del contenedor, así que Vite nunca
detecta cambios sin polling. `vite.config.js` tiene:

```js
server: {
    host: '0.0.0.0',       // escucha en todas las interfaces del contenedor
    hmr: { host: 'localhost', port: 5173 },  // el browser se conecta al host mapeado
    watch: { usePolling: true, interval: 1000 },
}
```

Al cargar la página por primera vez después de levantar los servicios, el browser
recibe el cliente HMR de `localhost:5173` y establece el WebSocket. A partir de ahí
cualquier cambio en Blade, CSS o JS dispara la recarga en ~1 segundo.

Para confirmar que está conectado: DevTools → Network → WS → debe verse una conexión
a `localhost:5173`.

#### Diferencias entre `compose.dev.yaml` y `compose.prod-local.yaml`

| | `compose.dev.yaml` (`:8000`) | `compose.prod-local.yaml` (`:8001`) |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `config:cache` / `view:cache` | No (se limpian) | Sí (se generan en entrypoint) |
| `opcache.validate_timestamps` | `1` (detecta cambios) | `0` (máxima performance) |
| Código fuente | bind mount (cambios inmediatos) | baked en la imagen |
| Assets CSS/JS | Vite dev server en `:5173` | `npm run build` dentro de la imagen |
| Cambios PHP | Visibles al instante | Requieren `--build` |
| Cuándo usarlo | Desarrollo diario | Validar que la imagen de prod funciona |

### `compose.prod-local.yaml` — probar la imagen de producción localmente

Construye la imagen exacta de producción (sin bind mounts, con `config:cache`,
`opcache` agresivo, assets compilados) apuntando al SQL Server del host. Útil para
detectar problemas que solo se manifiestan en modo `production` antes de hacer un deploy.

```powershell
# Primera vez o cuando cambia el código:
docker compose -f compose.prod-local.yaml up --build

# App → http://localhost:8001  (puerto distinto para no chocar con :8000 de dev)
```

Reutiliza el `.env` local pero sobreescribe `APP_ENV=production`, `APP_DEBUG=false`,
`APP_URL=http://localhost:8001` y la conexión a la DB. No tiene Vite ni bind mounts.

---

## 6. Topología — dos entornos, un solo host

Producción y staging conviven en el mismo host. Un único nginx edge rutea por
`server_name`. Cada entorno tiene su propio par blue/green y su propio worker.

```
                        Internet :80/:443
                               │
               ┌───────────────▼────────────────┐  compose.edge.yaml (persistente, único)
               │         nginx EDGE              │  balanza-edge
               │  server_name=balanza.dom.com    │
               │  server_name=staging.balanza.…  │
               └──────────┬────────────┬─────────┘
                          │            │
               prod/active │            │ staging/active
               upstream.conf│            │upstream.conf
                          ▼            ▼
              ┌───────────────┐   ┌─────────────────┐
              │  PRODUCCIÓN   │   │    STAGING        │
              │ blue  :8081   │   │ blue  :8083       │  compose.prod.yaml
              │ green :8082   │   │ green :8084       │  ENV_PREFIX=prod|staging
              └──────┬────────┘   └────────┬──────────┘
                     ▼                     ▼
         ┌──────────────────┐  ┌──────────────────────┐  compose.worker.yaml
         │ balanza-prod-    │  │ balanza-staging-      │  ENV_PREFIX=prod|staging
         │ worker           │  │ worker                │  queue:work + schedule:work
         └────────┬─────────┘  └──────────┬────────────┘
                  └───────────────┬────────┘
                                  ▼
              SQL Server externo compartido
              (prefijo de tabla por entorno: prod_ / stg_)
```

### Nombres de contenedores y puertos

| Entorno | Color | Contenedor | Puerto loopback |
|---------|-------|-----------|-----------------|
| prod | blue | `balanza-prod-app-blue` | `127.0.0.1:8081` |
| prod | green | `balanza-prod-app-green` | `127.0.0.1:8082` |
| staging | blue | `balanza-staging-app-blue` | `127.0.0.1:8083` |
| staging | green | `balanza-staging-app-green` | `127.0.0.1:8084` |
| prod | — | `balanza-prod-worker` | — |
| staging | — | `balanza-staging-worker` | — |
| edge | — | `balanza-edge` | `0.0.0.0:80` |

### Variables de parametrización de `compose.prod.yaml` y `compose.worker.yaml`

| Variable | prod | staging |
|----------|------|---------|
| `ENV_PREFIX` | `prod` | `staging` |
| `HTTP_PORT` | `8081` ó `8082` | `8083` ó `8084` |
| `COLOR` | `blue` ó `green` | `blue` ó `green` |
| `TAG` | SHA del commit | SHA del commit |
| `env_file` usado | `.env.prod` | `.env.staging` |

**Por qué un worker por entorno:** si staging y prod compartieran scheduler, los
reportes programados podrían dispararse dos veces contra distintas bases de datos.
Cada entorno necesita su propio ciclo de jobs aislado.

---

## 7. Los dos nginx (el de la app y el edge)

La confusión clásica del blue-green es mezclarlos. Son **roles distintos**:

| | nginx de la **app** | nginx **edge** / router |
|--|--------------------|-------------------------|
| Archivo | `docker/nginx/default.conf` | `docker/edge/default.conf.template` + `upstream-*.conf` |
| Dónde vive | dentro de cada contenedor app | host, contenedor `balanza-edge` persistente |
| ¿En la imagen? | **Sí**, horneado | **No** — sobrevive a los swaps |
| Qué hace | sirve `/public`, FastCGI a php-fpm | TLS, conmuta blue↔green, balancea |

El edge hace el blue-green así:

```
default.conf.template  →  include /etc/nginx/edge/prod/active-upstream.conf
   (renderizado a            include /etc/nginx/edge/staging/active-upstream.conf
    conf.d/default.conf                       │
    al arrancar)        <env>/active-upstream.conf  =  copia de  upstream-blue.conf | upstream-green.conf
                                          │
deploy.sh:  cp <env>/upstream-green.conf <env>/active-upstream.conf  &&  nginx -s reload
```

El `reload` es **graceful**: nginx termina las requests en vuelo con la config vieja
y atiende las nuevas con la nueva. Cero conexiones cortadas.

> **Dominios — única fuente de verdad: `.env.edge`.** Los `server_name` del edge no
> están hardcodeados: el template usa `${APP_DOMAIN}` (prod) y `${STAGE_DOMAIN}` (staging),
> que el entrypoint del image de nginx sustituye con `envsubst` al arrancar, leyéndolos de
> `.env.edge` (en el host, no se commitea — sobrevive al `git reset --hard` del deploy).
> `NGINX_ENVSUBST_FILTER` limita la sustitución a esas dos variables para no tocar las de
> nginx (`$host`, `$scheme`, …). **El cutover (`nginx -s reload`) NO re-renderiza el
> template** — solo recarga los `include`. Para cambiar un dominio: editar `.env.edge` y
> recrear el edge: `docker compose -p app-edge -f compose.edge.yaml up -d`.

---

## 8. El script de deploy

`docker/deploy.sh <TAG> [prod|staging]` corre **en el host** (lo invoca el workflow
por SSH). El segundo argumento determina el entorno; por defecto es `prod`.

```
1. docker login ghcr.io + docker pull <imagen>:<TAG>
2. Detecta color ACTIVO (lee docker/edge/<ENV_PREFIX>/active-upstream.conf)
3. Migraciones: BLOQUEADAS — el deploy nunca toca el schema (ver más abajo)
4. Levanta el color INACTIVO con la imagen nueva
5. Health-check loop:  curl http://127.0.0.1:<puerto>/up   (timeout 60s)
      └─ si falla → baja el inactivo y ABORTA (el viejo sigue sirviendo)
6. CUTOVER:  cp docker/edge/<ENV_PREFIX>/upstream-<inactivo>.conf active-upstream.conf
             docker exec balanza-edge nginx -t && nginx -s reload
7. Baja el color viejo
8. Actualiza el worker del entorno a la imagen nueva
9. docker image prune
```

**Ejemplos de invocación:**

```bash
# Deploy a producción (desde main):
bash docker/deploy.sh abc1234 prod

# Deploy a staging (desde rama staging):
bash docker/deploy.sh abc1234 staging
```

**Garantía de cero-downtime:** si el color nuevo no pasa el health-check, no hay
cutover; el deploy aborta dejando el color viejo intacto y sirviendo. El entorno
opuesto (ej: staging) no se ve afectado en ningún caso.

**Migraciones — siempre manuales, sin excepción.** El deploy no tiene ningún mecanismo
para migrar (ni opt-in ni opt-out). Cuando un release trae cambios de schema, el operador
los aplica manualmente antes o después del deploy:

```bash
# Migrar en producción:
ENV_PREFIX=prod COLOR=blue HTTP_PORT=8081 TAG=latest \
  docker compose -p prod-blue -f compose.prod.yaml run --rm app php artisan migrate --force

# Migrar en staging:
ENV_PREFIX=staging COLOR=blue HTTP_PORT=8083 TAG=latest \
  docker compose -p staging-blue -f compose.prod.yaml run --rm app php artisan migrate --force
```

> **Bind-mount safe:** el cutover usa `cp` (sobrescribe el inodo in-place), no `mv`
> ni symlink, para que el contenedor edge vea el cambio sin re-montar.

---

## 9. CI/CD con GitHub Actions

### Gate de calidad — local en `pre-push`, no en CI

El gate (pint + larastan + tests con cobertura ≥ 68% sobre SQL Server) **no corre
en GitHub Actions**: se ejecuta localmente antes de cada push mediante el hook
[`.githooks/pre-push`](../.githooks/pre-push), que bloquea el push si algo falla.

```
git config core.hooksPath .githooks      # activar una vez por clon
─ php vendor/bin/pint --test
─ php vendor/bin/phpstan analyse --memory-limit=512M
─ php artisan test --coverage --min=68
```

Equivale a `composer check`. El push asume código ya verificado, así que el deploy
no vuelve a correr el gate en la nube (evita levantar un SQL Server efímero por cada
merge y duplicar el costo del que ya pasó localmente).

### `deploy.yml` — despliegue (push a `main` o `staging`)

```
on: push  →  branches: [main, staging]

concurrency: deploy-<rama>   (prod y staging son independientes, no se bloquean)

job build   → resuelve ENV_PREFIX (main→prod, staging→staging)
            → docker build → push a ghcr.io/<repo>:<sha>
                                         y :<ENV_PREFIX>-latest
job deploy  → appleboy/ssh-action → en el host:
                git reset --hard <sha>
                GHCR_TOKEN=… bash docker/deploy.sh <sha> <ENV_PREFIX>
```

El job `deploy` usa `environment: prod` o `environment: staging` (GitHub Environments),
lo que permite definir secrets distintos por entorno si la VPS es diferente.

---

## 10. Setup inicial del servidor (runbook)

Se hace **una sola vez**. Después, cada push a `main`/`staging` despliega solo.

El setup son **4 fases, en este orden**, y no todas ocurren en la VPS:

| Fase | Dónde | Qué |
|------|-------|-----|
| **[0 · Repo](#fase-0--preparar-el-repo-y-poblar-ghcr)** | Tu máquina + GitHub | Dominios reales + primer push (puebla GHCR con la imagen) |
| **[1 · Acceso](#fase-1--clave-ssh-y-github-secrets)** | Tu máquina + web de GitHub | Par de claves SSH + GitHub Secrets |
| **[2 · Bootstrap VPS](#fase-2--bootstrap-de-la-vps)** | VPS | git/docker, repo, `.env`, red, edge — el "piso fijo" |
| **[3 · Primer deploy](#fase-3--primer-deploy)** | VPS | `deploy.sh` prod + staging + migraciones |
| **[4 · En adelante](#fase-4--deploys-posteriores-automáticos)** | Automático | Push → deploy |

> **El orden no se puede alterar:** la imagen debe existir en GHCR (fase 0) antes del
> primer deploy (fase 3); y dentro de la VPS el orden es **red → edge → deploy** — el
> edge necesita la red para arrancar, y el primer deploy necesita el edge arriba para el
> cutover. Saltarse ese orden hace fallar el arranque.

---

### Fase 0 — Preparar el repo y poblar GHCR

*(en tu máquina / el repo, una sola vez)*

**1. Dominios → `.env.edge` en el servidor.** Los `server_name` del edge ya **no** se
editan en ningún archivo del repo: el template `docker/edge/default.conf.template` usa
`${APP_DOMAIN}` / `${STAGE_DOMAIN}` y los toma de `.env.edge`. Este paso se hace en la
[fase 2.6](#fase-2--instalar-el-edge) (copiar `.env.edge.example` → `.env.edge` y completar).

> **Por qué en `.env.edge` y no en el repo.** El deploy hace `git reset --hard` en el
> servidor; un archivo trackeado con el dominio se sobrescribiría en cada deploy. `.env.edge`
> **no** está trackeado (igual que `.env.prod` / `.env.staging`), así que sobrevive y permite
> cambiar el dominio sin un commit ni un redeploy — ideal mientras se prueba con un dominio
> provisorio. Solo hay que recrear el edge para re-renderizar el template (ver fase 2.6).

**2. Primer push** a `main` y `staging`:

```bash
git push origin main
git push origin staging
```

Esto dispara el workflow, que **construye y sube las imágenes a GHCR** (`:prod-latest` y
`:staging-latest`). El job de *deploy* va a **fallar** —la VPS todavía no está lista—, y
está bien: de este push solo necesitás que la imagen quede publicada. El primer deploy
real es manual, en la [fase 3](#fase-3--primer-deploy).

---

### Fase 1 — Clave SSH y GitHub Secrets

*(en tu máquina y en la web de GitHub, una sola vez)*

GitHub Actions entra a la VPS por SSH sin que vos estés. Para eso necesita una **clave
privada** (guardada como secret) cuya mitad **pública** vive en la VPS. Instalar la
pública solo requiere el acceso SSH que ya te dio el proveedor del servidor — no hace
falta tener Docker ni el repo todavía.

Una clave SSH son **dos archivos que van juntos**:

- 🔒 **Privada** — secreta, nunca se comparte. La tiene **quien se conecta** (el runner de GitHub).
- 🔑 **Pública** — se reparte libremente. Va en **la máquina a la que te conectás** (la VPS).

```
   GitHub Actions runner                          VPS (servidor)
   ┌─────────────────────┐                      ┌────────────────────────┐
   │  Secret SSH_KEY      │   ssh usuario@IP     │ ~/.ssh/authorized_keys │
   │  = CLAVE PRIVADA 🔒  │ ───────────────────► │ = CLAVE PÚBLICA 🔑     │
   └─────────────────────┘   firma con privada   └────────────────────────┘
                              server verifica con la pública
```

> **Regla de oro:** la **pública** va en la máquina a la que entrás (la VPS). La **privada** se queda con quien entra (el Secret `SSH_KEY`). La pública **no** es un secret.

**1.1 — Generar un par dedicado** (no reuses tu clave personal; así es revocable sin afectar otros accesos):

```bash
ssh-keygen -t ed25519 -C "deploy-balanza-ci" -f "$HOME\.ssh\balanza_deploy"
# Cuando pida passphrase → Enter dos veces (sin passphrase: el runner no puede tipearla).
```

> En Windows (PowerShell) la ruta es `$env:USERPROFILE\.ssh\balanza_deploy`; el resto del comando es igual.

Genera dos archivos: `balanza_deploy` (🔒 privada) y `balanza_deploy.pub` (🔑 pública).

**1.2 — Instalar la pública en la VPS** — agregar la línea **real** de `balanza_deploy.pub` al `authorized_keys` del `SSH_USER`:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
cat ~/.ssh/balanza_deploy.pub >> ~/.ssh/authorized_keys   # el contenido real, NO el texto de ejemplo
chmod 600 ~/.ssh/authorized_keys
```

> Si generaste el par **en la VPS**, después de copiar la privada al secret (paso 1.3) borrala del server con `rm ~/.ssh/balanza_deploy`. La privada no debe quedar en la máquina que desbloquea.

**1.3 — Cargar la privada en el Secret `SSH_KEY`** — copiar el contenido **completo** de `balanza_deploy`, incluyendo las líneas `-----BEGIN OPENSSH PRIVATE KEY-----` y `-----END OPENSSH PRIVATE KEY-----`, y pegarlo en el secret.

**1.4 — Probar el par antes de confiar en el runner:**

```bash
ssh -i ~/.ssh/balanza_deploy <SSH_USER>@<SSH_HOST>
```

Si entra **sin pedir contraseña**, el par está bien y el runner va a poder igual.

| Síntoma | Causa |
|---|---|
| `Permission denied (publickey)` | La pública no quedó en `authorized_keys`, o permisos mal (`.ssh` = 700, `authorized_keys` = 600) |
| El runner pide contraseña / cuelga | La privada tiene passphrase, o el Secret quedó incompleto (sin las líneas BEGIN/END) |
| Funciona local pero no el runner | Se pegó solo parte de la privada en el secret |

**1.5 — Cargar el resto de los Secrets** en el repo → **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Valor |
|--------|-------|
| `SSH_HOST` | IP pública de la VPS |
| `SSH_USER` | usuario SSH (ej: `root`, `ubuntu`, `deploy`) |
| `SSH_KEY` | la **clave privada** del paso 1.3 |
| `SSH_PORT` | opcional, si no es 22 |
| `APP_DIR` | **ruta absoluta** al repo en la VPS (la obtenés al clonar, en la [fase 2](#fase-2--bootstrap-de-la-vps)), ej: `/root/infinito-reciclaje-balanza` |
| `GHCR_TOKEN` | PAT de GitHub con permiso `read:packages` (Settings → Developer settings → Personal access tokens) |

> El **push** a GHCR usa el `GITHUB_TOKEN` automático del runner (permiso `packages: write`
> declarado en el workflow). El **pull** desde la VPS necesita un PAT propio (`GHCR_TOKEN`)
> porque el `GITHUB_TOKEN` del runner es efímero y no tiene acceso fuera del job.

> **`APP_DIR`** es la ruta absoluta donde clonás el repo (fase 2). El deploy hace
> `cd $APP_DIR` antes de `git reset --hard` y `docker/deploy.sh`, así que debe apuntar a
> la carpeta con `.git/`, `docker/` y los `compose.*.yaml`. Si no lo seteás, el workflow
> usa `~/infinito-reciclaje-balanza` por default. Siempre absoluta, nunca relativa, y
> accesible por el `SSH_USER`.

### Fase 2 — Bootstrap de la VPS

*(en el servidor, una sola vez — este es el "piso fijo" que el workflow no recrea)*

**2.1 — Instalar git y Docker:**

```bash
# git: las imágenes cloud minimal no lo traen, y el host lo necesita tanto para
# el clone inicial como para el deploy automático (git fetch + git reset --hard).
sudo apt-get update && sudo apt-get install -y git

curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker    # aplicar sin cerrar sesión
```

**2.2 — Clonar el repo** (y anotar la ruta → es el secret `APP_DIR` de la fase 1):

```bash
git clone https://github.com/nfernandez-evolvere/infinito-reciclaje-balanza.git
cd infinito-reciclaje-balanza
pwd     # → ej. /root/infinito-reciclaje-balanza — ese string es APP_DIR
```

**2.3 — Crear los `.env` de cada entorno** (no se commitean: viven solo en el server):

```bash
cp .env.docker.example  .env.prod      && nano .env.prod
cp .env.staging.example .env.staging   && nano .env.staging
```

Variables críticas de `.env.prod`:

| Variable | Cómo obtenerla |
|----------|----------------|
| `APP_KEY` | `docker run --rm ghcr.io/nfernandez-evolvere/infinito-reciclaje-balanza:prod-latest php artisan key:generate --show` (la imagen ya está en GHCR desde la fase 0) |
| `APP_URL` | URL pública, ej: `https://balanza.tudominio.com` |
| `DB_HOST` | IP o hostname del SQL Server compartido |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | credenciales del SQL Server |
| `RESEND_KEY` | API key de Resend para el envío de emails |

`.env.staging` es igual pero **nunca comparte base ni clave** con prod: `APP_URL` de
staging, `DB_DATABASE` propia, `DB_TABLE_PREFIX=stg_` y un `APP_KEY` distinto (generá
otro con el mismo comando). Ver `.env.docker.example` / `.env.staging.example` y la
[sección 11](#11-secrets-y-variables-de-entorno) para la lista completa.

**2.4 — Crear la red compartida** (el edge la necesita para arrancar):

```bash
docker network create balanza-net
```

Por esta red se comunican todos los contenedores (`balanza-edge`,
`balanza-prod-app-blue/green`, `balanza-staging-app-blue/green`, `balanza-prod-worker`,
`balanza-staging-worker`). Es `external`: existe fuera de cualquier compose para que un
`docker compose down` no la borre y deje incomunicados a los demás.

**2.5 — Login a GHCR** (para que la VPS pueda bajar la imagen):

```bash
export GHCR_TOKEN="<PAT con read:packages>"
echo "$GHCR_TOKEN" | docker login ghcr.io -u <usuario-github> --password-stdin
```

**2.6 — Definir los dominios y levantar el edge** (el router del blue-green):

```bash
# Dominios del edge (única fuente de verdad — no se commitea):
cp .env.edge.example .env.edge   && nano .env.edge   # completar APP_DOMAIN y STAGE_DOMAIN

docker compose -p app-edge -f compose.edge.yaml up -d
```

Es **permanente**: no participa del swap blue-green y nunca se baja en deploys normales.
Es el único que escucha en el puerto 80 (y 443 si hay TLS). Necesita la red del paso 2.4.

Al arrancar, el entrypoint de nginx renderiza `default.conf.template` sustituyendo
`APP_DOMAIN` / `STAGE_DOMAIN` desde `.env.edge`, y deja servidos los dos dominios
(prod + staging). **Si cambiás un dominio después**, editá `.env.edge` y volvé a correr
`docker compose -p app-edge -f compose.edge.yaml up -d` (recrea el contenedor y re-renderiza
el template — un `nginx -s reload` no alcanza).

---

### Fase 3 — Primer deploy

*(en el servidor, una sola vez — el `deploy.sh` se corre a mano solo esta primera vez)*

**3.1 — Producción:**

```bash
GHCR_TOKEN="$GHCR_TOKEN" GHCR_USER="<usuario-github>" \
  bash docker/deploy.sh prod-latest prod
```

Hace pull de la imagen, levanta blue en `:8081`, health-check en `/up`, conmuta el edge
al upstream de prod y levanta el worker de prod. Después, **por separado**, aplicás las
migraciones (siempre manuales — la base es compartida):

```bash
docker exec balanza-prod-app-blue php artisan migrate --force
```

**3.2 — Staging:**

```bash
GHCR_TOKEN="$GHCR_TOKEN" GHCR_USER="<usuario-github>" \
  bash docker/deploy.sh staging-latest staging

docker exec balanza-staging-app-blue php artisan migrate --force
```

Staging queda servido por el mismo edge en `https://staging.balanza.tudominio.com`.

---

### Fase 4 — Deploys posteriores (automáticos)

Con el piso ya armado, cada push despliega solo:

| Push a | Despliega en | Tags de imagen |
|--------|-------------|----------------|
| `main` | producción | `:<sha>` + `:prod-latest` |
| `staging` | staging | `:<sha>` + `:staging-latest` |

Ambos deploys son independientes — uno no bloquea ni afecta al otro. Lo único que volvés
a hacer a mano en la VPS son las **migraciones**, cuando un release trae cambios de schema:

```bash
docker exec balanza-prod-app-blue php artisan migrate --force
docker exec balanza-staging-app-blue php artisan migrate --force
```

---

## 11. Secrets y variables de entorno

### GitHub Environments y Secrets

El workflow usa `environment: prod` o `environment: staging` para el job de deploy.
Esto habilita **GitHub Environments** (Settings → Environments), que permiten:
- Secrets distintos por entorno (útil si staging y prod están en VPS diferentes)
- Reglas de aprobación manual antes de deploys a prod
- Historial de deployments por entorno en la UI de GitHub

**Secrets a configurar** (Settings → Environments → `prod` / `staging`):

| Secret | Uso |
|--------|-----|
| `SSH_HOST` | IP o hostname de la VPS |
| `SSH_USER` | usuario SSH |
| `SSH_KEY` | clave privada SSH (el public key en `~/.ssh/authorized_keys` de la VPS) |
| `SSH_PORT` | opcional, default 22 |
| `APP_DIR` | ruta del repo en la VPS, ej: `/home/ubuntu/infinito-reciclaje-balanza` |
| `GHCR_TOKEN` | PAT con `read:packages` para pull desde el host |

Si prod y staging están en el **mismo VPS**, los secrets pueden ser idénticos en ambos
environments. Si están en VPS distintas, cada environment tiene sus propios valores.

### `.env.prod` y `.env.staging` (en el server, NO se commitean)

| Variable crítica | prod | staging |
|-----------------|------|---------|
| `APP_URL` | `https://balanza.tudominio.com` | `https://staging.balanza.tudominio.com` |
| `DB_DATABASE` | base de producción | base de staging (nunca la misma) |
| `DB_TABLE_PREFIX` | `prod_` | `stg_` |
| `LOG_LEVEL` | `warning` | `debug` |
| `MAIL_MAILER` | Resend (emails reales) | Mailtrap o Resend con dominio de test |
| `APP_KEY` | clave propia de prod | clave propia de staging (distinta) |

Ver `.env.docker.example` y `.env.staging.example` para la lista completa.

---

## 12. Gotchas y troubleshooting

| Síntoma | Causa | Solución |
|---------|-------|----------|
| `could not find driver` / no conecta a la DB | faltaba `pdo_sqlsrv` | ya incluido; verificar con `docker run --rm <img> php -m \| grep sqlsrv` |
| Error SSL al conectar a SQL Server | `msodbcsql18` cifra por defecto | `DB_ENCRYPT=no` o `DB_TRUST_SERVER_CERTIFICATE=yes` en `.env` |
| Dev no conecta a SQLEXPRESS | instancia nombrada no resoluble desde el contenedor | TCP/IP + puerto 1433 estático en SQLEXPRESS; `DB_HOST=host.docker.internal` |
| Worker marcado `unhealthy` | HEALTHCHECK hace `curl :8080/up`; el worker no corre nginx | `healthcheck: disable: true` en el servicio worker del compose (ya aplicado) |
| `supervisorctl` devuelve `does not include supervisorctl section` | faltaban `[unix_http_server]` y `[supervisorctl]` en el conf | ya incluidas en `web.conf` y `worker.conf`; verificar con `docker exec <contenedor> supervisorctl status` |
| Emails/reportes no se envían | worker no levantado, scheduler no corriendo, o `RESEND_KEY` no seteada | ver diagnóstico de emails más abajo |
| Reportes duplicados | dos schedulers corriendo | el scheduler va **solo** en el worker único, no en blue/green |
| El cutover no cambia el tráfico | edge no recargó | `docker exec balanza-edge nginx -t && docker exec balanza-edge nginx -s reload` |
| Deploy aborta en health-check | el color nuevo no levanta `/up` | revisar `docker logs balanza-<entorno>-app-<color>`; el viejo sigue sirviendo |
| `route:cache` falla | rutas con Closure | no se cachean rutas a propósito; nunca ejecutar `route:cache` |
| Build de la imagen en Mac ARM | ODBC + mssql exigen amd64 | `docker build --platform=linux/amd64` |
| Auto-refresh Vite no funciona (Windows) | WSL2 no propaga inotify al bind mount | `usePolling: true, interval: 1000` en `vite.config.js` (ya configurado) |
| `Class "Laravel\Pail\PailServiceProvider" not found` | `packages.php` del host (con require-dev) montado en el contenedor sin dev-deps | volumen anónimo `/app/bootstrap/cache` preserva el `packages.php` de la imagen (ya configurado en compose.dev.yaml) |

### Diagnóstico de emails/reportes no enviados

```bash
# 1. Verificar que el worker está corriendo y sus procesos están activos
docker exec <contenedor-worker> supervisorctl status
# Debe mostrar: queue RUNNING  y  scheduler RUNNING

# 2. Ver los logs del worker en tiempo real
docker logs <contenedor-worker> --tail=100 -f

# 3. Ver si hay jobs fallidos en la cola
docker exec <contenedor-worker> php artisan queue:failed

# 4. Ver las tareas programadas y cuándo se ejecutan
docker exec <contenedor-worker> php artisan schedule:list

# 5. Probar envío de email directamente
docker exec <contenedor-worker> php artisan tinker \
  --execute="Mail::raw('test', fn(\$m) => \$m->to('tu@email.com')->subject('test'));"
```

### Comandos útiles

```bash
# Ver qué color está activo (por entorno)
cat docker/edge/prod/active-upstream.conf
cat docker/edge/staging/active-upstream.conf

# Estado de todos los contenedores del proyecto
docker ps --filter name=balanza --format "table {{.Names}}\t{{.Status}}\t{{.Image}}"

# Logs de un color / del worker / del edge (ejemplos con prod)
docker logs -f balanza-prod-app-blue
docker logs -f balanza-prod-worker
docker logs -f balanza-edge

# Reintentar jobs fallidos
docker exec balanza-prod-worker php artisan queue:retry all

# Rollback rápido al color anterior (sin deploy) — ver §13 para ambos entornos
ENV_PREFIX=prod COLOR=blue HTTP_PORT=8081 TAG=<sha-anterior> \
  docker compose -p prod-blue -f compose.prod.yaml up -d
cp docker/edge/prod/upstream-blue.conf docker/edge/prod/active-upstream.conf
docker exec balanza-edge nginx -s reload
```

---

## 13. Cómo extender (TLS, escalado, rollback)

- **TLS / HTTPS**: descomentar el bloque `server { listen 443 ssl … }` en
  `docker/edge/default.conf.template` (su `server_name` ya usa `${APP_DOMAIN}`), montar los
  certificados en `docker/edge/certs/` y abrir el `443` en `compose.edge.yaml`. (Alternativa:
  poner Caddy/Traefik como edge para certificados automáticos de Let's Encrypt.)
- **Rollback manual**: la imagen anterior queda en el host tras el deploy. Para volver:
  ```bash
  # Rollback en prod a blue con SHA anterior:
  ENV_PREFIX=prod COLOR=blue HTTP_PORT=8081 TAG=<sha-anterior> \
    docker compose -p prod-blue -f compose.prod.yaml up -d
  cp docker/edge/prod/upstream-blue.conf docker/edge/prod/active-upstream.conf
  docker exec balanza-edge nginx -s reload

  # Rollback en staging:
  ENV_PREFIX=staging COLOR=blue HTTP_PORT=8083 TAG=<sha-anterior> \
    docker compose -p staging-blue -f compose.prod.yaml up -d
  cp docker/edge/staging/upstream-blue.conf docker/edge/staging/active-upstream.conf
  docker exec balanza-edge nginx -s reload
  ```
- **Escalar php-fpm**: ajustar `pm.max_children` en `docker/php-fpm/www.conf` según la
  RAM del host (regla: `max_children ≈ RAM_disponible / ~40MB por worker`).
- **Migrar a orquestador**: la separación web/worker/edge y la imagen única se trasladan
  bien a Docker Swarm o Kubernetes; el edge pasaría a ser un Ingress/Service.

---

*Plan de implementación original: `.claude/plans/lazy-tumbling-clover.md` (fuera del repo).*
