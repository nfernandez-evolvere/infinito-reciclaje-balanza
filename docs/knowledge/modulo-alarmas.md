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

1. **Dashboard** — en la parte superior, cuando hay alarmas activas sin resolver. Muestra la cantidad y un botón para ir al detalle.
2. **Módulo de Alarmas** — la lista completa con el detalle de cada alarma, incluyendo las ya resueltas.

**Ruta al módulo:** Sistema → Alertas (en la barra lateral)

---

## Tipos de alarma

### Tipo 1 — Gap de pesajes

**Qué es:** No se registraron pesajes durante un período prolongado dentro del horario operativo configurado.

**Ejemplo:** Pasaron 90 minutos en horario operativo sin ningún pesaje registrado.

> El horario operativo (hora de inicio y de fin) lo definís vos en la configuración de la alarma. Por defecto es de 8:00 a 18:00. Fuera de ese rango, la ausencia de pesajes no genera esta alarma.

**Causas posibles:**
- El operador está registrando pesajes en papel y olvidó cargarlos al sistema
- Problema técnico que impidió el acceso al sistema
- Hubo una pausa real en la operación (almuerzo extendido, lluvia intensa, etc.)
- El operador cerró el navegador y no pudo reconectarse

**Qué hacer:**
1. Contactar al operador para saber si hubo pesajes reales en ese período
2. Si hubo pesajes y no fueron registrados, cargarlos ahora
3. Si fue una pausa real (ej. lluvia), marcar la alarma como resuelta con un comentario

---

### Tipo 2 — Peso inusual

**Qué es:** Se registró un pesaje con un peso bruto que está fuera del rango configurado para ese tipo de vehículo.

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

### Tipo 3 — Frecuencia atípica por origen

**Qué es:** Un origen registró muchos más o muchos menos pesajes de lo habitual en el período.

**Ejemplo:** El Origen Norte tuvo 0 pesajes en todo el día cuando habitualmente tiene entre 8 y 12.

**Causas posibles:**
- Cambio en la planificación de rutas que no fue comunicado
- Feriado o evento que afectó ese origen
- El operador asignó los pesajes a un origen diferente por error
- Problema real de recolección en ese origen

**Qué hacer:**
1. Verificar con quien coordina la operación si hubo cambios en las rutas
2. Revisar si hay pesajes de ese origen asignados incorrectamente a otro origen
3. Si es un cambio legítimo, marcar la alarma como resuelta

---

### Tipo 4 — Volumen diario atípico

**Qué es:** El total de toneladas netas registradas en el día se desvía demasiado del promedio histórico de la operación (muy por encima o muy por debajo).

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

Este historial permite identificar patrones: si el mismo tipo de alarma se repite seguido en el mismo origen, puede haber un problema estructural que vale la pena investigar.

---

## Configuración de umbrales

Los umbrales que disparan las alarmas son configurables. Para acceder a la configuración, buscar el botón **Configurar umbrales** dentro del módulo de Alarmas.

### Umbral de gap de pesajes

Define cuántos minutos sin pesajes (durante el horario operativo) se necesitan para generar una alarma, y el horario operativo mismo.

| Configuración | Descripción |
|---------------|-------------|
| Minutos sin actividad | Minutos sin pesajes para disparar la alarma (default: 120 minutos) |
| Horario operativo — Desde | Hora a partir de la cual se empieza a evaluar la actividad (default: 8:00) |
| Horario operativo — Hasta | Hora hasta la cual se evalúa la actividad (default: 18:00) |

Si los minutos son muy pocos (ej: 20), se van a generar muchas alarmas falsas durante pausas normales como el almuerzo. Si son demasiados (ej: 3 horas), se pueden perder situaciones problemáticas.

Ajustá el **horario operativo** a la realidad de tu predio: si trabajás de 7 a 15, ponelo así y no recibirás alarmas por la tarde cuando no hay operación. Fuera del horario configurado, la falta de pesajes nunca dispara esta alarma.

### Umbrales de peso inusual

Los rangos de peso están definidos en **Padrón → Vehículos** (pestaña **Tipos de vehículo**; ver módulo de ABMs). Las alarmas de peso inusual se disparan cuando un pesaje queda fuera de esos rangos.

Para ajustar los umbrales de peso: ir a Padrón → Vehículos, pestaña Tipos de vehículo, y editar el tipo correspondiente.

### Umbral de volumen diario atípico

Define qué desviación respecto al promedio histórico de toneladas del día se considera atípica.

| Configuración | Descripción |
|---------------|-------------|
| Variación mínima | Porcentaje de desviación del promedio diario para disparar la alarma (default: 20%) |

### Umbral de frecuencia atípica

Define qué variación respecto al promedio histórico por origen se considera "atípica".

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
Sí. En la configuración de umbrales, podés desactivar un tipo de alarma completo si generan demasiado ruido. No se recomienda desactivar las alarmas de peso inusual.

**¿Qué pasa si ajusto un umbral y hay alarmas activas del umbral anterior?**
Las alarmas activas ya generadas no se recalculan. El nuevo umbral aplica solo para las alarmas futuras.

**¿Puedo ver las alarmas históricas de meses anteriores?**
Sí. El historial de alarmas no tiene límite de fecha. Podés filtrar por período para ver las alarmas de cualquier mes.

---

*Documento actualizado: 18/06/2026 | Versión: 1.2*
