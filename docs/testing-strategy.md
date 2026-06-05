# Estrategia de Testing — Infinito Reciclaje

> **Estado:** propuesta para revisión. Nada de esto está implementado todavía.
> **Decisiones tomadas:** seguimos en **PHPUnit 12** (no migramos a Pest) · montamos **CI en GitHub Actions**.
> **Objetivo:** pasar de una suite desigual (fuerte en ABM, vacía en el núcleo) a una estructura sólida, con cobertura medida y gate automático en cada PR.

---

## 1. Punto de partida

- **241 tests** en 16 archivos · 2 suites (`Unit`, `Feature`) · SQLite en memoria + `RefreshDatabase`.
- Calidad técnica buena (asserts anti-N+1, `Mail::fake`, casos borde de tara).
- Problemas: cobertura invertida respecto al riesgo (núcleo Pesaje y multi-tenant en cero), sin CI, sin cobertura medida, "Unit" usado para tests que tocan DB, duplicación de helpers, naming mixto.

### Cobertura por dominio (resumen)

| Dominio | Estado | Prioridad |
|---------|--------|-----------|
| Pesaje (ciclo de vida) | 🔴 0 tests | **P0** |
| Aislamiento multi-tenant | 🔴 0 tests | **P0** |
| SuperAdmin / Organización | 🔴 0 tests | P1 |
| Reportes + Job | 🔴 0 tests (WIP) | P2 |
| Zona / ZonaServicio CRUD | 🟠 parcial | P2 |
| Auth (reset/forgot) | 🟡 parcial | P3 |
| Endpoints sueltos (`vehiculos.buscar`, `onboarding.visto`) | 🟠 | P3 |
| Vehículo · TipoVehículo · TipoServicio · Usuario · Dashboard | 🟢 sólido | — |

---

## 2. Principios y convenciones

### 2.1 Modelo de testing: "Trophy", no pirámide

Para una app web Laravel CRUD-pesada, el grueso del valor está en los **tests de integración/feature** (HTTP → DB), no en unit puros. Adoptamos el *testing trophy*:

```
   /‾‾‾‾‾‾‾‾‾‾‾‾‾‾\   E2E / Browser   ← pocos, solo flujos JS críticos (futuro, Dusk)
  /                \
 /   Feature (HTTP) \  ← el backbone: request → response → DB
 \                  /
  \  Integration   /   ← service + repository con DB real
   \    Unit      /     ← puro, sin framework ni DB (rápido)
    \____________/
```

### 2.2 Tres suites en `phpunit.xml`

| Suite | Toca DB | Qué contiene | Velocidad |
|-------|---------|--------------|-----------|
| **Unit** | ❌ Nunca | Lógica pura: gates con `->make()`, métodos de modelo, cálculos extraíbles | ⚡ ms |
| **Integration** | ✅ | Services + Repositories instanciados directos (lo que hoy está mal clasificado en `Unit`) | media |
| **Feature** | ✅ | HTTP completo, agrupado por dominio en subcarpetas | media |

### 2.3 Convención de nombres

Unificar en **inglés descriptivo, sin prefijo `test_`** (ya usamos `#[Test]`, el prefijo es redundante). Los nombres de dominio que son clases reales del código (`Pesaje`, `Vehiculo`, `Organizacion`, `Zona`, `TipoServicio`, etc.) se mantienen tal cual — son nombres propios, no se traducen. Los tests nuevos siguen esto; los viejos se migran de forma oportunista.

```php
// ❌ viejo: prefijo redundante / idioma mixto
public function test_admin_puede_crear(): void

// ✅ nuevo: inglés, sin prefijo test_ (usamos #[Test])
#[Test]
public function admin_can_create_pesaje(): void
```

### 2.4 Reglas de escritura

