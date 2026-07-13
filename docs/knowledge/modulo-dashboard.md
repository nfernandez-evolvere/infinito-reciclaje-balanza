# Módulo Dashboard
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador
**Cuándo usarlo:** Referencia de cómo leer e interpretar el panel de control de la operación

---

## Para qué sirve el Dashboard

El Dashboard es tu vista de control de la operación. Te muestra en una sola pantalla cómo viene el día, cómo viene el mes y —si querés— cualquier período que elijas. Es la primera pantalla que ves cuando ingresás como administrador.

No reemplaza revisar los pesajes uno por uno — para eso está el módulo de Pesajes. El Dashboard te da la lectura rápida: si todo está bien, en 30 segundos lo sabés.

---

## Cómo se actualiza

El Dashboard se refresca **automáticamente cada 10 minutos**. Además, arriba a la derecha hay un botón que muestra la hora del último refresco ("Últ: 14:35") y te permite **actualizar manualmente** cuando quieras. No hace falta recargar la página del navegador.

---

## Los tres períodos: Hoy, Este mes y Personalizado

El contenido del Dashboard está organizado en tres pestañas:

| Pestaña | Qué muestra |
|---------|-------------|
| **Hoy** | La operación del día de hoy (desde las 00:00 hasta ahora) |
| **Este mes** | Los acumulados del mes en curso, con el gráfico de evolución diaria |
| **Personalizado** | Un rango de fechas que elegís vos (aparece solo cuando definís un rango en el filtro de período) |

Cada pestaña muestra sus propios KPIs, mapa de calor y desgloses para ese período.

Además, arriba hay un **filtro de período** (un panel colapsable en escritorio, un panel lateral en celular) donde elegís el rango personalizado. El botón de ayuda (?) al lado de las pestañas resume cómo se calculan los números.

---

## Horario operativo

El horario operativo (por defecto **8:00 a 18:00**) se usa para **una sola cosa**: la alarma de inactividad ("sin actividad" / gap de pesajes), que se evalúa una vez por día. Dentro de ese rango, si pasa demasiado tiempo sin pesajes, se genera una alarma; fuera de él, la ausencia de pesajes nunca genera alarma.

Los KPIs y desgloses del Dashboard **no** dependen de este horario: cuentan todos los pesajes del día sin importar la hora.

Podés ajustar el horario operativo en **Sistema → Alertas → Configuración**, en la tarjeta "Sin actividad en horario operativo". Ver [`modulo-alarmas.md`](modulo-alarmas.md).

---

## Banner de alertas

Arriba de todo, **solo cuando hay alarmas activas sin leer**, aparece un banner naranja que muestra la **cantidad** de alertas activas y un botón **Revisar** que te lleva al módulo de Alertas (Sistema → Alertas).

El banner no detalla el tipo de cada alarma — para ver el detalle usá el botón Revisar. Si no hay alarmas activas, el banner no aparece.

> **Importante:** el Dashboard **no** muestra una sección de "camiones en el predio ahora". El estado EN PREDIO de cada camión se consulta en el módulo de **Pesajes** (o en el Historial), filtrando por estado. Ver [`modulo-pesajes-admin.md`](modulo-pesajes-admin.md).

---

## KPIs del día (pestaña "Hoy")

Indicadores acumulados desde el inicio del día (00:00 hasta ahora). Cada KPI tiene un ícono de ayuda (?) y, al pasar el cursor sobre el porcentaje de variación, muestra la comparación contra el **mismo día del mes anterior**.

| KPI | Qué mide |
|-----|----------|
| **Pesajes hoy** | Cantidad total de pesajes registrados hoy |
| **Toneladas netas** | Suma de kg netos de todos los pesajes del día, en toneladas |
| **Promedio / viaje** | Toneladas netas promedio por pesaje |
| **Último pesaje** | Minutos transcurridos desde el último pesaje registrado hoy (indica si la operación está activa) |
| **kg / hectárea** | Kg netos por hectárea de zona de servicio (muestra "—" si no hay hectáreas cargadas) |
| **kg / persona** | Kg netos por habitante de la zona de servicio (muestra "—" si no hay habitantes cargados) |

Debajo de los KPIs, **cuando hay al menos un pesaje en el día**, aparecen el mapa de calor y los desgloses (ver más abajo). Si todavía no hay pesajes hoy, se muestra un mensaje indicándolo.

---

## KPIs del mes (pestaña "Este mes")

Indicadores acumulados del mes en curso (del día 1 al día de hoy). La comparación de variación es contra el **mismo período del mes anterior**.

| KPI | Qué mide |
|-----|----------|
| **Días operativos** | Cantidad de días del mes con al menos un pesaje registrado |
| **Pesajes del mes** | Total de pesajes desde el 1° del mes |
| **Toneladas del mes** | Suma de toneladas netas acumuladas en el mes |
| **kg / hectárea** | Kg netos acumulados por hectárea de zona de servicio |
| **kg / persona** | Kg netos acumulados por habitante de la zona de servicio |

La pestaña "Este mes" incluye además el **gráfico de evolución diaria** (ver abajo).

---

## Período personalizado (pestaña "Personalizado")

Cuando elegís un rango de fechas en el filtro de período, aparece la pestaña **Personalizado** con los KPIs de ese rango (pesajes, toneladas netas, días operativos, promedio por día, kg/hectárea y kg/persona), su gráfico de evolución y los mismos desgloses y mapa de calor que las otras pestañas.

