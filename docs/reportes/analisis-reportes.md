# Análisis de Reportes — Infinito Reciclaje
## Datos necesarios, métricas de mercado y mejoras al Dashboard

**Fuentes analizadas:** `REPORTE MARZO_ INFINITO RECICLAJE.xlsx` · `PPT PARA GOBIERNO_ INFINITO RECICLAJE.pdf` · `docs/03-data-model.md` · `DashboardService.php`
**Fecha:** 27/05/2026

---

## 0. Análisis del Excel actual — `REPORTE MARZO_ INFINITO RECICLAJE.xlsx`

### 0.1 Estructura del archivo

El Excel tiene 3 hojas con roles bien diferenciados:

| Hoja | Filas | Cols | Rol |
|------|-------|------|-----|
| `Dashboard- Infinito` | 150 | 17 | Resumen ejecutivo con tablas pivot y series diarias |
| `Reporte Infinito` | 3.426 | 17 | Datos raw de pesajes del mes |
| `no tocar` | ~60 | 6 | Tabla maestra de vehículos (N° interno → tara, zona habitual) |

---

### 0.2 Hoja `Reporte Infinito` — datos raw de pesajes

**Columnas registradas por el operador:**

| Col | Campo | Tipo | Notas |
|-----|-------|------|-------|
| B | N° correlativo | Entero | Numeración manual, puede repetirse entre meses |
| C | N° Interno | Entero | Identificador del vehículo (ej: 7101, 7462) |
| D | Tipo | Texto | Lookup desde hoja "no tocar" — puede quedar `#N/A` si el N° no existe en la tabla |
| E | Tara | Entero (kg) | Lookup desde hoja "no tocar" — peso vacío del vehículo |
| F | Pesaje bruto | Entero (kg) | Lo que marca la balanza |
| G | Kilos netos | Entero (kg) | `Pesaje - Tara`, calculado en celda |
| H | Fecha | Fecha | Día del pesaje |
| I | Hora | Hora | Momento del pesaje — **un registro tiene fecha 1903 (bug de Excel)** |
| J | Zona | Texto libre | Escrito a mano por el operador — sin normalización |

**No existe en el raw:**
- Hora de salida / egreso
- Turno (diurno / nocturno)
- Operador que registró
- Estado del pesaje (abierto / cerrado)
- Observaciones o alertas

---

### 0.3 Hoja `no tocar` — tabla maestra de vehículos

Tabla de referencia con ~60 vehículos activos:

| Campo | Ejemplo |
|-------|---------|
| N° Interno | 7030, 7101, 7462… |
| Tara (kg) | 7240, 8540, 11460… |
| Barrio/Zona habitual | "LAGUNA SOTO SUR", "Barrio Monta\xd1a"… |

El `VLOOKUP` del tipo y la tara en "Reporte Infinito" se hace contra esta tabla. Si el N° interno no figura → `#N/A` en tipo, tara copiada a mano o cero.

---

### 0.4 Hoja `Dashboard- Infinito` — resumen ejecutivo

Contiene **5 bloques concatenados verticalmente** en la misma hoja:

#### Bloque 1 — Resumen general (marzo 2026)

| Métrica | Valor real |
|---------|-----------|
| Total registros con peso | 3.119 |
| Total kg netos | 14.295.149 |
| Total toneladas | 14.295 t |
| Días con operación | 28 de 31 |
| Promedio diario | 510.541 kg / 510,5 t |
| Promedio por viaje | 4.583 kg |

#### Bloque 2 — Desglose por tipo de vehículo

| Tipo | Viajes | KG netos | % | KG/viaje |
|------|--------|----------|---|----------|
| Compactador | 1.215 | 6.805.752 | 47,6% | 5.601 |
| Volcador | 1.471 | 6.434.157 | 45,0% | 4.374 |
| Volquete | 432 | 1.051.400 | 7,4% | 2.434 |
| Particular | 1 | 0 | 0% | 0 |
| **TOTAL** | **3.119** | **14.291.309** | | **4.582** |

> **Discrepancia**: la fila TOTAL de kg (14.291.309) difiere del resumen general (14.295.149). Diferencia de 3.840 kg — hay 4 viajes del Particular con peso 0 que no suman pero sí cuentan en el total de registros.

#### Bloque 3 — Desglose diario por tipo de vehículo