- **AAA**: Arrange / Act / Assert, separados visualmente.
- **Un concepto por test** (puede tener varios `assert` del mismo concepto).
- **Factories siempre** (nunca `Model::create` manual salvo en el dominio bajo prueba).
- Nombre del test = comportamiento esperado, no implementación.
- Datos mínimos: crear solo lo que el test necesita.

### 2.5 Estándar de calidad — todo test debe ser "A"

Todo test nuevo (y toda mejora a tests existentes) debe cumplir el estándar de calidad **A**. No se mergea código que no lo cumpla.

| Criterio | ✅ Cumple (A) | ❌ No cumple (B o menos) |
|----------|--------------|------------------------|
| **Borde exacto** | Testea el valor exacto en los límites de la condición (`<`, `>`, `>=`, `<=`) | Solo prueba "claramente dentro" y "claramente fuera" |
| **Assert completo** | Verifica **todos** los campos relevantes del resultado (valor_anterior + motivo + usuario_id en logs; estado + metadatos en cancelar/egreso) | Solo verifica uno o dos campos del resultado |
| **Excepción con clave** | Al esperar `ValidationException`, verifica la **clave** del error — `assertArrayHasKey('campo', $e->errors())` en Integration o `assertSessionHasErrors('campo')` en Feature | Solo `expectException(ValidationException::class)` sin verificar qué campo falló |
| **End-to-end en Feature** | Los tests Feature verifican que los **datos persisten correctamente** (neto, estado, log) además del redirect | Solo verifican redirect + count |
| **Datos controlados** | Usa factories con valores **explícitos** para todo campo que el test afirma | Usa valores aleatorios del factory y asume el resultado |

```php
// ❌ B: solo verifica que lanza, no QUÉ falla
$this->expectException(ValidationException::class);
$this->service->marcarEgreso($pesaje, []);

// ✅ A: verifica la clave del error
try {
    $this->service->marcarEgreso($pesaje, []);
    $this->fail('Expected ValidationException');
} catch (ValidationException $e) {
    $this->assertArrayHasKey('estado', $e->errors());
}

// ❌ B: assert parcial del log
$this->assertDatabaseHas('pesajes_log', ['campo' => 'zona_id', 'valor_nuevo' => $id]);

// ✅ A: assert completo del log
$this->assertDatabaseHas('pesajes_log', [
    'pesaje_id'      => $pesaje->id,
    'campo'          => 'zona_id',
    'valor_anterior' => (string) $pesaje->zona_id,
    'valor_nuevo'    => (string) $nuevaZona->id,
    'motivo'         => 'Zona mal cargada',
    'usuario_id'     => $operador->id,
]);
```

> Este estándar aplica desde **Fase 2** en adelante. Los tests de Fase 0–1 (restructuración) se mejoran oportunistamente.

---

## 3. Nueva estructura de carpetas

```
tests/
├── TestCase.php                       → base (mejorada, ver §4.1)
├── Concerns/
│   ├── ActingAsRoles.php              → admin(), operador(), superAdmin()
│   └── InteractsWithTenants.php       → createOrganizacion(), actingInOrg($org)
├── Unit/                              → SOLO puro (sin DB)
│   ├── GateTest.php                   (ya existe)
│   ├── UserTest.php                   (ya existe)
│   └── …cálculos puros extraídos
├── Integration/                      → 🆕 service+repo con DB (movidos desde Unit/)
│   ├── DashboardServiceTest.php
│   ├── VehiculoServiceTest.php
│   ├── UsuarioServiceTest.php
│   ├── TipoServicioServiceTest.php
│   ├── TipoVehiculoServiceTest.php
│   └── PesajeServiceTest.php          🆕
└── Feature/
    ├── Auth/
    │   └── AuthTest.php               (ya existe; + reset/forgot)
    ├── Pesaje/                        🆕
    │   ├── CreatePesajeTest.php
    │   ├── PesajeEgresoTest.php
    │   ├── EditPesajeTest.php
    │   ├── CancelPesajeTest.php
    │   ├── PesajeHistoryTest.php
    │   └── ExportPesajeTest.php
    ├── Tenancy/                       🆕
    │   └── TenantIsolationTest.php
    ├── SuperAdmin/                    🆕
    │   ├── OrganizacionCrudTest.php
    │   ├── OrganizacionUsersTest.php
    │   └── SuperAdminDashboardTest.php
    ├── Reporte/                       🆕
    │   ├── ReporteConfiguracionTest.php
    │   ├── ReporteProgramadoTest.php
    │   └── GenerarEnviarReporteJobTest.php
    ├── Zona/                          🆕
    │   ├── ZonaCrudTest.php
    │   └── ZonaServicioTest.php
    ├── Vehiculo/   · Usuario/   · TipoServicio/   · TipoVehiculo/   · Dashboard/
    │   └── (mover los feature tests existentes aquí)
    └── Operador/
        └── OnboardingTest.php         🆕
```

