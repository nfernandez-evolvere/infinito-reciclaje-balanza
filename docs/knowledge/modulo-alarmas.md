# Módulo de alarmas
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador
**Cuándo usarlo:** Referencia de cómo funcionan las alarmas, cómo interpretarlas y cómo configurarlas

---

## Para qué sirven las alarmas

Las alarmas son avisos automáticos que el sistema genera cuando detecta situaciones inusuales en la operación. Te permiten identificar problemas sin tener que revisar manualmente cada pesaje.

No todas las alarmas indican un problema real — algunas son falsos positivos que se resuelven en segundos. La clave está en saber qué significa cada tipo y cuándo actuar.

---

## Dónde aparecen las alarmas

Las alarmas aparecen en dos lugares:

1. **Dashboard** — un banner naranja en la parte superior cuando hay alarmas activas sin resolver. Muestra solo la **cantidad** de alertas activas y un botón **Revisar** para ir al módulo. No detalla el tipo de cada alarma en el banner.
2. **Módulo de Alarmas** — la lista completa con el detalle de cada alarma, incluyendo las ya resueltas.

**Ruta al módulo:** Sistema → Alertas (en la barra lateral)

---

## Cuándo se generan las alarmas

Según el tipo, la alarma se genera en dos momentos distintos:

- **Al registrar un pesaje (en el momento):** las alarmas de **peso fuera de rango** y **vehículo no habitual** se generan apenas el operador guarda un pesaje que cumple la condición.
- **Al día siguiente (proceso automático):** las alarmas de **sin actividad (gap)**, **volumen diario atípico** y **frecuencia atípica por zona** las calcula un proceso que corre **todos los días a las 00:30** y analiza la jornada **del día anterior**. No se generan en tiempo real durante el día: recién las ves a la mañana siguiente.

En ningún caso el sistema envía emails ni notificaciones por fuera de la pantalla: las alarmas solo se ven en el Dashboard y en el módulo de Alertas.

---

## Tipos de alarma

### Tipo 1 — Sin actividad (gap de pesajes)

**Qué es:** No se registraron pesajes durante un período prolongado dentro del horario operativo configurado, o no hubo ningún pesaje en toda la jornada.

**Ejemplo:** No se registraron pesajes entre las 09:00 y las 11:30 (150 minutos) en horario operativo.

> Esta alarma se evalúa **al día siguiente** (00:30) sobre la jornada anterior, y **solo en días hábiles (lunes a sábado)** — los domingos no se evalúa. El horario operativo (hora de inicio y de fin) lo definís vos en la configuración; por defecto es de 8:00 a 18:00. Fuera de ese rango, la ausencia de pesajes no genera esta alarma. Se genera como máximo una alarma de este tipo por día.

**Causas posibles:**
- El operador estuvo registrando pesajes en papel y olvidó cargarlos al sistema
- Problema técnico que impidió el acceso al sistema
- Hubo una pausa real en la operación (almuerzo extendido, lluvia intensa, etc.)
- El operador cerró el navegador y no pudo reconectarse

**Qué hacer:**
1. Contactar al operador para saber si hubo pesajes reales en ese período
2. Si hubo pesajes y no fueron registrados, cargarlos ahora
3. Si fue una pausa real (ej. lluvia), marcar la alarma como resuelta con un comentario

---

### Tipo 2 — Peso fuera de rango

**Qué es:** Se registró un pesaje con un peso bruto que está fuera del rango configurado para ese tipo de vehículo. Se genera **en el momento** del registro (es el mismo caso que dispara el aviso naranja en el formulario del operador).

**Ejemplo:** Un Compactador (rango esperado: 10.000–26.500 kg) se registró con 4.200 kg.

**Causas posibles:**
- El operador ingresó mal el peso (ej: 4.200 en lugar de 14.200 o 24.200)
- El camión iba poco cargado por alguna razón operativa válida
- El rango configurado para ese tipo de vehículo no refleja la realidad actual de la flota

