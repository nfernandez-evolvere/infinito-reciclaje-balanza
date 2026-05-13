# Sprint 3 — Módulo Balanza
**Período:** Semanas 4–5 · 2–13 junio 2026
**Rama:** `feature/sprint-3-balanza`
**Dependencia:** Sprint 2 completado (padrón cargado, APIs de búsqueda listas)

## Objetivo
Pantalla principal del operador: flujo completo de pesaje en menos de 10 segundos con autocompletado, cálculo automático de neto y auditoría de ediciones. Historial del turno con egreso y correcciones.

---

## Sub-sprint 3.1 — Migraciones, modelos y APIs de autocompletado

### Tareas
- [ ] Migración `create_pesajes_table`: `id`, `vehiculo_id` (FK), `operador_id` (FK → `users`), `tipo_servicio_id` (FK), `zona_id` (FK), `turno` (nvarchar(10) nullable, CHECK IN 'Diurna'/'Nocturna' — NULL si el servicio no tiene turnos), `peso_bruto_kg`, `peso_tara_kg` (copia de tara al momento del ingreso), `peso_neto_kg` (calculado), `alerta_peso` (boolean), `observaciones` (nullable), `estado` (enum: `En predio`, `Cerrado`), `hora_salida` (nullable timestamp), `bruto_salida_kg` (nullable), `editado` (boolean, default `false`), timestamps
- [ ] Migración `create_pesajes_log_table`: `id`, `pesaje_id` (FK), `campo`, `valor_anterior`, `valor_nuevo`, `motivo`, `usuario_id` (FK), timestamps
- [ ] `PesajeRepository`, `PesajeLogRepository`
- [ ] `PesajeService`: `crear()`, `marcarEgreso()`, `editar()`
- [ ] `PesajeController`, `EgresoPesajeController`
- [ ] Form Requests: `StorePesajeRequest`, `UpdatePesajeRequest`, `EgresoPesajeRequest`
- [ ] `GET /api/vehiculos/buscar?q={texto}` → retorna vehículos activos con patente, número interno, tara, tipo, titular (máx. 6 resultados)
- [ ] `GET /api/servicios/{id}/zonas` → retorna zonas activas que tienen ese servicio asignado en `zona_servicios`; cada item incluye `{ id, nombre, turnos: [] }` para que el frontend sepa si debe mostrar el select de turno al elegir esa zona

### Tests unitarios
- `PesajeServiceTest::test_crear_copies_tara_from_vehiculo` — `peso_tara_kg` del pesaje = `tara_kg` del vehículo al momento de crear
- `PesajeServiceTest::test_crear_calculates_peso_neto` — `peso_neto_kg` = `peso_bruto_kg` - `peso_tara_kg`
- `PesajeServiceTest::test_crear_sets_alerta_when_peso_out_of_range` — peso fuera del rango del tipo → `alerta_peso = true`
- `PesajeServiceTest::test_crear_no_alerta_when_peso_in_range`
- `PesajeServiceTest::test_editar_creates_log_entry_per_modified_field` — editar `zona_id` → 1 entrada en `pesajes_log`
- `PesajeServiceTest::test_editar_creates_multiple_log_entries_for_multiple_fields`
- `PesajeServiceTest::test_editar_sets_editado_true`
- `PesajeServiceTest::test_marcar_egreso_sets_estado_cerrado`
- `PesajeServiceTest::test_marcar_egreso_sets_hora_salida`

### Tests de integración
- `VehiculoBuscarApiTest::test_returns_matches_by_patente` — `GET /api/vehiculos/buscar?q=ABC` → array con vehículos que contienen "ABC" en patente
- `VehiculoBuscarApiTest::test_returns_matches_by_numero_interno`
- `VehiculoBuscarApiTest::test_returns_at_most_6_results`
- `VehiculoBuscarApiTest::test_inactive_vehicles_not_returned`
- `VehiculoBuscarApiTest::test_empty_query_returns_empty_array`
- `ServicioZonasApiTest::test_returns_active_zonas_for_servicio` — `GET /api/servicios/{id}/zonas` → array de zonas activas del servicio
- `ServicioZonasApiTest::test_returns_empty_array_when_no_zonas` — servicio sin zonas asociadas → array vacío
- `ServicioZonasApiTest::test_inactive_zonas_not_returned` — zona inactiva del servicio no aparece en la respuesta
- `ServicioZonasApiTest::test_includes_tipo_vehiculo_sugerido` — respuesta incluye `tipo_vehiculo_sugerido` del servicio
- `ServicioZonasApiTest::test_each_zona_includes_turnos_array` — zona con Domiciliario configurado → item incluye `turnos: ['Diurna','Nocturna']`; zona con Barrido (sin turnos) → `turnos: []`
- `ServicioZonasApiTest::test_zona_not_assigned_to_service_not_returned` — zona que no tiene ese servicio en `zona_servicios` no aparece en la respuesta

