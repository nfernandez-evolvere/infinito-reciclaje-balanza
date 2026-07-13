# Entorno local en macOS con Docker + SQL Server

Guía para levantar el proyecto de cero en una Mac que **no tiene PHP ni Composer instalados**.
Todo corre en Docker: la base de datos (SQL Server) y la aplicación (imagen de la app).

> Probado en Mac **Intel (x86_64)**. La imagen oficial de SQL Server corre nativa.
> En Apple Silicon (M1/M2/M3) la imagen `mcr.microsoft.com/mssql/server` requiere emulación
> o usar `azure-sql-edge`; esta guía no cubre ese caso.

---

## 0. Requisitos

- **Docker Desktop** instalado y corriendo (`docker version` debe responder).
- El repositorio clonado y un archivo `.env` presente en la raíz.

No hace falta instalar PHP, Composer ni Node en el host: todo se ejecuta dentro de contenedores.

---

## 1. Levantar SQL Server en Docker

Corre el motor de base de datos en un contenedor con volumen persistente (los datos
sobreviven reinicios y `docker stop`).

```bash
docker run -e "ACCEPT_EULA=Y" -e "MSSQL_SA_PASSWORD=Balanza_2026!" \
  -p 1433:1433 --name mssql-balanza \
  -v mssql_balanza_data:/var/opt/mssql \
  -d mcr.microsoft.com/mssql/server:2022-latest
```

| Parámetro | Valor |
|-----------|-------|
| Nombre del contenedor | `mssql-balanza` |
| Puerto expuesto en el host | `1433` |
| Password de `sa` (admin) | `Balanza_2026!` |
| Volumen persistente | `mssql_balanza_data` |

Esperá ~15 segundos a que inicialice. Verificá que responde:

```bash
docker exec mssql-balanza /opt/mssql-tools18/bin/sqlcmd \
  -S localhost -U sa -P 'Balanza_2026!' -C -Q "SELECT @@VERSION"
```

---

## 2. Crear la base de datos y el login de la app

El `.env` usa el usuario `nico0689` / `Balanza_2026!` (la misma password que `sa`, para
recordar una sola). Se crea con `CHECK_POLICY = OFF` para no depender de la política del servidor.

```bash
docker exec mssql-balanza /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Balanza_2026!' -C -Q "
IF DB_ID('infinito_balanza') IS NULL CREATE DATABASE infinito_balanza;
GO
IF SUSER_ID('nico0689') IS NULL
  CREATE LOGIN nico0689 WITH PASSWORD = 'Balanza_2026!', CHECK_POLICY = OFF, DEFAULT_DATABASE = infinito_balanza;
GO
USE infinito_balanza;
IF USER_ID('nico0689') IS NULL CREATE USER nico0689 FOR LOGIN nico0689;
ALTER ROLE db_owner ADD MEMBER nico0689;
GO
"
```

Esto es idempotente: se puede volver a correr sin romper nada.

---

## 3. Configuración del `.env`

Para correr contra SQL Server en Docker, el `.env` debe tener:

```dotenv
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=infinito_balanza
DB_USERNAME=nico0689
DB_PASSWORD=Balanza_2026!
DB_SCHEMA=dbo
DB_TABLE_PREFIX=dev_
DB_TRUST_SERVER_CERTIFICATE=true
```

> **Nota:** cuando la app corre vía `compose.dev.yaml`, el compose **sobreescribe**
> `DB_HOST=host.docker.internal` y `DB_PORT=1433` por sus variables `environment:`.
> El contenedor de la app llega al de SQL Server a través del puerto `1433` del host.
> Los valores de arriba en el `.env` son los que se usan si algún día se corre PHP nativo.

---

## 4. Levantar la aplicación

La app corre con la misma imagen que producción, construida localmente. Trae PHP,
Node, los assets compilados y el driver `pdo_sqlsrv`.

```bash
# Primera vez, o cuando cambian dependencias / assets:
docker compose -f compose.dev.yaml up --build

# Día a día (solo cambios de PHP/Blade, sin rebuild):
docker compose -f compose.dev.yaml up
```

Servicios que levanta:

| Servicio | URL / Puerto | Descripción |
|----------|--------------|-------------|
| `app`    | http://localhost:8000 | La aplicación Laravel |
| `vite`   | http://localhost:5173 | Dev server de assets (hot reload) |
| `worker` | —            | Colas / jobs en background |

Dejá esa terminal abierta (los logs se ven ahí). Para los comandos de artisan, usá **otra terminal**.

---

## 5. Migrar la base de datos

Las migraciones son **siempre manuales** (el contenedor no migra solo). Desde otra terminal:

```bash
docker compose -f compose.dev.yaml exec app php artisan migrate
```

Verificar el estado en cualquier momento (solo lectura, seguro):

```bash
docker compose -f compose.dev.yaml exec app php artisan migrate:status
```

> ⚠️ **Nunca** ejecutar `migrate:fresh`, `migrate:reset` ni `db:wipe`.
> En producción la BD es compartida entre proyectos y esos comandos borran tablas de todos.
> En local también conviene evitar el hábito. Ver `CLAUDE.md` § Base de datos.

---

## 6. Correr los seeds

En entorno `local`, `DatabaseSeeder` ejecuta `DevSeeder`, que **limpia** los datos previos
y siembra dos organizaciones (Corrientes y Resistencia) con sus usuarios, tipos de vehículo,
tipos de servicio, zonas, vehículos, pesajes y alertas de ejemplo.

