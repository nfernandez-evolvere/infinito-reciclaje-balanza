# Sprint 6 — Alarmas + QA
**Período:** Semanas 8–9 · 7–14 julio 2026
**Rama:** `feature/sprint-6-alarmas`
**Dependencia:** Sprints 1–5 completados

## Objetivo
Detección proactiva de anomalías operativas. QA end-to-end con datos reales. Buffer de 2 días para correcciones antes del go-live el 14/07/2026.

---

## Sub-sprint 6.1 — Detección y registro de alarmas

### Tareas
- [ ] Migración `create_alarmas_table`: `id`, `tipo` (enum: `gap_pesajes`, `peso_inusual`, `frecuencia_atipica`), `descripcion`, `zona_id` (FK nullable), `vehiculo_id` (FK nullable), `resuelta` (boolean, default `false`), `resuelta_por` (FK nullable → `users`), `comentario_resolucion` (nullable), timestamps
- [ ] Migración `create_config_alarmas_table`: `id`, `tipo`, `umbral_min`, `umbral_max`, `activo`; seeder con valores por defecto (gap: 60 min, frecuencia: 50% desviación)
- [ ] `AlarmaService`: `detectarGap()`, `detectarPesoInusual(Pesaje $pesaje)`, `detectarFrecuenciaAtipica()`
- [ ] Disparar `detectarPesoInusual()` en el `PesajeService::crear()` — si `alerta_peso = true`, crear alarma automáticamente
- [ ] `AlarmaScheduledJob` o check por comando Artisan para `detectarGap()` y `detectarFrecuenciaAtipica()` — ejecutar cada 30 minutos durante horario operativo (8:00–18:00)
- [ ] No crear alarma duplicada si ya existe una activa del mismo tipo para la misma zona/vehículo

### Tests unitarios
- `AlarmaServiceTest::test_detectar_gap_creates_alarma_when_over_threshold` — última pesaje hace más de `umbral_min` minutos → alarma creada
- `AlarmaServiceTest::test_detectar_gap_no_alarma_outside_operating_hours` — fuera del horario 8:00–18:00 → sin alarma
- `AlarmaServiceTest::test_detectar_gap_no_alarma_when_under_threshold`
- `AlarmaServiceTest::test_detectar_peso_inusual_creates_alarma_for_alerta_peso_true` — pesaje con `alerta_peso = true` → alarma creada
- `AlarmaServiceTest::test_detectar_peso_inusual_no_alarma_for_alerta_peso_false`
- `AlarmaServiceTest::test_no_duplicate_alarma_when_active_already_exists` — llamar dos veces → solo 1 alarma en DB
- `AlarmaServiceTest::test_detectar_frecuencia_creates_alarma_when_deviation_exceeds_threshold`

### Tests de integración
- `AlarmaDeteccionTest::test_alarma_peso_created_automatically_on_out_of_range_pesaje` — `POST /pesajes` con peso fuera de rango → alarma en DB
- `AlarmaDeteccionTest::test_no_alarma_on_normal_pesaje` — peso en rango → sin alarma creada
- `AlarmaDeteccionTest::test_gap_alarma_created_when_threshold_exceeded` — simular tiempo sin pesajes → comando Artisan crea alarma

### Tests manuales
- [ ] Registrar pesaje con peso fuera del rango → en el Dashboard aparece el banner de alertas activas
- [ ] El banner muestra el tipo "Peso inusual" con la descripción del pesaje
- [ ] Registrar dos pesajes fuera de rango en la misma zona → solo 1 alarma del tipo (no duplicada)
- [ ] Simular gap (no registrar pesajes durante más de 60 minutos en horario operativo) → alarma de gap aparece
- [ ] Fuera del horario operativo → sin alarma de gap, aunque no haya pesajes

---

## Sub-sprint 6.2 — Panel de alarmas y configuración de umbrales

### Tareas
- [ ] `AlarmaController`: `index`, `resolve`
- [ ] Vista `admin/alarmas/index`:
  - Lista de alarmas activas con tipo, descripción, zona/vehículo afectado, fecha de creación
  - Lista de alarmas resueltas (historial) con quién resolvió, cuándo y comentario
  - Acción **Marcar como resuelta**: modal con campo `comentario_resolucion` (obligatorio)
  - Link desde cada alarma de tipo `peso_inusual` al pesaje correspondiente en el log de pesajes
- [ ] Vista `admin/alarmas/configuracion`:
  - Formulario para editar umbrales de cada tipo de alarma
  - Toggle para activar/desactivar cada tipo
- [ ] `AlarmaConfigController`: `edit`, `update`

### Tests unitarios
- `AlarmaServiceTest::test_resolver_sets_resuelta_true`
- `AlarmaServiceTest::test_resolver_stores_usuario_and_comentario`
- `AlarmaServiceTest::test_resolver_rejects_empty_comentario`

