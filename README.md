# Infinito Reciclaje — Sistema de Gestión de Balanza

Sistema web para el registro y control de pesajes de residuos urbanos. Arquitectura **multi-tenant con aislamiento por organización**: cada municipio u organización opera con sus datos completamente aislados, y los usuarios eligen con qué organización ingresar al iniciar sesión.

---

## Stack

- **Laravel 13** + PHP 8.3
- **SQL Server** (SQL Server 2017+, driver ODBC 17)
- **Tailwind CSS v4** + **Alpine.js** — design system propio inspirado en shadcn/ui
- **Blade** (sin React ni Vue)

---

## Arquitectura multi-tenant

El aislamiento de datos es **por columna**: todas las tablas de dominio tienen una FK `organizacion_id`. La organización activa se resuelve **desde la sesión** en el middleware `ResolveOrganizacion` y se selecciona en el login.

### Resolución de tenant

No hay subdominios: la app vive en un único dominio. El flujo es:

```
1. El usuario ingresa su email en /login.
2. La pantalla consulta a qué organizaciones pertenece ese email
   (GET login/organizaciones) y muestra un selector.
3. El usuario elige la organización y envía email + password + organizacion_id.
4. Al autenticar, se guarda organizacion_id en la sesión.
5. En cada request, ResolveOrganizacion lee session('organizacion_id'),
   valida que el usuario pertenezca a esa org activa, y la bindea como
   app('organizacion').
```

El `super_admin` no pertenece a ninguna organización: ingresa desde el contexto **"Administración del sistema"** (sin `organizacion_id`) y administra las organizaciones globalmente.

### Roles

| Rol | Pertenencia a org | Acceso |
|-----|-------------------|--------|
| `super_admin` | ninguna (cross-org) | CRUD de organizaciones, ingreso desde "Administración del sistema" |
| `admin` | una o varias orgs | Panel de administración de la org seleccionada |
| `operador` | una o varias orgs | Registro de pesajes de la org seleccionada |

> La relación usuario–organización es **muchos-a-muchos** (`organizacion_user`): un mismo `admin`/`operador` puede pertenecer a varias organizaciones y elegir cuál al ingresar.

### Tablas de dominio con `organizacion_id`

| Tabla | Unique constraints por org |
|-------|---------------------------|
| `users` | vínculo vía pivot `organizacion_user` |
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

La app corre en un único dominio (ej: `http://balanza.test` con Herd, o `http://127.0.0.1:8000` con `php artisan serve`). No se requieren entradas de subdominio en el archivo `hosts`.

---

## Variables de entorno clave

```env
# Aplicación
APP_URL=http://balanza.test

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
| `local` | `DevSeeder` | 2 orgs (Corrientes, Resistencia), super admin, 2 usuarios por org, un admin con acceso a ambas orgs, tipos de vehículo/servicio, vehículos y zonas |
| `staging` | `StagingSeeder` *(pendiente)* | — |
| `production` | `ProductionSeeder` *(pendiente)* | — |

Usuarios de dev (contraseña `Evolvere123!@`):

| Email | Rol | Organización |
|-------|-----|--------------|
| nfernandez@evolvere.com.ar | super_admin | — (Administración del sistema) |
| admin@corrientes.com | admin | Corrientes |
| operario@corrientes.com | operador | Corrientes |
| admin@resistencia.com | admin | Resistencia |
| operario@resistencia.com | operador | Resistencia |
| admin.doble@test.com | admin | Corrientes + Resistencia (prueba el selector de org) |

---

## Documentación

| Documento | Descripción |
|-----------|-------------|
| [`docs/01-brief-producto.md`](docs/01-brief-producto.md) | Requerimientos funcionales y no funcionales, módulos, perfiles |
| [`docs/02-roadmap.md`](docs/02-roadmap.md) | Plan de desarrollo: arquitectura de pantallas, permisos, sprints |
| [`docs/03-data-model.md`](docs/03-data-model.md) | Modelo de datos completo: tipos, constraints, índices, decisiones de diseño |
| [`docs/04-der.md`](docs/04-der.md) | Diagrama entidad-relación y estrategia de borrado |
| [`docs/05-design-system.md`](docs/05-design-system.md) | Componentes Blade (`x-ui.*`), tokens, tipografía, espaciado |
| [`docs/06-ux-writing.md`](docs/06-ux-writing.md) | Voz y tono, reglas de escritura por rol |
| [`docs/07-abm-guide.md`](docs/07-abm-guide.md) | Guía para construir módulos ABM (patrón canónico) |
| [`docs/08-testing-strategy.md`](docs/08-testing-strategy.md) | Estrategia y convenciones de testing |
| [`docs/09-deployment-docker.md`](docs/09-deployment-docker.md) | Infraestructura Docker, blue-green y CI/CD |

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
│   │   ├── ResolveOrganizacion.php   # Resolución de la org activa desde la sesión
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
