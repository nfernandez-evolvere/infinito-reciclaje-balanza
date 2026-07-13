# Plan — Generador de reportes v2

> Estado: **en curso** · Inicio: 03/07/2026
> Objetivo: llevar los reportes actuales a los 3 formatos de referencia entregados por el
> cliente (`docs/reportes/reportes-03-07-2026/`), **sin eliminar el generador actual** —
> se marca *deprecated* y se mantiene funcionando en paralelo.

---

## 1. Los 3 formatos de referencia

Los tres archivos de `docs/reportes/reportes-03-07-2026/` **no son tres reportes distintos**:
son tres formatos de salida de un mismo informe mensual (Mayo 2026, Corrientes).

| Archivo | Formato | Contenido |
|---|---|---|
| `Reporte Mayo.- Pesajes.pdf` | PDF, 18 págs, A4 apaisado | Informe institucional visual para el municipio |
| `Reporte Mayo.pptx` | PPTX, 18 slides | **Contenido idéntico al PDF**, editable en PowerPoint |
| `Reporte mayo por tipo de servicio.xlsx` | XLSX, 8 hojas | Workbook analítico detallado |

El PDF y el PPTX son la **misma pieza**. El Excel aporta el detalle profundo.

---

## 2. Decisiones tomadas

| Punto | Decisión | Motivo |
|---|---|---|
| **PPTX** | El PDF apaisado **es** el deck. Se entrega PDF rediseñado; no se genera `.pptx` real | Contenido idéntico; evita sumar `phpoffice/phppresentation` y perder fidelidad visual |
| **Descripciones de servicio** | Agregar `descripcion` (nullable) a `tipos_servicio` | Ligado al dato real; el admin lo carga una vez; conteo de zonas automático |
| **Prioridad** | **Excel detallado primero** (por N° interno → por servicios), luego PDF rediseñado | Mayor valor, 100% factible hoy, sin decisiones de diseño bloqueantes |
| **Regla canónica de servicio** | Clasificar **todo** por `pesaje.tipo_servicio_id`; dentro de cada servicio, desglosar por su zona | Los totales cierran (evita la inconsistencia del ejemplo, ver §5) |
| **Filtro de cancelados** | Excluir `estado = 'Cancelado'` (ya lo hace `paraReporte()`) | Consistencia con el dashboard |

---

## 3. Auditoría de factibilidad con los datos de hoy

**Veredicto: ~90% se genera con el modelo actual.** Lo que falta es *cálculo nuevo*
(barato, en memoria sobre la misma colección de pesajes), no datos nuevos.

Confirmado en el modelo:
- `vehiculos.numero_interno` (nullable) → hoja "Por N° interno".
- `zonas.tipo_servicio_id` (cada zona pertenece a un servicio) → todo el corte "por servicio".
- `pesajes.tipo_servicio_id`, `turno`, `zonas.hectareas`, `zonas.habitantes`, `zonas.geojson` → por servicio, kg/ha, kg/hab, choropleth.
- `PesajeRepository::paraReporte()` ya excluye cancelados y eager-loadea `zona`, `vehiculo.tipoVehiculo`, `tipoServicio`, `operador`.

