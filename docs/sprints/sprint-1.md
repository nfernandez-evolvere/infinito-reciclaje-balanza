# Sprint 1 — Cimientos
**Período:** Semana 1 · 12–16 mayo 2026
**Rama:** `feature/sprint-1-cimientos`

## Objetivo
Base técnica funcional: conexión a SQL Server verificada, autenticación con 2 roles diferenciados, layouts operativos para cada perfil y protección de rutas completa.

---

## Sub-sprint 1.1 — Configuración SQL Server y entorno

### Tareas
- [ ] Instalar extensiones PHP `sqlsrv` y `pdo_sqlsrv` (Windows dev + Linux prod)
- [ ] Configurar connection `sqlsrv` en `config/database.php`
- [ ] Variables en `.env`: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] Documentar variables en `.env.example`
- [ ] Verificar con `php artisan db:show`

### Tests unitarios
Ninguno — infraestructura sin lógica de negocio testeable en aislamiento.

### Tests de integración
- `DatabaseConnectionTest::test_sql_server_connection_is_reachable` — `DB::connection()->getPdo()` no lanza excepción
- `DatabaseConnectionTest::test_migrations_run_without_errors` — `artisan migrate:fresh` completa sin errores

### Tests manuales
- [ ] `php artisan db:show` muestra driver `sqlsrv` y el nombre de la base correcta
- [ ] `php artisan migrate` corre sin errores en entorno local Windows
- [ ] Verificar en SQL Server Management Studio / Azure Data Studio que la tabla `migrations` fue creada

---

## Sub-sprint 1.2 — Autenticación y roles

### Tareas
- [ ] `composer require laravel/breeze --dev` + `php artisan breeze:install blade`
- [ ] Mover vistas generadas por Breeze de `resources/views/auth/` a `resources/views/modules/auth/`
- [ ] Actualizar rutas de auth para apuntar a `modules.auth.*`
- [ ] Migración `users`: agregar `role` (enum: `operador`, `admin`, default `operador`), `onboarding_visto` (boolean, default `false`)
- [ ] Reescribir vista `login` con `<x-ui.*>` y UX Writing del sistema (voseo, sin tecnicismos)
- [ ] Eliminar vistas y rutas de `register`, `email/verify`, `password/*` de Breeze — usuarios solo se crean desde ABM
- [ ] Redireccionamiento post-login según rol: operador → `/balanza`, admin → `/admin/dashboard`
- [ ] Métodos helpers en `User`: `isAdmin()`, `isOperador()`
- [ ] `UserSeeder`: usuario `roberto` (operador) + `nacho` (admin)

### Tests unitarios
- `UserTest::test_is_admin_returns_true_for_admin_role` — `User` con `role = 'admin'` → `isAdmin()` retorna `true`
- `UserTest::test_is_operador_returns_true_for_operador_role` — `role = 'operador'` → `isOperador()` retorna `true`
- `UserTest::test_is_admin_returns_false_for_operador_role` — rol cruzado retorna `false`
- `UserTest::test_onboarding_visto_defaults_to_false` — usuario creado sin el campo → `onboarding_visto` es `false`

### Tests de integración
- `AuthTest::test_login_page_renders` — `GET /login` → HTTP 200
- `AuthTest::test_operador_redirected_to_balanza_after_login` — `POST /login` (roberto) → redirect `/balanza`
- `AuthTest::test_admin_redirected_to_dashboard_after_login` — `POST /login` (nacho) → redirect `/admin/dashboard`
- `AuthTest::test_invalid_credentials_returns_error` — `POST /login` con contraseña incorrecta → HTTP 422, error en campo `email`
- `AuthTest::test_register_route_does_not_exist` — `GET /register` → HTTP 404
- `AuthTest::test_logout_destroys_session` — `POST /logout` → sesión destruida, redirect a `/login`

### Tests manuales
- [ ] Login como `roberto` → aterriza en pantalla de pesaje del operador
- [ ] Login como `nacho` → aterriza en panel de administración
- [ ] Contraseña incorrecta → mensaje de error visible, sin detalles técnicos ("Credenciales incorrectas")
- [ ] Botón "Cerrar sesión" → redirige a login y destruye la sesión
- [ ] URL `/register` devuelve 404

---

## Sub-sprint 1.3 — Middleware, Gates y protección de rutas