> Las subcarpetas se auto-descubren bajo cada `<directory>` del testsuite — no requieren editar `phpunit.xml` (salvo el alta de la suite `Integration`).

---

## 4. Andamiaje (Fase 0)

### 4.1 `TestCase.php` mejorado

- Mantener el binding de organización actual.
- Agregar `$this->withoutVite()` para que los feature tests **no dependan de un build de Vite** (hoy `@vite(...)` en el layout exige el manifest; sin esto el CI necesitaría `npm run build`). Acelera CI y desacopla.
- Incluir los traits `ActingAsRoles` e `InteractsWithTenants`.

### 4.2 Trait `ActingAsRoles`

Elimina la duplicación de `admin()`/`operador()`/`payload()` repetida en ~7 archivos:

```php
trait ActingAsRoles
{
    protected function admin(array $attrs = []): User      { return User::factory()->admin()->create($attrs); }
    protected function operador(array $attrs = []): User    { return User::factory()->create($attrs); }
    protected function superAdmin(array $attrs = []): User  { return User::factory()->state(['role' => 'super_admin'])->create($attrs); }
}
```

### 4.3 Trait `InteractsWithTenants`

Clave para los tests de aislamiento. El global scope lee **una sola** instancia `app('organizacion')`, así que para simular dos organizaciones hay que rebindear entre setup y asserts:

```php
trait InteractsWithTenants
{
    protected function createOrganizacion(string $slug): Organizacion
    {
        return Organizacion::firstOrCreate(['slug' => $slug], ['nombre' => "Org $slug", 'activo' => true]);
    }

    /** Ejecuta el callback con $org como tenant activo y restaura el anterior. */
    protected function actingInOrg(Organizacion $org, callable $fn): mixed
    {
        $previo = app()->bound('organizacion') ? app('organizacion') : null;
        app()->instance('organizacion', $org);
        try { return $fn(); }
        finally { $previo ? app()->instance('organizacion', $previo) : app()->forgetInstance('organizacion'); }
    }
}
```

### 4.4 `phpunit.xml` — suite Integration + cobertura

```xml
<testsuites>
    <testsuite name="Unit">        <directory>tests/Unit</directory></testsuite>
    <testsuite name="Integration"> <directory>tests/Integration</directory></testsuite>
    <testsuite name="Feature">     <directory>tests/Feature</directory></testsuite>
</testsuites>
<source>
    <include><directory>app</directory></include>
</source>
<coverage>
    <report><html outputDirectory="build/coverage"/></report>
</coverage>
```

Cobertura corre principalmente en **CI** (driver PCOV en Linux es trivial; en Windows local es opcional vía Xdebug).

---

## 5. Roadmap por fases

Cada fase es entregable e independiente. Orden por riesgo de negocio, no por facilidad.

### Fase 0 — Andamiaje  ·  *sin tests nuevos, prepara el terreno*

