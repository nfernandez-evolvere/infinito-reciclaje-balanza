# Oportunidades de producto — Marco GIRSU Nacional
## Sistema de Gestión de Balanza · Infinito Reciclaje × EVOLVERE

> **Tipo de documento**: análisis de producto y mercado. No es alcance comprometido — es el insumo para decidir el roadmap post go-live (Etapa 2/3).
>
> **Fecha**: 07/07/2026 · **Fuentes**: portal de [Ambiente](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente) (Jefatura de Gabinete — Turismo y Ambiente) completo — incluye [Evaluación y Control Ambiental](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/evaluacion-y-control-ambiental) y [Desarrollo Sostenible y Gestión Climática](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/desarrollo-sostenible-y-gestion-climatica) —, normativa nacional GIRSU y de cambio climático, sistemas de información nacionales (SInIA, SIMARCC) y relevamiento de mercado. Listado completo de fuentes al final.

---

## 1. Resumen ejecutivo

El sistema hoy captura **la serie de datos exacta que el marco normativo argentino de residuos exige y va a exigir cada vez más**: quién dispuso cuánto, de qué zona, con qué servicio, cuándo — normalizada por habitantes y hectáreas, con geometría territorial.

Cuatro hechos del contexto regulatorio convierten ese activo en oportunidad:

1. El **Decreto 779/22** (reglamentario de la Ley 25.916) fija como principios rectores la **trazabilidad**, la **jerarquía de valorización** y el **monitoreo con indicadores** — exactamente lo que el sistema ya hace o puede hacer con extensiones menores.
2. La **ENGIRSU 2025** (nueva Estrategia Nacional GIRSU, en evaluación/publicación este año) tiene a los **sistemas de información** como pilar: viene una nueva ola de demanda de datos hacia municipios.
3. La región **Cuyo–Mesopotamia** (incluye Corrientes) tiene la **peor tasa de disposición final adecuada del país (15,2%)**. Un predio formal con datos trazables es la excepción regional: primer candidato a vidriera y a proveedor del formato de reporte cuando la ENGIRSU 2025 lo formalice.
4. La **Ley 27.520** (cambio climático) obliga a cada jurisdicción a elaborar **planes de respuesta con línea de base de emisiones GEI** — y el sector residuos es uno de los sectores del inventario nacional. Los kilos que pesa la balanza son el dato primario de esa línea de base municipal, que hoy nadie en la región tiene sistematizado.

**Recomendación**: priorizar 3 quick wins sin cambio de modelo (indicadores GIRSU, proyección de vida útil, export formato oficial), luego una única extensión de modelo (fracción de residuo por pesaje) que habilita todo el eje de economía circular, y elegir entre certificado de disposición o liquidación por tonelada según la necesidad comercial de Infinito Reciclaje.

---

## 2. El producto hoy — qué activo de datos tenemos

Resumen del alcance vigente (detalle en [`01-brief-producto.md`](01-brief-producto.md) y [`03-data-model.md`](03-data-model.md)):

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

### 3.1 Ley 25.916 — presupuestos mínimos de gestión de RSU (2004)

Marco nacional de gestión de residuos domiciliarios. Define responsabilidades: los **municipios son responsables de la ejecución, operación y mantenimiento** de recolección, transporte, tratamiento y disposición final. Es la ley madre bajo la cual opera el predio de Corrientes.

### 3.2 Decreto 779/22 — reglamentación y economía circular (2022)

Reglamenta la Ley 25.916 dieciocho años después y orienta todo el sistema a la **economía circular**. Es el documento normativo más accionable para el producto:

**Siete lineamientos operativos:**

1. Cuna a cuna (recuperación integral)
2. Proximidad (gestión cercana al origen)
3. Responsabilidad extendida del productor (REP)
4. Ecodiseño
5. Gradualidad implementativa
6. Mejores técnicas disponibles
7. **Trazabilidad** ← directamente implementable con nuestros datos

**Jerarquía de opciones** (orden de prioridad obligatorio): prevención → reutilización → recuperación → tratamiento → **disposición final como último recurso**. Todo indicador que muestre cuánto se desvía del enterramiento alimenta esta jerarquía.

**Código unificado de colores (Anexo II)**: fracciones estandarizadas — residuos secos valorizables, orgánicos, basura (+ sub-fracciones: plásticos, papel/cartón, vidrio, metales). Es la taxonomía natural para clasificar pesajes.

