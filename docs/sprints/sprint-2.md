# Sprint 2 — ABMs completos
**Período:** Semanas 2–3 · 19–30 mayo 2026
**Rama:** `feature/sprint-2-abms`
**Dependencia:** Sprint 1 completado (auth, layouts, rutas protegidas)

## Objetivo
Los 5 ABMs 100% funcionales con baja lógica. Checklist de configuración inicial visible en el panel admin. Sin padrón completo, el módulo Balanza no puede operar.

---

## Sub-sprint 2.1 — Tipos de vehículo

### Tareas
- [ ] Migración `create_tipos_vehiculo_table`: `id`, `nombre`, `peso_min_kg`, `peso_max_kg`, `activo` (default `true`), timestamps
- [ ] `TipoVehiculoRepository`: `all()`, `create()`, `update()`, `deactivate()`, `activate()`
- [ ] `TipoVehiculoService`: `listar()`, `crear()`, `actualizar()`, `desactivar()`
- [ ] `TipoVehiculoController` (resource): `index`, `store`, `update`, `destroy` (→ desactivar, no DELETE físico)
- [ ] `StoreTipoVehiculoRequest`: nombre requerido, `peso_min_kg` < `peso_max_kg`
- [ ] `UpdateTipoVehiculoRequest`: mismas reglas
- [ ] Vista `admin/tipos-vehiculo/index`: tabla con nombre y rangos, búsqueda, modal crear/editar, acción desactivar/activar
- [ ] Seeder con los 4 tipos del brief (Compactador, Volcador, Volquete, Particular)

### Tests unitarios
- `TipoVehiculoServiceTest::test_create_stores_record` — `crear()` con datos válidos → registro en DB
- `TipoVehiculoServiceTest::test_deactivate_sets_activo_false` — `desactivar(id)` → `activo = false` en DB
- `TipoVehiculoServiceTest::test_activate_sets_activo_true` — `activar(id)` → `activo = true`
- `TipoVehiculoServiceTest::test_update_modifies_rangos` — `actualizar()` con nuevos rangos → valores actualizados

### Tests de integración
- `TipoVehiculoTest::test_index_renders_list` — `GET /admin/tipos-vehiculo` → HTTP 200, contiene nombres de tipos
- `TipoVehiculoTest::test_admin_can_create` — `POST /admin/tipos-vehiculo` con datos válidos → HTTP 302, registro en DB
- `TipoVehiculoTest::test_validation_fails_when_min_greater_than_max` — `peso_min_kg` > `peso_max_kg` → HTTP 422
- `TipoVehiculoTest::test_admin_can_deactivate` — `DELETE /admin/tipos-vehiculo/{id}` → `activo = false`, HTTP 302
- `TipoVehiculoTest::test_physical_delete_not_allowed` — no existe ruta que ejecute `DELETE` físico
- `TipoVehiculoTest::test_operador_cannot_access` — `GET /admin/tipos-vehiculo` como operador → HTTP 403

### Tests manuales
- [ ] Crear tipo de vehículo → aparece en la tabla inmediatamente
- [ ] Editar rangos → cambios reflejados en la fila sin recargar
- [ ] Desactivar → tipo queda visible en tabla con estado "Inactivo", no desaparece
- [ ] Activar tipo inactivo → vuelve a estado "Activo"
- [ ] Intentar guardar sin nombre → error de validación visible en el modal
- [ ] Intentar guardar con `peso_min` > `peso_max` → error de validación visible

---

## Sub-sprint 2.2 — Tipos de servicio

### Tareas
- [ ] Migración `create_tipos_servicio_table`: `id`, `nombre`, `tipo_vehiculo_sugerido_id` (FK nullable → `tipos_vehiculo`), `activo`, timestamps
- [ ] Migración `create_tipos_servicio_turnos_table`: PK compuesta `(tipo_servicio_id, turno)`, FK → `tipos_servicio` ON DELETE CASCADE
- [ ] `TipoServicioRepository`, `TipoServicioService`: incluye `getTurnos(id)`, `syncTurnos(id, array)`
- [ ] `TipoServicioController` (resource)
- [ ] Form Requests: `StoreTipoServicioRequest`, `UpdateTipoServicioRequest`
- [ ] Vista index: tabla con tipo sugerido y pills de turno (Diurna / Nocturna), modal con checkboxes de turno
- [ ] Seeder tipos_servicio: Domiciliario, Voluminoso, Barrido, Servicios Especiales, Centros de Transferencia
- [ ] Seeder tipos_servicio_turnos: Domiciliario → Diurna, Domiciliario → Nocturna

### Tests unitarios
- `TipoServicioServiceTest::test_create_without_turnos` — servicio sin filas en `tipos_servicio_turnos` → `getTurnos()` retorna array vacío
- `TipoServicioServiceTest::test_sync_turnos_creates_records` — `syncTurnos(id, ['Diurna','Nocturna'])` → 2 filas en DB
- `TipoServicioServiceTest::test_sync_turnos_removes_unchecked` — sync con `['Diurna']` sobre servicio con 2 turnos → queda solo 1 fila
- `TipoServicioServiceTest::test_deactivate_sets_activo_false`

