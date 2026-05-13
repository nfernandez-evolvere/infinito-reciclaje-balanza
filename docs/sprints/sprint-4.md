# Sprint 4 — Pesajes admin + Dashboard
**Período:** Semanas 6–7 · 16–27 junio 2026
**Rama:** `feature/sprint-4-dashboard`
**Dependencia:** Sprint 3 completado (pesajes en DB, lógica de egreso y edición)

## Objetivo
Visibilidad completa de la operación para el admin: log filtrable de todos los pesajes históricos y panel de control en tiempo real con KPIs, gráficos y widget de camiones en predio.

---

## Sub-sprint 4.1 — Log de pesajes del administrador

### Tareas
- [ ] `PesajesAdminController`: `index`, `update` (editar con motivo), `egreso`
- [ ] Vista `admin/pesajes/index`: tabla completa con filtros combinados
- [ ] Filtros: búsqueda por patente o ID, estado (`Todos` / `En predio` / `Cerrado`), zona, tipo de servicio, operador, rango de fechas, "Solo con alerta de peso", "Solo editados"
- [ ] Header de la vista: conteo filtrado + toneladas netas totales de la vista actual
- [ ] Tabla: ID · entrada · salida · estado · patente · servicio · zona · bruto · tara · neto · operador; pill `Editado` en filas modificadas
- [ ] Mismas acciones que el operador: Marcar egreso · Editar con motivo · Ver historial de cambios
- [ ] Exportar Excel de la vista filtrada: `GET /admin/pesajes/export`
- [ ] Panel de detalle de pesaje (click en fila o botón): todos los campos + historial de cambios si fue editado

### Tests unitarios
- `PesajeFilterServiceTest::test_filter_by_date_range` — pesajes fuera del rango no retornados
- `PesajeFilterServiceTest::test_filter_by_zona`
- `PesajeFilterServiceTest::test_filter_by_estado_en_predio` — solo devuelve pesajes con `estado = 'En predio'`
- `PesajeFilterServiceTest::test_filter_by_operador`
- `PesajeFilterServiceTest::test_filter_con_alerta_only` — `alerta_peso = true` únicamente
- `PesajeFilterServiceTest::test_filter_editados_only` — `editado = true` únicamente
- `PesajeFilterServiceTest::test_no_filters_returns_all`

### Tests de integración
- `PesajesAdminTest::test_admin_can_view_all_pesajes` — `GET /admin/pesajes` → HTTP 200, contiene pesajes de todos los operadores
- `PesajesAdminTest::test_operador_cannot_access_admin_pesajes` — como operador → HTTP 403
- `PesajesAdminTest::test_filter_by_fecha_returns_correct_results`
- `PesajesAdminTest::test_filter_by_zona_returns_correct_results`
- `PesajesAdminTest::test_admin_can_edit_pesaje_with_motivo` — `PUT /admin/pesajes/{id}` con motivo → HTTP 302, log creado
- `PesajesAdminTest::test_admin_edit_records_admin_as_usuario_in_log` — `pesajes_log.usuario_id` = admin, no el operador original
- `PesajesAdminTest::test_admin_can_mark_egreso`
- `PesajesAdminTest::test_excel_export_returns_downloadable_file` — `GET /admin/pesajes/export` → respuesta con `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`

### Tests manuales
- [ ] Admin ve pesajes de todos los operadores (no solo los propios)
- [ ] Filtro por rango de fechas funciona correctamente
- [ ] Combinar filtros (fecha + zona + estado) retorna solo los pesajes que cumplen todos
- [ ] "Limpiar filtros" restaura la vista completa
- [ ] Editar pesaje como admin → en el historial de cambios, el usuario registrado es el admin (no el operador original)
- [ ] Exportar Excel con filtros activos → archivo contiene solo los registros filtrados
- [ ] Exportar Excel con sin filtros → archivo contiene todos los pesajes

---

## Sub-sprint 4.2 — Dashboard: KPIs y gráficos

### Tareas
- [ ] `DashboardService`: `kpisDelDia()`, `kpisDelMes()`, `evolucionDiaria()`, `desgloseByZona()`, `desgloseByTipoVehiculo()`
- [ ] `DashboardController`: single action, pasa datos al view
- [ ] Vista `admin/dashboard`:
  - KPIs del día (4 cards): pesajes, toneladas netas, promedio por viaje, horas operativas; delta vs. promedio histórico en cada card
  - KPIs del mes (3 cards): pesajes acumulados, toneladas acumuladas, días operativos
  - Gráfico de evolución diaria (últimos 7 días) con Chart.js: barras de toneladas netas por día
  - Tabla por zona: pesajes, toneladas netas, porcentaje del total
  - Tabla por tipo de vehículo: pesajes, toneladas netas, porcentaje del total

