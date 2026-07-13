# Oportunidades de producto — Marco GIRSU Nacional
## Sistema de Gestión de Balanza · Infinito Reciclaje × EVOLVERE

> **Tipo de documento**: análisis de producto y mercado. No es alcance comprometido — es el insumo para decidir el roadmap post go-live (Etapa 2/3).
>
> **Fecha**: 07/07/2026 · **Revisión de fuentes**: 08/07/2026 — cada afirmación normativa/estadística de este documento fue re-verificada contra el texto oficial o la fuente primaria disponible (ver [§0](#0-nota-metodológica-y-nivel-de-confianza) y bibliografía numerada en [§7](#7-fuentes-y-notas-de-verificación)). Se corrigió un error material de la versión anterior (vigencia del dato regional de disposición final, §3.4).

---

## 0. Nota metodológica y nivel de confianza

Este documento se construyó en dos pasadas. La primera (07/07/2026) usó un asistente de fetch web que resume páginas con un modelo de lenguaje — rápido, pero con riesgo real de imprecisión o de tomar un resumen por una cita textual. Ante la pregunta directa de si los datos eran reales, se hizo una **segunda pasada de verificación** (08/07/2026) releyendo fuentes primarias cuando fue posible y contrastando cada cifra con al menos otra fuente independiente.

**Resultado de la verificación**: la mayoría de las citas normativas se sostienen (texto de decretos/leyes confirmado, en varios casos con la cita textual del artículo). Se encontró **un error material** que se corrige en este documento: la estadística regional de disposición final (15,2% en Cuyo–Mesopotamia) **no es un dato reciente** — proviene de un estudio de 2010, no de un relevamiento actual. Se mantiene en el documento porque sigue siendo el dato oficial vigente (la propia página de argentina.gob.ar lo reproduce sin fecha visible), pero ahora se etiqueta con su año real y se explica por qué esa antigüedad es, en sí misma, parte del argumento de oportunidad.

Cada afirmación cuantitativa o normativa de este documento lleva una marca **[N]** que remite a la fuente numerada en §7, con una etiqueta de confianza:

- 🟢 **Confirmado** — texto oficial leído directamente o cita textual verificada en al menos dos fuentes independientes.
- 🟡 **Confirmado con reserva** — confirmado por fuentes secundarias serias (estudios jurídicos, medios especializados) que citan el texto, pero no se pudo leer el documento oficial de forma directa (bloqueo 403 o de certificado en el fetch).
- 🔴 **No verificado / inferencia razonable** — afirmación plausible por conocimiento general del marco (ej. metodología IPCC estándar) o por una fuente única de menor autoridad, no confirmada de forma independiente en esta revisión. Se marca así explícitamente para que no se use como si fuera un hecho establecido.

---

## 1. Resumen ejecutivo

El sistema hoy captura una serie de datos que se alinea con lo que el marco normativo argentino de residuos exige y va a exigir cada vez más: quién dispuso cuánto, de qué zona, con qué servicio, cuándo — normalizada por habitantes y hectáreas, con geometría territorial.

Cuatro hechos del contexto regulatorio sostienen esa oportunidad:

1. El **Decreto 779/22** (reglamentario de la Ley 25.916) fija como principios rectores la **trazabilidad**, la **jerarquía de valorización** y menciona el monitoreo con indicadores entre sus recomendaciones operativas [2][3] 🟡 — alineado con lo que el sistema ya hace o puede hacer con extensiones menores.
2. La **ENGIRSU** (Estrategia Nacional GIRSU) cumplió su horizonte 2005–2025 y hay una actualización en curso [15] 🟡 — el pilar de "sistemas de información" que declaraba la estrategia original sugiere que viene una nueva demanda de datos hacia jurisdicciones locales, aunque el contenido puntual de la actualización no está publicado todavía y esto es una proyección, no un hecho confirmado.
3. La región **Cuyo–Mesopotamia** (incluye Corrientes) aparece con la **peor tasa de disposición final adecuada del país (15,2%)** en el estudio regional BID-AIDIS-OPS **de 2010** [5][6] 🟡 — es el dato oficial más reciente y públicamente disponible, lo cual es revelador en sí mismo: **hace más de 15 años que nadie mide esto de nuevo**. Un predio formal con datos trazables como el de Corrientes puede ser, literalmente, la fuente de dato más actualizada de la región.
4. La **Ley 27.520** (cambio climático) obliga a las provincias (y CABA) a elaborar **planes de respuesta con línea de base de emisiones GEI** [7] 🟢, y el sector residuos es uno de los sectores estándar de cualquier inventario de gases de efecto invernadero bajo metodología IPCC/CMNUCC 🔴 (inferencia razonable, no confirmada línea por línea en el documento nacional revisado). Los kilos que pesa la balanza son un insumo directo de esa línea de base, hoy ausente en la región.

**Recomendación**: priorizar 3 quick wins sin cambio de modelo (indicadores GIRSU, proyección de vida útil, export formato oficial), luego una única extensión de modelo (fracción de residuo por pesaje) que habilita todo el eje de economía circular, y elegir entre certificado de disposición o liquidación por tonelada según la necesidad comercial de Infinito Reciclaje.

---

## 2. El producto hoy — qué activo de datos tenemos

Resumen del alcance vigente (detalle en [`01-brief-producto.md`](01-brief-producto.md) y [`03-data-model.md`](03-data-model.md)) — esta sección describe el sistema propio, no requiere fuente externa:

| Dato | Dónde vive | Calidad |
|------|-----------|---------|
| Pesajes: fecha/hora ingreso y egreso, bruto, tara, neto, estado | `pesajes` (datetime2(3), auditado, cancelación con motivo) | Alta — trazable, con log de cambios |
| Vehículo: patente, tipo, titular (municipal/particular), capacidad | `vehiculos` + `vehiculos_log` | Alta — padrón mantenido, tara auditada |
| Territorio: zonas con hectáreas, habitantes, barrios, polígono GeoJSON | `zonas` (`hectareas`, `habitantes`, `geojson`, centroide) | Alta — habilita per cápita, densidad y mapas |
| Servicio: tipo (Domiciliario, Voluminoso, Barrido, Especiales, Centros de Transferencia) | `tipos_servicio` + relación N:M con zonas y turnos/horarios | Alta |
| Operación: turno, operador, alertas de peso fuera de rango | `pesajes.turno`, `operador_id`, `alerta_peso` | Alta |
| Multi-tenant: organizaciones aisladas | `organizaciones` + global scopes | Alta |

**Capacidades de salida ya construidas** (reutilizables por cualquier funcionalidad nueva):

- Reporte mensual PDF/Excel con snapshot congelado y re-descarga idéntica
- Gráficos SVG server-side para PDF (`SvgChartService`) y Chart.js en web
- Mapa choropleth por zona (Leaflet, geometría propia)
- Envío programado por email (Resend) con revisión/aprobación opcional
- Conclusiones narrativas por IA (Gemini, configurable por organización)
- Motor de alertas con umbrales configurables

**Lo que el sistema NO captura hoy** (relevante para las oportunidades):

- Composición/fracción del residuo (todo ingreso es "residuo" sin clasificar)
- Material que **sale** del predio (recuperado/valorizado) — solo egresos de camiones
- Capacidad total y remanente del predio
- Dimensión económica (tarifas, canon por tonelada)

---

## 3. Marco normativo y programas nacionales

### 3.1 Ley 25.916 — presupuestos mínimos de gestión de RSU (2004) [1] 🟢

Marco nacional de gestión de residuos domiciliarios, de **presupuestos mínimos** — es decir, fija el piso de protección ambiental y delega en cada jurisdicción la designación de su "autoridad competente" y el diseño de su sistema de gestión adaptado a sus características locales; la ley no nombra literalmente a "los municipios" como responsables, sino que dice que **"serán autoridades competentes de la presente ley los organismos que determinen cada una de las jurisdicciones locales"**, responsables de garantizar recolección, transporte, tratamiento y disposición final habilitados [1]. En la práctica, para el predio de Corrientes esa autoridad competente es la Municipalidad, que es quien opera el sistema — corregido respecto de la versión anterior de este documento, que simplificaba diciendo directamente "los municipios son responsables".

### 3.2 Decreto 779/22 — reglamentación y economía circular (2022)

Reglamenta la Ley 25.916 dieciocho años después y orienta el sistema hacia la **economía circular**. El texto oficial completo no pudo leerse de forma directa en esta revisión (el fetch a argentina.gob.ar devolvió error 403), pero los pasajes citados abajo están confirmados **textualmente** por múltiples fuentes jurídicas secundarias independientes que reproducen el articulado (estudios de abogados, medios especializados) [2][3] 🟡:

**Siete lineamientos operativos — Artículo 6:**

1. **Cuna a cuna**: *"idear, diseñar y producir de forma tal que los elementos que componen los productos, bienes y servicios puedan ser sosteniblemente recuperados y valorizados en todas las etapas de su ciclo de vida"*
2. **Proximidad**: *"gestión integral (...) en los sitios que resulten adecuados y lo más cercanos posibles al lugar de su generación"*
3. **Responsabilidad extendida del productor (REP)**: asignación de responsabilidad objetiva y financiamiento a quien introduce por primera vez en el mercado bienes que luego devienen residuos
4. **Ecodiseño**: incentivos para integrar aspectos ambientales en el diseño de bienes, mejorando su potencial de valorización
5. **Gradualidad**: adaptación "racional, temporal y paulatina" a los objetivos de la reglamentación
6. **Mejores técnicas disponibles**: priorizar la alternativa más eficaz según jurisdicción, tipología y composición del residuo
7. **Trazabilidad**: *"los sistemas de gestión empleados (...) deberán ser autosuficientes permitiendo conocer stocks, flujos de generación, trayectos y cantidades valorizadas y dispuestas finalmente en forma desagregada por cada etapa"* ← el más directamente implementable con nuestros datos

**Jerarquía de opciones** (mismo Art. 6): prevención → reutilización → recuperación → tratamiento → **disposición final como última opción**. Todo indicador que muestre cuánto se desvía del enterramiento alimenta esta jerarquía.

**Código unificado de colores (Anexo II)** [3] 🟡: siete fracciones con color asignado — secos reciclables (verde), basura (negro), orgánicos reciclables (marrón), plásticos (amarillo), papel y cartón (azul), vidrio (blanco), metales (gris). Es la taxonomía natural para clasificar pesajes por fracción (oportunidad B1).

**Recomendaciones operativas de "monitoreo e indicadores"**: mencionadas en la síntesis oficial del decreto revisada en la primera pasada [2] 🔴 — no se pudo re-confirmar la cita textual exacta en esta segunda revisión; se mantiene como orientación general razonable dado el resto del articulado, no como cita literal.

### 3.3 ENGIRSU — Estrategia Nacional GIRSU (2005–2025) [15] 🟡

La estrategia nacional 2005 fue diseñada con un horizonte de implementación de 20 años. El portal oficial indica que se está evaluando el cumplimiento del período y que hay una actualización en desarrollo, sin fecha de publicación confirmada en esta revisión — **la expresión "ENGIRSU 2025" usada en la versión anterior de este documento es una etiqueta de trabajo nuestra, no el nombre oficial confirmado de un documento ya publicado.** Tratar como una expectativa razonable, no como un hecho.

Pilares declarados de la estrategia original: economía circular, cierre progresivo de basurales a cielo abierto, sistemas de información, fortalecimiento de gobernanza y comunicación participativa [15].

**Implicancia de producto** (proyección, no normativa confirmada): si la actualización de la estrategia formaliza un flujo de datos hacia las jurisdicciones locales, el sistema puede tener el formato de reporte listo antes de que sea exigido.

### 3.4 Observatorio Nacional GIRSU e indicadores de referencia — ⚠️ dato de 2010, no actual

Creado por la **Resolución SAyDS 21/2009** [4] 🟢 como espacio de sistematización de información y comunicación de políticas públicas de RSU.

Los indicadores de referencia que suele citar el sector (y que la propia página oficial de argentina.gob.ar reproduce) provienen de la **Evaluación Regional del Manejo de Residuos Sólidos Urbanos en América Latina y el Caribe**, un estudio conjunto del **Banco Interamericano de Desarrollo (BID), la Asociación Interamericana de Ingeniería Sanitaria y Ambiental (AIDIS) y la Organización Panamericana de la Salud (OPS)**, publicado en **2010** [5][6] 🟡:

| Indicador | Valor | Año del dato |
|-----------|-------|--------------|
| Generación per cápita | 1,15 kg/hab/día | 2010 |
| Cobertura de recolección (población urbana) | 99,8% | 2010 |
| Disposición final en relleno sanitario | 64,7% | 2010 |
| Disposición inadecuada | 35,3% (9,9% vertederos controlados + 24,6% basurales a cielo abierto) | 2010 |

**Desigualdad regional** (mismo estudio, mismo año):

| Región | Disposición final adecuada | Año del dato |
|--------|---------------------------|--------------|
| Resto del país | 79,4% | 2010 |
| Norte | 50,1% | 2010 |
| **Cuyo–Mesopotamia (incluye Corrientes)** | **15,2%** | 2010 |

**Corrección respecto de la versión anterior**: este documento presentaba estas cifras sin fecha, dando la impresión de un diagnóstico actual. Son de **2010** — dieciséis años al momento de escribir esto. No se encontró, en esta revisión, un relevamiento regional posterior que las reemplace. Esto no invalida el argumento comercial: al contrario, **la ausencia de datos regionales actualizados en 15+ años es en sí misma la oportunidad** — un predio con pesaje sistemático y trazable es, hoy, una fuente de dato más fresca que la que tiene el propio Estado nacional sobre esta región.

### 3.5 Indicadores ODS relacionados

- **ODS 11.6.1** [13] 🟢 — definición oficial confirmada: *"proporción de residuos sólidos municipales recogidos y administrados en instalaciones controladas con respecto al total de residuos municipales generados, desglosada por ciudad"*. El predio, al ser una instalación controlada y auditable, es exactamente el tipo de dato que este indicador necesita.
- **ODS 12.5.1** ("tasa nacional de reciclaje") 🔴 — es un indicador estándar de la Agenda 2030 a nivel global, pero en esta revisión **no se pudo confirmar con una fuente primaria específica que Argentina lo esté cuantificando activamente con una metodología publicada**, ni la atribución previa a un informe puntual de la AGN. Se retira esa cita específica del documento anterior por no haber podido verificarla de forma independiente; se mantiene el indicador como referencia conceptual de hacia dónde apunta la política de valorización, no como un hecho de reporte confirmado.

### 3.6 Programas de asistencia (PMGIRSU) y autoridad provincial

- **PMGIRSU — Programas Municipales para la Gestión de Residuos Sólidos Urbanos** [14] 🟡: financiados con aportes del Tesoro Nacional, dan asistencia técnica y financiera a municipios para: planes GIRSU, erradicación de basurales a cielo abierto, construcción/ampliación de rellenos sanitarios, plantas de separación y reciclado, y adquisición de vehículos/maquinaria de recolección. Confirmado por resoluciones del boletín oficial (454/2020, 267/2021) y páginas de programas provinciales que replican el esquema nacional.
- **ICAA — Instituto Correntino del Agua y del Ambiente** [9] 🟢: confirmado como autoridad ambiental **única** de la provincia de Corrientes — organismo autárquico competente en recursos hídricos, gestión ambiental, tierras fiscales/islas y minería. Destinatario natural de los reportes del predio.

### 3.7 Ley 27.520 — presupuestos mínimos de adaptación y mitigación al cambio climático (2019) [7] 🟢

Texto oficial confirmado directamente. Piezas relevantes, con su artículo exacto:

- **Art. 20 — Planes de Respuesta al Cambio Climático**: obligación de **las provincias y la Ciudad Autónoma de Buenos Aires** (no de los municipios directamente) de elaborar, mediante proceso participativo, planes con información de línea de base y patrón de emisiones GEI de su territorio. **Corrección respecto de la versión anterior**: el documento anterior decía "cada jurisdicción (y los municipios que adhieren)" de forma imprecisa — la obligación legal es provincial; los municipios participan de forma indirecta (a través del plan provincial, o voluntariamente vía redes como la RAMCC, ver abajo).
- **Art. 17 — Sistema Nacional de Información sobre Cambio Climático**: creado como instrumento de diagnóstico para los planes de respuesta y para dar robustez y transparencia al inventario nacional de GEI.
- **Art. 18 — Plan Nacional de Adaptación y Mitigación al Cambio Climático**: marco de políticas de Estado de largo plazo.

**Informe Bienal de Transparencia (IBT1)** [8] 🟡: confirmado que Argentina presentó este informe ante la CMNUCC/ONU, con un Inventario Nacional de GEI que cubre 1990–2022. **No se pudo confirmar en esta revisión** que el documento detalle explícitamente un mecanismo formal de reporte municipio→provincia→Nación, ni que mencione el sector residuos línea por línea — es razonable asumir que "Residuos" es uno de los sectores del inventario porque **es uno de los cinco sectores estándar de cualquier inventario GEI bajo metodología IPCC** (Energía, Procesos Industriales, Agricultura/AFOLU, Residuos, y a veces "Otros") 🔴, pero esto es una inferencia metodológica general, no una cita confirmada del documento argentino.

**RAMCC — Red Argentina de Municipios frente al Cambio Climático** [10] 🟢: confirmada como red de más de 285 municipios de 18 provincias que impulsa los **Planes Locales de Acción Climática (PLAC)**, cuyo primer paso es un inventario de GEI del territorio — esta sí es una vía **municipal y voluntaria** (no la obligación legal de la Ley 27.520, que es provincial) donde el dato de residuos del sistema puede insertarse directamente.

**Implicancia de producto**: la oportunidad más sólida de este eje no es "reportar a la ONU" (cadena no confirmada), sino alimentar el inventario GEI de un **Plan Local de Acción Climática vía RAMCC**, si el municipio de Corrientes adhiere o quisiera adherir — eso sí está documentado y es una vía real, más corta y verificada que la mencionada en la versión anterior.

### 3.8 Sistemas de información ambiental nacionales: SInIA y SIMARCC

- **SInIA / CIAM** ([ciam.ambiente.gob.ar](https://ciam.ambiente.gob.ar/)) [11] 🟢 — Sistema Integrado de Información Ambiental, confirmado en vivo: publica indicadores por eje temático, incluye sección de "Sustancias y Residuos" y portal de datos abiertos.
- **SIMARCC** ([simarcc.ambiente.gob.ar](https://simarcc.ambiente.gob.ar/)) [12] 🟢 — Sistema de Mapas de Riesgo del Cambio Climático, confirmado en vivo: capas georreferenciadas de riesgo climático por territorio. Se cruza con los polígonos GeoJSON de `zonas` (oportunidad A6). **No se verificó en esta revisión** si SIMARCC expone las capas en un formato técnicamente consumible (WMS/tiles/GeoJSON) fuera de su visor — sigue pendiente como tarea de verificación técnica antes de comprometer esfuerzo de desarrollo.

### 3.9 Residuos peligrosos (Ley 24.051) — fuera de alcance recomendado, dato actualizado

Régimen separado con registros nacionales de generadores/transportistas/operadores y manifiestos de transporte — otro mercado, con complejidad regulatoria y de certificación mucho mayor.

**Dato de mercado, revisado y precisado** [16] 🟡: un observatorio creado en 2021 por la Facultad de Ciencias Económicas de la UBA junto con la Universidad Nacional de Rosario (UNR) y las cámaras sectoriales **CATRIES y CAITPA** publica informes bimestrales/anuales de tratamiento de residuos peligrosos industriales. Según su informe más reciente (período julio 2024–junio 2025), reproducido por múltiples medios especializados de forma consistente:

- Argentina genera del orden de **20 millones de toneladas/año** de residuos peligrosos industriales.
- Solo **5,57%** se gestiona correctamente (~1,08 millones de toneladas/año tratadas).
- De más de **252.000 empresas generadoras**, solo **~31.500 (12,48%)** contratan servicios de tratamiento.
- El promedio histórico del observatorio ronda el **5%**, con oscilaciones entre 4% y 8% según el período.

**Corrección respecto de la versión anterior**: la cifra "solo ~5%" ya estaba en el documento, pero como una mención suelta atribuida de forma imprecisa. Ahora está con la fuente, el período y las cifras completas. Sigue sin cambiar la recomendación: es otro mercado, no se persigue en este roadmap (ver §4.C4).

---

## 4. Oportunidades de funcionalidad

Organizadas en tres niveles según el cambio que requieren: **A** — cero cambio de modelo de datos; **B** — extensión mínima del modelo; **C** — apuestas de plataforma.

Esfuerzo: **S** (días), **M** (1–2 semanas), **L** (3+ semanas).

---

### A. Quick wins — con los datos que ya tenemos

#### A1. Sección "Indicadores GIRSU" en Dashboard y Reportes — esfuerzo S

**Qué es**: presentar los KPIs existentes en el vocabulario del sector, con benchmark contra la referencia nacional disponible.

| Indicador | Cálculo | Datos |
|-----------|---------|-------|
| Generación per cápita (kg/hab/día) | `SUM(peso_neto_kg) / SUM(zonas.habitantes) / días del período` | Ya disponibles |
| Comparativa vs referencia nacional | per cápita propio vs 1,15 kg/hab/día [5] (dato de 2010, ver §3.4) | Ya disponibles |
| Toneladas gestionadas en instalación controlada (ODS 11.6.1 [13]) | `SUM(peso_neto_kg)` del período | Ya disponible |
| Per cápita por zona con semáforo | ya existe el per cápita por zona; agregar comparativa | Ya disponible |

**Cambios**: ninguno en modelo. Nueva card en Dashboard + nueva sección del reporte mensual (reutiliza `SvgChartService`). El benchmark nacional va como valor configurable por organización (regla: no hardcodear valores de negocio), con default 1,15 **y una nota visible de que el dato de referencia es de 2010** — la honestidad sobre la vigencia del benchmark es parte del valor: le muestra al municipio que el sistema mismo puede generar el dato actualizado que hoy no existe.

**Valor**: el admin le presenta al municipio números en el vocabulario del sector. Diferenciador inmediato del reporte mensual. Prepara el terreno para A5.

#### A2. Certificado de disposición final — esfuerzo M

**Qué es**: documento PDF verificable que certifica que un vehículo/titular dispuso N kg en el predio en una fecha o período. Dos variantes:

- **Por pesaje**: certificado individual con nº de ticket (el `uuid` público de `pesajes` ya existe para esto), patente, fecha/hora entrada y salida, kg netos.
- **Por período/titular**: certificado consolidado (ej: "Empresa X dispuso 12.450 kg en junio 2026 en N viajes").

**Fundamento normativo**: la **trazabilidad** es lineamiento explícito del Art. 6 del Decreto 779/22 [2][3]. Los generadores privados (vehículos con `titular` particular) necesitan constancia de disposición adecuada ante auditorías ambientales, certificaciones ISO 14001 y licitaciones.

**Cambios**: sin cambio de modelo (el `uuid` de `pesajes` ya da la verificabilidad). Nueva plantilla PDF (reutiliza Browsershot/mPDF), acción `export` en el dominio pesajes, y opcionalmente una ruta pública de verificación por UUID (`/verificar/{uuid}` — muestra datos mínimos, sin login).

**Valor**: el predio pasa de "lugar donde se tira" a "eslabón que certifica". Para generadores privados es un servicio con valor monetizable (combina con B4).

#### A3. Proyección de vida útil del relleno — esfuerzo S/M

**Qué es**: con el acumulado histórico de toneladas y la capacidad total del predio, proyectar la fecha estimada de saturación.

**Cálculo**: capacidad remanente = capacidad total − acumulado dispuesto; proyección por promedio móvil (últimos 90/180 días) con banda optimista/pesimista. Presentación como card de Dashboard + sección de reporte + alerta nueva (`vida_util_relleno` cuando el remanente proyectado cae bajo un umbral de meses configurable).

**Cambios**: dos campos nuevos de configuración por organización (capacidad total en toneladas, acumulado histórico previo al sistema como offset inicial). Cero cambio en `pesajes`.

**Valor**: *la* pregunta que un intendente le hace al predio y que ningún Excel responde. Insumo directo de planificación de inversión municipal (celda nueva, expansión). Argumento de venta potente para nuevos tenants. Esta oportunidad no depende de ningún dato normativo externo — es la de mayor certeza del documento.

#### A4. Estimación de emisiones GEI (metano) — esfuerzo M

**Qué es**: sección opcional del reporte que estima las emisiones de metano generadas por las toneladas dispuestas, usando el método de primer orden del IPCC (tier 1, factores default para residuos urbanos).

**Fundamento**: los rellenos son una fuente reconocida de metano de origen municipal. La Ley 27.520 exige a las **provincias** planes de respuesta con línea de base de emisiones (§3.7) [7], y la vía municipal más concreta y verificada es un inventario de GEI para un Plan Local de Acción Climática si el municipio participa de la RAMCC [10]. En ambos casos el dato del sector residuos hoy se estima a mano o no existe.

**Cambios**: sin cambio de modelo. Servicio de cálculo nuevo (`EmisionesService`) + sección opcional del reporte (configurable por organización, como las conclusiones IA). La precisión mejora mucho si se combina con B1 (composición por fracción — el factor de emisión depende de la fracción orgánica).

**Valor**: diferenciador; conecta el producto con la agenda climática municipal. Recomendado activarlo *después* de B1, y etiquetar siempre como "estimación tier 1" para no sobre-prometer precisión.

#### A5. Export "formato oficial" — esfuerzo S

**Qué es**: exportación Excel/CSV con la estructura de datos que suelen pedir los relevamientos provinciales (ICAA) y nacionales: toneladas mensuales por origen, per cápita, cobertura, método de disposición.

**Cambios**: sin cambio de modelo. Una plantilla más de PhpSpreadsheet sobre las queries existentes de reportes.

**Valor**: convierte el reporte interno en un insumo más fácil de adaptar a lo regulatorio. Barato hoy; si se publica una actualización de la ENGIRSU con un formato específico, se ajusta la plantilla.

#### A6. Capa de riesgo climático (SIMARCC) sobre el mapa de zonas — esfuerzo S/M

**Qué es**: superponer las capas públicas de riesgo climático del SIMARCC (§3.8) — inundación, precipitaciones extremas, olas de calor — sobre el mapa Leaflet de zonas que ya existe en Dashboard y Reportes.

**Cambios**: sin cambio de modelo. Los polígonos GeoJSON de `zonas` ya están; se agrega la capa externa como overlay opcional del panel de mapa existente. **Requiere verificación técnica previa** (no realizada en esta revisión): confirmar que SIMARCC expone las capas en un formato consumible (WMS/tiles/GeoJSON descargable) — si solo ofrece visor cerrado, la alternativa es un export estático puntual.

**Valor**: qué zonas de recolección están en área de riesgo de inundación es información de planificación operativa real en Corrientes (rutas, frecuencias en temporada de lluvias) y suma una página de alto impacto visual al reporte.

**Riesgo**: dependencia de un servicio externo cuya interfaz de datos no fue verificada. Mantener siempre como capa opcional que degrada sin romper el mapa.

---

### B. Extensiones mínimas del modelo de datos

#### B1. Fracción de residuo por pesaje — esfuerzo M · la llave del eje circular

**Qué es**: clasificar cada pesaje según el código unificado de colores del Anexo II del Decreto 779/22 [3] (secos reciclables, orgánicos reciclables, basura — con sub-fracciones opcionales a futuro: plásticos, papel/cartón, vidrio, metales).

**Modelo de datos** (siguiendo las reglas SQL Server del proyecto):

```php
// Nueva tabla maestra: fracciones (ABM estándar, patrón 07-abm-guide)
Schema::create('fracciones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
    $table->string('nombre', 100);            // 'Mezclados', 'Secos reciclables', 'Orgánicos'
    $table->string('color', 20)->nullable();  // código de color del Anexo II (para UI)
    $table->boolean('valorizable')->default(false);
    $table->boolean('activo')->default(true);
    $table->timestamps();
});

// En pesajes: FK nullable (compatibilidad total con histórico)
$table->foreignId('fraccion_id')->nullable()
      ->constrained('fracciones')->noActionOnDelete(); // noAction: fracciones ya cascadea de organizaciones
```

**UX Balanza**: un select más, con sugerencia automática por tipo de servicio (mismo patrón que la sugerencia de tipo de vehículo). Con default inteligente, el costo para Roberto es ~0 segundos en el caso común. `NULL` = sin clasificar (histórico y organizaciones que no adopten la funcionalidad).

**Qué habilita**: % desviado de enterramiento (jerarquía del Art. 6 del 779/22), composición para el cálculo GEI (A4), y todo el módulo B2. La conexión con "ODS 12.5.1" se retira de este documento por no estar confirmada (ver §3.5) — la justificación de B1 se sostiene igual por la jerarquía del decreto y por B2, sin necesitar esa cita.

#### B2. Módulo de valorización — egresos de material recuperado — esfuerzo L

**Qué es**: registrar el material que **sale** del predio como recurso (venta de reciclables, retiro por cooperativas, compost). Cierra el balance de masa: entró X, se recuperó Y, se enterró Z.

**Modelo de datos**: nueva tabla `movimientos_material` (misma anatomía que `pesajes`: organización, vehículo opcional, fracción, kg, destino/comprador, operador, fecha, auditoría). Reutiliza el flujo de balanza con una variante "egreso de material". Requiere B1.

**Indicadores nuevos**: tasa de recuperación (`kg valorizados / kg ingresados`), evolución de valorización por fracción, destino de materiales — el objetivo de reducción y valorización de la ENGIRSU [15] hecho dashboard.

**Valor**: transforma el relato del producto de "sistema de balanza" a "plataforma de economía circular del predio". Es la funcionalidad que más eleva el techo comercial del producto.

#### B3. Padrón de recuperadores urbanos / cooperativas — esfuerzo M

**Qué es**: ABM de recuperadores/cooperativas que operan en el predio, asociable a los movimientos de material de B2.

**Fundamento**: la formalización de recuperadores urbanos es un componente social recurrente en los programas nacionales de gestión de residuos (PMGIRSU incluido, §3.6) [14].

**Modelo**: tabla `recuperadores` (organización, nombre, tipo persona/cooperativa, CUIT opcional, contacto, activo) + FK opcional en `movimientos_material`. Un ABM estándar según [`07-abm-guide.md`](07-abm-guide.md).

**Valor**: le da al municipio el dato social que los programas nacionales suelen pedir (cuántos recuperadores, cuánto material formalizado). Bajo esfuerzo si B2 existe.

#### B4. Liquidación por disposición — canon por tonelada — esfuerzo L

**Qué es**: tarifario por tipo de servicio/titular y liquidación mensual automática para generadores privados que pagan por disponer.

**Modelo**: tabla `tarifas` (organización, tipo_servicio_id opcional, vigencia desde/hasta, $/tonelada) + generación de liquidación mensual por titular (agrupando `pesajes` por `vehiculos.titular`). La liquidación reutiliza el motor de reportes (PDF + snapshot + email).

**Valor**: funcionalidad estándar del software de weighbridge y fuente de recaudación directa para el municipio. Sinergia natural con A2 (certificado + liquidación en el mismo envío).

**Riesgo**: roza el dominio de facturación fiscal (AFIP/ARCA). Mantenerlo como *liquidación* (documento interno de cobro) y no como factura electrónica evita esa complejidad en esta etapa.

---

### C. Apuestas de plataforma (Etapa 3+)

#### C1. Reporte normativo hacia provincia/Nación

Si se formaliza un flujo de datos de jurisdicciones locales hacia la Nación (actualización de la ENGIRSU, §3.3 — no confirmada aún), el sistema podría generar el paquete completo (indicadores + export oficial + mapa) listo para presentar ante el ICAA [9] o hacia el portal de datos abiertos del SInIA [11]. Depende de A1+A5 y de un trigger externo que **hoy no está confirmado** — no comprometer desarrollo hasta que exista una convocatoria real.

#### C2. Trazabilidad entre predios (centros de transferencia)

Ya existe el tipo de servicio "Centros de Transferencia". Extender la cadena origen → transferencia → disposición final entre organizaciones del mismo tenant profundiza el principio de trazabilidad del Art. 6 del 779/22 [2]. Relevante recién cuando haya un tenant con esa topología real — no anticipar.

#### C3. Open data / API pública

Portal de transparencia del municipio con indicadores públicos (toneladas, per cápita, mapa). Hoy explícitamente fuera de alcance (Etapa 1 excluye API pública); es el paso natural cuando el municipio quiera comunicar resultados. El portal de datos abiertos del SInIA [11] es una referencia de formato razonable si se quisiera integrar en el futuro.

#### C4. Residuos peligrosos (Ley 24.051) — no recomendado

Registros nacionales, manifiestos, certificación de operadores: es otro producto y otro mercado, con barrera regulatoria alta y una tasa de formalización nacional muy baja (~5,57% según el observatorio UBA-UNR-CATRIES-CAITPA, §3.9) [16]. Solo reconsiderar si aparece un cliente operador de peligrosos con contrato que financie el desarrollo.

---

## 5. Matriz de priorización

| # | Funcionalidad | Esfuerzo | Valor cliente | Valor comercial | Cambio de modelo | Dependencias |
|---|--------------|----------|---------------|-----------------|------------------|--------------|
| A1 | Indicadores GIRSU + benchmark | S | Alto | Alto | No | — |
| A3 | Vida útil del relleno | S/M | **Muy alto** | Alto | Config only | — |
| A5 | Export formato oficial | S | Medio | Medio-alto | No | A1 |
| B1 | Fracción por pesaje | M | Medio | **Muy alto** (habilita todo el eje B) | Sí (aditivo, nullable) | — |
| A2 | Certificado de disposición | M | Alto | Alto | No | — |
| A4 | Emisiones GEI | M | Medio | Medio | No | Mejor con B1 |
| A6 | Capa riesgo climático (SIMARCC) | S/M | Medio | Medio | No | Verificación técnica de capas SIMARCC (pendiente) |
| B4 | Liquidación por tonelada | L | **Muy alto** (recaudación) | Alto | Sí | — |
| B2 | Valorización / egresos de material | L | Alto | **Muy alto** | Sí | B1 |
| B3 | Recuperadores urbanos | M | Medio | Medio | Sí | B2 |
| C1 | Reporte normativo hacia Nación | M | Alto | Alto (condicional) | No | A1+A5 + trigger externo no confirmado |

### Secuencia propuesta (post go-live 14/07/2026)

```
Sprint post-go-live 1  →  A1 + A3          (indicadores + vida útil: impacto visible inmediato)
Sprint post-go-live 2  →  A5 + B1          (export oficial + fracciones: siembra del eje circular)
Sprint post-go-live 3  →  A2 ó B4          (según prioridad comercial: trazabilidad vs recaudación)
Oportunista            →  A6               (si la verificación técnica de capas SIMARCC da positiva)
Etapa 3                →  B2 → B3 → A4     (módulo valorización completo + GEI con composición real)
Trigger externo         →  C1               (solo si se confirma un flujo de reporte nacional formal)
```

---

## 6. Riesgos y consideraciones

| Riesgo | Mitigación |
|--------|-----------|
| El benchmark nacional (1,15 kg/hab/día) tiene 16 años y no hay reemplazo público | Mostrarlo siempre con su año de origen (2010); valor configurable por organización, nunca hardcodeado |
| La expectativa de una "ENGIRSU 2025" o flujo de reporte formal no está confirmada como publicada | No comprometer desarrollo de C1 hasta que exista una convocatoria real y verificable |
| Precisión pobre del cálculo GEI sin composición | Publicar A4 solo tras B1, y etiquetar siempre como "estimación tier 1" |
| B1 agrega un paso al flujo del operador (< 10 seg es criterio de aceptación) | Sugerencia automática por servicio con default; medir el tiempo de flujo en piloto antes de generalizar |
| B4 roza dominio fiscal | Alcance = liquidación interna, no factura electrónica |
| Este documento cita normativa y estudios de terceros que pueden actualizarse o discontinuarse | Revisar las fuentes de §7 antes de tomar cualquier decisión de inversión grande basada en un trigger externo (ej. C1) |

---

## 7. Fuentes y notas de verificación

Cada fuente indica su nivel de confianza (🟢 confirmado directamente / 🟡 confirmado con reserva vía fuentes secundarias / 🔴 no verificado de forma independiente) y qué afirmación concreta del documento sostiene.

| # | Fuente | Sostiene | Confianza |
|---|--------|----------|-----------|
| [1] | [Ley 25.916 — texto oficial](https://www.argentina.gob.ar/normativa/nacional/ley-25916-98327/texto) | §3.1 — autoridades competentes, responsabilidades de gestión | 🟢 |
| [2] | [Decreto 779/2022 — texto oficial](https://www.argentina.gob.ar/normativa/nacional/decreto-779-2022-375566/texto) (fetch directo bloqueado con 403; confirmado vía [Liga del Consorcista](https://ligadelconsorcista.org/decreto-779-2022-reglamenta-ley-25916-de-gestion-de-residuos-domiciliarios) y [Allende & Brea](https://allende.com/esg-sustentabilidad/ley-de-gestion-integral-de-residuos-domesticos-no-25-916-reglamento-11-29-2022/), que citan el Art. 6 textualmente) | §3.2 — 7 lineamientos, jerarquía de opciones | 🟡 |
| [3] | [Anexo II Decreto 779/2022 — código de colores](https://aldiaargentina.microjuris.com/wp-content/uploads/2022/11/ANX_-Anexo-II-Decreto-779-2022.pdf) | §3.2 — 7 fracciones y colores | 🟡 |
| [4] | Resolución SAyDS 21/2009 (referenciada en [Jefatura de Gabinete, acta 22/6/2009](https://www.infoleg.gob.ar/basehome/actos_gobierno/actosdegobierno22-6-2009-3.htm)) | §3.4 — creación del Observatorio Nacional GIRSU | 🟢 |
| [5] | Banco Interamericano de Desarrollo / AIDIS / OPS — *Evaluación Regional del Manejo de Residuos Sólidos Urbanos en América Latina y el Caribe* (2010) | §3.4 — indicadores nacionales y regionales de disposición final | 🟡 (estudio confirmado, fetch del PDF original no realizado en esta pasada) |
| [6] | [El sector de residuos sólidos en la Argentina — argentina.gob.ar](https://www.argentina.gob.ar/ambiente/preservacion-control/gestionresiduos/argentina) | §3.4 — reproduce las cifras de [5] sin fecha visible | 🟡 (fetch directo bloqueado con 403; confirmado vía búsqueda) |
| [7] | [Ley 27.520 — texto oficial](https://www.argentina.gob.ar/normativa/nacional/ley-27520-333515/texto) | §3.7 — Arts. 17, 18, 20 | 🟢 |
| [8] | [Primer Informe Bienal de Transparencia (IBT1)](https://www.argentina.gob.ar/interior/ambiente/accion/segunda-ndc) | §3.7 — inventario GEI nacional 1990–2022 | 🟡 (existencia y contenido general confirmados; mecanismo de reporte subnacional NO confirmado) |
| [9] | [ICAA — Instituto Correntino del Agua y del Ambiente](https://icaa.gov.ar/) | §3.6 — autoridad ambiental de Corrientes | 🟢 |
| [10] | [RAMCC — Red Argentina de Municipios frente al Cambio Climático](https://www.ramcc.net/) | §3.7 — PLAC, inventario GEI municipal | 🟢 |
| [11] | [SInIA / CIAM](https://ciam.ambiente.gob.ar/) | §3.8 — sistema de información ambiental, datos abiertos | 🟢 |
| [12] | [SIMARCC](https://simarcc.ambiente.gob.ar/) | §3.8 — mapas de riesgo climático | 🟢 |
| [13] | [Indicador ODS 11.6.1 — INE España / Chile Agenda 2030](https://www.ine.es/dyngs/ODS/es/indicador.htm?id=5096) | §3.5 — definición oficial del indicador | 🟢 |
| [14] | [Boletín Oficial — Resolución 454/2020](https://www.boletinoficial.gob.ar/detalleAviso/primera/238694/20201217), [Resolución 267/2021](https://www.boletinoficial.gob.ar/detalleAviso/primera/248688/20210827) | §3.6 — PMGIRSU, objetivos y financiamiento | 🟡 |
| [15] | [Estrategia Nacional para la GIRSU — argentina.gob.ar](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/evaluacion-y-control-ambiental/gestion-de-residuos-solidos-3) | §3.3 — pilares de la ENGIRSU | 🟡 |
| [16] | Observatorio de Residuos Peligrosos (UBA-FCE / UNR / CATRIES / CAITPA), informe jul-2024–jun-2025, reproducido por [Visión Sustentable](https://www.visionsustentable.com/2026/03/10/residuos-peligrosos-argentina-informe-observatorio-2025/), [Futuro Sustentable](https://futurosustentable.com.ar/residuos-peligrosos-el-94-no-recibe-tratamiento-y-expone-fallas-en-control-ambiental/) y otros medios | §3.9 — tasa de tratamiento de residuos peligrosos | 🟡 |

**Otras páginas del portal de Ambiente consultadas** (sin cita numerada por no aportar datos cuantitativos nuevos): [Ambiente — portal principal](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente), [Evaluación y Control Ambiental](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/evaluacion-y-control-ambiental), [Desarrollo Sostenible y Gestión Climática](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/desarrollo-sostenible-y-gestion-climatica), [Residuos Sólidos Urbanos](https://www.argentina.gob.ar/interior/ambiente/control/rsu), [Etapas de la gestión integral de RSU](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/evaluacion-y-control-ambiental/gestion-de-residuos-solidos-0), [Gabinete Nacional de la Ley 27.520](https://www.argentina.gob.ar/ambiente/cambio-climatico/gabinete-nacional).

**Fuente citada en la versión anterior y retirada en esta revisión** por no haber podido confirmarla de forma independiente: "Informe AGN 2023-136" como fuente de la cuantificación de ODS 11.6.1/12.5.1 en Argentina — el fetch directo al PDF falló dos veces (error de certificado) y la atribución original se basaba en un resumen de búsqueda ambiguo, no en una lectura confirmada del documento. No se afirma que el informe sea falso — solo que no pudo verificarse en esta pasada, y por eso se retira la cita puntual del cuerpo del documento.

---

*Documento creado: 07/07/2026 · Revisado y verificado: 08/07/2026 · Análisis de producto — no es alcance comprometido.*