### Tests de integración
- `AlarmaTest::test_admin_can_view_alarmas_list` — `GET /admin/alarmas` → HTTP 200
- `AlarmaTest::test_admin_can_resolve_alarma` — `POST /admin/alarmas/{id}/resolver` con comentario → `resuelta = true`, HTTP 302
- `AlarmaTest::test_resolve_requires_comentario` — sin comentario → HTTP 422
- `AlarmaTest::test_resolved_alarma_moves_to_historial` — alarma resuelta no aparece en la lista activa
- `AlarmaTest::test_operador_cannot_access_alarmas` — como operador → HTTP 403
- `AlarmaConfigTest::test_admin_can_update_umbral` — `PUT /admin/alarmas/configuracion` → valores actualizados en DB
- `AlarmaConfigTest::test_deactivating_type_stops_detection` — tipo desactivado → no se crean alarmas de ese tipo

### Tests manuales
- [ ] Lista de alarmas activas visible con tipo, descripción y fecha
- [ ] Marcar como resuelta con comentario → alarma pasa al historial
- [ ] Intentar resolver sin comentario → botón deshabilitado o error visible
- [ ] Historial muestra quién resolvió, cuándo y el comentario ingresado
- [ ] Link desde alarma de peso → lleva al pesaje específico en el log de pesajes admin
- [ ] Cambiar umbral de gap de 60 a 30 minutos → la próxima detección usa el nuevo umbral
- [ ] Desactivar tipo "frecuencia_atipica" → no se generan más alarmas de ese tipo

---

## Sub-sprint 6.3 — QA end-to-end

Este sub-sprint no tiene tareas de desarrollo. Es validación integral del sistema completo con datos reales o del seeder completo.

### Tests de regresión (automatizados)
Ejecutar el suite completo antes de cada sesión de QA:
```bash
php artisan test --parallel
```
Todos los tests de los sprints anteriores deben pasar en verde antes de iniciar QA manual.

### Tests manuales — Flujo operador completo
- [ ] Login como Roberto → modal de bienvenida aparece en primer login
- [ ] Registrar 5 pesajes de vehículos distintos, distintos servicios y zonas
- [ ] Registrar egreso de 3 de esos camiones
- [ ] Editar un pesaje con motivo (corregir el peso)
- [ ] Verificar que el historial de cambios del pesaje editado es correcto
- [ ] Cerrar sesión y volver a hacer login → modal de bienvenida no reaparece
- [ ] Usar solo atajos de teclado (sin mouse) para registrar un pesaje completo

### Tests manuales — Flujo admin completo
- [ ] Login como Nacho → si el padrón está vacío, el checklist de configuración inicial es visible
- [ ] Cargar padrón completo (tipos vehículo, servicios, zonas, vehículos, usuarios) → checklist desaparece
- [ ] Ver Dashboard: KPIs reflejan los pesajes registrados por Roberto
- [ ] Widget de camiones en predio muestra los 2 camiones sin egreso registrado
- [ ] Revisar log de pesajes: ver el pesaje editado, confirmar que el historial de cambios es correcto
- [ ] Editar un pesaje adicional desde el panel admin → usuario registrado en el log es Nacho
- [ ] Generar reporte del período de prueba → totales coinciden con los KPIs del Dashboard
- [ ] Exportar PDF → archivo correcto, legible, con encabezado
- [ ] Exportar Excel → archivo con detalle de pesajes y hoja de resumen
- [ ] Verificar alarma de peso inusual generada por el pesaje fuera de rango
- [ ] Resolver la alarma con un comentario descriptivo
- [ ] Dashboard no muestra más el banner de alertas

### Tests manuales — Casos borde
- [ ] Operador sin conexión: desconectar red → aviso "Sin conexión" visible; registrar pesaje → reconectar → datos sincronizados
- [ ] Intentar registrar un vehículo que no está en el padrón → autocompletado sin resultados, flujo correcto
- [ ] Intentar guardar un pesaje con el formulario incompleto → no se puede
- [ ] Intentar acceder a rutas del admin como operador (directo en URL) → 403
- [ ] Desactivar un vehículo del padrón → no aparece más en el autocompletado del operador
- [ ] Modificar la tara de un vehículo → pesajes anteriores mantienen la tara original

### Tests manuales — Entorno Linux (servidor de producción)
- [ ] Deploy en servidor Linux con la configuración de `.env` de producción
- [ ] `php artisan migrate` en producción → sin errores
- [ ] Generación de PDF con `wkhtmltopdf` en Linux → archivo correcto
- [ ] Generación de Excel → archivo correcto
- [ ] Login y flujo completo de pesaje desde el servidor de producción

### Tests manuales — UX Writing y accesibilidad
- [ ] Todos los mensajes de error son en español, usan voseo y no exponen detalles técnicos
- [ ] El operador nunca ve un stack trace ni un mensaje en inglés
- [ ] Mensajes de validación son específicos ("Ingresá el peso bruto", no "Campo requerido")
- [ ] Los estados vacíos tienen copy descriptivo (no "No results" ni "null")

---

## Criterio de completitud del sprint (= criterio de go-live)

- [ ] Suite de tests completa pasa en verde (`php artisan test --parallel`)
- [ ] Flujo completo del operador validado con Roberto en condiciones reales
- [ ] Flujo completo del admin validado con Nacho en condiciones reales
- [ ] PDF generado correctamente en el servidor Linux de producción
- [ ] Padrón cargado con los datos reales de la Municipalidad de Corrientes
- [ ] Alarmas activas y configurables
- [ ] Base de conocimiento completa y revisada con los usuarios
- [ ] Sin bugs críticos o bloqueantes abiertos