### Tests unitarios
- `DashboardServiceTest::test_kpis_del_dia_cuenta_solo_pesajes_de_hoy`
- `DashboardServiceTest::test_kpis_del_dia_calcula_toneladas_correctamente` — suma de `peso_neto_kg` / 1000
- `DashboardServiceTest::test_kpis_del_dia_calcula_promedio_por_viaje`
- `DashboardServiceTest::test_kpis_del_mes_acumula_desde_dia_1`
- `DashboardServiceTest::test_evolucion_diaria_retorna_exactamente_7_dias`
- `DashboardServiceTest::test_evolucion_diaria_incluye_dias_sin_pesajes_como_cero`
- `DashboardServiceTest::test_desglose_por_zona_agrupa_correctamente`
- `DashboardServiceTest::test_desglose_por_tipo_vehiculo_agrupa_correctamente`

### Tests de integración
- `DashboardTest::test_admin_can_access_dashboard` — `GET /admin/dashboard` → HTTP 200
- `DashboardTest::test_operador_cannot_access_dashboard` — como operador → HTTP 403
- `DashboardTest::test_dashboard_renders_with_empty_db` — sin pesajes en DB → KPIs en cero, sin errores
- `DashboardTest::test_kpis_reflect_stored_pesajes` — crear N pesajes → KPIs del día reflejan esos datos

### Tests manuales
- [ ] Dashboard con DB vacía → KPIs muestran cero, gráfico vacío, sin errores visuales
- [ ] Registrar un pesaje desde el operador → KPIs del día se actualizan al recargar el dashboard
- [ ] Gráfico de 7 días: cada barra corresponde al día correcto con los datos del día
- [ ] Días sin pesajes muestran barra en cero (no omitidos)
- [ ] Tabla por zona: todas las zonas activas aparecen, porcentajes suman 100%
- [ ] Delta de KPIs: si no hay historial previo, se muestra "sin comparación disponible" (no error)

---

## Sub-sprint 4.3 — Widget camiones en predio + banner de alertas

### Tareas
- [ ] Widget "Camiones en el predio": solo renderizado si hay pesajes con `estado = 'En predio'`; tabla con patente, tipo, servicio, zona, hora de entrada, tiempo transcurrido, operador
- [ ] Banner de alertas activas: solo renderizado si hay alarmas sin resolver; muestra conteo y botón "Revisar" → `/admin/alarmas`
- [ ] Actualización automática del dashboard: polling cada 60 segundos o refresh manual (sin WebSocket en Etapa 1)

### Tests unitarios
- `DashboardServiceTest::test_camiones_en_predio_retorna_solo_estado_en_predio`
- `DashboardServiceTest::test_camiones_en_predio_retorna_array_vacio_cuando_ninguno`

### Tests de integración
- `DashboardTest::test_widget_camiones_hidden_when_no_en_predio_pesajes` — sin pesajes En predio → HTML no contiene el widget
- `DashboardTest::test_widget_camiones_shows_correct_vehicles` — pesaje En predio → widget contiene la patente
- `DashboardTest::test_alertas_banner_hidden_when_no_active_alarmas` — sin alarmas activas → banner no renderizado
- `DashboardTest::test_alertas_banner_visible_when_active_alarmas_exist` — alarma activa en DB → banner visible con conteo correcto

### Tests manuales
- [ ] Sin camiones en predio → sección del widget no aparece
- [ ] Registrar un pesaje → camión aparece en el widget tras recargar
- [ ] Registrar egreso → camión desaparece del widget
- [ ] Con alarmas activas → banner naranja/rojo visible con el número correcto
- [ ] Sin alarmas activas → banner no aparece (no deja espacio vacío)
- [ ] Botón "Revisar" del banner → navega a la pantalla de alarmas

---

## Criterio de completitud del sprint

- [ ] Log de pesajes admin con todos los filtros funcionando
- [ ] Ediciones del admin registradas con el usuario correcto en el log de auditoría
- [ ] Exportación Excel descargable y con datos correctos
- [ ] Dashboard renderiza sin errores con DB vacía y con datos
- [ ] KPIs calculados correctamente según los datos en DB
- [ ] Widget de camiones en predio y banner de alertas condicionales
- [ ] Tests unitarios y de integración pasan en verde
- [ ] Test manual con datos reales del seeder: verificar que KPIs coinciden con conteos manuales
