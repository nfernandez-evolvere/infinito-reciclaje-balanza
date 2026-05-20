# Infinito Reciclaje — Sistema de Gestión de Balanza

Sistema web para el registro y control de pesajes de residuos urbanos. Arquitectura **multi-tenant por subdominio**: cada municipio u organización opera en su propio subdominio con datos completamente aislados.

---

## Stack

- **Laravel 13** + PHP 8.3
- **SQL Server** (SQL Server 2017+, driver ODBC 17)
- **Tailwind CSS v4** + **Alpine.js** — design system propio inspirado en shadcn/ui
- **Blade** (sin React ni Vue)

---

## Arquitectura multi-tenant

El aislamiento de datos es **por columna**: todas las tablas de dominio tienen una FK `organizacion_id`. El tenant se resuelve a partir del subdominio del request en el middleware `ResolveOrganizacion`.

### Resolución de tenant

```
{slug}.balanza.test        →  organización "slug"
super.balanza.test         →  contexto super admin (sin org)
balanza.test               →  sin tenant (redirige a login)
```

El subdominio bruto se extrae del host y se le stripea el prefijo configurado en `APP_SUBDOMAIN_PREFIX`:

| Ambiente   | `APP_SUBDOMAIN_PREFIX` | Ejemplo org                       | Super admin                  |
|------------|------------------------|-----------------------------------|------------------------------|
| Local      | *(vacío)*              | `corrientes.balanza.test`         | `super.balanza.test`         |
| Staging    | `staging-`             | `staging-corrientes.inf-bal.com`  | `staging-super.inf-bal.com`  |
| Producción | *(vacío)*              | `corrientes.inf-bal.com`          | `super.inf-bal.com`          |

Un único wildcard DNS `*.inf-bal.com` cubre staging y producción.

### Roles

| Rol | `organizacion_id` | Acceso |
|-----|-------------------|--------|
| `super_admin` | `NULL` | CRUD de organizaciones, acceso desde subdominio `super` |
| `admin` | org FK | Panel de administración de su organización |
| `operador` | org FK | Registro de pesajes |

### Tablas de dominio con `organizacion_id`

| Tabla | Unique constraints por org |
|-------|---------------------------|
| `users` | `(organizacion_id, email)` |
| `tipos_vehiculo` | — |
| `tipos_servicio` | `(organizacion_id, nombre)` |
| `vehiculos` | `(organizacion_id, patente)`, `(organizacion_id, numero_interno)` |
| `zonas` | `(organizacion_id, nombre)` |
| `zona_servicios` | FK chain desde zonas / tipos_servicio |

El trait `BelongsToOrganizacion` (`app/Models/Concerns/`) aplica un global scope automático en todos los modelos de dominio: las queries filtran por la org del request sin intervención manual.

---

## Convención de tablas por ambiente

Las tablas se prefijan por ambiente via `DB_TABLE_PREFIX`:

| Ambiente | `DB_TABLE_PREFIX` | Ejemplo |
|----------|-------------------|---------|
| Local    | `dev_`            | `infinito_balanza.dev_users` |
| Staging  | `stg_`            | `infinito_balanza.stg_users` |
| Producción | `prod_`         | `infinito_balanza.prod_users` |

El schema de SQL Server siempre es `infinito_balanza`. La base de datos del servidor es `Evolvere`.
Formato completo: `[Evolvere].[infinito_balanza].[{prefix}_{tabla}]`.

---

## Setup local

### Prerequisitos

