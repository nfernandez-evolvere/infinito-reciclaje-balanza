# Deploy desde cero en una VPS nueva
## Sistema de Gestión de Balanza · Infinito Reciclaje

> **Tipo de documento**: guía operativa **desde cero**. Parte de una VPS recién
> aprovisionada (solo acceso SSH) y termina con la app corriendo en staging y prod
> detrás de Cloudflare. Es la **checklist ejecutable**, con los valores concretos de
> esta instalación y el troubleshooting de los errores reales que aparecieron.
> Para el "por qué" de la arquitectura, ver [`09-deployment-docker.md`](09-deployment-docker.md).
>
> **Servidor de referencia**: VPS Dattaweb `179.43.124.205`, SSH en el puerto `5522`.
>
> ⚠️ Este documento contiene datos de infraestructura (IP, puertos, rutas). **Nunca** se
> escriben tokens, contraseñas ni claves privadas: esos viven solo en los Secrets de
> GitHub y en los `.env` del servidor.

---

## Lo que necesitás antes de empezar

| Requisito | Detalle |
|-----------|---------|
| VPS con acceso SSH root | Ubuntu 22.04/24.04, IP pública (acá `179.43.124.205`) |
| Repo en GitHub | `nfernandez-evolvere/infinito-reciclaje-balanza` (privado) |
| Dominio en Cloudflare | uno para prod, uno para staging (o el hostname provisorio del proveedor) |
| Acceso al SQL Server compartido | host, una base propia por entorno, usuario y contraseña |
| Git en tu máquina | para generar las claves y pushear |

---

## El orden importa — no se puede alterar

```
  Fase 0            Fase 1           Fase 2              Fase 3            Fase 4        Fase 5
 ┌────────┐       ┌────────┐       ┌──────────┐       ┌──────────┐     ┌──────────┐  ┌─────────┐
 │ Poblar │       │ Secrets│       │ Bootstrap│       │  Primer  │     │Cloudflare│  │  Deploy │
 │  GHCR  │──────►│  de    │──────►│   del    │──────►│  deploy  │────►│ DNS+SSL  │─►│  auto   │
 │ (push) │       │ GitHub │       │  server  │       │ (manual) │     │          │  │ (push)  │
 └────────┘       └────────┘       └──────────┘       └──────────┘     └──────────┘  └─────────┘
```

Dos reglas de oro del orden:

1. **La imagen debe existir en GHCR (Fase 0) antes del primer deploy (Fase 3)** — porque
   para generar el `APP_KEY` y para el pull, el server baja la imagen desde GHCR.
2. **Dentro del server: red → edge → deploy.** El edge necesita la red para arrancar, y el
   primer deploy necesita el edge arriba para el cutover.

---

## Fase 0 — Poblar GHCR con la imagen (en tu máquina)

El workflow `deploy.yml` corre en cada push a `main`/`staging`: construye la imagen, la
sube a GHCR y después intenta desplegar. En este primer push **el deploy va a fallar** (la
VPS todavía no existe como destino) y **está bien** — de acá solo necesitás que la imagen
quede publicada.

```bash
git push origin main
git push origin staging
```

Verificá en `https://github.com/users/nfernandez-evolvere/packages` que aparezca el package
`infinito-reciclaje-balanza` con los tags `:prod-latest` y `:staging-latest`.

---

## Fase 1 — Secrets de GitHub (en tu máquina + web de GitHub)

En **repo → Settings → Secrets and variables → Actions**. Si el deploy corre bajo
*Environments* (`staging`/`prod`), los secrets van **en cada environment** o como
*Repository secrets*.

| Secret | Valor |
|--------|-------|
| `SSH_HOST` | `179.43.124.205` |
| `SSH_PORT` | `5522` (si dejás el SSH en 22, omitilo — el workflow usa 22 por default) |
| `SSH_USER` | `root` |
| `SSH_KEY` | contenido **completo** de la clave privada (paso 1.1) |
| `APP_DIR` | `/root/infinito-reciclaje-balanza` (ruta absoluta del clon — fase 2.6) |
| `GHCR_TOKEN` | PAT classic con scope **`read:packages`** (paso 1.2) |

### 1.1 — Clave SSH para que Actions entre al server

**Debe ser sin passphrase** (una clave cifrada rompe la action con
`ssh: this private key is passphrase protected`). En PowerShell (Windows):