Serie por fecha con columnas: `Fecha | Total Viajes | Total KG | Compactador Viajes | Compactador KG | Volcador Viajes | Volcador KG | Volquete Viajes | Volquete KG`

Estadísticas del período (calculadas sobre los 28 días operativos):

| Estadístico | Viajes/día | KG/día |
|-------------|-----------|--------|
| Promedio | 111,5 | 510.541 |
| Máximo | 146 (09/03) | 775.020 (30/03) |
| Mínimo | 8 (22/03) | 27.600 (22/03) |

> **22/03**: solo 8 viajes y 27.600 kg — el documento PDF lo menciona como día con corte de luz.
> **29/03**: 79 viajes pero 734.300 kg — el volcador ese día registró 696.380 kg (96 viajes), un outlier importante.

#### Bloque 4 — Tabla cruzada zona × tipo de vehículo

**Período parcial: 20/03 al 31/03** (12 días de los 28 operativos del mes).

Zonas consolidadas y sus totales en ese período:

| Zona | Viajes | KG | % |
|------|--------|----|---|
| SERVICIO ESPECIAL | 237 | 1.302.980 | 19,6% |
| ZONA SUR 1 | 207 | 1.055.916 | 15,9% |
| ZONA NORTE | 201 | 939.770 | 14,1% |
| ZONA SUR 2 | 176 | 773.989 | 11,6% |
| ZONA 16 NOCTURNA | 23 | 151.940 | 2,3% |
| ZONA 6 DIURNA | 25 | 189.500 | 2,9% |
| *(18 zonas numeradas DIURNA/NOCTURNA)* | … | … | … |
| CENTRO DE TRANSFERENCIA | 1 | 10.180 | 0,2% |

**Zonas numeradas con split turno:** ZONA 1 a ZONA 18 + ZONA 16 NOCTURNA.
El split DIURNA/NOCTURNA **no viene del campo "turno"** del raw (no existe) — se deriva de la hora del pesaje.

#### Bloque 5 — Serie diaria por zona (mismo período 20/03–31/03)

Tabla de `zona × fecha` con kg netos por día, con fila TOTALES al pie.

---

### 0.5 Problemas críticos del Excel como fuente de datos

#### Problema 1 — Zonas son texto libre (764 variantes únicas)

El campo zona se escribe a mano. Para 3.416 pesajes hay **764 valores únicos distintos** para el mismo mes. Ejemplos de variantes para la misma zona real:

| Zona canónica | Variantes encontradas en el raw |
|--------------|--------------------------------|
| Barrio Montaña | `MONTA\xd1A`, `Monta\xd1a`, `monmta\xd1a`, `MONMRTA\xd1A`, `b monta\xd1a`, `barrio monta\xd1a`, `MONTA\xd1A BARIDO`, `B MONTA\xd1A`, `Zona monta\xd1a`… |
| Zona 1 | `ZONA 1`, `zona 1`, `ZONA  1`, `zona 1-Molina Punta`, `ZONA 1 DIURNA`, `ZONA 1 NOCTURNA`, `zona1`… |
| Servicio Especial | `SERVICIO ESPECIAL`, `Servicio Especial`, `servicio especial`, `SERV ESPECIAL`, `servicio esepecial`, `SERVICIO ESEPECIAL`, `servicioespecial`… |
| Zona Norte | `ZONA NORTE`, `zona norte`, `ZONA  NORTE`, `S.NORTE`, `NORTE`, `Zona Norte`, `ZONANO`… |

**Impacto en el sistema:** el campo `zona` del formulario de pesaje debe ser un **select / autocomplete contra la tabla `zonas`**, nunca texto libre. Sin esto, los reportes por zona son imposibles de automatizar.

#### Problema 2 — El turno no es un campo, se infiere de la hora

La clasificación DIURNA/NOCTURNA del dashboard **no existe como dato** en el raw. Se construye a partir de la hora del pesaje. La regla exacta de corte no está documentada en el Excel (probablemente antes/después de las 14:00 o 18:00).

**Acción necesaria:** definir con el cliente la hora de corte entre turno diurno y nocturno. El campo `turno` en el modelo de datos debe calcularse automáticamente al registrar el pesaje, no pedírselo al operador.

#### Problema 3 — Sin hora de salida

El raw solo tiene hora de entrada. No existe campo de egreso. El tiempo en predio y el estado "En predio" / "Cerrado" son conceptos que el sistema agrega — no existen en el Excel.

#### Problema 4 — Sin operador