- [ ] Crear `tests/Concerns/ActingAsRoles.php` e `InteractsWithTenants.php`.
- [ ] Mejorar `TestCase.php` (`withoutVite`, usar traits).
- [ ] Alta de suite `Integration` en `phpunit.xml` + bloque `<coverage>`.
- [ ] Mover `*ServiceTest.php` de `tests/Unit/` → `tests/Integration/` (cambiar namespace `Tests\Unit` → `Tests\Integration`).
- [ ] Mover feature tests existentes a subcarpetas de dominio (`Feature/Vehiculo/`, etc.).
- [ ] Refactor de los tests existentes para usar los traits (quitar `admin()`/`operador()` locales).
- [ ] Correr `php artisan test` y confirmar 241 verdes tras la reorganización.

### Fase 1 — CI (GitHub Actions)  ·  *el mayor salto hacia "prácticas de la industria"*

Ver §6. Gate en cada push/PR: **Pint → Larastan → tests (con cobertura)**.

### Fase 2 — 🔴 Núcleo: Pesaje + Multi-tenancy  ·  **P0**

El entregable más importante. Cubre el corazón del sistema y la invariante de seguridad #1.

**`Integration/PesajeServiceTest.php`** — lógica de [`PesajeService`](../app/Services/PesajeService.php):
- `crear` calcula `peso_neto = max(0, bruto - tara)` usando la tara del vehículo.
- `crear` marca `alerta_peso = true` cuando el bruto cae fuera del rango del tipo de vehículo.
- `crear` no marca alerta dentro de rango; estado inicial `En predio`, `editado = false`.
- `marcarEgreso` setea `Cerrado` + `hora_salida`; guarda `bruto_salida_kg` si viene.
- `marcarEgreso` lanza `ValidationException` si el pesaje ya está cerrado.
- `editar` exige `motivo` no vacío (lanza si falta).
- `editar` registra en `pesajes_log` solo los campos que cambian (uno por campo).
- `editar` recalcula `peso_neto` cuando cambia `peso_bruto_kg` y marca `editado = true`.
- `editar` no escribe log ni toca `editado` si nada cambió.
- `cancelar` setea `Cancelado` + `cancelado_por_id` + `cancelado_at` + motivo, y deja log de `estado`.
- `cancelar` lanza si ya estaba cancelado.

**`Feature/Pesaje/CreatePesajeTest.php`** — [`Operador\PesajeController@store`](../app/Http/Controllers/Operador/PesajeController.php) + [`StorePesajeRequest`](../app/Http/Requests/StorePesajeRequest.php):
- Operador autenticado crea un pesaje y redirige a `pesajes.show`.
- Admin también puede crear (la request lo autoriza).
- Guest redirige a login.
- Validación: `vehiculo_id`/`tipo_servicio_id`/`zona_id` requeridos y deben existir.
- Validación: `peso_bruto_kg` requerido, entero, `min:1`.
- Validación: `turno` solo `Diurna`/`Nocturna`; `observaciones` máx 500.
- Persiste con `operador_id` = usuario autenticado.

**`Feature/Pesaje/PesajeEgresoTest.php`** — `egreso` + [`EgresoPesajeRequest`](../app/Http/Requests/EgresoPesajeRequest.php):
- Marca egreso y redirige (operador → `historial`, admin → `admin.pesajes.index`).
- `bruto_salida_kg` opcional pero si viene debe ser entero `min:1`.
- Egreso sobre pesaje ya cerrado falla con error de validación.

**`Feature/Pesaje/EditPesajeTest.php`** — `update` + [`UpdatePesajeRequest`](../app/Http/Requests/UpdatePesajeRequest.php):
- `motivo` requerido (la regla lo exige siempre).
- Editar bruto recalcula neto y persiste; aparece en log.
- Campos `sometimes`: editar solo `zona_id` no toca el resto.

**`Feature/Pesaje/CancelPesajeTest.php`** — `cancelar` + [`CancelarPesajeRequest`](../app/Http/Requests/CancelarPesajeRequest.php):
- `motivo` requerido, `min:5`, `max:500` (con mensajes custom).
- Cancela, deja log, redirige con toast.

