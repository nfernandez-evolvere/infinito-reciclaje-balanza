# Módulo Dashboard
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador (Nacho)
**Cuándo usarlo:** Referencia de cómo leer e interpretar el panel de control en tiempo real

---

## Para qué sirve el Dashboard

El Dashboard es tu vista de control de la operación. Te muestra en una sola pantalla qué está pasando ahora mismo en el predio, cómo va el día y cómo viene el mes. Es la primera pantalla que ves cuando ingresás como administrador.

No reemplaza revisar los pesajes uno por uno — para eso está el módulo de Pesajes. El Dashboard te da la lectura rápida: si todo está bien, en 30 segundos lo sabés.

---

## Horario operativo

El sistema considera como horario operativo el rango **8:00 a 18:00**. Algunos indicadores y alertas se calculan en función de ese rango. Fuera de ese horario, la ausencia de pesajes no genera alertas.

---

## Sección 1 — Alertas activas

Esta sección aparece **solo cuando hay alertas** sin resolver. Está en la parte superior de la pantalla, con un color llamativo (naranja o rojo según el tipo).

Muestra:
- Cantidad de alertas activas
- Tipo de alerta (gap de pesajes, peso inusual, frecuencia atípica)
- Un botón **Ver alertas** que te lleva al módulo de Alarmas

Si no hay alertas activas, esta sección no aparece y el resto del dashboard ocupa ese espacio.

---

## Sección 2 — Camiones en el predio ahora

Muestra los camiones que **entraron pero todavía no salieron** (pesajes con estado EN PREDIO).

Para cada camión muestra:
- Patente y número interno
- Tipo de servicio y zona
- Hora de entrada
- Tiempo transcurrido desde la entrada

Esta sección se actualiza automáticamente. Si un operador registra un egreso, el camión desaparece de esta lista sin necesidad de recargar la página.

**Cuándo prestar atención:** si un camión lleva más de 2 horas en el predio sin que se haya registrado su egreso, probablemente el operador se olvidó de marcarlo. Podés pedirle al operador que lo registre, o editarlo vos desde el módulo de Pesajes.

---

## Sección 3 — KPIs del día

Indicadores acumulados desde el inicio del día operativo (00:00 hasta ahora).

| KPI | Qué mide |
|-----|----------|
| **Pesajes** | Cantidad total de pesajes registrados hoy |
| **Toneladas netas** | Suma de kg netos de todos los pesajes, expresada en toneladas |
| **Promedio por viaje** | Toneladas netas ÷ cantidad de pesajes |
| **Horas operativas** | Horas transcurridas desde el primer pesaje del día |

Estos valores se actualizan con cada nuevo pesaje registrado por el operador.

---

## Sección 4 — KPIs del mes

Indicadores acumulados del mes en curso (del día 1 al día de hoy).

Muestra los mismos indicadores que los KPIs del día, más una comparación con el mismo período del mes anterior cuando el dato está disponible.

---

## Sección 5 — Evolución diaria (gráfico)

Gráfico de barras con los últimos 7 días. Cada barra muestra las toneladas netas registradas ese día.

Te permite ver de un vistazo si hay días con actividad inusualmente alta o baja. Un día con una barra muy corta (pocas toneladas) puede indicar:
- Día feriado o paro
- Problema con el sistema que impidió registrar pesajes
- Simplemente poca actividad real

---

## Sección 6 — Por zona

Tabla con el desglose de la operación del día por zona:

| Columna | Descripción |
|---------|-------------|
| Zona | Nombre de la zona |
| Pesajes | Cantidad de pesajes registrados para esa zona hoy |
| Toneladas netas | Suma de kg netos de la zona, en toneladas |
| % del total | Proporción respecto al total del día |

---

## Sección 7 — Por tipo de vehículo

Tabla similar a la de zonas, pero desglosada por tipo de vehículo (Compactador, Volcador, Volquete, Particular).

Útil para verificar que la distribución de la flota está dentro de los parámetros esperados.

---

## Con qué frecuencia revisar el Dashboard

Durante el horario operativo (8:00–18:00) se recomienda revisarlo **al menos una vez por hora**. Las alertas aparecen automáticamente, pero el sistema no envía notificaciones fuera de la pantalla — si no abrís el Dashboard, no sabés que hay una alerta.

Una revisión rápida de 30 segundos alcanza para detectar:
- Camiones que llevan demasiado tiempo en el predio
- Alertas activas
- Caídas repentinas en la actividad (posible gap de pesajes)

---

## Qué hacer con cada sección

| Sección | Qué hacés si algo llama la atención |
|---------|-------------------------------------|
| Alertas activas | Ir a Alarmas, revisar cada alerta y marcarla como resuelta |
| Camiones en predio | Hablar con el operador para que registre el egreso, o hacerlo vos desde Pesajes |
| KPIs del día muy bajos | Verificar con el operador si hay algún problema operativo o de sistema |
| Gráfico con día en cero | Investigar si fue feriado, paro o problema técnico |
| Zona con actividad inusual | Cruzar con el log de Pesajes para ver qué pesajes corresponden |

---

## Preguntas frecuentes

**¿El Dashboard se actualiza solo o tengo que recargar?**
Se actualiza automáticamente. No hace falta que recargues la página.

**¿Puedo ver el Dashboard de días anteriores?**
No directamente. El Dashboard siempre muestra el estado actual. Para ver la actividad de días anteriores, usar el módulo de Reportes o el log de Pesajes con filtros por fecha.

**¿Qué pasa si el operador no registró pesajes en todo el día?**
Los KPIs del día aparecen en cero y se genera una alerta de tipo "gap de pesajes" durante el horario operativo. El Dashboard lo muestra en la sección de alertas.

**¿Los KPIs del mes incluyen el día de hoy?**
Sí, incluyen todos los datos desde el día 1 del mes hasta el momento en que estás viendo el Dashboard.

**¿Las toneladas son netas o brutas?**
Todos los indicadores del Dashboard trabajan con **toneladas netas** (peso bruto menos tara).

**¿Puedo filtrar el Dashboard por zona o por tipo de servicio?**
No en esta versión. El Dashboard muestra la operación total. Para análisis filtrados, usar el módulo de Reportes.

---

*Documento generado: 12/05/2026 | Versión: 1.0*