No hay dato de quién registró cada pesaje. El sistema agrega trazabilidad de operador desde cero.

#### Problema 5 — Bug de hora en Excel

Un registro tiene `1903-06-01 00:00:00` como hora — error de Excel al interpretar un valor numérico como fecha. El sistema debe validar que la hora sea del día actual al guardar.

#### Problema 6 — Tabla maestra de vehículos fuera de sync

Si se agrega un vehículo nuevo sin actualizar la hoja "no tocar", el lookup queda en `#N/A`. En el sistema esto se resuelve con la tabla `vehiculos` que el admin mantiene.

#### Problema 7 — Tabla zona × tipo solo cubre la segunda mitad del mes

El bloque 4 del Dashboard cubre solo del 20/03 al 31/03. No queda claro si la primera mitad del mes no tiene datos o si es un corte intencional del reporte.

---

### 0.6 Lo que el sistema aporta que el Excel no tiene

| Capacidad | Excel | Sistema nuevo |
|-----------|-------|---------------|
| Hora de egreso / tiempo en predio | ✗ | ✓ |
| Turno calculado automáticamente | ✗ (manual) | ✓ |
| Operador que registró | ✗ | ✓ |
| Zonas normalizadas (sin variantes de texto) | ✗ | ✓ |
| Alertas automáticas (peso anómalo, gap operativo) | ✗ | ✓ |
| Reportes sin trabajo manual | ✗ | ✓ |
| Historial de cambios / auditoría | ✗ | ✓ |
| Acceso multi-usuario en tiempo real | ✗ | ✓ |

---

## 1. Reporte oficial al municipio — datos que el cliente produce hoy

El informe mensual tiene 6 secciones. Esta tabla documenta qué dato se muestra, cómo se calcula desde la DB y qué condición previa necesita.

### 1.1 Resumen general

| Métrica | Label en reporte | Cálculo SQL | Condición |
|---------|-----------------|-------------|-----------|
| Días con al menos un pesaje | Días operativos | `COUNT(DISTINCT CAST(created_at AS DATE))` | — |
| Total de viajes registrados | Total ingresos | `COUNT(*)` en `pesajes` | — |
| Suma pesos netos | KG ingresados | `SUM(peso_neto_kg) / 1000.0` (en toneladas) | — |
| Toneladas / días operativos | Promedio KG / día | `SUM / COUNT(dias)` | Días > 0 |
| Promedio de peso neto por viaje | Promedio KG / viaje | `AVG(peso_neto_kg)` | Viajes > 0 |

Todos los filtros son opcionales: período `desde/hasta`, zona, tipo de servicio, tipo de vehículo.

---

### 1.2 Evolución diaria de toneladas

Serie diaria de toneladas netas con estadísticas del período:

| Dato | Cálculo |
|------|---------|
| Toneladas por día | `GROUP BY CAST(created_at AS DATE)` → `SUM(peso_neto_kg)/1000` |
| Promedio del período | Media de la serie diaria |
| Máximo diario | `MAX` de la serie |
| Mínimo diario | `MIN` de la serie (excluir días con 0 por corte/feriado o incluirlos — decisión de negocio) |

El gráfico del PDF es de barras verticales. El promedio se muestra como texto sobre el gráfico, no como línea.

**Nota de diseño:** el mínimo de 27,6 t del 22/03 corresponde a un día con corte de luz, no a una operación normal. Hay que analizar si incluir días con gaps en el mínimo o excluirlos.

---

### 1.3 Por tipo de vehículo

Tabla con una fila por tipo de vehículo + fila TOTAL.

| Columna | Cálculo |
|---------|---------|
| Tipo | `tipos_vehiculo.nombre` |
| Viajes | `COUNT(pesajes.id)` |
| KG netos | `SUM(peso_neto_kg) / 1000.0` |
| % del total | `SUM / total_periodo * 100` |
| KG / viaje | `AVG(peso_neto_kg)` o `SUM/COUNT` (equivalente) |

JOIN requerido: `pesajes → vehiculos → tipos_vehiculo`.

Datos de Marzo 2026 para referencia:
| Tipo | Viajes | KG netos | % | KG/viaje |
|------|--------|----------|---|----------|
| Compactador | 1.215 | 6.806 t | 47,6% | 5.601 |
| Volcador | 1.470 | 6.427 t | 44,9% | 4.372 |
| Volquete | 432 | 1.051 t | 7,4% | 2.434 |
| Particulares | 4 | 8 t | 0,1% | 2.125 |