```bash
docker compose -f compose.dev.yaml exec app php artisan db:seed
```

### Usuarios sembrados

Todos con la password **`Evolvere123!@`**:

| Email | Rol | Organización |
|-------|-----|--------------|
| `nfernandez@evolvere.com.ar` | super_admin | (todas) |
| `admin.doble@test.com`       | admin       | Corrientes + Resistencia |
| `admin@corrientes.com`       | admin       | Corrientes |
| `operario@corrientes.com`    | operador    | Corrientes |
| `admin@resistencia.com`      | admin       | Resistencia |
| `operario@resistencia.com`   | operador    | Resistencia |

> Reejecutar `db:seed` regenera todo desde cero (borra y vuelve a crear los datos de ejemplo).

---

## 7. Ver los datos con un administrador de base de datos

### Opción recomendada — Azure Data Studio (nativo Mac, gratis, hecho para SQL Server)

Descargar de: https://learn.microsoft.com/sql/azure-data-studio/download

Crear una conexión con estos datos:

| Campo | Valor |
|-------|-------|
| Server | `127.0.0.1,1433` (o `localhost,1433`) |
| Authentication type | SQL Login |
| User name | `nico0689` (o `sa` para admin total) |
| Password | `Balanza_2026!` (misma para ambos usuarios) |
| Database | `infinito_balanza` |
| Trust server certificate | ✅ Sí (marcar) |

Las tablas del proyecto tienen el prefijo **`dev_`** (por `DB_TABLE_PREFIX`).
Ej: `dev_pesajes`, `dev_users`, `dev_vehiculos`.

### Alternativa — DBeaver (universal, gratis)

https://dbeaver.io — usar el driver **SQL Server**, mismos datos de conexión. En las
propiedades del driver activar `trustServerCertificate=true`.

### Sin instalar nada — consulta rápida por CLI

```bash
docker exec -it mssql-balanza /opt/mssql-tools18/bin/sqlcmd \
  -S localhost -U nico0689 -P 'Balanza_2026!' -C -d infinito_balanza \
  -Q "SELECT TOP 10 * FROM dev_pesajes;"
```

---

## 8. Comandos útiles

### Contenedor de SQL Server

```bash
docker stop mssql-balanza     # apagar (los datos persisten en el volumen)
docker start mssql-balanza    # encender
docker logs mssql-balanza     # ver logs del motor
```

### Aplicación

```bash
# Bajar la app (Ctrl+C en la terminal del up, o desde otra terminal):
docker compose -f compose.dev.yaml down

# Ejecutar cualquier comando artisan:
docker compose -f compose.dev.yaml exec app php artisan <comando>

# Abrir una shell dentro del contenedor de la app:
docker compose -f compose.dev.yaml exec app bash

# Tinker (consola interactiva de Laravel):
docker compose -f compose.dev.yaml exec app php artisan tinker
```

---

## 9. Puesta en marcha completa (resumen)

Desde cero, en orden:

```bash
# 1. SQL Server
docker run -e "ACCEPT_EULA=Y" -e "MSSQL_SA_PASSWORD=Balanza_2026!" \
  -p 1433:1433 --name mssql-balanza \
  -v mssql_balanza_data:/var/opt/mssql \
  -d mcr.microsoft.com/mssql/server:2022-latest

# 2. Base + login (esperar ~15s a que SQL Server arranque)
docker exec mssql-balanza /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Balanza_2026!' -C -Q "
IF DB_ID('infinito_balanza') IS NULL CREATE DATABASE infinito_balanza;
GO
IF SUSER_ID('nico0689') IS NULL CREATE LOGIN nico0689 WITH PASSWORD='Balanza_2026!', CHECK_POLICY=OFF, DEFAULT_DATABASE=infinito_balanza;
GO
USE infinito_balanza;
IF USER_ID('nico0689') IS NULL CREATE USER nico0689 FOR LOGIN nico0689;
ALTER ROLE db_owner ADD MEMBER nico0689;
GO"

# 3. App
docker compose -f compose.dev.yaml up --build -d

# 4. Migrar + seed
docker compose -f compose.dev.yaml exec app php artisan migrate
docker compose -f compose.dev.yaml exec app php artisan db:seed
```

Abrir http://localhost:8000 e iniciar sesión con `admin@corrientes.com` / `Evolvere123!@`.

---

## 10. Troubleshooting

| Síntoma | Causa / solución |
|---------|------------------|
| `could not find driver` | La imagen de la app no cargó `pdo_sqlsrv`. Rebuild: `docker compose -f compose.dev.yaml build --no-cache app`. |
| `Login failed for user 'nico0689'` | El login no se creó o la password no coincide. Reejecutar el paso 2. |
| `Cannot open database "infinito_balanza"` | La base no existe. Reejecutar el paso 2. |
| El puerto 1433 ya está en uso | Hay otro SQL Server local. Bajarlo, o mapear otro puerto (`-p 1434:1433`) y ajustar `DB_PORT`. |
| Los assets (CSS/JS) no cargan | El servicio `vite` no está corriendo. Verificar que `compose.dev.yaml up` levantó los tres servicios. |
| Cambié el `.env` y no toma efecto | `docker compose -f compose.dev.yaml exec app php artisan config:clear`. |