**Recomendaciones operativas explícitas**: señalización, comunicación ambiental y **"monitoreo e indicadores"** — respaldo normativo directo para un módulo de indicadores.

### 3.3 ENGIRSU — Estrategia Nacional GIRSU (2005–2025 → 2025+)

La estrategia nacional 2005 cumplió su horizonte de 20 años. **La ENGIRSU 2025 está en evaluación y publicación durante este año.** Sus pilares declarados:

- Economía circular y las "7 R" (reducir, rechazar, reutilizar, reparar, recuperar, rediseñar, reciclar)
- Cierre progresivo de basurales a cielo abierto
- **Sistemas de información** ← pilar explícito: la Nación necesita datos de municipios
- Fortalecimiento de gobernanza y comunicación participativa

**Implicancia de producto**: cuando la ENGIRSU 2025 formalice el flujo de datos municipio → provincia → Nación, los municipios van a necesitar generar esos reportes. El sistema puede tener el formato listo *antes*.

### 3.4 Observatorio Nacional GIRSU e indicadores que releva la Nación

Creado por Resolución SAyDS 21/2009. Sistematiza información, elabora informes y releva la línea de base nacional. Indicadores de referencia publicados:

| Indicador | Valor nacional | Fuente del dato |
|-----------|---------------|-----------------|
| Generación per cápita | **1,15 kg/hab/día** | Estudios de línea de base |
| Cobertura de recolección | 99,8% (población urbana) | Relevamiento nacional |
| Disposición final en relleno sanitario | **64,7%** | Relevamiento nacional |
| Disposición inadecuada | 35,3% (9,9% vertederos controlados + 24,6% basurales a cielo abierto) | Relevamiento nacional |

**Desigualdad regional** — el dato comercialmente más importante del documento:

| Región | Disposición final adecuada |
|--------|---------------------------|
| Resto del país | 79,4% |
| Norte | 50,1% |
| **Cuyo–Mesopotamia (incluye Corrientes)** | **15,2%** |

La región donde opera el cliente es la de peor desempeño del país. Cada municipio de la región que quiera salir de esa estadística va a necesitar exactamente lo que este sistema hace. **El multi-tenant ya construido es la infraestructura para capturar ese mercado.**

### 3.5 Indicadores ODS que Argentina reporta

La Nación usa estudios de línea de base para cuantificar dos indicadores de la Agenda 2030 que se calculan con datos que el sistema tiene (o tendría con la extensión de fracciones):

- **ODS 11.6.1** — proporción de RSU recolectados y gestionados en instalaciones controladas: el predio *es* una instalación controlada; cada tonelada pesada alimenta este indicador.
- **ODS 12.5.1** — tasa nacional de reciclaje (toneladas valorizadas): requiere registrar la fracción valorizada (hoy no capturada).

### 3.6 Programas de asistencia (PMGIRSU) y autoridad provincial

- **PMGIRSU**: programas nacionales de asistencia técnica y financiera a municipios para planes GIRSU y erradicación de basurales. Los municipios que aplican deben presentar diagnósticos con datos — otro consumidor del export oficial.
- **ICAA** (Instituto Correntino del Agua y del Ambiente): autoridad ambiental provincial de Corrientes, destinataria natural de los reportes del predio.

### 3.7 Ley 27.520 — cambio climático y planes de respuesta jurisdiccionales

Ley de presupuestos mínimos de adaptación y mitigación al cambio climático (2019). Piezas relevantes para el producto:

- **Planes de respuesta por jurisdicción**: cada provincia (y los municipios que adhieren) debe elaborar su plan con **información de línea de base y patrón de emisiones GEI** del territorio. El sector residuos es uno de los sectores del inventario nacional — el dato primario es exactamente lo que pesa la balanza.
- **Sistema Nacional de Información sobre Cambio Climático**: creado por la ley como instrumento de diagnóstico para los planes de respuesta y para dar robustez y transparencia al inventario nacional de GEI. Otro sistema nacional que va a demandar datos de abajo hacia arriba.
- **Informe Bienal de Transparencia (IBT)**: Argentina reporta a la ONU (UNFCCC) su inventario sectorial, residuos incluido. La cadena de datos es municipio → provincia → Nación → ONU, y hoy el eslabón municipal es el más débil.
- **Redes municipales**: la RAMCC (Red Argentina de Municipios frente al Cambio Climático) agrupa a los municipios que elaboran Planes Locales de Acción Climática — todos necesitan la línea de base de residuos que este sistema produce como subproducto.

