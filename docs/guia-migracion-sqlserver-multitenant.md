# Guía de setup — SQL Server + Multi-tenant

Multi-tenant con aislamiento por columna `organizacion_id` y selección de organización en el login (sin subdominios). Esta guía cubre el setup de SQL Server, los prefijos de tabla por ambiente y el despliegue.

## Índice

1. [Pre-requisitos](#1-pre-requisitos)
2. [Preparar SQL Server](#2-preparar-sql-server)
3. [Ejecutar los scripts SQL](#3-ejecutar-los-scripts-sql)
4. [Configurar Laravel (.env)](#4-configurar-laravel-env)
5. [Verificar la conexión](#5-verificar-la-conexión)
6. [Servidor local (Windows + Laragon + nginx)](#6-servidor-local-windows--laragon--nginx)
7. [Configurar el dominio en producción (VPS + nginx)](#7-configurar-el-dominio-en-producción-vps--nginx)
8. [Crear el super admin y la primera organización](#8-crear-el-super-admin-y-la-primera-organización)
9. [Agregar una nueva organización](#9-agregar-una-nueva-organización)
10. [Agregar ambiente stg o prod](#10-agregar-ambiente-stg-o-prod)
11. [Despliegue en servidor con Docker](#11-despliegue-en-servidor-con-docker)

---

## 1. Pre-requisitos

### Extensiones PHP requeridas

El driver de SQL Server para PHP requiere dos extensiones. Verificar que estén habilitadas:

```powershell
php -m | findstr sqlsrv
```

Debe aparecer `sqlsrv` y `pdo_sqlsrv`. Si no están:

1. Descargar los drivers desde [Microsoft SQL Server Drivers for PHP](https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server)
2. Copiar `php_sqlsrv_xx_ts.dll` y `php_pdo_sqlsrv_xx_ts.dll` a la carpeta de extensiones de PHP (`C:\php\ext\`)
3. Agregar en `php.ini`:
   ```ini
   extension=sqlsrv
   extension=pdo_sqlsrv
   ```
4. Reiniciar PHP / Herd

### Versión de SQL Server

Compatible con SQL Server 2012 o superior. Verificar la versión:

```sql
SELECT @@VERSION;
```

---

## 2. Preparar SQL Server

### 2.1 Crear el login y el usuario

Ejecutar en SQL Server Management Studio (SSMS) o Azure Data Studio, conectado con un usuario con permisos de administrador:

```sql
-- Crear login (si no existe)
IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'balanza_app')
    CREATE LOGIN [balanza_app] WITH PASSWORD = 'TuPasswordSegura123!';

-- Seleccionar la base de datos del proyecto
USE [NombreDeTuBaseDeDatos];

-- Crear usuario en la base de datos
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'balanza_app')
    CREATE USER [balanza_app] FOR LOGIN [balanza_app];

-- Otorgar permisos necesarios
GRANT SELECT, INSERT, UPDATE, DELETE ON SCHEMA::infinito_balanza TO [balanza_app];
GRANT CREATE TABLE TO [balanza_app];  -- solo para el primer setup

-- Configurar el schema por defecto del usuario
-- Esto es CRÍTICO: permite que Laravel resuelva las tablas sin especificar el schema
ALTER USER [balanza_app] WITH DEFAULT_SCHEMA = infinito_balanza;
```

> **Por qué es importante el DEFAULT_SCHEMA:** Laravel genera queries como `SELECT * FROM [dev_users]`.
> SQL Server resuelve esto a `[infinito_balanza].[dev_users]` automáticamente usando el schema
> por defecto del usuario. Sin este paso, las queries fallarán con "Invalid object name".

### 2.2 Crear el schema

El schema `infinito_balanza` se crea automáticamente con el primer script SQL (ver sección 3).
Si preferís crearlo manualmente antes:

```sql
USE [NombreDeTuBaseDeDatos];

IF NOT EXISTS (SELECT 1 FROM sys.schemas WHERE name = 'infinito_balanza')
    EXEC('CREATE SCHEMA infinito_balanza');
```

---

## 3. Ejecutar los scripts SQL

Los scripts están en `database/sql/dev/` y son idempotentes — se pueden re-ejecutar sin error.

### Orden de ejecución

```
001_laravel_tables.sql   →  tablas del framework (sessions, cache, jobs)
002_domain_tables.sql    →  tablas de dominio del proyecto
```

### Cómo ejecutarlos

**Opción A — SSMS:**
1. Abrir SSMS y conectarse al servidor
2. Seleccionar la base de datos correcta en el dropdown
3. Abrir el archivo SQL (`File → Open → File`)
4. Ejecutar con F5

**Opción B — sqlcmd (línea de comandos):**

```powershell
sqlcmd -S TU_SERVIDOR -d TU_BASE_DE_DATOS -U balanza_app -P TuPassword -i "database\sql\dev\001_laravel_tables.sql"
sqlcmd -S TU_SERVIDOR -d TU_BASE_DE_DATOS -U balanza_app -P TuPassword -i "database\sql\dev\002_domain_tables.sql"
```

### Verificar que las tablas se crearon

```sql
USE [NombreDeTuBaseDeDatos];
SELECT TABLE_SCHEMA, TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'infinito_balanza'
ORDER BY TABLE_NAME;
```

Deben aparecer todas las tablas con prefijo `dev_`.

---

## 4. Configurar Laravel (.env)

Editar `.env` en la raíz del proyecto:

```env
# Comentar o eliminar la línea de SQLite
# DB_CONNECTION=sqlite

# Activar SQL Server
DB_CONNECTION=sqlsrv
DB_HOST=TU_SERVIDOR           # IP, hostname, o SERVIDOR\INSTANCIA
DB_PORT=1433
DB_DATABASE=NombreDeTuBaseDeDatos
DB_USERNAME=balanza_app
DB_PASSWORD=TuPasswordSegura123!
DB_TABLE_PREFIX=dev_

# URL base de la app
APP_URL=http://balanza.test
```

> `DB_TABLE_PREFIX=dev_` le indica a Laravel que prefije todas las tablas con `dev_`.
> Para staging usar `stg_`, para producción `prod_`.

Limpiar la caché de configuración después de editar:

```powershell
php artisan config:clear
php artisan cache:clear
```

---

## 5. Verificar la conexión

```powershell
php artisan tinker
```

```php
// Verificar conexión básica
DB::connection()->getPdo();
// → PDO {#...} significa que conectó

// Verificar que las tablas son visibles
DB::table('organizaciones')->count();
// → 0 (sin datos todavía)
```

Si hay error de conexión, verificar:
- Que `pdo_sqlsrv` esté habilitado: `php -m | findstr sqlsrv`
- Que el firewall del servidor permita el puerto 1433
- Que el servicio SQL Server Browser esté corriendo (para instancias con nombre)
- Para instancias con nombre: usar `DB_HOST=SERVIDOR\INSTANCIA` y dejar `DB_PORT` vacío

---

## 6. Servidor local (Windows + Laragon + nginx)

La app corre en un **único dominio** (no hay subdominios por organización): basta con `balanza.test`.

El stack local usa **Laragon** como servidor nginx y **PHP 8.5** desde `C:\php\` vía CGI.

> Si usás [Herd Lite](https://herd.laravel.com/) o `php artisan serve`, esta sección no aplica:
> la app queda disponible directamente en el dominio/puerto que provea esa herramienta.

### 6.1 Instalar Laragon y activar nginx

1. Instalar [Laragon](https://laragon.org/) (Full o Lite).
2. En el panel de Laragon: **Menu → Preferences → Server → Use nginx** (desactivar Apache si está activo).
3. Hacer clic en **Start All**.

### 6.2 Virtual host para `balanza.test`

Crear `C:\laragon\etc\nginx\sites-enabled\balanza.test.conf`:

```nginx
server {
    listen 8080;
    server_name balanza.test;

    root "C:/ruta/al/proyecto/public";
    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include "C:/laragon/bin/nginx/nginx-1.28.2/conf/snippets/fastcgi-php.conf";
        fastcgi_pass 127.0.0.1:10988;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location ~ /\.ht { deny all; }
}
```

> Ajustar la versión de nginx en el `include` si es distinta. Revisar en `C:\laragon\bin\nginx\`.

El `fastcgi_pass` apunta al puerto 10988 (PHP 8.5, ver sección 6.3).
Laragon bloquea el puerto 80 sin privilegios de administrador — en local se usa **puerto 8080**.

### 6.3 PHP 8.5 vía CGI (puerto 10988)

Laragon incluye PHP 8.3 en su CGI propio (puerto 10987). Este proyecto requiere PHP ≥ 8.3 y
en el equipo de desarrollo se usa PHP 8.5 desde `C:\php\`. Para arrancarlo:

```powershell
Start-Process "C:\php\php-cgi.exe" -ArgumentList "-b 127.0.0.1:10988" -WindowStyle Hidden
```

**Este proceso no sobrevive reinicios.** Volver a ejecutar el comando cada vez que se reinicia
la máquina o después de hacer un Stop All / Start All en Laragon.

Para automatizarlo, crear una tarea en el Programador de tareas de Windows:
- Programa: `C:\php\php-cgi.exe`
- Argumentos: `-b 127.0.0.1:10988`
- Disparador: Al iniciar sesión

### 6.4 Agregar `balanza.test` al archivo hosts

Una sola vez. Abrir PowerShell **como Administrador**:

```powershell
Add-Content "C:\Windows\System32\drivers\etc\hosts" "127.0.0.1`tbalanza.test"
```

> No se necesita una entrada por organización: todas las orgs comparten el mismo dominio.
> La organización activa se elige en el login, no en la URL.

### 6.5 Verificar

Recargar la configuración de nginx:

```powershell
& "C:\laragon\bin\nginx\nginx-1.28.2\nginx.exe" -s reload
```

Abrir en el navegador: `http://balanza.test:8080/login`

> Las URLs locales llevan `:8080` porque nginx no puede usar el puerto 80 sin privilegios de admin.

---

## 7. Configurar el dominio en producción (VPS + nginx)

Al no haber subdominios, alcanza con un registro DNS simple y un virtual host para el dominio.

### 7.1 DNS

En el panel de DNS del dominio, apuntar el host a la IP del VPS:

```
Tipo : A
Nombre: @            (o el host que corresponda, ej: app)
Valor : IP_DEL_VPS
TTL  : 3600
```

### 7.2 nginx — virtual host

```nginx
server {
    listen 80;
    listen [::]:80;

    server_name inf-bal.com;

    root /var/www/infinito-reciclaje-balanza/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass php-fpm:9000;          # contenedor PHP-FPM (Docker)
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Recargar nginx:

```bash
sudo nginx -t && sudo systemctl reload nginx
```

### 7.3 .env por ambiente

Lo único que cambia entre ambientes es el prefijo de tablas (`DB_TABLE_PREFIX`) y la URL.

**.env producción:**
```env
APP_URL=https://inf-bal.com
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=sqlsrv
DB_TABLE_PREFIX=prod_
# ... resto de credenciales
```

**.env staging:**
```env
APP_URL=https://staging.inf-bal.com
APP_ENV=staging
APP_DEBUG=false

DB_CONNECTION=sqlsrv
DB_TABLE_PREFIX=stg_
# ... resto de credenciales
```

---

## 8. Crear el super admin y la primera organización

El `super_admin` no pertenece a ninguna organización y administra el sistema globalmente.
La forma más simple de crear el bootstrap es vía tinker (usa los modelos, válido para cualquier schema):

```powershell
php artisan tinker
```

```php
// Super admin (no pertenece a ninguna org)
\App\Models\User::create([
    'name'     => 'Super Admin',
    'email'    => 'super@inf-bal.com',
    'password' => 'tu-password-segura',   // se hashea automáticamente
    'role'     => 'super_admin',
    'activo'   => true,
]);

// Primera organización
\App\Models\Organizacion::create([
    'nombre' => 'Municipio Ejemplo',
    'activo' => true,
]);
```

A partir de acá, el resto se hace desde la UI: iniciar sesión con el super admin en
`/login` (contexto **"Administración del sistema"**) y administrar las organizaciones y sus
usuarios desde el panel.

---

## 9. Agregar una nueva organización

### 9.1 Vía super admin (flujo normal)

1. Ir a `/login` e ingresar como super admin (contexto "Administración del sistema").
2. Ir a **Organizaciones → Nueva organización**.
3. Completar el **nombre** y asignar el **email del administrador** inicial.
   - Si el email no existe, se crea la cuenta y se envía un email de invitación para que
     defina su contraseña.
   - Si ya existe, se lo vincula a la organización y se le notifica.

El admin de la organización podrá luego, desde su panel, dar de alta operadores y más admins.

### 9.2 Vincular un usuario existente a una organización (manual)

El vínculo usuario–organización vive en el pivot `organizacion_user`. Si necesitás hacerlo a mano:

```php
// php artisan tinker
$org  = \App\Models\Organizacion::where('nombre', 'Municipio Ejemplo')->first();
$user = \App\Models\User::where('email', 'admin@municipio.gob.ar')->first();

$org->users()->syncWithoutDetaching([$user->id]);
```

Un mismo usuario puede pertenecer a varias organizaciones y elegir con cuál ingresar en el login.

---

## 10. Agregar ambiente stg o prod

Los scripts SQL para staging y producción son idénticos a los de dev, solo cambia el prefijo de las tablas.

### Crear los scripts

```powershell
# Copiar scripts de dev a stg
Copy-Item "database\sql\dev\001_laravel_tables.sql" "database\sql\stg\001_laravel_tables.sql"
Copy-Item "database\sql\dev\002_domain_tables.sql"  "database\sql\stg\002_domain_tables.sql"

# Reemplazar prefijo dev_ → stg_
(Get-Content "database\sql\stg\001_laravel_tables.sql") -replace 'dev_', 'stg_' | Set-Content "database\sql\stg\001_laravel_tables.sql"
(Get-Content "database\sql\stg\002_domain_tables.sql") -replace 'dev_', 'stg_' | Set-Content "database\sql\stg\002_domain_tables.sql"
```

Repetir para `prod` cambiando `stg_` por `prod_`.

### Cambiar de ambiente en Laravel

Solo cambiar `DB_TABLE_PREFIX` en el `.env` del servidor correspondiente:

| Ambiente   | `DB_TABLE_PREFIX` | `APP_URL`                      |
|------------|-------------------|--------------------------------|
| Local      | `dev_`            | `http://balanza.test`          |
| Staging    | `stg_`            | `https://staging.inf-bal.com`  |
| Producción | `prod_`           | `https://inf-bal.com`          |

---

## 11. Despliegue en servidor con Docker

El stack de producción/staging se levanta con Docker Compose:
- **nginx** — reverse proxy, termina SSL, sirve assets estáticos
- **php-fpm** — ejecuta PHP, incluye las extensiones `sqlsrv` y `pdo_sqlsrv`
- **SQL Server** — externo (servidor Evolvere), no se incluye en Docker

### 11.1 Dockerfile (PHP-FPM + sqlsrv)

```dockerfile
FROM php:8.4-fpm-bookworm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    curl gnupg unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

# ODBC Driver 17 for SQL Server
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
 && curl https://packages.microsoft.com/config/debian/12/prod.list \
        > /etc/apt/sources.list.d/mssql-release.list \
 && apt-get update \
 && ACCEPT_EULA=Y apt-get install -y msodbcsql17 \
 && rm -rf /var/lib/apt/lists/*

# Extensiones PHP
RUN pecl install sqlsrv pdo_sqlsrv \
 && docker-php-ext-enable sqlsrv pdo_sqlsrv \
 && docker-php-ext-install pcntl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### 11.2 nginx (dentro del contenedor o en el host)

```nginx
server {
    listen 80;
    server_name inf-bal.com;

    root /var/www/html/public;
    index index.php;

    charset utf-8;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass php-fpm:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location ~ /\.(?!well-known).* { deny all; }
}
```

### 11.3 docker-compose.yml

```yaml
services:
  php-fpm:
    build: .
    volumes:
      - ./storage:/var/www/html/storage
    env_file: .env.production
    restart: unless-stopped

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./public:/var/www/html/public:ro
      - ./storage/app/public:/var/www/html/public/storage:ro
      - /etc/letsencrypt:/etc/letsencrypt:ro
    depends_on:
      - php-fpm
    restart: unless-stopped
```

> El volumen de `storage/` se monta para que los logs y archivos subidos persistan entre
> deploys. El directorio `public/` se sirve directamente por nginx sin pasar por PHP-FPM.

### 11.4 Variables de entorno en producción

Crear `.env.production` en el servidor (nunca comitear):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inf-bal.com

DB_CONNECTION=sqlsrv
DB_HOST=IP_SERVIDOR_SQL
DB_PORT=1433
DB_DATABASE=Evolvere
DB_USERNAME=balanza_app
DB_PASSWORD=<password>
DB_SCHEMA=infinito_balanza
DB_TABLE_PREFIX=prod_

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 11.5 Primer deploy

```bash
docker compose build
docker compose up -d

# Migraciones (solo la primera vez o después de cambios de schema)
docker compose exec php-fpm php artisan migrate --force

# Verificar
docker compose exec php-fpm php artisan tinker --execute="echo DB::connection()->getPdo() ? 'DB OK' : 'FAIL';"
```

### 11.6 Actualizar la aplicación

```bash
git pull
docker compose build php-fpm
docker compose up -d --no-deps php-fpm
docker compose exec php-fpm php artisan config:cache
docker compose exec php-fpm php artisan route:cache
docker compose exec php-fpm php artisan view:cache
```

> No correr migraciones automáticamente en cada deploy — revisarlas siempre antes en staging.
> SQL Server en producción no permite rollback de ALTER TABLE, así que toda migración
> destructiva debe probarse en staging primero.