```powershell
# -N '""' = passphrase vacía (en PowerShell 5.1 las comillas anidadas son necesarias)
ssh-keygen -t ed25519 -C "deploy-balanza-ci" -f "$env:USERPROFILE\.ssh\balanza_deploy" -N '""'

# Verificar que NO tiene passphrase (imprime la pública sin preguntar nada):
ssh-keygen -y -f "$env:USERPROFILE\.ssh\balanza_deploy"

# Copiar la privada al portapapeles para pegar en el secret SSH_KEY:
Get-Content "$env:USERPROFILE\.ssh\balanza_deploy" -Raw | Set-Clipboard
```

- La **privada** (`balanza_deploy`) → secret `SSH_KEY`, completa (líneas `BEGIN/END`
  incluidas y salto de línea final).
- La **pública** (`balanza_deploy.pub`) → se instala en el server en la fase 2.3.

### 1.2 — PAT para que el server baje la imagen de GHCR

El **push** de la imagen lo hace el runner con su `GITHUB_TOKEN` automático. El **pull**
desde el server necesita un PAT propio (el del runner es efímero):

1. GitHub → **Settings → Developer settings → Personal access tokens → Tokens (classic)**.
2. **Generate new token (classic)** con **solo** el scope **`read:packages`**.
3. Pegarlo en el secret `GHCR_TOKEN`.

> Con el repo **privado**, el package también es privado: sin este PAT el pull da
> `403 Forbidden` aunque el `docker login` diga "Login Succeeded".

---

## Fase 2 — Bootstrap del server (en la VPS, una sola vez)

```bash
ssh -p 5522 root@179.43.124.205      # (un server recién aprovisionado suele estar en el 22)
```

### 2.1 — Sistema base + Docker + git

```bash
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y git
curl -fsSL https://get.docker.com | sh
```

### 2.2 — (Opcional) Endurecer el SSH al puerto 5522

Si querés mover el SSH del 22 al 5522 (como en este server), hacelo con cuidado para no
quedarte afuera:

```bash
sudo sed -i 's/^#\?Port .*/Port 5522/' /etc/ssh/sshd_config
sudo ufw allow 5522/tcp           # ABRIR el puerto nuevo ANTES de reiniciar sshd
sudo systemctl restart ssh
# NO cierres esta sesión: abrí OTRA terminal y probá `ssh -p 5522 root@179.43.124.205`.
# Solo cuando confirmes que entra, cerrá la primera.
```

Y actualizá el secret `SSH_PORT=5522` (fase 1).

### 2.3 — Instalar la clave pública de Actions

Pegá la línea real de `balanza_deploy.pub` (la de la fase 1.1) en el `authorized_keys`:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
echo "ssh-ed25519 AAAA... deploy-balanza-ci" >> ~/.ssh/authorized_keys   # ← el contenido REAL
chmod 600 ~/.ssh/authorized_keys
```

### 2.4 — ⚠️ Liberar el puerto 80

La VPS de Dattaweb viene con un **nginx del host** (systemd) ocupando el 80 (resto de la
imagen base). El edge es el único que debe escuchar ahí; si no lo liberás, el edge muere
al arrancar con `failed to bind host port 0.0.0.0:80: address already in use`.

```bash
sudo ss -lptn 'sport = :80'       # ¿quién lo tiene?
sudo systemctl stop nginx
sudo systemctl disable nginx      # ← imprescindible: sin disable vuelve tras un reboot
sudo ss -lptn 'sport = :80'       # sin output = libre
```

### 2.5 — Firewall (dos capas)

```bash
sudo ufw allow 5522/tcp           # SSH (¡no te dejes afuera!)
sudo ufw allow 80/tcp             # Cloudflare → edge (HTTP)
sudo ufw allow 443/tcp            # para cuando sumes TLS al edge
sudo ufw enable
sudo ufw status verbose
```

Y en el **panel de Dattaweb**: revisá que su firewall (capa aparte, no se ve desde el
server) tenga 80 y 443 entrantes abiertos.

### 2.6 — Deploy key del server → GitHub + clonar el repo

El deploy hace `git fetch` en el server. Con el repo privado, sin credencial falla con
`fatal: could not read Username for 'https://github.com'`. La solución estable es una
**deploy key SSH read-only** (no vence, no se rota como un PAT):

```bash
# Clave del server → GitHub
ssh-keygen -t ed25519 -f ~/.ssh/github_repo -N "" -C "balanza-server-repo"
cat ~/.ssh/github_repo.pub
# → pegarla en GitHub → repo → Settings → Deploy keys → Add deploy key
#   read-only (NO tildar "Allow write access")

cat >> ~/.ssh/config <<'EOF'
Host github.com
  IdentityFile ~/.ssh/github_repo
  IdentitiesOnly yes
EOF

# Clonar (por SSH, no HTTPS)
cd ~
git clone git@github.com:nfernandez-evolvere/infinito-reciclaje-balanza.git
# La primera vez pregunta por el host key de github.com → responder "yes"