### Tests de integración
- `TipoServicioTest::test_admin_can_create_without_turnos` — `POST /admin/tipos-servicio` sin turnos → HTTP 302, sin filas en `tipos_servicio_turnos`
- `TipoServicioTest::test_admin_can_create_with_turnos` — `POST` con `turnos[] = ['Diurna','Nocturna']` → 2 filas en `tipos_servicio_turnos`
- `TipoServicioTest::test_turno_rejects_invalid_value` — `turnos[] = ['Mañana']` → HTTP 422
- `TipoServicioTest::test_delete_servicio_cascades_turnos` — eliminar servicio → filas de `tipos_servicio_turnos` eliminadas (CASCADE)
- `TipoServicioTest::test_admin_can_deactivate`
- `TipoServicioTest::test_operador_cannot_access`

### Tests manuales
- [ ] Crear tipo de servicio sin turnos → guarda correctamente, columna turno vacía en tabla
- [ ] Crear Domiciliario con Diurna y Nocturna → dos pills en la tabla
- [ ] Editar servicio: desmarcar Nocturna → solo queda el pill Diurna
- [ ] Modal de creación/edición: checkboxes Diurna / Nocturna independientes
- [ ] Modal de edición: select de tipo de vehículo muestra solo activos

---

## Sub-sprint 2.3 — Zonas

### Tareas
- [ ] Migración `create_zonas_table`: `id`, `nombre`, `tipo_servicio_id` (FK nullable → `tipos_servicio`), `hectareas` (decimal, nullable), `barrios` (int, nullable), `habitantes` (int, nullable), `activo`, timestamps
- [ ] `ZonaRepository`, `ZonaService`
- [ ] `ZonaController` (resource)
- [ ] Form Requests: `StoreZonaRequest`, `UpdateZonaRequest`
- [ ] Vista index: tabla con nombre, servicio, hectáreas, habitantes, estado; modal crear/editar con select de tipo de servicio

### Tests unitarios
- `ZonaServiceTest::test_create_zona_with_all_fields`
- `ZonaServiceTest::test_create_zona_with_only_required_fields` — campos demográficos nullable
- `ZonaServiceTest::test_deactivate_sets_activo_false`

### Tests de integración
- `ZonaTest::test_admin_can_create_zona`
- `ZonaTest::test_zona_with_zero_hectareas_is_valid` — admite cero (no nullable obligatorio)
- `ZonaTest::test_admin_can_deactivate_zona`
- `ZonaTest::test_operador_cannot_access`

### Tests manuales
- [ ] Crear zona sin hectáreas ni habitantes → se guarda, celdas vacías en tabla
- [ ] Editar zona para agregar hectáreas → valor visible en tabla
- [ ] Desactivar zona → no aparece en el select de tipos de servicio al editar

---

## Sub-sprint 2.4 — Padrón de vehículos

### Tareas
- [ ] Migración `create_vehiculos_table`: `id`, `patente`, `numero_interno` (unique), `tara_kg`, `tipo_vehiculo_id` (FK), `titular`, `capacidad_kg` (nullable), `observaciones` (nullable), `activo`, timestamps
- [ ] `VehiculoRepository`, `VehiculoService`
- [ ] `VehiculoController` (resource)
- [ ] Form Requests: `StoreVehiculoRequest` (patente única, `numero_interno` único, `tara_kg` > 0), `UpdateVehiculoRequest`
- [ ] Vista index: tabla con búsqueda por patente/número interno, filtro activo/inactivo, pills de estado
- [ ] Modal crear/editar con todos los campos del padrón

### Tests unitarios
- `VehiculoServiceTest::test_create_vehiculo`
- `VehiculoServiceTest::test_tara_must_be_greater_than_zero`
- `VehiculoServiceTest::test_deactivate_sets_activo_false`
- `VehiculoServiceTest::test_inactive_vehiculo_not_returned_in_search`

### Tests de integración
- `VehiculoTest::test_admin_can_create_vehiculo`
- `VehiculoTest::test_duplicate_patente_fails_validation` — misma patente → HTTP 422
- `VehiculoTest::test_duplicate_numero_interno_fails_validation`
- `VehiculoTest::test_tara_zero_fails_validation`
- `VehiculoTest::test_admin_can_deactivate_vehiculo`
- `VehiculoTest::test_operador_cannot_access_abm`
- `VehiculoBuscarApiTest::test_active_vehicle_returned_by_patente` — `GET /api/vehiculos/buscar?q=ABC` → vehículo activo en respuesta
- `VehiculoBuscarApiTest::test_inactive_vehicle_not_returned` — vehículo inactivo no aparece en la API