Es la forma de analizar un tramo específico (una semana, un trimestre, un evento puntual) sin salir del Dashboard.

---

## Gráfico de evolución diaria

En las pestañas "Este mes" y "Personalizado" hay un gráfico de barras con las **toneladas netas de cada día** del período. Incluye una línea de promedio.

Te permite ver de un vistazo si hay días con actividad inusualmente alta o baja. Un día con una barra muy corta (pocas toneladas) puede indicar:
- Día feriado o paro
- Problema con el sistema que impidió registrar pesajes
- Simplemente poca actividad real

---

## Mapa de calor por zona

Cada pestaña incluye un **mapa de calor** (choropleth) que pinta las zonas según su intensidad de recolección en el período. Para cada zona calcula cuatro métricas sobre el peso neto: **toneladas**, **viajes**, **per cápita** (kg/habitante) y **densidad** (kg/hectárea).

- Las zonas con polígono dibujado se pintan en el mapa; el resto se listan aparte.
- Si hay más de un servicio, un selector permite **filtrar el mapa por servicio** (cada zona pertenece a un servicio).
- El mapa suma todos los turnos de cada zona.

---

## Desglose por tipo de vehículo (distribución de flota)

Panel con la operación del período desglosada por tipo de vehículo (Compactador, Volcador, Volquete, Particular…), con un gráfico de dona. Para cada tipo muestra viajes, toneladas, kg/viaje y porcentaje del total.

Útil para verificar que la distribución de la flota está dentro de los parámetros esperados.

---

## Desglose por zona y turno

Panel con la operación del período desglosada por zona, acompañado de un gráfico. **Una misma zona puede ocupar varias filas, una por turno.** Si hay más de un servicio, un selector permite elegir de qué servicio ver las zonas.

| Columna | Descripción |
|---------|-------------|
| Zona y turno | Nombre de la zona (con el turno, si corresponde) |
| Viajes | Cantidad de pesajes de esa zona/turno en el período |
| Toneladas | Suma de kg netos, en toneladas |
| kg/viaje | Promedio de kg netos por pesaje |
| kg/ha | Kg por hectárea (si la zona tiene hectáreas cargadas; si no, "—") |
| kg/hab | Kg por habitante (si la zona tiene habitantes cargados; si no, "—") |
| Porcentaje | Proporción respecto al total del período |

---

## Con qué frecuencia revisar el Dashboard

Durante el horario operativo (por defecto 8:00–18:00) se recomienda revisarlo **al menos una vez por hora**. El banner de alertas aparece automáticamente, pero el sistema **no envía notificaciones por fuera de la pantalla** — si no abrís el Dashboard (o el módulo de Alertas), no te enterás de que hay una alarma.

Una revisión rápida de 30 segundos alcanza para detectar:
- Alarmas activas (banner naranja)
- Caídas o picos repentinos en la actividad del día
- Cómo viene la evolución del mes respecto al mes anterior

---

## Qué hacer con cada sección

| Sección | Qué hacés si algo llama la atención |
|---------|-------------------------------------|
| Banner de alertas | Ir a Sistema → Alertas, revisar cada alarma y marcarla como resuelta |
| KPIs del día muy bajos | Verificar con el operador si hay algún problema operativo o de sistema |
| Gráfico con día en cero | Investigar si fue feriado, paro o problema técnico |
| Zona con actividad inusual | Cruzar con el log de Pesajes para ver qué pesajes corresponden |
| Camiones sin egreso | Ir a **Pesajes** y filtrar por estado EN PREDIO (el Dashboard no lo lista) |

---

## Preguntas frecuentes

**¿El Dashboard se actualiza solo o tengo que recargar?**
Se actualiza automáticamente cada 10 minutos. También podés forzar un refresco con el botón de arriba a la derecha. No hace falta recargar la página del navegador.

**¿Puedo ver el Dashboard de días o períodos anteriores?**
Sí. Además de "Hoy" y "Este mes", el filtro de período te deja elegir un **rango personalizado** de fechas y ver sus KPIs, evolución y desgloses en la pestaña "Personalizado". Para el detalle pesaje por pesaje, usá el módulo de Pesajes o de Reportes.

**¿Dónde veo qué camiones están en el predio ahora?**
En el módulo de **Pesajes** (o en el Historial), filtrando por estado EN PREDIO. El Dashboard no tiene una sección de camiones en predio.

**¿Qué pasa si el operador no registró pesajes en todo el día?**
Los KPIs del día aparecen en cero. Si además fue un día hábil (lunes a sábado) dentro del horario operativo, a las 00:30 del día siguiente el sistema genera una alarma de tipo "sin actividad" (gap de pesajes). Ver [`modulo-alarmas.md`](modulo-alarmas.md).

**¿Los KPIs del mes incluyen el día de hoy?**
Sí, incluyen todos los datos desde el día 1 del mes hasta el momento en que estás viendo el Dashboard.

**¿Las toneladas son netas o brutas?**
Todos los indicadores del Dashboard trabajan con **toneladas netas** (peso bruto menos tara). Los pesajes cancelados no se cuentan.

**¿Puedo filtrar el Dashboard?**
Por **período** sí (Hoy / Este mes / rango personalizado), y el **mapa de calor** se puede filtrar por servicio. Para desgloses por servicio, tipo de vehículo o zona con más detalle, usá el módulo de Reportes.

---

*Documento actualizado: 13/07/2026 | Versión: 2.0*