---

### 1.4 KG netos por zona (tabla cruzada)

La tabla más compleja. Cada fila es una combinación **zona + turno**. Las columnas son los tipos de vehículo con viajes y kg separados.

**Label de fila:** `zonas.nombre + ' ' + UPPER(pesajes.turno)` cuando hay turno; solo `zonas.nombre` cuando `pesajes.turno IS NULL`.

**Columnas por tipo de vehículo (una por cada tipo activo):**
- Viajes del tipo en esa zona
- KG netos del tipo en esa zona

**Columnas de total:**
- Total viajes
- Total KG
- % del total general del período

**Ordenamiento:** descendente por total KG.

**Color-coding de filas** (por rango de total KG):
| Rango | Color en PDF |
|-------|-------------|
| > 500 t | Azul oscuro (fila destacada) |
| 150–500 t | Azul medio |
| 80–150 t | Azul claro |
| 50–80 t | Gris claro |
| 30–50 t | Verde claro |
| < 30 t | Sin color |

**Query base:**
```sql
SELECT
    z.nombre + COALESCE(' ' + UPPER(p.turno), '') AS zona_label,
    z.id AS zona_id,
    p.turno,
    SUM(CASE WHEN tv.nombre = 'Compactador' THEN 1 ELSE 0 END)          AS comp_viajes,
    SUM(CASE WHEN tv.nombre = 'Compactador' THEN p.peso_neto_kg ELSE 0 END) AS comp_kg,
    SUM(CASE WHEN tv.nombre = 'Volcador' THEN 1 ELSE 0 END)              AS volc_viajes,
    SUM(CASE WHEN tv.nombre = 'Volcador' THEN p.peso_neto_kg ELSE 0 END)    AS volc_kg,
    SUM(CASE WHEN tv.nombre = 'Volquete' THEN 1 ELSE 0 END)              AS volq_viajes,
    SUM(CASE WHEN tv.nombre = 'Volquete' THEN p.peso_neto_kg ELSE 0 END)    AS volq_kg,
    COUNT(p.id)                                                           AS total_viajes,
    SUM(p.peso_neto_kg)                                                   AS total_kg
FROM pesajes p
JOIN zonas z ON z.id = p.zona_id
JOIN vehiculos v ON v.id = p.vehiculo_id
JOIN tipos_vehiculo tv ON tv.id = v.tipo_vehiculo_id
WHERE p.created_at BETWEEN :desde AND :hasta
GROUP BY z.nombre, z.id, p.turno
ORDER BY total_kg DESC
```

**Problema de escalabilidad:** los tipos de vehículo son dinámicos (el admin puede crear nuevos). El `CASE WHEN` hardcodeado no escala. El `ReporteService` debe construir el pivot en PHP agrupando primero por `(zona, turno)` y luego por tipo, en lugar de hacer el pivot en SQL.

---

### 1.5 Densidad de generación (kg / hectárea)

Indicador: `SUM(peso_neto_kg) / zonas.hectareas` por combinación zona+turno.

**Requisito de datos:** `zonas.hectareas` debe estar cargado. Si es NULL → la zona no aparece en este ranking o se muestra "S/D". Si es 0 → división por cero, usar `NULLIF(z.hectareas, 0)`.

| Métrica | Cálculo |
|---------|---------|
| kg / ha | `SUM(p.peso_neto_kg) / NULLIF(z.hectareas, 0)` |
| Ranking | Top 20 por kg/ha descendente |

**Color-coding del gráfico:**
| Rango | Color |
|-------|-------|
| ≥ 1.000 kg/ha | Rojo |
| 700–999 kg/ha | Naranja |
| 500–699 kg/ha | Naranja claro |
| 300–499 kg/ha | Amarillo |
| 200–299 kg/ha | Verde claro |
| < 200 kg/ha | Gris |

**Línea de referencia promedio:** `AVG(kg_por_ha)` del conjunto total como línea punteada sobre el gráfico.

**Conclusiones automáticas detectables:**
1. *Zonas críticas* → `kg/ha >= umbral_alto` (ej: ≥ 800): necesitan mayor frecuencia
2. *Zonas grandes con bajo volumen* → `hectareas > umbral_grande` (ej: > 400 ha) AND `kg/ha < umbral_bajo` (ej: < 200): optimización posible
3. *Zonas con alta eficiencia* → `kg/ha` entre los top 5 sin ser las de mayor hectáreas: buen rendimiento operativo