### Tareas
- [ ] `app/Http/Middleware/EnsureRole.php` — verifica `auth()->user()->role` contra el rol requerido; lanza 403 si no coincide
- [ ] Registrar en `bootstrap/app.php` con alias `role`
- [ ] Gates en `AppServiceProvider::boot()`:
  - `record-weighing` — solo operador
  - `view-own-historial` — solo operador
  - `edit-pesaje` — operador (propio del turno) + admin
  - `manage-masters` — solo admin
  - `view-dashboard` — solo admin
  - `manage-usuarios` — solo admin
- [ ] Grupos de rutas: `Route::middleware(['auth', 'role:operador'])` y `Route::middleware(['auth', 'role:admin'])`
- [ ] Rutas placeholder para todas las pantallas de ambos perfiles

### Tests unitarios
- `GateTest::test_operador_can_record_weighing` — Gate `record-weighing` con usuario operador → `true`
- `GateTest::test_operador_cannot_manage_masters` — Gate `manage-masters` con operador → `false`
- `GateTest::test_admin_can_view_dashboard` — Gate `view-dashboard` con admin → `true`
- `GateTest::test_admin_can_manage_masters` — Gate `manage-masters` con admin → `true`
- `GateTest::test_admin_cannot_record_weighing` — Gate `record-weighing` con admin → `false`

### Tests de integración
- `RouteProtectionTest::test_operador_cannot_access_admin_routes` — `GET /admin/dashboard` como operador → HTTP 403
- `RouteProtectionTest::test_admin_cannot_access_balanza` — `GET /balanza` como admin → HTTP 403
- `RouteProtectionTest::test_unauthenticated_redirects_to_login` — `GET /admin/dashboard` sin sesión → redirect `/login`
- `RouteProtectionTest::test_operador_can_access_balanza` — `GET /balanza` como operador → HTTP 200

### Tests manuales
- [ ] Como `roberto`, navegar a `/admin/dashboard` → error 403 o redirect
- [ ] Como `nacho`, navegar a `/balanza` → error 403 o redirect
- [ ] Sin sesión activa, navegar a cualquier ruta protegida → redirect a `/login`
- [ ] Cerrar sesión e intentar navegar con el botón Atrás del navegador → redirect a login (no muestra la pantalla cacheada)

---

## Sub-sprint 1.4 — Layouts y navegación

### Tareas
- [ ] `layouts/operador.blade.php`: header (logo, reloj en vivo Alpine, chip de usuario, botón logout), nav inline (Pesaje / Historial), footer sticky de turno
- [ ] `layouts/admin.blade.php`: sidebar de 240px; flat items (Operación: Dashboard · Pesajes · Reportes; Padrón: Zonas · Tipos de servicio), separador, accordion groups (Transporte: Vehículos · Tipos de vehículo; Sistema: Usuarios); footer con avatar · nombre · rol · logout; toggle collapse con `localStorage`
- [ ] Anti-flash de dark mode en `<head>`
- [ ] Toggle de dark mode en ambos layouts
- [ ] Dark mode: clase `.dark` en `<html>` + tokens del tema activo

### Tests unitarios
Ninguno — presentación sin lógica de negocio.

### Tests de integración
- `LayoutTest::test_operador_layout_renders_for_operador` — `GET /balanza` como operador → HTML contiene nav con "Pesaje" e "Historial"
- `LayoutTest::test_admin_layout_renders_for_admin` — `GET /admin/dashboard` como admin → HTML contiene sidebar con "Dashboard" y "Pesajes"

### Tests manuales
- [ ] Layout operador: header visible con reloj en vivo, nav con dos ítems, footer sticky al hacer scroll
- [ ] Layout admin: sidebar con los 3 grupos, ítems de navegación correctos en cada grupo
- [ ] Sidebar colapsa a íconos al hacer clic en el toggle; estado persiste al recargar la página
- [ ] Al colapsar, hovear un ícono muestra el tooltip con el nombre del ítem
- [ ] Dark mode toggle funciona en ambos layouts; preferencia persiste en localStorage
- [ ] En pantallas pequeñas, el layout admin muestra el sidebar sobre el contenido (no desplaza)

---

## Onboarding guiado — Sprint 1: campo DB

La migración de `users` incluye `onboarding_visto boolean default false`. No hay UI todavía — el componente de bienvenida al operador se construye en Sprint 3.

---

## Criterio de completitud del sprint

- [ ] Login con 2 roles funciona y redirige al layout correcto
- [ ] Rutas protegidas: cada rol solo accede a su área
- [ ] Layouts renderizados con navegación operativa
- [ ] Gates definidos y testeados
- [ ] `UserSeeder` crea usuarios de prueba correctamente
- [ ] Tests unitarios y de integración pasan en verde
- [ ] Tests manuales verificados y documentados como completos