### Tests manuales
- [ ] Crear vehículo → aparece en la tabla
- [ ] Intentar crear con patente duplicada → error de validación en modal
- [ ] Intentar crear con tara = 0 → error de validación
- [ ] Desactivar vehículo → desaparece del autocompletado del operador (verificar en Sprint 3)
- [ ] Búsqueda por patente parcial filtra en tiempo real
- [ ] Búsqueda por número interno filtra correctamente
- [ ] Filtro "Inactivos" muestra vehículos desactivados

---

## Sub-sprint 2.5 — ABM Usuarios

### Tareas
- [ ] `UsuarioRepository`, `UsuarioService`
- [ ] `UsuarioController` (resource)
- [ ] `StoreUsuarioRequest`: nombre de usuario único, contraseña requerida al crear
- [ ] `UpdateUsuarioRequest`: sin contraseña (se resetea por acción separada)
- [ ] Vista index: tabla con avatar inicial, nombre completo, pill de rol, estado
- [ ] Modal crear: usuario, nombre completo, rol, contraseña inicial
- [ ] Modal editar: usuario, nombre completo, rol (sin contraseña)
- [ ] Acción "Resetear contraseña": modal con nueva contraseña temporal
- [ ] Acción desactivar/activar: baja lógica

### Tests unitarios
- `UsuarioServiceTest::test_create_user_hashes_password` — contraseña guardada como hash bcrypt
- `UsuarioServiceTest::test_reset_password_updates_hash` — nuevo hash diferente al anterior
- `UsuarioServiceTest::test_deactivate_prevents_login` — usuario inactivo no puede autenticarse

### Tests de integración
- `UsuarioTest::test_admin_can_create_operador`
- `UsuarioTest::test_admin_can_create_admin`
- `UsuarioTest::test_admin_can_reset_password` — `POST /admin/usuarios/{id}/reset-password` → HTTP 302, contraseña actualizada
- `UsuarioTest::test_deactivated_user_cannot_login` — usuario con `activo = false` → HTTP 422 en login
- `UsuarioTest::test_duplicate_username_fails_validation`
- `UsuarioTest::test_operador_cannot_access_abm_usuarios`
- `UsuarioTest::test_no_physical_delete_route_exists`

### Tests manuales
- [ ] Crear operador con contraseña inicial → operador puede hacer login con esas credenciales
- [ ] Resetear contraseña → contraseña vieja ya no funciona, nueva sí
- [ ] Desactivar usuario → no puede hacer login ("Credenciales incorrectas", sin exponer motivo)
- [ ] No existe ningún botón de "Eliminar" en ningún usuario
- [ ] Crear usuario con nombre de usuario ya existente → error de validación en modal

---

## Sub-sprint 2.6 — Checklist de configuración inicial (onboarding admin)

### Tareas
- [ ] `SetupChecklistService::getEstado()`: devuelve array con estado boolean de cada uno de los 5 pasos, calculado en tiempo real desde conteos de DB
- [ ] Componente `components/onboarding/setup-checklist.blade.php`: banner con barra de progreso (`X de 5 completados`), lista de pasos con estado visual, link a cada ABM desde cada paso pendiente
- [ ] Incluir en `layouts/admin.blade.php` — visible en todas las pantallas del panel admin
- [ ] El banner se oculta automáticamente cuando `getEstado()` retorna todos los pasos como `true`

### Tests unitarios
- `SetupChecklistServiceTest::test_all_steps_incomplete_when_db_empty` — DB vacía → todos los pasos `false`
- `SetupChecklistServiceTest::test_step_tipos_vehiculo_complete_when_has_records`
- `SetupChecklistServiceTest::test_step_vehiculos_complete_when_has_records`
- `SetupChecklistServiceTest::test_step_usuarios_operadores_complete_when_operador_exists` — solo cuenta usuarios con `role = 'operador'`
- `SetupChecklistServiceTest::test_all_complete_when_all_tables_have_records`

### Tests de integración
- `SetupChecklistTest::test_banner_visible_when_setup_incomplete` — admin con DB vacía → HTML contiene el componente de checklist
- `SetupChecklistTest::test_banner_hidden_when_all_steps_complete` — todos los conteos > 0 → componente no renderizado

### Tests manuales
- [ ] Con DB vacía: banner visible, todos los pasos con círculo vacío, barra en 0%
- [ ] Cargar tipos de vehículo → paso 1 se marca con check verde, barra avanza
- [ ] Completar todos los pasos → banner desaparece al recargar cualquier página del panel
- [ ] Links de cada paso pendiente llevan al ABM correcto
- [ ] Barra de progreso muestra el conteo correcto en cada estado intermedio

---

## Criterio de completitud del sprint

- [ ] Los 5 ABMs funcionales con crear, editar, desactivar/activar y baja lógica
- [ ] API de búsqueda de vehículos filtra correctamente los inactivos
- [ ] Checklist de configuración visible y reactivo en el panel admin
- [ ] Todos los seeders corren sin errores con `php artisan db:seed`
- [ ] Tests unitarios y de integración pasan en verde
- [ ] Tests manuales verificados: flujo completo de carga del padrón