---

### 1.6 Continuidad operativa (sección manual hoy)

Esta sección **no tiene fuente de datos automática** en el sistema actual. Hoy se construye con planillas del operador e inspección visual de gaps en timestamps.

**Datos que muestra el PDF de Marzo:**
- Tabla de cortes: fecha · hora desde · hora hasta · duración · observación
- Resumen: cantidad de días con corte, corte más largo
- Impacto estimado: toneladas y viajes no registrados (estimado por extrapolación del promedio)

**Opciones de implementación:**

| Opción | Costo | Precisión | Automática |
|--------|-------|-----------|------------|
| A — Gaps detectados desde timestamps | Bajo | Media — detecta ausencia, no causa | Sí |
| B — Alarmas existentes (`gap_pesajes`) | Cero | Media — ya implementado en Sprint 6 | Sí |
| C — Tabla `incidentes_operativos` (nueva) | Medio | Alta — operador registra causa | No |

**Recomendación:** usar la opción B para el reporte automático (datos ya en `alarmas` cuando `tipo = 'gap_pesajes'`). La tabla en el PDF se puede generar desde `alarmas` con fecha, hora inicio/fin del gap y duración calculada. La causa ("corte de luz") se podría agregar como campo `comentario_resolucion` en la alarma cuando el admin la resuelve.

---

## 2. Métricas adicionales de mercado

Más allá del reporte actual, estos son los indicadores estándar en sistemas de gestión de residuos municipales y los que los administradores querrían ver.

### 2.1 Eficiencia operativa de flota

| Métrica | Descripción | Cálculo | Dónde mostrar |
|---------|-------------|---------|---------------|
| **Tasa de llenado** | Qué % de la capacidad máxima se está usando en promedio | `AVG(peso_neto_kg) / AVG(vehiculos.capacidad_kg) * 100` (requiere `capacidad_kg` cargado) | Reporte, Dashboard |
| **KG / viaje por zona** | Cuánto carga cada camión en promedio según la zona de origen | `AVG(peso_neto_kg)` GROUP BY zona | Reporte |
| **Viajes / día operativo** | Cadencia promedio de la operación | `COUNT(pesajes) / COUNT(dias_operativos)` | Dashboard, Reporte |
| **Dispersión de peso** | Variabilidad de los pesos: máx, mín, desvío estándar por tipo de vehículo | Estadísticas sobre `peso_neto_kg` | Reporte (análisis) |

### 2.2 Análisis temporal

| Métrica | Descripción | Cálculo | Valor |
|---------|-------------|---------|-------|
| **Distribución horaria** | En qué horas del día llegan más camiones | `DATEPART(HOUR, created_at)` GROUP BY hora | Detectar picos y optimizar personal |
| **Distribución por día de la semana** | Qué días hay más actividad | `DATEPART(WEEKDAY, created_at)` | Planificación semanal |
| **Tiempo promedio en predio** | Desde ingreso hasta `hora_salida` | `AVG(DATEDIFF(MINUTE, created_at, hora_salida))` solo para estado='Cerrado' | Eficiencia del proceso de pesaje |
| **Tendencia mensual acumulada** | Progresión de toneladas día a día dentro del mes vs mes anterior | Serie acumulada | Dashboard (curva acumulada) |

### 2.3 Indicadores geográficos y demográficos

| Métrica | Descripción | Cálculo | Condición |
|---------|-------------|---------|-----------|
| **Per cápita** | KG recolectados por habitante | `SUM(peso_neto_kg) / zonas.habitantes` | `zonas.habitantes` cargado |
| **Densidad por barrio** | KG / ha como proxy de densidad urbana | `SUM(peso_neto_kg) / NULLIF(zonas.hectareas, 0)` | `zonas.hectareas` cargado |
| **Ranking de zonas por eficiencia** | Zonas que generan más con menos viajes | `SUM(kg) / COUNT(viajes)` por zona | — |
| **Cobertura de turnos** | Qué % de zonas operaron en turno nocturno vs diurno | `COUNT` por turno | — |

### 2.4 Calidad del servicio y auditoría