**Qué hacer:**
1. Ir al módulo de Pesajes y buscar el pesaje en cuestión (el link desde la alarma lo lleva directo)
2. Comparar con pesajes históricos del mismo vehículo para ver si es anómalo
3. Si es un error de tipeo, editarlo con el motivo correspondiente
4. Si el peso es correcto, marcar la alarma como resuelta con una explicación

---

### Tipo 3 — Vehículo no habitual

**Qué es:** Se registró un pesaje con un tipo de vehículo que no coincide con los tipos habituales del servicio elegido. Se genera **en el momento** del registro.

**Ejemplo:** El servicio Domiciliario tiene como habituales Compactador y Volcador, y se registró un pesaje con un Volquete.

> Es solo un aviso: el pesaje se guarda igual. El tipo de vehículo "habitual" de cada servicio es una sugerencia configurable en **Configuración → Servicios**, no una restricción.

**Causas posibles:**
- Se usó un vehículo distinto al habitual por una necesidad operativa puntual
- El vehículo tiene mal asignado su tipo en el padrón
- El servicio tiene mal configurados sus tipos habituales

**Qué hacer:**
1. Verificar que el tipo del vehículo en el padrón sea correcto
2. Si el uso fue legítimo, marcar la alarma como resuelta con un comentario

---

### Tipo 4 — Frecuencia atípica por zona

**Qué es:** Una zona registró muchos más o muchos menos pesajes de lo habitual respecto a su promedio histórico. Se evalúa **al día siguiente** sobre la jornada anterior.

**Ejemplo:** La Zona Norte tuvo 0 pesajes en todo el día cuando habitualmente tiene entre 8 y 12.

**Causas posibles:**
- Cambio en la planificación de rutas que no fue comunicado
- Feriado o evento que afectó esa zona
- El operador asignó los pesajes a una zona diferente por error
- Problema real de recolección en esa zona

**Qué hacer:**
1. Verificar con quien coordina la operación si hubo cambios en las rutas
2. Revisar si hay pesajes de esa zona asignados incorrectamente a otra zona
3. Si es un cambio legítimo, marcar la alarma como resuelta

---

### Tipo 5 — Volumen diario atípico

**Qué es:** El total de toneladas netas registradas en el día se desvía demasiado del promedio de los últimos 30 días (muy por encima o muy por debajo). Se evalúa **al día siguiente** sobre la jornada anterior.

**Ejemplo:** Un día se registran 12 toneladas cuando el promedio ronda las 40.

**Causas posibles:**
- Día feriado, paro o evento que redujo la recolección
- Una jornada de actividad inusualmente alta
- Pesajes sin cargar o cargados con pesos mal ingresados
- El promedio histórico todavía es bajo porque hay pocos días de operación

**Qué hacer:**
1. Cruzar con el gráfico de evolución diaria del Dashboard para ver si el día realmente fue atípico
2. Verificar con el operador si quedaron pesajes sin registrar
3. Si la variación tiene una explicación operativa, marcar la alarma como resuelta con un comentario

---

## Cómo resolver una alarma

1. Ir a **Sistema → Alertas** o hacer clic en **Ver alertas** desde el Dashboard.
2. Encontrá la alarma en la lista.
3. Hacé clic en la alarma para ver su detalle.
4. Tomá la acción correspondiente (editar un pesaje, contactar al operador, etc.).
5. Una vez que la situación está atendida, hacé clic en **Marcar como resuelta**.
6. Escribí un breve comentario explicando qué pasó y qué se hizo. Ejemplo: *"El operador confirmó que hubo una pausa operativa por lluvia entre las 10:30 y las 12:00"*.

Las alarmas resueltas pasan a la sección de historial y dejan de aparecer en el Dashboard.

---

## Historial de alarmas