**Implicancia de producto**: refuerza directamente la oportunidad A4 (emisiones GEI). El reporte del sistema puede ser el capítulo "residuos" del inventario GEI municipal sin trabajo adicional del municipio.

### 3.8 Sistemas de información ambiental nacionales: SInIA y SIMARCC

El portal de Ambiente expone dos sistemas nacionales operativos:

- **SInIA / CIAM** ([ciam.ambiente.gob.ar](https://ciam.ambiente.gob.ar/)) — Sistema Integrado de Información Ambiental: publica indicadores por eje temático con sección dedicada de **"Sustancias y Residuos"** y portal de **datos abiertos**. Es el destino natural de un feed de datos municipales de residuos; hoy la información de RSU que integra proviene de relevamientos puntuales, no de sistemas operacionales en tiempo real como este.
- **SIMARCC** ([simarcc.ambiente.gob.ar](https://simarcc.ambiente.gob.ar/)) — Sistema de Mapas de Riesgo del Cambio Climático: capas georreferenciadas públicas de riesgo climático (inundación, temperatura, eventos extremos) por territorio. **Se cruza directamente con nuestros polígonos GeoJSON de zonas**: superponer riesgo climático sobre las zonas de recolección es una funcionalidad de mapa de bajo costo (ver A6).

### 3.9 Lo que queda fuera: residuos peligrosos (Ley 24.051)

Régimen separado con registros nacionales de generadores/transportistas/operadores y **manifiestos** de transporte. Es otro mercado, con alta complejidad regulatoria y certificación. Según informes recientes, solo ~5% de los residuos peligrosos se trata adecuadamente en Argentina — hay mercado, pero requiere un producto distinto. **No recomendado para este roadmap** (ver §4.C).

---

## 4. Oportunidades de funcionalidad

Organizadas en tres niveles según el cambio que requieren: **A** — cero cambio de modelo de datos; **B** — extensión mínima del modelo; **C** — apuestas de plataforma.

Esfuerzo: **S** (días), **M** (1–2 semanas), **L** (3+ semanas).

---

### A. Quick wins — con los datos que ya tenemos

#### A1. Sección "Indicadores GIRSU" en Dashboard y Reportes — esfuerzo S

**Qué es**: presentar los KPIs existentes en el vocabulario oficial del Observatorio Nacional, con benchmark contra el promedio nacional.

| Indicador | Cálculo | Datos |
|-----------|---------|-------|
| Generación per cápita (kg/hab/día) | `SUM(peso_neto_kg) / SUM(zonas.habitantes) / días del período` | ✅ Ya disponibles |
| Comparativa vs nacional | per cápita propio vs **1,15 kg/hab/día** | ✅ Constante configurable |
| Toneladas gestionadas en instalación controlada (ODS 11.6.1) | `SUM(peso_neto_kg)` del período | ✅ Ya disponible |
| Per cápita por zona con semáforo | ya existe el per cápita por zona; agregar comparativa | ✅ Ya disponible |

**Cambios**: ninguno en modelo. Nueva card en Dashboard + nueva sección del reporte mensual (reutiliza `SvgChartService`). El benchmark nacional va como valor configurable por organización (regla: [no hardcodear valores de negocio](#)), con default 1,15.

**Valor**: el admin le presenta al municipio números en el idioma de la Nación. Diferenciador inmediato del reporte mensual. Prepara el terreno para A5.

#### A2. Certificado de disposición final — esfuerzo M

**Qué es**: documento PDF verificable que certifica que un vehículo/titular dispuso N kg en el predio en una fecha o período. Dos variantes:

- **Por pesaje**: certificado individual con nº de ticket (el `uuid` público de `pesajes` ya existe para esto), patente, fecha/hora entrada y salida, kg netos.
- **Por período/titular**: certificado consolidado (ej: "Empresa X dispuso 12.450 kg en junio 2026 en N viajes").

**Fundamento normativo**: la **trazabilidad** es lineamiento del Decreto 779/22. Los generadores privados (vehículos con `titular` particular) necesitan constancia de disposición adecuada ante auditorías ambientales, certificaciones ISO 14001 y licitaciones.

**Cambios**: sin cambio de modelo (el `uuid` de `pesajes` ya da la verificabilidad). Nueva plantilla PDF (reutiliza Browsershot/mPDF), acción `export` en el dominio pesajes, y opcionalmente una ruta pública de verificación por UUID (`/verificar/{uuid}` — muestra datos mínimos, sin login).

**Valor**: el predio pasa de "lugar donde se tira" a "eslabón que certifica". Para generadores privados es un servicio con valor monetizable (combina con B4).

#### A3. Proyección de vida útil del relleno — esfuerzo S/M

**Qué es**: con el acumulado histórico de toneladas y la capacidad total del predio, proyectar la fecha estimada de saturación.

**Cálculo**: capacidad remanente = capacidad total − acumulado dispuesto; proyección por promedio móvil (últimos 90/180 días) con banda optimista/pesimista. Presentación como card de Dashboard + sección de reporte + alerta nueva (`vida_util_relleno` cuando el remanente proyectado cae bajo un umbral de meses configurable).

**Cambios**: dos campos nuevos de configuración por organización (capacidad total en toneladas, acumulado histórico previo al sistema como offset inicial). Cero cambio en `pesajes`.

**Valor**: *la* pregunta que un intendente le hace al predio y que ningún Excel responde. Insumo directo de planificación de inversión municipal (celda nueva, expansión). Argumento de venta potente para nuevos tenants.

#### A4. Estimación de emisiones GEI (metano) — esfuerzo M

**Qué es**: sección opcional del reporte que estima las emisiones de metano generadas por las toneladas dispuestas, usando el método de primer orden del IPCC (tier 1, factores default para residuos urbanos en clima subtropical).

**Fundamento**: los rellenos son la principal fuente de metano de origen municipal. La **Ley 27.520** exige a las jurisdicciones planes de respuesta con línea de base de emisiones (§3.7), el inventario nacional reporta el sector residuos a la ONU vía el Informe Bienal de Transparencia, y los municipios de la RAMCC elaboran Planes Locales de Acción Climática — en todos los casos el dato del sector residuos hoy se estima a mano o directamente no existe. El reporte del sistema puede ser el capítulo "residuos" del inventario GEI municipal sin trabajo adicional del municipio.

**Cambios**: sin cambio de modelo. Servicio de cálculo nuevo (`EmisionesService`) + sección opcional del reporte (configurable por organización, como las conclusiones IA). La precisión mejora mucho si se combina con B1 (composición por fracción — el factor de emisión depende de la fracción orgánica).

**Valor**: diferenciador fuerte; conecta el producto con la agenda climática municipal. Recomendado activarlo *después* de B1 para no publicar números de precisión pobre.

#### A5. Export "formato oficial" — esfuerzo S

**Qué es**: exportación Excel/CSV con la estructura de datos que piden los relevamientos provinciales (ICAA) y nacionales (línea de base ENGIRSU, PMGIRSU): toneladas mensuales por origen, per cápita, cobertura, método de disposición.

**Cambios**: sin cambio de modelo. Una plantilla más de PhpSpreadsheet sobre las queries existentes de reportes.

**Valor**: convierte el reporte interno en el insumo regulatorio directo. Barato hoy; cuando la ENGIRSU 2025 formalice el formato, se ajusta la plantilla y el sistema queda como el proveedor natural del dato. **Timing perfecto: la estrategia se publica este año.**

#### A6. Capa de riesgo climático (SIMARCC) sobre el mapa de zonas — esfuerzo S/M

**Qué es**: superponer las capas públicas de riesgo climático del SIMARCC (§3.8) — inundación, precipitaciones extremas, olas de calor — sobre el mapa Leaflet de zonas que ya existe en Dashboard y Reportes.

**Cambios**: sin cambio de modelo. Los polígonos GeoJSON de `zonas` ya están; se agrega la capa externa (tiles/WMS del SIMARCC o export estático de sus capas para la zona de Corrientes) como overlay opcional del panel de mapa existente. Requiere una verificación técnica previa: confirmar que SIMARCC expone las capas en un formato consumible (WMS/tiles/GeoJSON descargable) — si solo ofrece visor cerrado, la alternativa es un export estático puntual.

**Valor**: cruza la operación con la agenda de adaptación de la Ley 27.520 — qué zonas de recolección están en área de riesgo de inundación es información de planificación operativa real (rutas, frecuencias en temporada de lluvias, un dato no menor en Corrientes) y suma una página de alto impacto visual al reporte. Es la única oportunidad del documento que usa datos *externos* ya publicados por la Nación en lugar de producir datos propios.

**Riesgo**: dependencia de un servicio externo (disponibilidad y continuidad del SIMARCC). Mantener siempre como capa opcional que degrada sin romper el mapa (mismo principio de resiliencia que la operación de Balanza).

---

### B. Extensiones mínimas del modelo de datos

#### B1. Fracción de residuo por pesaje — esfuerzo M · **la llave del eje circular**

**Qué es**: clasificar cada pesaje según el código unificado de colores del Decreto 779/22 (Anexo II): residuos mezclados ("basura"), secos valorizables, orgánicos — con sub-fracciones opcionales a futuro.

**Modelo de datos** (siguiendo las reglas SQL Server del proyecto):

```php
// Nueva tabla maestra: fracciones (ABM estándar, patrón 07-abm-guide)
Schema::create('fracciones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
    $table->string('nombre', 100);            // 'Mezclados', 'Secos valorizables', 'Orgánicos'
    $table->string('color', 20)->nullable();  // código de color del Anexo II (para UI)
    $table->boolean('valorizable')->default(false); // alimenta ODS 12.5.1
    $table->boolean('activo')->default(true);
    $table->timestamps();
});

// En pesajes: FK nullable (compatibilidad total con histórico)
$table->foreignId('fraccion_id')->nullable()
      ->constrained('fracciones')->noActionOnDelete(); // ⚠️ noAction: fracciones ya cascadea de organizaciones
```

**UX Balanza**: un select más, **con sugerencia automática por tipo de servicio** (mismo patrón que la sugerencia de tipo de vehículo — ej: "Domiciliario" sugiere "Mezclados"). Con default inteligente, el costo para Roberto es ~0 segundos en el caso común. `NULL` = sin clasificar (histórico y organizaciones que no adopten la funcionalidad).

**Qué habilita**: tasa de valorización (ODS 12.5.1), % desviado de enterramiento (jerarquía del 779/22), composición para el cálculo GEI (A4), y todo el módulo B2.

#### B2. Módulo de valorización — egresos de material recuperado — esfuerzo L

**Qué es**: registrar el material que **sale** del predio como recurso (venta de reciclables, retiro por cooperativas, compost). Cierra el balance de masa: entró X, se recuperó Y, se enterró Z.

**Modelo de datos**: nueva tabla `movimientos_material` (misma anatomía que `pesajes`: organización, vehículo opcional, fracción, kg, destino/comprador, operador, fecha, auditoría). Reutiliza el flujo de balanza con una variante "egreso de material". Requiere B1.

**Indicadores nuevos**: tasa de recuperación (`kg valorizados / kg ingresados`), evolución de valorización por fracción, destino de materiales. Es el **objetivo 1 de la ENGIRSU** (reducción y valorización) hecho dashboard.

**Valor**: transforma el relato del producto de "sistema de balanza" a "plataforma de economía circular del predio". Es la funcionalidad que más eleva el techo comercial del producto — y la más citada en la oferta internacional de software del sector (los líderes del mercado facturan sobre trazabilidad de valorización, no sobre pesaje).

#### B3. Padrón de recuperadores urbanos / cooperativas — esfuerzo M

**Qué es**: ABM de recuperadores/cooperativas que operan en el predio, asociable a los movimientos de material de B2.

**Fundamento**: el portal nacional tiene sección específica de recuperadores urbanos; su formalización es política de Estado y componente social de todo plan GIRSU (y de los requisitos PMGIRSU).

**Modelo**: tabla `recuperadores` (organización, nombre, tipo persona/cooperativa, CUIT opcional, contacto, activo) + FK opcional en `movimientos_material`. Un ABM estándar según [`07-abm-guide.md`](07-abm-guide.md).

**Valor**: le da al municipio el dato social que los programas nacionales piden (cuántos recuperadores, cuánto material formalizado). Bajo esfuerzo si B2 existe.

#### B4. Liquidación por disposición — canon por tonelada — esfuerzo L

**Qué es**: tarifario por tipo de servicio/titular y liquidación mensual automática para generadores privados que pagan por disponer.

**Modelo**: tabla `tarifas` (organización, tipo_servicio_id opcional, vigencia desde/hasta, $/tonelada) + generación de liquidación mensual por titular (agrupando `pesajes` por `vehiculos.titular`). La liquidación reutiliza el motor de reportes (PDF + snapshot + email).

**Valor**: funcionalidad estándar del software de weighbridge internacional y **fuente de recaudación directa para el municipio** — el argumento con el que un sistema pasa de "gasto" a "se paga solo". Sinergia natural con A2 (certificado + factura en el mismo envío).

**Riesgo**: roza el dominio de facturación fiscal (AFIP/ARCA). Mantenerlo como *liquidación* (documento interno de cobro) y no como factura electrónica evita esa complejidad en esta etapa.

---

### C. Apuestas de plataforma (Etapa 3+)

#### C1. Reporte normativo "en un click" hacia provincia/Nación

Cuando la ENGIRSU 2025 formalice el flujo de datos municipio → provincia → Nación, el sistema genera el paquete completo (indicadores + export oficial + mapa) listo para presentar ante el ICAA o el sistema nacional que se defina. Los destinos concretos ya identificados: **SInIA** (sección "Sustancias y Residuos" + datos abiertos, §3.8), el **Sistema Nacional de Información sobre Cambio Climático** (Ley 27.520, §3.7) y el relevamiento de línea de base ENGIRSU. **Posicionarse antes de que sea obligatorio convierte al sistema en el estándar de facto regional** — con 15,2% de disposición adecuada en Cuyo–Mesopotamia, hay decenas de municipios que van a necesitar esto y el multi-tenant ya está construido. Depende de A1+A5; el trigger es la publicación de la ENGIRSU 2025 (monitorear).

#### C2. Trazabilidad entre predios (centros de transferencia)

Ya existe el tipo de servicio "Centros de Transferencia". Extender la cadena origen → transferencia → disposición final entre organizaciones del mismo tenant implementa el principio de trazabilidad de punta a punta del 779/22. Relevante recién cuando haya un tenant con esa topología real — no anticipar.

#### C3. Open data / API pública

Portal de transparencia del municipio con indicadores públicos (toneladas, per cápita, mapa). Hoy explícitamente fuera de alcance (Etapa 1 excluye API pública); es el paso natural cuando el municipio quiera comunicar resultados. Bajo esfuerzo técnico llegado el momento (los endpoints de datos del dashboard ya existen). El formato de referencia para el dataset público es el del portal de datos abiertos del SInIA — publicar en formato compatible hace que el dato municipal sea directamente integrable al sistema nacional.

#### C4. Residuos peligrosos (Ley 24.051) — **no recomendado**

Registros nacionales, manifiestos, certificación de operadores: es otro producto y otro mercado, con barrera regulatoria alta. Solo reconsiderar si aparece un cliente operador de peligrosos con contrato que financie el desarrollo. Documentado aquí para que la decisión de *no* hacerlo quede explícita.

---

## 5. Matriz de priorización

| # | Funcionalidad | Esfuerzo | Valor cliente | Valor comercial | Cambio de modelo | Dependencias |
|---|--------------|----------|---------------|-----------------|------------------|--------------|
| A1 | Indicadores GIRSU + benchmark | S | Alto | Alto | No | — |
| A3 | Vida útil del relleno | S/M | **Muy alto** | Alto | Config only | — |
| A5 | Export formato oficial | S | Medio | **Muy alto** (timing ENGIRSU 2025) | No | A1 |
| B1 | Fracción por pesaje | M | Medio | **Muy alto** (habilita todo el eje B) | Sí (aditivo, nullable) | — |
| A2 | Certificado de disposición | M | Alto | Alto | No | — |
| A4 | Emisiones GEI | M | Medio | Medio (sube con Ley 27.520 activa) | No | Mejor con B1 |
| A6 | Capa riesgo climático (SIMARCC) | S/M | Medio | Medio | No | Verificación técnica de capas SIMARCC |
| B4 | Liquidación por tonelada | L | **Muy alto** (recaudación) | Alto | Sí | — |
| B2 | Valorización / egresos de material | L | Alto | **Muy alto** | Sí | B1 |
| B3 | Recuperadores urbanos | M | Medio | Medio | Sí | B2 |
| C1 | Reporte normativo un-click | M | Alto | Muy alto | No | A1+A5 + publicación ENGIRSU 2025 |

### Secuencia propuesta (post go-live 14/07/2026)

```
Sprint post-go-live 1  →  A1 + A3          (indicadores GIRSU + vida útil: impacto visible inmediato)
Sprint post-go-live 2  →  A5 + B1          (export oficial + fracciones: siembra del eje circular)
Sprint post-go-live 3  →  A2 ó B4          (según prioridad comercial: trazabilidad vs recaudación)
Oportunista            →  A6               (si la verificación técnica de capas SIMARCC da positiva,
                                            entra en cualquier sprint como tarea chica de mapa)
Etapa 3                →  B2 → B3 → A4     (módulo valorización completo + GEI con composición real)
Trigger externo        →  C1               (al publicarse la ENGIRSU 2025 — monitorear)
```

**Criterio de la secuencia**: primero lo que se ve en el reporte que el admin ya entrega todos los meses (A1/A3/A5 no cambian el modelo y elevan el producto ante el municipio), después la única migración estructural (B1) que desbloquea el resto, y las apuestas grandes (B2/B4) recién con el sistema estabilizado en producción.

---

## 6. Riesgos y consideraciones

| Riesgo | Mitigación |
|--------|-----------|
| La ENGIRSU 2025 define un formato de reporte distinto al anticipado | A5 se diseña como plantilla configurable; el costo de ajuste es bajo. No sobre-invertir en el formato antes de la publicación |
| Benchmarks nacionales desactualizados (1,15 kg/hab/día proviene de línea de base previa) | Valores configurables por organización, nunca hardcodeados (regla del proyecto) |
| Precisión pobre del cálculo GEI sin composición | Publicar A4 solo tras B1, o etiquetar explícitamente como "estimación tier 1" |
| B1 agrega un paso al flujo del operador (< 10 seg es criterio de aceptación) | Sugerencia automática por servicio con default; medir el tiempo de flujo en piloto antes de generalizar |
| B4 roza dominio fiscal | Alcance = liquidación interna, no factura electrónica |
| Cambios de estructura ministerial nacional (el área ambiente cambió de jerarquía) | Las oportunidades se anclan en **leyes y decretos** (25.916, 779/22), no en programas de gestión que pueden discontinuarse |

---

## 7. Fuentes

**Portal de Ambiente (Jefatura de Gabinete — Turismo y Ambiente):**

- [Ambiente — portal principal](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente)
- [Evaluación y Control Ambiental](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/evaluacion-y-control-ambiental)
- [Desarrollo Sostenible y Gestión Climática](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/desarrollo-sostenible-y-gestion-climatica)
- [Residuos Sólidos Urbanos — marco normativo y Decreto 779/22](https://www.argentina.gob.ar/interior/ambiente/control/rsu)
- [Estrategia Nacional para la GIRSU (ENGIRSU)](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/evaluacion-y-control-ambiental/gestion-de-residuos-solidos-3)
- [El sector de residuos sólidos en la Argentina](https://www.argentina.gob.ar/ambiente/preservacion-control/gestionresiduos/argentina)
- [Etapas de la gestión integral de RSU](https://www.argentina.gob.ar/jefatura/turismo-y-ambiente/ambiente/evaluacion-y-control-ambiental/gestion-de-residuos-solidos-0)
- [Gabinete Nacional de la Ley 27.520](https://www.argentina.gob.ar/ambiente/cambio-climatico/gabinete-nacional)
- [Ficha RSU — argentina.gob.ar (PDF)](https://www.argentina.gob.ar/sites/default/files/residuos.pdf)

**Sistemas de información nacionales:**

- [SInIA / CIAM — Sistema Integrado de Información Ambiental](https://ciam.ambiente.gob.ar/)
- [SIMARCC — Sistema de Mapas de Riesgo del Cambio Climático](https://simarcc.ambiente.gob.ar/)

**Normativa e informes:**

- [Ley 27.520 — presupuestos mínimos de adaptación y mitigación al cambio climático (texto)](https://www.argentina.gob.ar/normativa/nacional/ley-27520-333515/texto)
- [Informe AGN 2023 — auditoría de gestión ambiental (PMGIRSU, ODS 11.6.1/12.5.1)](https://www.agn.gob.ar/sites/default/files/informes/2023-136-Informe.pdf)
- Ley 25.916 (presupuestos mínimos RSU) · Decreto 779/22 (reglamentación) · Ley 24.051 (residuos peligrosos) · Resolución SAyDS 21/2009 (Observatorio Nacional GIRSU)

---

*Documento creado: 07/07/2026 · Análisis de producto — no es alcance comprometido.*