cd infinito-reciclaje-balanza
pwd                # → /root/infinito-reciclaje-balanza  ← este string es el secret APP_DIR
git fetch --all    # smoke test: debe pasar sin pedir usuario/contraseña
```

### 2.7 — Los `.env` de cada entorno

No se commitean; viven solo en el server y sobreviven al `git reset --hard` del deploy.

```bash
cp .env.docker.example  .env.prod      && nano .env.prod
cp .env.staging.example .env.staging   && nano .env.staging
```

Variables críticas (lista completa en `09-deployment-docker.md` §11):

| Variable | Cómo obtenerla |
|----------|----------------|
| `APP_KEY` | `docker run --rm ghcr.io/nfernandez-evolvere/infinito-reciclaje-balanza:staging-latest php artisan key:generate --show` — **una distinta por entorno** (necesita la imagen de la Fase 0) |
| `APP_URL` | URL pública del entorno |
| `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | credenciales del SQL Server compartido |
| `DB_TABLE_PREFIX` | staging usa `stg_` y **base propia** — nunca comparte base ni clave con prod |
| `RESEND_KEY` | API key de Resend |
| `REVERB_APP_ID/KEY/SECRET` | valores propios por entorno (`REVERB_HOST` ya viene al contenedor en el `.example`) |

### 2.8 — Red compartida + login a GHCR

```bash
docker network create balanza-net

# Login persistente para que deploy.sh pueda hacer pull:
echo "EL_PAT_read_packages" | docker login ghcr.io -u nfernandez-evolvere --password-stdin
```

### 2.9 — Dominios y edge (el router del blue-green)

```bash
cp .env.edge.example .env.edge && nano .env.edge
# APP_DOMAIN   = dominio público de prod
# STAGE_DOMAIN = dominio público de staging (registro A en Cloudflare → 179.43.124.205)

docker compose -p app-edge -f compose.edge.yaml up -d
```

**Verificación obligatoria antes de seguir** (los tres deben dar bien):

```bash
docker ps --filter name=balanza-edge --format '{{.Names}}\t{{.Status}}'          # Up
docker inspect -f '{{range $k,$v := .NetworkSettings.Networks}}{{$k}} {{end}}' balanza-edge   # balanza-net
docker exec balanza-edge nginx -t                                                # test is successful
```

> **Gotchas del edge:**
> - Si `nginx -t` falla con `host not found in upstream`, los `active-upstream.conf` quedaron
>   apuntando a un contenedor inexistente. Restaurá los placeholders:
>   `git checkout -- docker/edge/prod/active-upstream.conf docker/edge/staging/active-upstream.conf`
>   (los placeholders `127.0.0.1:9` existen justo para que el edge levante antes del primer deploy).
> - Si cambiás `.env.edge` después, **recreá** el edge (`up -d`) — un `nginx -s reload` no
>   re-renderiza el template de dominios.

---

## Fase 3 — Primer deploy (manual, en el server)

Solo esta primera vez el `deploy.sh` se corre a mano. Exportá el PAT para el pull:

```bash
export GHCR_USER='nfernandez-evolvere'
export GHCR_TOKEN='EL_PAT_read_packages'
```

**Staging:**

```bash
bash docker/deploy.sh staging-latest staging
docker exec balanza-staging-app-blue php artisan migrate --force   # migraciones SIEMPRE manuales
```

**Producción:**

```bash
bash docker/deploy.sh prod-latest prod
docker exec balanza-prod-app-blue php artisan migrate --force
```

Cada `deploy.sh` hace: pull de la imagen → levanta blue → health-check en `/up` → conmuta
el edge al upstream real → levanta worker + reverb del entorno.

> ⚠️ **Migraciones**: la base SQL Server es compartida entre proyectos. **Prohibido siempre**
> `migrate:fresh`, `migrate:reset`, `db:wipe` (borran las tablas de TODOS los proyectos).
> Solo `migrate --force`.

---

## Fase 4 — Cloudflare

1. **DNS** → registro **A** de cada dominio → `179.43.124.205`, modo **Proxied** (nube naranja).
2. **SSL/TLS → Overview → Encryption mode = `Flexible`**.

> **Por qué Flexible**: el edge solo escucha HTTP en el 80 (el 443 está comentado en
> `compose.edge.yaml` hasta sumar TLS). Con modo `Full`/`Full (strict)`, Cloudflare intenta
> conectarse al origin por 443, no hay listener, y da **Error 521 "Web server is down"**
> aunque el server esté perfecto. Flexible = Cloudflare habla con el origin por HTTP:80.
>
> Para prod, el siguiente paso (aparte) es habilitar el 443 en el edge con un certificado
> de origin de Cloudflare y pasar a `Full (strict)`.