El módulo muestra todas las alarmas: activas e históricas. Las alarmas resueltas incluyen:
- Quién la resolvió
- Cuándo se resolvió
- El comentario de resolución

Este historial permite identificar patrones: si el mismo tipo de alarma se repite seguido en la misma zona, puede haber un problema estructural que vale la pena investigar.

---

## Configuración de umbrales

Los umbrales que disparan las alarmas son configurables desde **Sistema → Alertas → Configuración**. Cada tipo de alarma tiene su propia tarjeta, con un switch para activarla o desactivarla y sus umbrales.

### Umbral de gap de pesajes

Define cuántos minutos sin pesajes (durante el horario operativo) se necesitan para generar una alarma, y el horario operativo mismo.

| Configuración | Descripción |
|---------------|-------------|
| Minutos sin actividad | Minutos sin pesajes para disparar la alarma (default: 120 minutos) |
| Horario operativo — Desde | Hora a partir de la cual se empieza a evaluar la actividad (default: 8:00) |
| Horario operativo — Hasta | Hora hasta la cual se evalúa la actividad (default: 18:00) |

Si los minutos son muy pocos (ej: 20), se van a generar muchas alarmas falsas durante pausas normales como el almuerzo. Si son demasiados (ej: 3 horas), se pueden perder situaciones problemáticas.

Ajustá el **horario operativo** a la realidad de tu predio: si trabajás de 7 a 15, ponelo así y no recibirás alarmas por la tarde cuando no hay operación. Fuera del horario configurado, la falta de pesajes nunca dispara esta alarma.

### Umbrales de peso fuera de rango

Los rangos de peso están definidos en **Configuración → Vehículos** (pestaña **Tipos de vehículo**; ver módulo de ABMs). Las alarmas de peso fuera de rango se disparan cuando un pesaje queda fuera de esos rangos.

Para ajustar los umbrales de peso: ir a Configuración → Vehículos, pestaña Tipos de vehículo, y editar el tipo correspondiente.

### Umbral de volumen diario atípico

Define qué desviación respecto al promedio histórico de toneladas del día se considera atípica.

| Configuración | Descripción |
|---------------|-------------|
| Variación mínima | Porcentaje de desviación del promedio diario para disparar la alarma (default: 20%) |

### Umbral de frecuencia atípica por zona

Define qué variación respecto al promedio histórico por zona se considera "atípica".

| Configuración | Descripción |
|---------------|-------------|
| Variación mínima | Porcentaje de desviación del promedio para disparar la alarma (default: 30%) |

---

## Cuándo revisar las alarmas

Las alarmas aparecen en el Dashboard automáticamente, pero el sistema no envía notificaciones por fuera de la pantalla. Revisá el Dashboard al menos una vez por hora durante el horario operativo para no perderte una alarma activa.

Al inicio del día siguiente es buena práctica revisar si quedaron alarmas sin resolver del día anterior.

---

## Preguntas frecuentes

**¿Una alarma puede cerrarse sola?**
No. Las alarmas requieren resolución manual. Permanecen activas hasta que las cerrás vos.

**¿Las alarmas activas afectan la operación?**
No bloquean nada. Son avisos informativos — el operador puede seguir registrando pesajes aunque haya alarmas activas.

**¿Puedo desactivar un tipo de alarma?**
Sí. En **Sistema → Alertas → Configuración**, cada tarjeta tiene un switch para desactivar ese tipo de alarma si genera demasiado ruido. No se recomienda desactivar las alarmas de peso fuera de rango.

**¿Qué pasa si ajusto un umbral y hay alarmas activas del umbral anterior?**
Las alarmas activas ya generadas no se recalculan. El nuevo umbral aplica solo para las alarmas futuras.

**¿Puedo ver las alarmas históricas de meses anteriores?**
Sí. El historial de alarmas no tiene límite de fecha. Podés filtrar por período para ver las alarmas de cualquier mes.

---

*Documento actualizado: 13/07/2026 | Versión: 2.0*
