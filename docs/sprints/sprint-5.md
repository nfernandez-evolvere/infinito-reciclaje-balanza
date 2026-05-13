# Sprint 5 — Reportes
**Período:** Semanas 7–8 · 30 junio – 4 julio 2026
**Rama:** `feature/sprint-5-reportes`
**Dependencia:** Sprint 4 completado (datos en DB con agregaciones validadas en dashboard)

## Objetivo
El admin genera, previsualiza y exporta el reporte mensual en PDF (para entregar al municipio) y Excel (para análisis interno) en menos de 5 minutos desde que abre la pantalla.

---

## Sub-sprint 5.1 — Motor de reportes (Service y lógica de datos)

### Tareas
- [ ] `ReporteService`: método `generar(ReporteRequest $params)` que devuelve un DTO con:
  - Resumen ejecutivo: período, total pesajes, total toneladas netas, promedio diario, días operativos
  - Detalle por zona: pesajes, toneladas netas, promedio por pesaje, kg per cápita (si `habitantes > 0`), kg/ha (si `hectareas > 0`)
  - Detalle por tipo de servicio: pesajes, toneladas netas, % del total
  - Detalle por tipo de vehículo: pesajes, toneladas netas, % del total, cantidad de vehículos únicos
  - Evolución diaria: array con un objeto por día del período (fecha, pesajes, toneladas)
  - Pesajes con alerta de peso: listado completo
- [ ] `ReporteFilterRequest`: período (`mes`, `trimestre`, `rango`), `fecha_desde`, `fecha_hasta`, `zona_id` (nullable), `tipo_servicio_id` (nullable), `tipo_vehiculo_id` (nullable)
- [ ] Validación: `fecha_desde` < `fecha_hasta`, al menos uno de los campos de período definido

### Tests unitarios
- `ReporteServiceTest::test_calcula_totales_correctamente` — suma de todos los pesajes del período
- `ReporteServiceTest::test_filter_by_month_includes_only_that_month`
- `ReporteServiceTest::test_filter_by_custom_date_range`
- `ReporteServiceTest::test_filter_by_zona_excludes_other_zonas`
- `ReporteServiceTest::test_filter_by_tipo_servicio`
- `ReporteServiceTest::test_calcula_per_capita_when_habitantes_available` — `(toneladas * 1000) / habitantes`
- `ReporteServiceTest::test_per_capita_null_when_habitantes_is_zero` — no devuelve cero, devuelve `null`
- `ReporteServiceTest::test_calcula_densidad_when_hectareas_available`
- `ReporteServiceTest::test_densidad_null_when_hectareas_is_zero`
- `ReporteServiceTest::test_evolucion_diaria_includes_every_day_of_period` — incluye días sin pesajes como `{ toneladas: 0 }`
- `ReporteServiceTest::test_porcentajes_suman_100_en_desglose`
- `ReporteServiceTest::test_periodo_sin_pesajes_retorna_ceros_no_error`

### Tests de integración
- `ReporteTest::test_admin_can_generate_reporte` — `POST /admin/reportes/generar` con período válido → HTTP 200 con datos
- `ReporteTest::test_filter_by_zona_retorna_solo_esa_zona`
- `ReporteTest::test_operador_cannot_access_reportes` — como operador → HTTP 403
- `ReporteTest::test_validation_fails_without_period`
- `ReporteTest::test_validation_fails_when_fecha_desde_after_fecha_hasta`

### Tests manuales
- [ ] Generar reporte del mes actual → totales coinciden con los KPIs del mes en el Dashboard
- [ ] Generar con filtro de zona → solo datos de esa zona, el resto no aparece
- [ ] Período sin pesajes → reporte en cero sin errores visuales
- [ ] Per cápita visible en zonas con habitantes cargados; ausente en zonas con 0

---

## Sub-sprint 5.2 — Vista de reportes y preview en pantalla