### Tests manuales
- [ ] `GET /api/vehiculos/buscar?q=ABC` en Postman/browser → respuesta JSON con datos correctos
- [ ] Buscar vehículo inactivo por patente → no aparece en la respuesta
- [ ] `GET /api/servicios/1/zonas` → respuesta JSON con array de zonas activas del servicio y tipo de vehículo sugerido

---

## Sub-sprint 3.2 — Formulario de pesaje (3 pasos)

### Tareas
- [ ] Vista `operador/pesaje`: 3 pasos secuenciales con Alpine.js
- [ ] **Paso 1 — Vehículo:** input libre, popper de autocompletado (hasta 6 resultados), Enter selecciona el primero, badges de solo lectura (Tara · Tipo · Titular · Interno) aparecen al seleccionar
- [ ] **Paso 2 — Tipo de servicio:** select nativo → al elegir servicio, carga vía API las zonas activas con sus turnos; popula select de zona; al elegir zona, si esa zona+servicio tiene turnos configurados, aparece el select de turno obligatorio; badge de tipo de vehículo habitual; warning naranja si tipo del vehículo ≠ sugerido (no bloquea)
- [ ] **Paso 3 — Peso bruto:** input numérico estilo display, Tara y Neto estimado actualizados en tiempo real; borde verde si en rango / naranja si fuera de rango; hint con rango siempre visible
- [ ] Campo `observaciones` editable (autocompleta desde padrón del vehículo)
- [ ] Summary card (verde cuando el form está completo): vehículo, servicio, zona, tipo, bruto, tara, neto, operador
- [ ] Barra sticky inferior: `Limpiar (Esc)` · hint contextual · `GUARDAR PESAJE (Ctrl+S)`
- [ ] Chips de atajos visibles en pantalla: `↵` · `Ctrl+S` · `Esc`
- [ ] Overlay de éxito (1,1 s): check animado, "Pesaje guardado", auto-dismiss → foco regresa al input de vehículo
- [ ] Confirmación de logout si hay form con datos sin guardar

### Tests unitarios
Cubiertos por `PesajeServiceTest` del sub-sprint anterior.

### Tests de integración
- `StorePesajeTest::test_operador_can_store_pesaje` — `POST /operador/pesajes` con datos completos → HTTP 302, registro en DB
- `StorePesajeTest::test_peso_neto_calculated_and_stored_correctly`
- `StorePesajeTest::test_tara_copied_from_vehiculo_at_store_time` — modificar tara del vehículo después → pesaje existente mantiene tara original
- `StorePesajeTest::test_alerta_peso_stored_correctly`
- `StorePesajeTest::test_admin_cannot_store_via_operador_route` — `POST /operador/pesajes` como admin → HTTP 403
- `StorePesajeTest::test_validation_requires_vehiculo_id`
- `StorePesajeTest::test_validation_requires_tipo_servicio_id`
- `StorePesajeTest::test_validation_requires_peso_bruto`
- `StorePesajeTest::test_validation_rejects_peso_bruto_zero_or_negative`
- `StorePesajeTest::test_turno_required_when_service_has_turnos` — Domiciliario sin `turno` → HTTP 422
- `StorePesajeTest::test_turno_null_accepted_when_service_has_no_turnos` — Barrido sin `turno` → HTTP 302

### Tests manuales
- [ ] Flujo completo: buscar vehículo → Enter → elegir servicio → ingresar peso → Ctrl+S → overlay de éxito → formulario limpio. Tiempo total < 10 segundos
- [ ] Seleccionar vehículo → badges de Tara, Tipo, Titular y N° interno se completan automáticamente
- [ ] Cambiar tipo de servicio → select de zona se repopula con las zonas activas de ese servicio
- [ ] Elegir Domiciliario → zonas con turnos configurados; al elegir una zona → aparece select de turno (Diurna / Nocturna); el botón Guardar queda deshabilitado hasta elegir turno
- [ ] Elegir Barrido → zonas sin turnos configurados; al elegir zona → no aparece select de turno; avanza directo al peso
- [ ] Peso dentro del rango → borde verde, sin aviso
- [ ] Peso fuera del rango → borde naranja, aviso naranja con rango esperado
- [ ] Aviso naranja no impide guardar (el botón sigue habilitado)
- [ ] Form incompleto (falta el peso) → botón GUARDAR deshabilitado
- [ ] Esc limpia el formulario completamente
- [ ] Ctrl+S guarda cuando el form está completo; no hace nada si está incompleto
- [ ] Cerrar pestaña con form sucio → navegador pide confirmación

---

## Sub-sprint 3.3 — Historial del turno, egreso y edición