- PHP 8.3+ con extensiones `pdo_sqlsrv` y `sqlsrv`
- ODBC Driver 17 for SQL Server
- Node.js 20+
- [Herd Lite](https://herd.laravel.com/) (o cualquier servidor PHP local)
- Acceso al servidor SQL Server

### Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configurar `.env` con las credenciales de la BD (ver sección Variables de entorno).

```bash
php artisan migrate
php artisan db:seed       # solo en local (APP_ENV=local)
npm run dev
```

### Subdominios en local (Windows + Herd Lite)

Herd Lite no soporta wildcard DNS. Agregar una entrada por subdominio en
`C:\Windows\System32\drivers\etc\hosts` (PowerShell como Administrador):

```powershell
Add-Content -Path "C:\Windows\System32\drivers\etc\hosts" -Value "127.0.0.1`tsuper.balanza.test"
Add-Content -Path "C:\Windows\System32\drivers\etc\hosts" -Value "127.0.0.1`tcorrientes.balanza.test"
Add-Content -Path "C:\Windows\System32\drivers\etc\hosts" -Value "127.0.0.1`tresistencia.balanza.test"
```

Al crear una nueva organización desde el panel, agregar su subdominio con el mismo comando.

---

## Variables de entorno clave

```env
# Aplicación
APP_URL=http://balanza.test
APP_SUBDOMAIN_PREFIX=          # vacío en local y prod; "staging-" en staging

# Base de datos
DB_CONNECTION=sqlsrv
DB_HOST=<host>
DB_PORT=1433
DB_DATABASE=Evolvere
DB_USERNAME=<usuario>
DB_PASSWORD=<password>
DB_SCHEMA=infinito_balanza
DB_TABLE_PREFIX=dev_           # dev_ | stg_ | prod_
```

---

## Seeders

Los seeds están segmentados por ambiente. `DatabaseSeeder` despacha el seeder correcto según `APP_ENV`:

| Ambiente | Seeder | Contenido |
|----------|--------|-----------|
| `local` | `DevSeeder` | 2 orgs (Corrientes, Resistencia), super admin, 2 usuarios por org, tipos de vehículo/servicio, vehículos y zonas |
| `staging` | `StagingSeeder` *(pendiente)* | — |
| `production` | `ProductionSeeder` *(pendiente)* | — |

Usuarios de dev:

| Email | Contraseña | Rol | Subdominio |
|-------|-----------|-----|------------|
| nfernandez@evolvere.com.ar | 1234 | super_admin | `super.balanza.test` |
| admin@corrientes.com | 1234 | admin | `corrientes.balanza.test` |
| operario@corrientes.com | 1234 | operador | `corrientes.balanza.test` |
| admin@resistencia.com | 1234 | admin | `resistencia.balanza.test` |
| operario@resistencia.com | 1234 | operador | `resistencia.balanza.test` |

---

## Documentación

| Documento | Descripción |
|-----------|-------------|
| [`docs/roadmap.md`](docs/roadmap.md) | Plan de desarrollo: sprints, schema, arquitectura de pantallas |
| [`docs/data-model.md`](docs/data-model.md) | Modelo de datos completo: tipos, constraints, índices, decisiones de diseño |
| [`docs/design-system.md`](docs/design-system.md) | Componentes Blade (`x-ui.*`), tokens, tipografía, espaciado |
| [`docs/ux-writing.md`](docs/ux-writing.md) | Voz y tono, reglas de escritura por rol |
| [`docs/guia-migracion-sqlserver-multitenant.md`](docs/guia-migracion-sqlserver-multitenant.md) | Guía completa de setup SQL Server + subdominios por ambiente |
| [`docs/Brief_Producto_Etapa1.md`](docs/Brief_Producto_Etapa1.md) | Requerimientos funcionales y no funcionales |

---

## Arquitectura del código

```
app/
├── Database/               # Grammars SQL Server personalizadas (schema + prefix)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Panel de administración por org
│   │   └── SuperAdmin/     # CRUD de organizaciones
│   ├── Middleware/
│   │   ├── ResolveOrganizacion.php   # Resolución de tenant por subdominio
│   │   └── EnsureRole.php            # Control de acceso por rol
│   └── Requests/           # Form Requests (validación)
├── Models/
│   ├── Concerns/
│   │   └── BelongsToOrganizacion.php # Trait: global scope + auto-assign org
│   └── Organizacion.php
├── Repositories/           # Acceso a datos (Eloquent)
└── Services/               # Lógica de negocio
```

Patrón: **Controller → Service → Repository**. Controllers delgados, lógica en Services.