> El **5522 es solo SSH** — Cloudflare nunca lo usa. El tráfico web entra por 80/443.

**Prueba end-to-end**: abrir `https://TU_STAGE_DOMAIN` en el browser → debe cargar la app.

---

## Fase 5 — Deploys en adelante (automáticos)

Con el piso armado, cada push despliega solo:

| Push a | Despliega en | Tags |
|--------|-------------|------|
| `main` | producción | `:<sha>` + `:prod-latest` |
| `staging` | staging | `:<sha>` + `:staging-latest` |

Lo único que seguís haciendo a mano en el server son las **migraciones**, cuando un release
trae cambios de schema:

```bash
# El color activo lo dice el log del deploy ("Tráfico staging ahora en green")
docker exec balanza-staging-app-green php artisan migrate --force
docker exec balanza-staging-app-green php artisan migrate:status
```

---

## Troubleshooting — errores reales del primer deploy, en orden de aparición

| # | Síntoma (textual) | Causa | Fix |
|---|-------------------|-------|-----|
| 1 | `ssh.ParsePrivateKey: ssh: this private key is passphrase protected` | el secret `SSH_KEY` tiene una clave cifrada | clave sin passphrase (fase 1.1) |
| 2 | `fatal: could not read Username for 'https://github.com'` | repo privado y el server no tiene credencial git | deploy key SSH + remote por SSH (2.6) |
| 3 | `403 Forbidden` al hacer pull de GHCR (con "Login Succeeded" antes) | el PAT autentica pero no tiene `read:packages` | regenerar PAT con `read:packages` (1.2) |
| 4 | `container ... is not running` en el cutover | el `balanza-edge` no está corriendo | `docker ps -a` + `docker logs balanza-edge` para la causa real (suele ser #5 o #6) |
| 5 | `failed to bind host port 0.0.0.0:80: address already in use` | nginx del host ocupa el 80 | `systemctl stop nginx && systemctl disable nginx` (2.4) |
| 6 | `nginx: [emerg] host not found in upstream "balanza-staging-app-green:8080"` | el edge no está en `balanza-net`, o el `active-upstream.conf` apunta a un contenedor inexistente | recrear el edge en la red; restaurar placeholders con `git checkout -- docker/edge/*/active-upstream.conf` (2.9) |
| 7 | Cloudflare **Error 521 "Web server is down"** | SSL en `Full` con edge HTTP-only, o puerto 80 cerrado en ufw/panel Dattaweb | SSL `Flexible` (fase 4) + firewall (2.5) |
| 8 | 404/502 **sin** marca de Cloudflare | el `Host` no matchea ningún `server_name` del edge | revisar `STAGE_DOMAIN`/`APP_DOMAIN` en `.env.edge` y recrear el edge |

### Comandos de diagnóstico rápido

```bash
docker ps -a --format '{{.Names}}\t{{.Status}}' | grep balanza        # qué corre y qué murió
docker logs --tail 30 balanza-edge                                    # por qué murió el edge
docker inspect -f '{{range $k,$v := .NetworkSettings.Networks}}{{$k}} {{end}}' balanza-edge   # ¿en la red?
docker exec balanza-edge getent hosts balanza-staging-app-green       # ¿resuelve al app?
docker exec balanza-edge nginx -t                                     # ¿config válida?
sudo ss -lptn 'sport = :80'                                           # ¿quién ocupa el 80?

# Desde TU máquina (no el server): ¿el origin responde salteando Cloudflare?
curl -I http://179.43.124.205/ -H "Host: TU_STAGE_DOMAIN"
```

### Regla de lectura de fallas del cutover

El cutover (`deploy.sh` paso 6) hace `docker exec balanza-edge nginx -t && nginx -s reload`.
Si falla, el problema **nunca** es el app nuevo (ya pasó el health-check): es siempre el
edge — parado (#4/#5), fuera de la red (#6) o con config inválida (#6).

### Regla de oro de `balanza-net`

Es una red `external` para que sobreviva a los `docker compose down`. Si alguna vez la borrás
y recreás, **todo contenedor que existía antes queda pegado a la red vieja** (mismo nombre,
distinto ID) y deja de resolver DNS. Por eso, si necesitás recrear la red: bajá primero todos
los contenedores de balanza, después recreá la red, después recreá edge + apps.

---

*Documento creado: 15/07/2026 — a partir del primer deploy real a staging y sus errores
consecutivos. Mantenerlo al día: cada error nuevo de deploy se agrega a la tabla de
troubleshooting con su causa y su fix.*