**`Feature/Pesaje/PesajeHistoryTest.php`** — `index`:
- Admin ve filtros extendidos (zona, servicio, alerta, editados); operador no.
- Filtros por fecha/patente/estado/operario funcionan.
- KPIs del turno se calculan.

**`Feature/Pesaje/ExportPesajeTest.php`** — `export`:
- Devuelve CSV con headers correctos y BOM UTF-8.
- Solo admin (la ruta está bajo `role:admin`).

**`Feature/Tenancy/TenantIsolationTest.php`** — la invariante crítica (usa `InteractsWithTenants`):
- Un admin de Org A **no ve** pesajes/vehículos/usuarios de Org B en los index.
- Un admin de Org A recibe **404** al acceder por ID a un recurso de Org B (route-model binding respeta el scope).
- Crear un recurso lo asigna automáticamente a la org del usuario actuante.
- El `show`/`update`/`cancelar` de un pesaje de otra org no es accesible.
- (Regresión) Verificar que el `withoutGlobalScopes()` del Job no filtra datos de otra org en el flujo normal.

### Fase 3 — 🔴 SuperAdmin / Organización  ·  **P1**

**`Feature/SuperAdmin/OrganizacionCrudTest.php`** — [`OrganizacionController`](../app/Http/Controllers/SuperAdmin/OrganizacionController.php) bajo `role:super_admin`:
- Solo `super_admin` accede; admin y operador → 403; guest → login.
- CRUD: index, store (+ `StoreOrganizacionRequest`), update, destroy, toggle.
- Validaciones de slug único, nombre requerido.

**`Feature/SuperAdmin/OrganizacionUsersTest.php`**:
- `addUser` agrega usuario a la org (+ `AddUserToOrganizacionRequest`); dispara notificación (`Notification::fake`).
- `removeUser` lo quita del pivot sin borrar el usuario.
- `resetUserPassword` cambia el hash.
- `searchUsers` filtra correctamente.

**`Feature/SuperAdmin/SuperAdminDashboardTest.php`** + **`Integration/SuperAdminDashboardServiceTest.php`**:
- Métricas agregadas cross-organización (el super_admin sí ve todo).

### Fase 4 — 🟠 Reportes + Job  ·  **P2**

Aislar dependencias externas (AI, mPDF, mail) con fakes/mocks.

**`Feature/Reporte/GenerarEnviarReporteJobTest.php`** — [`GenerarEnviarReporteJob`](../app/Jobs/GenerarEnviarReporteJob.php):
- `Mail::fake()` → se envía 1 mail por destinatario; clase correcta (`ReporteMensualMail` vs `ReporteAlertaMail` según `tipo`).
- Actualiza `ultimo_envio_at` y `proximo_envio_at`.
- Con `ai_enabled = false` no instancia el servicio de IA.
- `calcularPeriodo` resuelve `mes_anterior`/`mes_actual`/`semana_actual`.
- (mockear `ReporteService`/`PdfService` para no generar PDF real).

**`Feature/Reporte/ReporteConfiguracionTest.php`** / **`ReporteProgramadoTest.php`**:
- CRUD de programados (+ requests `Admin/*`), solo admin.
- `enviarAhoraProgramado` despacha el job (`Queue::fake` → `assertPushed`).
- Update de configuración persiste.

### Fase 5 — 🟠 Zona / ZonaServicio + endpoints sueltos + Auth  ·  **P2–P3**

**`Feature/Zona/ZonaCrudTest.php`** — [`ZonaController`](../app/Http/Controllers/Admin/ZonaController.php) + `Store/UpdateZonaRequest`:
- CRUD + toggle, solo admin, validaciones (nombre, hectáreas, habitantes).