| Métrica | Descripción | Fuente | Valor |
|---------|-------------|--------|-------|
| **Tasa de alertas de peso** | Qué % de pesajes tuvieron `alerta_peso = 1` | `COUNT WHERE alerta_peso=1 / COUNT * 100` | Detectar problemas con tara o sobrecarga |
| **Tasa de edición** | Qué % de pesajes fueron modificados post-registro | `COUNT WHERE editado=1 / COUNT * 100` | Indicador de calidad del operador |
| **Pesajes sin egreso** | Camiones que ingresaron pero nunca marcaron salida | `COUNT WHERE estado='En predio' AND created_at < hoy - N horas` | Datos incompletos |
| **Actividad por operador** | Pesajes y toneladas registrados por cada operador | `GROUP BY operador_id` | Control de turno y productividad |

### 2.5 Comparativas temporales

| Métrica | Descripción |
|---------|-------------|
| **MoM (mes a mes)** | Variación % de toneladas vs el mes anterior |
| **YoY (año a año)** | Variación % vs el mismo mes del año anterior |
| **Tendencia de 3 meses** | Media móvil de 3 meses para suavizar variaciones |
| **Mejor / peor día del período** | Fecha con mayor y menor actividad |
| **Días sin operación** | Días del período sin ningún pesaje (feriados, cortes, etc.) |

### 2.6 Métricas para contratos municipales (valor diferenciador)

Estos indicadores son específicamente útiles para presentar al municipio como evidencia de cumplimiento del contrato:

| Métrica | Descripción | Relevancia |
|---------|-------------|-----------|
| **Cobertura de zonas** | % de zonas activas que registraron actividad en el período | Verificar que todas las rutas se cubrieron |
| **Frecuencia de recolección por zona** | Días promedio entre visitas a cada zona | Contrastar con frecuencia contractual |
| **Consistencia operativa** | Días operativos / días laborables del mes | Si el contrato exige X días/semana |
| **Capacidad instalada vs utilizada** | Toneladas registradas vs estimación de generación de la ciudad | Necesita dato externo del municipio |
| **Reducción de residuos a disposición final** | Diferencia entre generación estimada y lo que llega al predio | Necesita dato externo |

---

## 3. Análisis del Dashboard actual y mejoras propuestas

### 3.1 Estado actual del Dashboard

El `DashboardService` calcula:

**KPIs del día** (4 cards):
- `total` — pesajes del día
- `toneladas` — suma neta del día
- `promedio` — toneladas promedio por pesaje del día
- `horas_op` — tiempo desde el primer pesaje del día

**KPIs del mes** (3 cards):
- `total` — pesajes del mes
- `toneladas` — toneladas del mes
- `dias_op` — días con actividad del mes

**Evolución diaria:** gráfico de barras con selector 7d / 15d / 90d

**Desgloses del día:**
- Por zona: pesajes, toneladas, %
- Por tipo de vehículo: pesajes, toneladas, %

**Camiones en predio:** tabla live de los en estado 'En predio'

---

### 3.2 Problemas del Dashboard actual

| Componente | Problema | Impacto |
|-----------|---------|---------|
| **KPI "Horas operativas"** | Mide desde el *primer pesaje* hasta ahora — no refleja nada accionable. No dice si la operación fue intensa o floja. Crece aunque no haya pesajes. | Confunde más de lo que informa |
| **Evolución diaria — sin línea de promedio** | El gráfico de barras no tiene referencia visual. El admin no puede ver de un vistazo si hoy está por encima o debajo de lo normal. | Requiere comparación mental |
| **Desgloses por zona y vehículo — solo del día** | La tabla de zona muestra el día actual. Si el admin entra a las 8:00 AM, está vacía. No tiene contexto histórico. | Empty state frecuente, poco valor a primera hora |
| **Desglose zona — sin kg/ha ni turno** | La zona "ZONA 1" no dice si fue turno diurno o nocturno. No hay densidad kg/ha, que es el indicador clave del reporte. | Pérdida de información vs el reporte |
| **Desglose vehículo — sin kg/viaje** | La métrica más importante para eficiencia de flota (kg/viaje) no aparece en el dashboard, solo en el reporte. | Indicador clave ausente |
| **KPI mes — sin comparación de toneladas** | `delta` solo compara pesajes (conteo), no toneladas. Subir en viajes pero bajar en toneladas pasaría desapercibido. | Métrica incompleta |
| **Sin indicador de actividad reciente** | No hay una señal visual de si llegaron camiones en las últimas X horas. El admin no sabe si la operación está activa o detenida. | Pérdida de visibilidad operativa |

