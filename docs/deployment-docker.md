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
6. [Producción — topología blue-green](#6-producción--topología-blue-green)
7. [Los dos nginx (el de la app y el edge)](#7-los-dos-nginx-el-de-la-app-y-el-edge)
8. [El script de deploy](#8-el-script-de-deploy)
9. [CI/CD con GitHub Actions](#9-cicd-con-github-actions)
10. [Setup inicial del servidor (runbook)](#10-setup-inicial-del-servidor-runbook)
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
│
├── compose.dev.yaml               # desarrollo: imagen prod contra SQL Server del host
├── compose.prod.yaml              # producción: stack WEB de un color (blue|green)
├── compose.worker.yaml            # producción: cola + scheduler (instancia única)
├── compose.edge.yaml              # producción: nginx router persistente (blue-green)
│
├── docker/
│   ├── entrypoint.sh              # prepara la app y arranca el CMD
│   ├── deploy.sh                  # orquesta el swap blue-green (corre en el host)
│   ├── nginx/default.conf         # nginx de la APP (horneado en la imagen)
│   ├── php/php.ini                # ajustes PHP de producción
│   ├── php/opcache.ini            # OPcache (validate_timestamps=0)
│   ├── php-fpm/www.conf           # pool php-fpm (listen 127.0.0.1:9000)
│   ├── supervisor/web.conf        # grupo WEB: nginx + php-fpm
│   ├── supervisor/worker.conf     # grupo WORKER: queue + scheduler
│   └── edge/
│       ├── nginx.conf             # nginx EDGE / router del blue-green
│       ├── upstream-blue.conf     # upstream → balanza-app-blue
│       ├── upstream-green.conf    # upstream → balanza-app-green
│       └── active-upstream.conf   # upstream ACTIVO (lo reescribe deploy.sh)
│
└── .github/workflows/
    ├── ci.yml                     # pint + larastan + tests (SQL Server service)
    └── deploy.yml                 # build/push GHCR + deploy SSH
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

### `entrypoint.sh` (corre como root antes del CMD)

1. `php artisan storage:link` (idempotente).
2. `config:cache` + `view:cache` + `event:cache`.
3. `chown` de caches a `www-data` y `exec "$@"`.

> **Migraciones — nunca automáticas (en ningún ambiente).** La base es compartida
> entre proyectos, así que aplicar el schema es **siempre** una acción deliberada del
> operador. `AUTO_MIGRATE` queda en `false` en dev y prod, y el deploy **no** migra por
> defecto. Ver [sección 8](#8-el-script-de-deploy).

---

## 5. Desarrollo local

`compose.dev.yaml` corre **la misma imagen de producción**, construida localmente,
apuntando al **SQL Server (SQLEXPRESS) instalado en el host Windows**.

```powershell
docker compose -f compose.dev.yaml up --build
# App en http://localhost:8000
```

Dos servicios: `app` (grupo web, `APP_DEBUG=true`) y `worker` (cola + scheduler).
Ambos usan `extra_hosts: host.docker.internal:host-gateway` para alcanzar la base del
host, y ambos con `AUTO_MIGRATE=false` — el contenedor **nunca** migra solo (la base del
host es la compartida; las migraciones se corren a mano y deliberadamente).

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

---

## 6. Producción — topología blue-green

```
                    Internet :80/:443
                          │
              ┌───────────▼────────────┐   compose.edge.yaml (persistente)
              │   nginx EDGE (router)   │   balanza-edge
              │   include active-upstream.conf ─┐
              └───────────┬────────────┘        │ upstream activo
                 cutover  │ (reload)             ▼
            ┌─────────────┴─────────────┐   blue ⇄ green
            ▼                           ▼
   ┌─────────────────┐        ┌─────────────────┐   compose.prod.yaml
   │ balanza-app-blue│        │balanza-app-green│   (un proyecto compose por color)
   │   :8081 (web)   │        │  :8082 (web)    │   image: ghcr.io/.../<sha>
   └────────┬────────┘        └────────┬────────┘
            └────────────┬─────────────┘
                         ▼
              ┌─────────────────────┐   compose.worker.yaml
              │  balanza-worker     │   queue:work + schedule:work
              │  (instancia única)  │   NO participa del blue-green
              └──────────┬──────────┘
                         ▼
         SQL Server externo compartido (prefijo de tabla del proyecto)
```

- Cada **color** es un proyecto compose independiente (`-p app-blue` / `-p app-green`)
  con la misma imagen y distinto puerto loopback (8081/8082) y `container_name`.
- El **worker** es único y persistente; se actualiza tras el cutover (su reinicio es
  un parpadeo invisible — los jobs se reintentan).
- Todos comparten la red Docker externa **`balanza-net`** para resolverse por nombre.

**Por qué el worker separado:** si blue y green corrieran cada uno `schedule:work`
contra la DB compartida, los reportes se enviarían **dos veces** durante el solape.

---

## 7. Los dos nginx (el de la app y el edge)

La confusión clásica del blue-green es mezclarlos. Son **roles distintos**:

| | nginx de la **app** | nginx **edge** / router |
|--|--------------------|-------------------------|
| Archivo | `docker/nginx/default.conf` | `docker/edge/nginx.conf` + `upstream-*.conf` |
| Dónde vive | dentro de cada contenedor app | host, contenedor `balanza-edge` persistente |
| ¿En la imagen? | **Sí**, horneado | **No** — sobrevive a los swaps |
| Qué hace | sirve `/public`, FastCGI a php-fpm | TLS, conmuta blue↔green, balancea |

El edge hace el blue-green así:

```
docker/edge/nginx.conf  →  include /etc/nginx/edge/active-upstream.conf
                                          │
active-upstream.conf  =  copia de  upstream-blue.conf  |  upstream-green.conf
                                          │
deploy.sh:  cp upstream-green.conf active-upstream.conf  &&  nginx -s reload
```

El `reload` es **graceful**: nginx termina las requests en vuelo con la config vieja
y atiende las nuevas con la nueva. Cero conexiones cortadas.

---

## 8. El script de deploy

`docker/deploy.sh <TAG>` corre **en el host** (lo invoca el workflow por SSH). Flujo:

```
1. docker login ghcr.io + docker pull <imagen>:<TAG>
2. Detecta color ACTIVO (lee active-upstream.conf) → INACTIVO = el otro
3. Migraciones: SOLO si RUN_MIGRATIONS=true (opt-in deliberado). Por defecto NO migra
4. Levanta el color INACTIVO con la imagen nueva
5. Health-check loop:  curl http://127.0.0.1:<puerto>/up   (timeout 60s)
      └─ si falla → baja el inactivo y ABORTA (el viejo sigue sirviendo)
6. CUTOVER:  cp upstream-<inactivo>.conf active-upstream.conf
             docker exec balanza-edge nginx -t && nginx -s reload
7. Baja el color viejo
8. Actualiza el worker a la imagen nueva
9. docker image prune
```

**Garantía de cero-downtime:** si el color nuevo no pasa el health-check, no hay
cutover; el deploy aborta dejando el color viejo intacto y sirviendo.

**Migraciones — nunca automáticas.** Por defecto el deploy **no toca el schema** (la
base es compartida entre proyectos; aplicarlo es siempre deliberado). Cuando un release
incluya migraciones —ya revisadas y backward-compatible (expand/contract)— se corre el
deploy con el opt-in explícito:

```bash
RUN_MIGRATIONS=true bash docker/deploy.sh <TAG>
```

Alternativa equivalente, fuera del deploy, en cualquier momento controlado:

```bash
docker compose -p app-blue -f compose.prod.yaml run --rm app php artisan migrate
```

> **Bind-mount safe:** el cutover usa `cp` (sobrescribe el inodo in-place), no `mv`
> ni symlink, para que el contenedor edge vea el cambio sin re-montar.

---

## 9. CI/CD con GitHub Actions

### `ci.yml` — gate de calidad (en cada PR, y reusable)

```
services: mssql (mcr.microsoft.com/mssql/server:2022)
─ setup-php 8.4 (sqlsrv, pdo_sqlsrv, …, coverage: pcov)
─ composer install
─ cp .env.testing.example .env.testing      # .env.testing está gitignoreado
─ esperar SQL Server + crear DB infinito_balanza_testing (vía PDO)
─ pint --test
─ composer analyse        (larastan)
─ php artisan test --coverage --min=68
```

Las variables `DB_*` se pasan por `env:` del job y **pisan** a `.env.testing`
(phpdotenv en modo immutable no sobrescribe variables ya presentes en el entorno),
apuntando la suite al service container en vez de la instancia nombrada local.
La guardia en `tests/TestCase.php` exige que el nombre de la base contenga "test".

### `deploy.yml` — despliegue (en push a `main`)

```
job tests   → uses: ./.github/workflows/ci.yml   (corre el gate completo)
job build   → docker build → push a ghcr.io/<repo>:<sha> y :latest  (cache gha)
job deploy  → appleboy/ssh-action → en el host:
                git reset --hard <sha>
                GHCR_TOKEN=… bash docker/deploy.sh <sha>
```

`concurrency: deploy-production` con `cancel-in-progress: false` → nunca se cancela
un deploy a mitad del cutover. Como `ci.yml` solo se dispara en `pull_request` y
`workflow_call`, no hay corridas duplicadas al mergear.

> **Branch protection recomendado:** en `main`, exigir el check **CI** para poder
> mergear. Así el gate corre en el PR y el deploy asume código ya verificado.

---

## 10. Setup inicial del servidor (runbook)

En el host Linux de producción, **una sola vez**:

```bash
# 1. Clonar el repo (trae compose, docker/edge, deploy.sh)
git clone https://github.com/nfernandez-evolvere/infinito-reciclaje-balanza.git
cd infinito-reciclaje-balanza

# 2. Red compartida entre edge / blue / green / worker
docker network create balanza-net

# 3. Variables de entorno de producción
cp .env.docker.example .env.prod
#   editar .env.prod: APP_KEY, DB_HOST/DATABASE/USERNAME/PASSWORD, RESEND_KEY, …
#   APP_KEY:  docker run --rm <imagen> php artisan key:generate --show

# 4. Login a GHCR (PAT con read:packages)
echo "$GHCR_TOKEN" | docker login ghcr.io -u <usuario> --password-stdin

# 5. Edge persistente (router del blue-green)
docker compose -p app-edge -f compose.edge.yaml up -d

# 6. Primer deploy. El schema arranca vacío, así que ESTE deploy sí migra
#    (paso deliberado, opt-in explícito):
RUN_MIGRATIONS=true GHCR_TOKEN=… GHCR_USER=… bash docker/deploy.sh <sha-o-latest>
```

A partir de acá cada push a `main` despliega solo, **sin tocar el schema**. Cuando un
release traiga migraciones, se corre ese deploy con `RUN_MIGRATIONS=true` de forma
deliberada (ver sección 8).

---

## 11. Secrets y variables de entorno

### GitHub → Settings → Secrets and variables → Actions

| Secret | Uso |
|--------|-----|
| `SSH_HOST` | host del server de producción |
| `SSH_USER` | usuario SSH |
| `SSH_KEY` | clave privada SSH (el público va en el server) |
| `SSH_PORT` | *(opcional)* puerto SSH, default 22 |
| `APP_DIR` | *(opcional)* ruta del repo en el server |
| `GHCR_TOKEN` | PAT con `read:packages` (el host hace pull de GHCR) |

> El push a GHCR usa `GITHUB_TOKEN` automático (con `packages: write`). El **pull**
> desde el host necesita un PAT propio (`GHCR_TOKEN`) porque el `GITHUB_TOKEN` del
> runner es efímero.

### `.env.prod` (en el server, NO se commitea)

Ver `.env.docker.example`. Claves críticas: `APP_KEY`, conexión `DB_*` al SQL Server
externo, `DB_ENCRYPT`/`DB_TRUST_SERVER_CERTIFICATE` según el certificado del server,
`RESEND_KEY` para mails.

---

## 12. Gotchas y troubleshooting

| Síntoma | Causa | Solución |
|---------|-------|----------|
| `could not find driver` / no conecta a la DB | faltaba `pdo_sqlsrv` | ya incluido; verificar con `docker run --rm <img> php -m \| grep sqlsrv` |
| Error SSL al conectar a SQL Server | `msodbcsql18` cifra por defecto | `DB_ENCRYPT=no` o `DB_TRUST_SERVER_CERTIFICATE=yes` |
| Dev no conecta a SQLEXPRESS | instancia nombrada no resoluble desde el contenedor | TCP/IP + puerto 1433 estático en SQLEXPRESS; `DB_HOST=host.docker.internal` |
| Reportes duplicados | dos schedulers corriendo | el scheduler va **solo** en el worker único, no en blue/green |
| El cutover no cambia el tráfico | edge no recargó | `docker exec balanza-edge nginx -t && nginx -s reload` |
| Deploy aborta en health-check | el color nuevo no levanta `/up` | revisar `docker compose -p app-<color> logs app`; el viejo sigue sirviendo |
| `route:cache` falla | rutas con Closure | no se cachean rutas a propósito; no ejecutar `route:cache` |
| Build de la imagen en Mac ARM | ODBC + mssql exigen amd64 | `docker build --platform=linux/amd64` |

Comandos útiles:

```bash
# Ver qué color está activo
grep balanza-app docker/edge/active-upstream.conf

# Logs de un color / del worker / del edge
docker compose -p app-blue   -f compose.prod.yaml   logs -f app
docker compose -p app-worker -f compose.worker.yaml logs -f worker
docker logs -f balanza-edge

# Estado de los contenedores
docker ps --filter name=balanza
```

---

## 13. Cómo extender (TLS, escalado, rollback)

- **TLS / HTTPS**: descomentar el bloque `server { listen 443 ssl … }` en
  `docker/edge/nginx.conf`, montar los certificados en `docker/edge/certs/` y abrir
  el `443` en `compose.edge.yaml`. (Alternativa: poner Caddy/Traefik como edge para
  certificados automáticos de Let's Encrypt.)
- **Rollback manual**: como el color viejo se baja pero la imagen anterior queda en
  el host, para volver atrás basta levantar el color previo con el TAG anterior y
  reapuntar el edge:
  ```bash
  COLOR=blue HTTP_PORT=8081 TAG=<sha-anterior> docker compose -p app-blue -f compose.prod.yaml up -d
  cp docker/edge/upstream-blue.conf docker/edge/active-upstream.conf
  docker exec balanza-edge nginx -s reload
  ```
- **Escalar php-fpm**: ajustar `pm.max_children` en `docker/php-fpm/www.conf` según la
  RAM del host (regla: `max_children ≈ RAM_disponible / ~40MB por worker`).
- **Migrar a orquestador**: la separación web/worker/edge y la imagen única se trasladan
  bien a Docker Swarm o Kubernetes; el edge pasaría a ser un Ingress/Service.

---

*Plan de implementación original: `.claude/plans/lazy-tumbling-clover.md` (fuera del repo).*