**`Feature/Zona/ZonaServicioTest.php`** — [`ZonaServicioController`](../app/Http/Controllers/Admin/ZonaServicioController.php):
- Vincular servicio a zona (+ turnos/horarios), update, destroy.

**Endpoints sueltos:**
- `Shared\VehiculoController@buscar` (autocomplete de balanza): busca por patente/interno, scope de org.
- `Operador\OnboardingController@store`: marca `onboarding_visto = true` para el usuario.

**Auth (completar):**
- `PasswordResetLinkController` / `NewPasswordController`: flujo forgot/reset password.

### Fase 6 — Arch tests + gate de cobertura  ·  *consolidación*

**`Unit/Arch/ArquitecturaTest.php`** — blindar las reglas de `CLAUDE.md`:
- Ningún controller usa `__invoke`.
- Controllers no contienen `DB::`, `->validate(`, ni `Model::` (lógica/validación fuera del controller).
- Models no dependen de `Mail`/`Http`.
- Requests extienden `FormRequest`; Services viven en `App\Services`; Repos en `App\Repositories`.

> PHPUnit no trae arch tests nativas como Pest. Opciones: (a) tests de reflexión/`Finder` propios (simple, sin dependencias), (b) agregar `ta-tikoma/phpunit-architecture-test`. Recomendado: empezar con (a).

Gate de cobertura en CI: subir umbral mínimo gradualmente (ej. arrancar en el % actual y no permitir que baje).

---

## 6. Gate de calidad — local en `pre-push`

El gate **no corre en GitHub Actions**: se ejecuta localmente antes de cada push
mediante el hook [`.githooks/pre-push`](../.githooks/pre-push), que bloquea el push
si pint, larastan o los tests fallan. El proyecto se testea sobre **SQL Server real**
(no SQLite — ver [`CLAUDE.md`](../CLAUDE.md) y `.env.testing`), por lo que el gate
necesita una instancia de SQL Server accesible localmente.

```sh
git config core.hooksPath .githooks      # activar una vez por clon

# .githooks/pre-push (equivale a `composer check`):
php vendor/bin/pint --test                            # formato
php vendor/bin/phpstan analyse --memory-limit=512M    # análisis estático (larastan)
php artisan test --coverage --min=68                  # tests + cobertura ≥ 68%
```

`deploy.yml` (push a `main`) **no** vuelve a correr el gate: solo buildea la imagen y
deploya. Asume código ya verificado localmente, evitando levantar un SQL Server efímero
en la nube y duplicar el costo del gate que ya pasó en el pre-push.

---

## 7. Definición de "robusto" (criterios de salida)

- ✅ Todo push corre tests + análisis estático + lint vía el hook `pre-push` y se bloquea en rojo.
- ✅ Cobertura medida, visible, con umbral que no puede bajar.
- ✅ El ciclo de vida del Pesaje y el aislamiento multi-tenant tienen tests dedicados.
- ✅ Cero dominios con controller/service en producción y 0 tests.
- ✅ Taxonomía coherente (Unit puro / Integration / Feature por dominio) y naming unificado.
- ✅ Helpers compartidos (sin duplicación de `admin()`/`operador()`).
- ✅ Reglas de arquitectura de CLAUDE.md verificadas automáticamente.

---

## 8. Estimación relativa de esfuerzo

| Fase | Esfuerzo | Riesgo que mitiga |
|------|----------|-------------------|
| F0 Andamiaje | S | — (habilitador) |
| F1 CI | S | Regresiones silenciosas |
| F2 Pesaje + Tenancy | **L** | 🔴 Negocio + seguridad |
| F3 SuperAdmin | M | 🔴 Privilegio alto |
| F4 Reportes + Job | M | 🟠 Subsistema WIP |
| F5 Zona + sueltos + Auth | M | 🟠 Cobertura general |
| F6 Arch + gate | S | Degradación de diseño |

Orden recomendado de ejecución: **F0 → F1 → F2** (entrega el mayor valor temprano), luego F3–F6 según prioridad.