### Brechas de datos (únicas)
- **A — Descripciones de servicio** (PDF pág. 9): `tipos_servicio` solo tiene `nombre`.
  → Fase 3 agrega `descripcion`. (`config.servicios` json es contenido libre de "quiénes
  somos", NO ligado a los tipos reales.)
- **B — Columnas de la hoja cruda** del cliente que no modelamos: `Marca`, `Residuo`,
  `Generador`, `Chofer`, `Disponible`. → **No se replican**; la hoja "Base de datos" v2
  usa el subconjunto útil que ya expone la hoja "Detalle" actual.

---

## 4. Mapa de secciones

### PDF/PPTX (18 páginas)

| # | Página | Estado hoy |
|---|---|---|
| 1 | Portada | ✅ existe |
| 2 | Resumen ejecutivo — 5 KPIs | ✅ **exacto** |
| 3 | ¿Cuánto ingresa por semana? | ⚠️ `porSemana()` nuevo |
| 4 | Recolección por día de la semana (Lun–Dom) | ⚠️ `porDiaSemana()` nuevo |
| 5 | Divisor "Análisis por flota" | ✅ |
| 6 | Composición por tipo de vehículo (donut + tabla) | ✅ `calcularPorVehiculo` |
| 7 | Resumen operativo (3 bloques) | ⚠️ falta `vehiculosOperativos()` (distinct) |
| 8 | Divisor "Zonas y servicios" | ✅ |
| 9 | ¿Qué es cada servicio? (descripción + N° zonas) | ❌ Fase 3 (`descripcion`) |
| 10 | ¿Cuánto recolecta cada servicio? | ⚠️ `porServicio()` nuevo |
| 11–17 | Zonas por servicio (KPIs + desglose por zona) | ⚠️ `zonasPorServicio()` nuevo |
| 18 | Cierre / Gracias | ✅ |

### XLSX (8 hojas) — orden de construcción: **N° interno primero, servicios después**

| Hoja | Bloques | Estado hoy |
|---|---|---|
| **Resumen** | KPIs generales ✅ · resumen por día ✅ (`evolucion`) · **servicio × tipo vehículo** ⚠️ · **viajes × servicio** ⚠️ | parcial |
| **Por vehículo N° Interno** | resumen por tipo ✅ · diario × tipo ✅ (`pivots.diario`) · **viajes por N° interno × día** ⚠️ | parcial |
| **5 hojas por servicio** (Domiciliario, Voluminosos, Barrido, Centro de Transferencia, Servicios Especiales) | resumen del servicio ⚠️ · desglose por zona ⚠️ · zona × día ⚠️ (por servicio) | nuevo |
| **Base de datos** | detalle crudo | ✅ (hoja "Detalle" actual, sin columnas no modeladas) |

Estructura exacta de cada hoja (según el archivo de referencia):

- **Resumen**: `RESUMEN GENERAL` (5 KPIs) · `RESUMEN POR DÍA` (Fecha·Día·Kg·Viajes, 31 filas + TOTAL) ·
  `RESUMEN POR SERVICIO Y TIPO DE VEHÍCULO` (Servicio × {Compactador, Volcador, Volquete} + Total kg) ·
  `RESUMEN DE VIAJES POR SERVICIOS` (mismo cruce, en viajes).
- **Por vehículo N° Interno**: `RESUMEN POR TIPO DE VEHÍCULO` (Tipo·Viajes·Kilos·Prom kg/viaje + TOTAL) ·
  `ANÁLISIS DIARIO DE INGRESOS` (Día × {Viajes, KG} por tipo + Total) ·
  `CANTIDAD DE VIAJES POR NÚMERO INTERNO` (Interno·Tipo × una columna por día del período).
- **Por servicio** (una hoja por servicio): `RESUMEN DEL SERVICIO` (Viajes, Kg) ·
  `DESGLOSE POR ZONA` (Zona·Viajes·Kilos·% del servicio + TOTAL) ·
  `KG NETOS POR ZONA Y POR DÍA` (Zona × columna por día).

---

## 5. Hallazgos importantes (para no arrastrar errores del ejemplo)

1. **El reporte de referencia es internamente inconsistente.** En Domiciliario el "resumen por
   servicio" da 5.865.778 kg / 957 viajes, pero el "desglose por zona" del mismo servicio da
   6.061.074 kg / 998 viajes: clasifica el servicio de dos formas distintas (servicio del pesaje
   vs. servicio de la zona). → v2 clasifica **siempre** por `pesaje.tipo_servicio_id` para que
   cada total de servicio sea la suma de sus zonas.
2. **La clasificación del ejemplo es rara** ("Domiciliario = 100% Compactador"). Nuestro
   `tipo_servicio_id` es explícito por pesaje, así que el output de v2 será más correcto y
   **no coincidirá número a número** con el PPTX/Excel de muestra. Es esperado.
3. **No hay capacidad PPTX en el stack** (el PDF sale de HTML vía Browsershot/Chrome). Por eso
   el PPTX se resuelve con el PDF-deck (§2).

---

## 6. Fases

### Fase 0 — Andamiaje
- `@deprecated` en docblocks de `ReporteController::exportExcel`, `exportPdfPresentacion`,
  `ReporteExcelExport`, `pdf-presentacion.blade.php`. Siguen funcionando.
- Rutas nuevas para v2 (`…/excel-v2`, `…/pdf-v2`); las viejas quedan.

### Fase 1 — Agregaciones nuevas (extender `ReporteService`, sin duplicar `generar()`)
Métodos puros sobre la `$pesajes` ya cargada — cero queries extra:

| Método | Alimenta |
|---|---|
| `porSemana($pesajes, $desde, $hasta)` | PDF pág. 3 |
| `porDiaSemana($pesajes)` | PDF pág. 4 |
| `vehiculosOperativos($pesajes)` | PDF pág. 7 |
| `porServicio($pesajes)` | PDF pág. 10 + Excel Resumen |
| `servicioPorTipoVehiculo($pesajes, $tipos)` | Excel Resumen (kg y viajes) |
| `porNumeroInterno($pesajes, $fechas)` | Excel "Por N° interno" |
| `zonasPorServicio($pesajes, $desde, $hasta)` | Excel hojas por servicio + PDF págs. 11–17 |

Se reutilizan `calcularPorVehiculo`, `pivotsParaExcel` (`diario`, `zonaTipo`, `zonaDia`),
`etiquetaZona`, `desglosarPorTipo`.

### Fase 2 — Excel v2 (`ReporteExcelExportV2`, 8 hojas)
- **2a — N° interno primero**: hoja Resumen + hoja "Por vehículo N° Interno".
- **2b — servicios después**: 5 hojas por servicio + hoja "Base de datos".
- Reutiliza los helpers de estilo del `ReporteExcelExport` actual (extraer a un trait/base común
  si conviene, sin tocar el export v1).
- Controller `exportExcelV2` + ruta; snapshot v2 e integración en historial.

### Fase 3 — Descripción de servicio
- Migración `descripcion nvarchar(300) null` en `tipos_servicio`.
- Campo en el ABM (`TipoServicio` request/vista). Habilita PDF pág. 9.

### Fase 4 — PDF v2 (`pdf-presentacion-v2.blade.php`, 18 páginas)
- Rediseño visual siguiendo `docs/reportes/prompt-rediseno-visual.md` (donuts, deltas MoM,
  insight boxes), reutilizando `PdfService`/Browsershot y el choropleth actual.

### Fase 5 — Integración
- Botones "Excel v2 / PDF v2" en la UI de reportes.
- Snapshot v2 e historial.
- Tests (`ReporteServiceTest` para cada agregación nueva, estándar A de
  `docs/08-testing-strategy.md`): borde exacto, assert completo, datos controlados.

---

## 7. Orden de trabajo acordado

1. **Fase 1** — agregaciones (empezando por las que alimentan "Por N° interno").
2. **Fase 2a** — Excel: hoja Resumen + "Por vehículo N° Interno".
3. **Fase 2b** — Excel: hojas por servicio + "Base de datos".
4. Luego Fase 3 y Fase 4 (PDF), Fase 5.

---

## 8. Estado de avance

| Fase | Estado | Notas |
|---|---|---|
| Andamiaje (deprecar v1) | ✅ | `@deprecated` en `ReporteExcelExport`; rutas v2 nuevas, v1 intactas |
| Fase 1 — agregaciones | ✅ | `porServicio`, `datosExcelV2` (+ `servicioPorTipoVehiculo`, `porNumeroInterno`, `zonasPorServicio`, `resumenPorDia`) en `ReporteService`. `porSemana`/`porDiaSemana`/`vehiculosOperativos` quedan para el PDF (Fase 4) |
| Refactor base Excel | ✅ | `ReporteExcelBase` (paleta + helpers + bloque diario + hoja detalle); v1 y v2 heredan. Sin duplicación |
| Fase 2a — Excel Resumen + Por N° interno | ✅ | `ReporteExcelExportV2` |
| Fase 2b — Excel hojas por servicio + Base de datos | ✅ | Una hoja por servicio (kg desc) + hoja "Base de datos" |
| Controller + ruta | ✅ | `exportExcelV2` · `GET admin/reportes/excel-v2` (`admin.reportes.excel-v2`). Descarga directa (sin historial aún) |
| Tests | ✅ | `ReporteServiceTest` (+5 estándar A) · `ExportReporteExcelV2Test` (3). Suite reportes 136/136 verde |
| Fase 3 — descripción de servicio | ✅ | Migración aditiva `descripcion` en `tipos_servicio` (aplicada) + campo en el ABM (modal, requests, Alpine) + 4 tests |
| Fase 4 — PDF v2 | ✅ | `pdf-presentacion-v2.blade.php` (reusa el sistema visual del v1) con las páginas nuevas: **semana**, **día de la semana**, **resumen operativo**, **¿qué es cada servicio?** (usa `descripcion` + conteo de zonas), **ranking por servicio** y **zonas por servicio**. Agregaciones `porSemana`/`porDiaSemana`/`vehiculosOperativos` + `porServicio` con descripción/zonas. `exportPdfV2` · `GET admin/reportes/pdf-v2`. Verificado renderizando 18 páginas con Browsershot. Tests: +4 agregación, +3 feature (Blade real, Chrome mockeado) |
| Fase 5a — botones en la UI | ✅ | El header de reportes ahora expone menús "Excel ▾" y "PDF ▾" con la opción **v2 (recomendada)** primero y el **formato clásico (v1)** debajo. `header-generar.blade.php` + test `ReporteExportButtonsTest`. Reusa el `<x-ui.dropdown-menu>` existente (sin rebuild de assets) |
| Fase 5b — historial/snapshot v2 | ✅ | Las descargas v2 (Excel y PDF) quedan en el historial con snapshot congelado y se re-descargan idénticas. `ReporteSnapshotService::capturarV2/rehidratarV2` (codificador recursivo de fechas Carbon, `version: 2` dentro del JSON — sin migración). `downloadHistorial` enruta por versión a `renderExcelV2`/`responderPdfV2`. Test de round-trip `ReporteHistorialV2Test` (Excel + PDF). Suite reportes verde |

**Archivos nuevos (Fase 5):** `tests/Feature/Reporte/ReporteExportButtonsTest.php`, `tests/Feature/Reporte/ReporteHistorialV2Test.php`.
**Modificados (Fase 5):** `header-generar.blade.php` (menús v2 + clásico), `ReporteSnapshotService` (`capturarV2`/`rehidratarV2` + codificador de fechas), `ReporteController` (`exportExcelV2`/`exportPdfV2` registran en historial, `responderPdfV2`, `downloadHistorial` enruta por versión).

---

## 8bis. v2 como default en todos los puntos de generación

Tras el plan original, se hizo v2 el **formato por defecto en todo el sistema** (no solo una opción):

| Punto de generación | Antes | Ahora |
|---|---|---|
| Botones del header (Generar) | menú v2 + clásico | **descarga directa v2** (Excel/PDF), sin desplegable |
| Descarga de un programado (`downloadPdf/ExcelProgramado`) | v1 | **v2** (informe mensual); alertas sigue v1 |
| Envío programado por email (`GenerarReporteJob` → `EnviarReporteJob`/`ReporteEnvioService`) | v1 (`capturar` + `ReporteExcelExport` + `pdf-presentacion`) | **v2** (`capturarV2` + `ReporteExcelExportV2` + `pdf-presentacion-v2`) |
| Historial (re-descarga) | por versión del snapshot | igual (v1 viejo se reproduce v1; v2 nuevo se reproduce v2) |

El generador v1 queda solo para reproducir entradas históricas viejas y el informe de **alertas** (que no tiene versión v2). Tests del job programado actualizados (mock de `ReporteService` stubbea los métodos v2 con estructuras vacías reales).

## 8ter. Rediseño visual del PDF v2 (11 páginas)

El template `pdf-presentacion-v2.blade.php` fue **reescrito por completo** siguiendo un diseño de
referencia entregado por el cliente (`docs/Informe Mensual - Infinito Reciclaje.html`, formato
"bundler" con el HTML real embebido en un `<script type="__bundler/template">` codificado en JSON).

**Estructura nueva — 11 páginas fijas + N dinámicas de zonas:**

| # | Página | Fuente de datos |
|---|---|---|
| 01 | Portada | `kpis`, `config.municipalidad_nombre` |
| 02 | Quiénes somos | `config.intro_empresa` / `config.servicios` (features) + conteos (`porServicio`, `vehiculos`, `flotaActiva`) |
| 03 | Resumen ejecutivo | `kpis` + mini-barras de `semanas` |
| 04 | ¿Cuánto ingresa por semana? | `semanas` (nota dinámica si la última semana tiene ≠7 días) |
| 05 | Recolección según el día | `diaSemana` (insight dinámico: día top vs. día mínimo, sin asumir "fin de semana") |
| 06 | Por tipo de vehículo | `vehiculos` (donut SVG con `stroke-dasharray` proporcional a kg) |
| 07 | ¿Qué es cada servicio? | `porServicio` (nombre, `descripcion` de la Fase 3, `zonas`) |
| 08 | ¿Cuánto recolecta cada servicio? | `porServicio` (barras horizontales + insight top-2 dinámico) |
| 09 | Zonas del servicio principal | `zonasServicio[0]` (top 12 zonas + nota si hay más) |
| 10 | Zonas — otros servicios | `zonasServicio[1..]`, chunks de 4 por página |
| 11 | Cierre | `kpis`, `config`, período |

**Decisiones de diseño:**
- Paleta y tokens (`--g-*`, `--ink-*`, `--line`, `--bg`) tomados tal cual del HTML de referencia.
- Nada de datos "inventados": el bloque "Quiénes somos" usa `config.intro_empresa`/`config.servicios`
  (mismo dato configurable que ya existía en el v1) en vez de hardcodear textos institucionales.
- Insights dinámicos (no hardcodeados): notas sobre semana con más/menos días, día top/mínimo de la
  semana, concentración top-2 servicios — todos calculados en el propio Blade a partir de los datos.
- Paginación de zonas: el servicio con más kg tiene su propia página (top 12 zonas, con nota de cuántas
  quedan afuera); el resto de los servicios se agrupan en páginas de hasta 4 tarjetas compactas.
- Numeración de página (`Pág. NN / TOTAL`) calculada dinámicamente antes de renderizar (la cantidad de
  páginas de zonas depende de cuántos servicios/zonas tenga cada organización).
- Se eliminaron del v2 las páginas de mapa de calor (choropleth) y análisis IA que tenía el v1 — no
  forman parte del formato de referencia. El v1 ("formato clásico") sigue teniendo ambas.

**Verificado:** renderizado real contra la base de Corrientes (junio 2026, 1.053 pesajes, 3 servicios,
15 zonas) — las 11 páginas confirmadas visualmente (capturas), números cruzando entre páginas (donut
100%, semanas sumando el total, zonas sumando el total del servicio). Suite de reportes 116/116 verde
sin cambios (el rediseño no tocó agregaciones ni controller, solo la plantilla Blade).

## 9. Cierre

Las 5 fases del plan están completas y verificadas. Los tres formatos objetivo se generan desde la
pantalla de reportes (Excel por servicio, PDF institucional; el PPTX se resuelve como PDF-deck), respetan
los filtros del período, quedan registrados en el historial con snapshot y se re-descargan idénticos. El
generador v1 sigue accesible como "formato clásico" y marcado `@deprecated`.

**Discriminador de versión:** vive dentro de `reportes_generados.snapshot` (`version: 2`); las entradas v1
(sin ese campo o legacy sin snapshot) siguen el camino v1. No hizo falta migración.

**Archivos nuevos (Fase 4):** `resources/views/modules/admin/reportes/pdf-presentacion-v2.blade.php`,
`tests/Feature/Reporte/ExportReportePdfV2Test.php`.
**Modificados (Fase 4):** `ReporteService` (`porSemana`/`porDiaSemana`/`vehiculosOperativos`, `porServicio` con
`descripcion`+`zonas`, `zonasPorServicio` público), `ReporteController` (`exportPdfV2`+`construirReportePdfV2`),
`routes/admin.php`, `ReporteServiceTest`.

> **Nota:** la página de "Análisis estratégico" (conclusiones IA) del PDF v2 solo aparece si la IA está
> configurada (`ai_enabled` + api key); sin configurar se omite, como en el v1.

**Archivos nuevos:** `app/Exports/ReporteExcelBase.php`, `app/Exports/ReporteExcelExportV2.php`,
`tests/Feature/Reporte/ExportReporteExcelV2Test.php`.
**Modificados:** `ReporteService` (agregaciones v2), `ReporteExcelExport` (hereda base, deprecated),
`ReporteController` (+`exportExcelV2`), `routes/admin.php`, `ReporteServiceTest`.
