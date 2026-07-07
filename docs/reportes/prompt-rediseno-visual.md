# Prompt — Rediseño visual del reporte mensual (Infinito Reciclaje)

> Pegá este prompt en Claude (claude.ai, modo artefacto HTML) para generar una nueva plantilla
> del informe mensual, más visual. Genera un mockup HTML autocontenido con datos de ejemplo;
> luego se traduce a Blade (`pdf-presentacion.blade.php`) reemplazando los datos por las variables reales.

---

## PROMPT

Sos un diseñador de información especializado en informes ejecutivos y data-visualization para el sector público. Necesito que diseñes una **plantilla HTML de un informe mensual imprimible**, mucho más visual que la actual, para una empresa de gestión de residuos.

### Contexto del negocio
- **Empresa:** Infinito Reciclaje — opera una balanza digital que pesa los camiones de recolección de residuos urbanos.
- **Destinatario del informe:** el **gobierno municipal** con el que Infinito tiene contrato. El informe es la evidencia mensual de cumplimiento del servicio: cuántos viajes, cuántas toneladas, cobertura por zona, densidad de generación, continuidad operativa.
- **Tono:** institucional, serio, confiable, pero moderno y visualmente atractivo. Es un documento que se presenta a funcionarios — tiene que verse profesional y transmitir transparencia y precisión. No infantil, no “startup”. Piense en un informe de sostenibilidad corporativa o un memoria anual de una empresa de servicios.

### Identidad de marca (respetar)
- **Color primario: verde reciclaje.** Paleta de verdes en OKLCH (de oscuro a claro):
  `oklch(0.247 0.052 144)` (más oscuro) → `oklch(0.523 0.135 144)` (primary) → `oklch(0.905 0.088 144)` (claro).
- **Neutrales:** grises fríos para texto y fondos (`oklch(0.145 0 0)` a `oklch(0.985 0 0)`).
- **Estados:** rojo `oklch(0.568 0.268 27)` (alertas/crítico), ámbar `oklch(0.745 0.210 95)` (advertencia).
- **Tipografía:** Inter (todos los pesos). Números grandes en peso 800, tabulares (`font-variant-numeric: tabular-nums`).
- Estética limpia, mucho aire, jerarquía tipográfica marcada, uso de círculos/formas geométricas decorativas sutiles, acentos de color solo donde aportan significado.

### Formato técnico (obligatorio)
- **Páginas A4 apaisadas:** cada `.page` mide `297mm × 210mm`, con `page-break-after: always`. El documento se imprime a PDF, una sección por hoja.
- **HTML + CSS autocontenido**, todo inline (sin frameworks CSS). Print-ready: usar `-webkit-print-color-adjust: exact`.
- Ninguna sección puede exceder los 210mm de alto: si el contenido es variable (tablas largas), paginar en varias `.page`.
- Datos de ejemplo hardcodeados y realistas (los reemplazo luego por variables). Usá valores plausibles de una ciudad mediana argentina.

### Datos disponibles (usar exactamente estos; no inventar métricas nuevas sin marcar)
El sistema puede alimentar estas secciones. Diseñá una página (o más) para cada una:

1. **Portada** — nombre del municipio, período (ej: “Mayo 2026”), rango de fechas, logo, subtítulo “Sistema de Balanza Digital · Gestión de Residuos”.
2. **Quiénes somos** — intro de la empresa + 3 tarjetas de servicios (Recolección y Reciclaje / Datos y Trazabilidad / Capacitación).
3. **Resumen ejecutivo (KPIs):** total de viajes, toneladas totales, días operativos (X de N), promedio toneladas/día, promedio kg/viaje. Diseñar como fila de KPI-cards de alto impacto visual.
4. **Evolución diaria de toneladas** — serie por día (fecha, viajes, toneladas). Mostrar promedio, máximo y mínimo del período. Gráfico de barras verticales con línea de promedio. Incluir “insight boxes” destacando el mejor día y los días con caídas (ej: corte de luz).
5. **Por tipo de vehículo** — tabla + gráfico: tipo (Compactador, Volcador, Volquete, Particular), viajes, toneladas, % del total, kg/viaje. Fila TOTAL.
6. **KG netos por zona (tabla cruzada zona × turno):** cada fila es zona + turno (Mañana/Tarde/Noche o sin turno). Columnas: viajes y kg por tipo de vehículo, total viajes, total kg, % del total. Ordenada por kg descendente. **Color-coding de filas por rango de toneladas** (escala de calor: rojo >500t, naranja, ámbar, amarillo, verde <30t).
7. **Mapa de calor por zona (choropleth):** mapa geográfico donde cada zona se colorea según densidad de generación (kg/ha), con un ranking lateral de las zonas top y una leyenda de escala de color. (En el mockup, simulá el mapa con un placeholder o un SVG estilizado.)
8. **Densidad de generación (kg/hectárea):** ranking de zonas por kg/ha, barras horizontales con color por intensidad y línea de referencia del promedio.
9. **Alertas del período** — 4 tipos: peso fuera de rango, volumen diario atípico, sin actividad (gaps), frecuencia de zona atípica. Tarjetas-resumen con conteo por tipo + detalle en lista.
10. **Análisis estratégico (conclusiones):** página oscura estilo “cierre”, con 3–4 bloques numerados de conclusiones/recomendaciones en texto.
11. **Cierre / Gracias** — página final con branding.

### Qué significa “más visual” (el objetivo del rediseño)
La versión actual es correcta pero muy “tabla + barra CSS”. Quiero elevarla:
- **KPIs con más impacto:** números gigantes, iconografía clara (camión, balanza, calendario, hoja de reciclaje), micro-visualizaciones (sparklines, anillos de progreso, deltas vs mes anterior con flechas ↑↓).
- **Gráficos más ricos:** además de barras, considerar donut/anillos para reparto por tipo de vehículo, área para la evolución, treemap o barras apiladas para zona×tipo.
- **Infografía de datos:** que el resumen ejecutivo cuente una historia de un vistazo, no solo liste cifras.
- **Sistema visual coherente:** mismos radios, sombras suaves, tratamiento de acentos y decoraciones geométricas en todas las páginas.
- **Comparativas temporales visibles:** deltas vs mes anterior (MoM) donde tenga sentido.
- Jerarquía impecable: eyebrow → título → bajada → contenido, con reglas/separadores de acento verde.

### Entregable
Generá el HTML completo como un solo archivo/artefacto, con todas las páginas, comentado por sección, usando datos de ejemplo realistas. Que pueda abrirlo, imprimirlo a PDF y ver el resultado final. Priorizá el diseño visual y la claridad de la información sobre la cantidad de datos.

---

## Notas para adaptar el resultado a Blade (uso interno, no forma parte del prompt)

- Variables reales que llegan a la vista: `$reporte['kpis']`, `['evolucion']`, `['zonas']`, `['vehiculos']`, `['alertas']`, `['conclusiones']`, `['config']`, `['desde']`, `['hasta']`.
  - `kpis`: `total`, `toneladas`, `dias_op`, `dias_rango`, `promedio_ton_dia`, `promedio_kg_viaje`.
  - `zonas[]`: `nombre`, `turno`, `viajes`, `toneladas`, `kg_viaje`, `porcentaje`, `kg_ha`, `kg_hab`.
  - `vehiculos[]`: `nombre`, `viajes`, `toneladas`, `kg_viaje`, `porcentaje`.
- El PDF se renderiza con Browsershot **sin base URL** → imágenes (logo) deben embeberse como data URI; assets externos como Leaflet ya se cargan por CDN en la plantilla actual.
- Regla de paginación del proyecto: **cada `.page` = una hoja A4**; si una sección crece, chunkear en varias `.page` (ver `docs` / memoria `project_pdf_page_chunking`).
- Ruta de preview local del PDF actual: `GET /admin/reportes/preview-pdf` (solo entorno local).