---

### 3.3 Mejoras propuestas para el Dashboard

#### Cambio 1 — KPI "Horas operativas" → "KG / viaje hoy"

**Actual:** tiempo desde el primer pesaje del día (crece siempre, no aporta).
**Propuesto:** promedio de kg netos por viaje del día, con delta vs promedio histórico del mismo tipo de día.

```php
// Reemplazar en DashboardService::kpisDelDia()
// Antes:
$horasOp = $primerPesaje ? round($primerPesaje->created_at->diffInMinutes(now()) / 60, 1) : 0;

// Después:
$kgPorViaje = $total > 0 ? round($pesajes->avg('peso_neto_kg')) : 0;
// + delta vs promedio de los últimos 30 días
```

**Impacto:** la métrica más usada en el reporte mensual aparece en el dashboard operativo.

---

#### Cambio 2 — Evolución diaria: agregar línea de promedio

Agregar al dataset una serie secundaria con el promedio del período como línea horizontal de referencia (línea punteada). ApexCharts soporta `annotations.yaxis` para esto sin cambiar la estructura del gráfico.

```js
// En el baseOptions() del componente evolucion.blade.php
annotations: {
    yaxis: [{
        y: promedioDelPeriodo,
        borderColor: '#a1a1aa',
        strokeDashArray: 4,
        label: {
            text: 'Promedio ' + promedioDelPeriodo + ' t',
            style: { color: '#a1a1aa', fontSize: '11px' }
        }
    }]
}
```

El `promedio` ya se calcula en el `DashboardService` — solo hay que pasarlo al componente.

---

#### Cambio 3 — Desgloses: ampliar de "hoy" a "período seleccionable"

**Opción A (simple):** agregar un selector "Hoy / Esta semana / Este mes" en los headers de las cards de desglose. Un cambio de Alpine que llama a un endpoint `GET /admin/dashboard/desglose?periodo=7d`.

**Opción B (integrada):** ligar el período de los desgloses al mismo selector del gráfico de evolución (7d / 15d / 3m). El admin mueve el selector y actualizan tanto el gráfico como las tablas.

La opción B es más coherente pero requiere refactorizar el gráfico para que el selector sea externo al componente. La opción A es un cambio de 1–2 horas.

---

#### Cambio 4 — Desglose zona: agregar columna "KG / viaje" y split de turno

La tabla actual muestra `nombre | pesajes | toneladas | %`. Propuesta:

| Zona | Turno | Viajes | Toneladas | KG/viaje | % |
|------|-------|--------|-----------|----------|---|

El split de turno requiere cambiar el `GROUP BY` de `zona_id` a `(zona_id, turno)` en `desgloseByZona()`. El `nombre` se construye igual que en el reporte.

---

#### Cambio 5 — Desglose vehículo: agregar columna "KG / viaje"

La tabla actual muestra `tipo | pesajes | toneladas | %`. Agregar:

| Tipo | Viajes | Toneladas | KG/viaje | % |
|------|--------|-----------|----------|---|

`KG/viaje = SUM(peso_neto_kg) / COUNT(*)` por tipo. Un cambio de 5 líneas en el servicio y 1 columna en la tabla Blade.

---

#### Cambio 6 — Nuevo KPI: "Último pesaje hace X minutos"

Un indicador de actividad reciente que reemplaza (o acompaña) a "Horas operativas":

```php
$ultimoPesaje = Pesaje::whereDate('created_at', today())->latest()->first();
$minutos = $ultimoPesaje ? $ultimoPesaje->created_at->diffInMinutes(now()) : null;
```

Comportamiento visual:
- `< 15 min` → verde ("Hace 8 min")
- `15–60 min` → amarillo ("Hace 42 min")
- `> 60 min` → rojo ("Hace 1h 20 min")
- Sin pesajes hoy → neutro ("Sin actividad hoy")

Este indicador tiene valor operativo real: el admin ve de un vistazo si la balanza está operando.

---

#### Cambio 7 — KPIs del mes: agregar delta de toneladas

Actualmente `kpisMes['delta']` compara conteo de viajes. Agregar `delta_toneladas`:

```php
// En DashboardService::kpisDelMes()
$toneladasMesAnterior = Pesaje::whereDate('created_at', '>=', $inicioMesAnterior)
    ->whereDate('created_at', '<=', $finMesAnterior)
    ->sum('peso_neto_kg') / 1000;

$deltaToneladas = $toneladasMesAnterior > 0
    ? round((($toneladas - $toneladasMesAnterior) / $toneladasMesAnterior) * 100, 1)
    : null;
```