### Tareas
- [ ] Vista `operador/historial`: KPIs del turno en el header (pesajes, toneladas netas, promedio, camiones en predio)
- [ ] Tabla del turno: entrada · salida · estado (pill) · patente · servicio · zona · bruto · tara · neto; pill "Editado" en filas modificadas
- [ ] Empty state: "Sin pesajes en este turno todavía."
- [ ] Acción **Marcar egreso** (solo en filas `En predio`): modal con hora actual + `bruto_salida_kg` opcional → estado `Cerrado`
- [ ] Acción **Editar** (propios del turno, solo `En predio` o `Cerrado` del día): modal con campos editables + `motivo` obligatorio → crea entradas en `pesajes_log`
- [ ] Acción **Ver historial de cambios**: modal read-only con el log (campo · anterior → nuevo · motivo · usuario · fecha)
- [ ] Footer sticky del turno: último pesaje + acumulados + camiones en predio (actualización en tiempo real)

### Tests unitarios
- `PesajeServiceTest::test_marcar_egreso_rejects_already_closed` — pesaje `Cerrado` → excepción
- `PesajeServiceTest::test_editar_requires_motivo` — editar sin `motivo` → excepción de validación
- `PesajeServiceTest::test_recalculates_neto_when_bruto_edited` — editar `peso_bruto_kg` → `peso_neto_kg` recalculado

### Tests de integración
- `HistorialTurnoTest::test_only_shows_today_pesajes` — pesajes de ayer no aparecen en el historial del turno
- `HistorialTurnoTest::test_shows_kpis_correctly`
- `EgresoPesajeTest::test_operador_can_mark_egreso` — `POST /operador/pesajes/{id}/egreso` → estado `Cerrado`, HTTP 302
- `EgresoPesajeTest::test_cannot_mark_egreso_on_closed_pesaje` — pesaje ya cerrado → HTTP 422
- `EditPesajeTest::test_operador_can_edit_own_pesaje` — `PUT /operador/pesajes/{id}` con motivo → HTTP 302
- `EditPesajeTest::test_edit_requires_motivo` — `motivo` vacío → HTTP 422
- `EditPesajeTest::test_edit_creates_log_entry` — 1 entrada en `pesajes_log` por campo modificado
- `EditPesajeTest::test_edit_sets_editado_true`
- `EditPesajeTest::test_operador_cannot_edit_other_operators_pesaje`

### Tests manuales
- [ ] Historial muestra los pesajes del turno con estados correctos
- [ ] KPIs se actualizan al registrar un nuevo pesaje (sin recargar la página)
- [ ] Marcar egreso → estado cambia a CERRADO, hora de salida visible, botón "Marcar egreso" desaparece
- [ ] Intentar marcar egreso en pesaje ya cerrado → no aparece el botón
- [ ] Editar peso bruto → neto se recalcula correctamente, pill "Editado" aparece en la fila
- [ ] Editar sin escribir motivo → botón Guardar deshabilitado dentro del modal
- [ ] Ver historial de cambios → muestra: campo, valor anterior, valor nuevo, motivo, usuario, fecha
- [ ] El historial de cambios no es editable (read-only)

---

## Sub-sprint 3.4 — Modal de bienvenida al operador (onboarding)

### Tareas
- [ ] Componente `components/onboarding/bienvenida-operador.blade.php`: modal con los 3 pasos del formulario ilustrados, atajos de teclado, botón "Entendido"
- [ ] En `layouts/operador.blade.php`: mostrar modal automáticamente si `auth()->user()->onboarding_visto === false`
- [ ] Ruta `POST /operador/onboarding/visto` → `User::update(['onboarding_visto' => true])`; responde JSON `{ "ok": true }` para Alpine
- [ ] Botón `?` en el header del layout operador: reabre el modal sin modificar el flag

### Tests unitarios
- `OnboardingServiceTest::test_mark_as_seen_sets_flag_true` — `marcarVisto(user)` → `onboarding_visto = true` en DB

### Tests de integración
- `OnboardingTest::test_modal_rendered_when_onboarding_not_seen` — `GET /operador/pesaje` con `onboarding_visto = false` → HTML contiene el componente modal
- `OnboardingTest::test_modal_not_rendered_when_already_seen` — `onboarding_visto = true` → componente no renderizado
- `OnboardingTest::test_post_visto_sets_flag` — `POST /operador/onboarding/visto` → `onboarding_visto = true` en DB, respuesta JSON `{ "ok": true }`
- `OnboardingTest::test_unauthenticated_cannot_post_visto` — sin sesión → HTTP 401

### Tests manuales
- [ ] Primer login del operador → modal aparece automáticamente al cargar la pantalla de pesaje
- [ ] Clic en "Entendido" → modal se cierra, no vuelve a aparecer en el próximo login
- [ ] Clic en el ícono `?` del header → modal se abre nuevamente
- [ ] Abrir modal con `?` después de haberlo visto → `onboarding_visto` permanece `true` (no se resetea)

---

## Criterio de completitud del sprint

- [ ] Flujo completo de pesaje funcional en < 10 segundos
- [ ] Cálculo correcto de neto y copia de tara al momento del ingreso
- [ ] Historial con egreso y edición auditada operativos
- [ ] Modal de bienvenida al primer login del operador
- [ ] Tests unitarios y de integración pasan en verde
- [ ] Test manual de flujo completo con datos del padrón real (o seeder)