### Tareas
- [ ] Vista `admin/reportes/index`: panel de filtros (período, zona, servicio, tipo de vehículo) + área de preview
- [ ] Botón **Generar reporte**: envía formulario, renderiza preview en la misma página (sin recargar completo)
- [ ] Preview en pantalla:
  - Resumen ejecutivo (cards con totales)
  - Tabla detalle por zona con indicadores per cápita y densidad
  - Tabla detalle por servicio
  - Tabla detalle por tipo de vehículo
  - Gráfico de evolución diaria (Chart.js)
  - Listado de pesajes con alerta de peso
- [ ] Estado vacío cuando no se generó ningún reporte todavía: "Seleccioná un período y hacé clic en Generar reporte"
- [ ] Estado sin datos: "No hay pesajes registrados para el período seleccionado"

### Tests de integración
- `ReportePreviewTest::test_preview_renders_with_data` — `POST /admin/reportes/generar` con pesajes en DB → respuesta contiene tablas con datos
- `ReportePreviewTest::test_preview_renders_empty_state_when_no_pesajes` — período sin datos → mensaje de sin datos

### Tests manuales
- [ ] Seleccionar mes con datos → clic en "Generar reporte" → preview aparece con todas las secciones
- [ ] Cambiar los filtros y volver a generar → preview se actualiza correctamente
- [ ] Sin generar reporte: área de preview muestra el estado vacío, no una tabla en blanco
- [ ] Con filtro de zona activo → tabla de zonas muestra solo esa zona; tablas de servicio y vehículo solo incluyen datos de esa zona
- [ ] Gráfico de evolución: cada punto del eje X es un día del período seleccionado

---

## Sub-sprint 5.3 — Exportación PDF y Excel

### Tareas
- [ ] Instalar `knplabs/snappy` + `wkhtmltopdf`; configurar `WKHTMLTOPDF_BINARY` en `.env` (diferente para Windows dev vs Linux prod); agregar `'no-sandbox' => true` para entorno headless Linux
- [ ] Instalar `maatwebsite/laravel-excel`
- [ ] Vista Blade `admin/reportes/pdf.blade.php`: layout de impresión con logo, encabezado institucional, todas las secciones del reporte; sin sidebar ni navbar
- [ ] `ReportePdfExport` usando Snappy: `GET /admin/reportes/pdf` → descarga PDF con los filtros activos de la sesión
- [ ] `ReporteExcelExport` class: `GET /admin/reportes/excel` → descarga Excel con pesaje a pesaje (todos los campos) + hoja de resumen
- [ ] Los filtros activos en la vista se pasan como query params a ambas rutas de exportación

### Tests de integración
- `ReporteExportTest::test_pdf_download_returns_pdf_content_type` — `GET /admin/reportes/pdf?mes=2026-06` → `Content-Type: application/pdf`
- `ReporteExportTest::test_excel_download_returns_xlsx_content_type` — `GET /admin/reportes/excel?mes=2026-06` → `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- `ReporteExportTest::test_export_respects_filters` — exportar con `zona_id=1` → Excel contiene solo pesajes de esa zona
- `ReporteExportTest::test_operador_cannot_export` — como operador → HTTP 403

### Tests manuales
- [ ] Exportar PDF → archivo descargable, legible y con formato correcto (logo, encabezado, todas las secciones)
- [ ] PDF no muestra el sidebar ni la navbar del sistema
- [ ] Exportar Excel → archivo con hoja de resumen y hoja de detalle de pesajes
- [ ] Excel con filtro de zona → solo pesajes de esa zona en la hoja de detalle
- [ ] **Prueba crítica en servidor Linux:** verificar que la generación de PDF funciona con el path de wkhtmltopdf de Linux y la opción `no-sandbox`
- [ ] PDF generado en Linux es idéntico (o equivalente) al generado en Windows

---

## Criterio de completitud del sprint

- [ ] Motor de reportes calcula correctamente todos los indicadores con datos reales
- [ ] Per cápita y densidad muestran `null` / vacío (no cero) cuando los datos demográficos no están cargados
- [ ] Preview en pantalla renderiza sin errores para períodos con y sin datos
- [ ] PDF exportable en Windows y Linux
- [ ] Excel con hoja de detalle y hoja de resumen
- [ ] Tests unitarios y de integración pasan en verde
- [ ] Test manual: totales del reporte coinciden con los KPIs del Dashboard para el mismo período