Mostrarlo bajo el KPI de toneladas del mes como ya se hace para pesajes.

---

### 3.4 Resumen de cambios priorizados

| Prioridad | Cambio | Esfuerzo | Impacto |
|-----------|--------|----------|---------|
| 🔴 Alta | Reemplazar "Horas operativas" por "KG/viaje" | Bajo — 30 min | Métrica clave del reporte aparece en dashboard |
| 🔴 Alta | Agregar columna "KG/viaje" en desglose vehículo | Bajo — 20 min | Consistencia entre dashboard y reporte |
| 🔴 Alta | Split turno en desglose zona | Medio — 1 hora | Los datos de zona son zona+turno, no solo zona |
| 🟡 Media | Línea de promedio en gráfico evolución | Bajo — 30 min | Referencia visual inmediata |
| 🟡 Media | KPI "Último pesaje hace X min" | Bajo — 45 min | Visibilidad operativa en tiempo real |
| 🟡 Media | Delta de toneladas en KPIs del mes | Bajo — 30 min | Completar la comparativa mensual |
| 🟢 Baja | Selector de período en desgloses | Medio — 2 horas | Elimina el problema de "vacio a las 8 AM" |

---

## 4. Estructura del ReporteService (Sprint 5)

Basado en todo lo anterior, el `ReporteService` debe exponer:

```php
class ReporteService
{
    // Parámetros compartidos en todos los métodos
    // $filtros: ['desde' => Carbon, 'hasta' => Carbon, 'zona_id' => ?int,
    //            'tipo_servicio_id' => ?int, 'tipo_vehiculo_id' => ?int]

    public function kpisResumen(array $filtros): array
    // Retorna: total_viajes, toneladas, dias_operativos, promedio_dia, promedio_viaje
    // Extra: comparativa con período equivalente anterior (mismo N de días)

    public function evolucionDiaria(array $filtros): array
    // Retorna: [{fecha, toneladas, viajes}] por día
    // Extra: promedio, maximo, minimo calculados sobre la serie

    public function porTipoVehiculo(array $filtros): array
    // Retorna: [{tipo, viajes, toneladas, porcentaje, kg_por_viaje}]

    public function porZona(array $filtros): array
    // Retorna: [{zona_label, zona_id, turno, [tipo => {viajes, kg}], total_viajes, total_kg, porcentaje}]
    // Ordenado por total_kg DESC

    public function densidadGeneracion(array $filtros): array
    // Retorna: [{zona_label, total_kg, hectareas, kg_por_ha, categoria_color}]
    // Solo zonas con hectareas != NULL
    // categoria_color: 'critical' | 'high' | 'medium-high' | 'medium' | 'low-medium' | 'low'

    public function perCapita(array $filtros): array
    // Retorna: [{zona_label, total_kg, habitantes, kg_por_habitante}]
    // Solo zonas con habitantes != NULL

    public function actividadPorOperador(array $filtros): array
    // Retorna: [{operador, viajes, toneladas, porcentaje}]

    public function gapsOperativos(array $filtros): array
    // Retorna: alarmas de tipo 'gap_pesajes' en el período
    // [{fecha, hora_inicio, hora_fin, duracion_minutos, zona_afectada}]
}
```

---

## 5. Campos de zonas críticos para reportes

Para que el módulo de reportes genere los indicadores de densidad y per cápita, el ABM de Zonas debe tener estos campos obligatoriamente cargados antes del primer reporte:

| Campo | Tabla | Estado | Impacto si está vacío |
|-------|-------|--------|----------------------|
| `hectareas` | `zonas` | Nullable | Densidad kg/ha no calculable → sección "S/D" |
| `habitantes` | `zonas` | Nullable | Per cápita no calculable → sección "S/D" |
| `barrios` | `zonas` | Nullable | Solo informativo — sin impacto en cálculos |

**Recomendación:** agregar una validación visual en el ABM de Zonas que advierta cuántas zonas tienen hectáreas o habitantes sin cargar (similar al checklist de onboarding). El admin debe saberlo antes de intentar generar el reporte.

---

*Documento generado: 27/05/2026*
*Sprint de referencia: Sprint 5 — Reportes automáticos*
