# Módulo de reportes
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador
**Cuándo usarlo:** Referencia de cómo generar, interpretar y exportar reportes de la operación

---

## Para qué sirve este módulo

El módulo de Reportes genera los reportes formales de la operación de recolección. Reemplaza la tarea manual de armar los reportes mensuales en Excel — algo que antes llevaba entre 2 y 3 horas por mes.

El reporte principal es el **reporte mensual** que se entrega al municipio. También podés generar reportes trimestrales o por rango de fechas personalizado para análisis internos.

---

## Cómo acceder

**Ruta:** Operación → Reportes (en la barra lateral)

---

## Estructura del módulo

El módulo de Reportes tiene cuatro pestañas:

| Pestaña | Para qué sirve |
|---------|---------------|
| **Programados** | Configurar el envío automático de reportes por email |
| **Generar** | Generar un reporte para un período específico y exportarlo en PDF o Excel |
| **Historial** | Ver todo lo generado y enviado, revisar y aprobar envíos pendientes, re-descargar reportes y reintentar fallos |
| **Configuración** | Datos institucionales de los PDFs, análisis con IA, tipos de reporte activos y revisión de envíos |

---

## Cómo generar un reporte

### Paso 1 — Elegir el período

Seleccioná el rango de fechas que querés incluir en el reporte:

| Opción | Descripción |
|--------|-------------|
| Mes | Un mes completo (seleccionás el mes y el año) |
| Trimestre | Tres meses consecutivos (seleccionás el trimestre y el año) |
| Rango personalizado | Cualquier rango: elegís fecha de inicio y fecha de fin |

Para el reporte mensual de la Municipalidad, usá la opción **Mes**.

### Paso 2 — Aplicar filtros (opcional)

Podés acotar el reporte por cualquier combinación de:

| Filtro | Opciones |
|--------|----------|
| Tipo de servicio | Un servicio específico o todos |
| Zona | Una zona específica o todas (se acota según el servicio elegido) |
| Tipo de vehículo | Un tipo específico o todos |

Si no aplicás ningún filtro, el reporte incluye todos los datos del período seleccionado.

### Paso 3 — Generar

Hacé clic en **Generar reporte**. El sistema procesa los datos y muestra una vista previa en pantalla.

### Paso 4 — Revisar la vista previa

Antes de exportar, revisá:
- Que el período sea correcto
- Que los totales tengan sentido
- Que no haya zonas o servicios faltantes

### Paso 5 — Exportar

Elegí el formato de salida:

| Formato | Cuándo usarlo |
|---------|---------------|
| **PDF** | Para entregar al municipio — formato fijo, con logo y encabezado oficial |
| **Excel** | Para análisis adicional — todos los datos en columnas editables |

---

## Qué incluye el reporte

El reporte mensual incluye las siguientes secciones:

### Resumen ejecutivo
- Período del reporte
- Total de pesajes registrados
- Total de toneladas netas
- Promedio diario de toneladas
- Días operativos del período

### Por zona y turno
Las zonas se agrupan por su servicio. Para cada zona (y turno, si corresponde): cantidad de viajes, toneladas netas, kg/viaje, porcentaje del total y kg por hectárea (si la zona tiene hectáreas cargadas).

### Por tipo de vehículo
Para cada tipo de vehículo: cantidad de viajes, toneladas netas, kg/viaje y porcentaje del total del período.

### Evolución diaria
Un registro por día del período, mostrando los pesajes y las toneladas netas de cada jornada.

---

## Cuándo generar el reporte mensual

El reporte mensual se genera **después del último día del mes**, cuando ya están registrados todos los pesajes. El flujo habitual:

1. El 1° de cada mes (o los primeros días hábiles), generar el reporte del mes anterior.
2. Revisar la vista previa y verificar que los totales sean coherentes.
3. Exportar en PDF para el municipio.
4. Exportar en Excel para el archivo interno.

---

## Indicador de densidad (kg por hectárea)

Si las zonas tienen cargadas sus hectáreas, la tabla "Por zona y turno" del PDF incluye la columna **kg/ha** (kilogramos netos recolectados por hectárea en el período).

| Indicador | Cálculo | Descripción |
|-----------|---------|-------------|
| kg por hectárea | Kg netos ÷ Hectáreas de la zona | Kg recolectados por hectárea en el período |

Si una zona tiene las hectáreas en cero, la columna muestra "—" para esa zona. Los indicadores per cápita (kg por habitante) se ven en el Dashboard y en el mapa de calor, no en el PDF.

---

## Reportes programados

La pestaña **Programados** te permite configurar el envío automático de reportes por email. El sistema genera el reporte y lo envía solo, sin que tengas que hacerlo manualmente.

### Cómo crear un reporte programado

1. Ir a **Operación → Reportes**, pestaña **Programados**.
2. Hacer clic en **Programar reporte**.
3. Completar el formulario:

| Campo | Descripción |
|-------|-------------|
| Nombre | Nombre identificatorio del programado (ej: "Reporte mensual municipio") |
| Tipo de reporte | **Reporte mensual** (resumen de operación) o **Alertas** (reporte de alertas del período) |
| Frecuencia | **Diaria** (cubre el día anterior), **Semanal** (los 7 días anteriores), **Quincenal** (los 15 días anteriores) o **Mensual** (el mes anterior al envío) |
| Primer envío | Desde cuándo corre el programado: hoy o una fecha futura. El envío sale ese día a las 08:00 y se repite según la frecuencia manteniendo el día elegido — si elegís el 1 del mes con frecuencia mensual, corre todos los 1 cubriendo el mes anterior completo. |
| Formatos del envío | Solo para **Reporte mensual**: elegí en qué se adjunta el reporte al email — **PDF**, **Excel** o ambos. Tenés que dejar al menos uno marcado. Las **Alertas** se envían siempre en PDF, así que este campo no aparece. |
| Destinatarios | Uno o más emails — presioná Enter o coma para confirmar cada uno |
| Revisión antes de enviar | **Según configuración general** (heredar), **Revisar siempre** o **Enviar directo**. La opción del programado pisa la configuración global. Ver la sección "Revisión de envíos". |
| Activo | Switch para activar o desactivar el envío automático |

4. Guardar.

> El **Excel** adjunto se abre directamente en Google Sheets, así que sirve tanto para quien usa Excel como para quien trabaja en la nube. Si marcás los dos formatos, el email llega con ambos archivos adjuntos.

### Acciones disponibles sobre un programado existente

Desde el menú de acciones (⋯) de cada programado:
- **Editar** — modificar cualquier campo
- **Enviar ahora** — generar el reporte ya mismo, sin esperar la próxima ejecución programada. Según la configuración de revisión, queda pendiente de aprobación en el Historial o se envía directo.
- **Descargar PDF** — obtener el PDF del último período sin enviarlo por email
- **Eliminar** — borrar el programado definitivamente. Los reportes ya generados o pendientes de revisión no se pierden: siguen en el Historial y se pueden aprobar igual.

### Cuándo ver el próximo envío

La tabla muestra **Último envío** y **Próximo envío** para cada programado. El próximo envío queda fijado por la fecha de **Primer envío** elegida al crear el programado y avanza según la frecuencia (siempre a las 08:00). Al editar, el campo muestra la fecha del próximo envío: si no la tocás, el cronograma no cambia. "Enviar ahora" tampoco lo mueve — el envío programado sigue en pie.

---

## Revisión de envíos (aprobación manual)

Por defecto, **ningún reporte programado se envía solo**: el sistema lo genera, lo deja **pendiente de revisión** en la pestaña Historial y espera tu aprobación. Recién cuando lo aprobás, el email sale hacia los destinatarios. Esto es especialmente importante cuando el reporte incluye el análisis generado con IA: nada llega al municipio sin que alguien lo haya leído.

### Cómo funciona el flujo

1. El sistema genera el reporte en la fecha programada (o cuando usás "Enviar ahora") y congela su contenido.
2. El reporte queda **En revisión** en el Historial. Los administradores reciben un email de aviso, y la pantalla de Reportes muestra un banner y un contador en la pestaña Historial.
3. Desde la acción **Revisar** podés: ver el PDF y el Excel exactamente como se enviarían, corregir el texto del análisis (si el reporte usa IA), aprobar el envío o descartarlo.
4. Al aprobar, el envío sale en los próximos minutos hacia los destinatarios configurados.

> Lo que ves en la revisión es exactamente lo que se envía: el contenido queda congelado al generarse. Si después se corrige un pesaje del período, el reporte aprobado no cambia.

### Configurarlo

- **Global** — pestaña Configuración, card "Revisión de envíos": el switch "Requerir revisión antes de enviar" viene **activado por defecto**. Si lo apagás, los programados se generan y envían directo, sin aprobación.
- **Por reporte programado** — campo "Revisión antes de enviar" del formulario: cada programado puede seguir la configuración general (**heredar**), **revisar siempre** o **enviar directo**. La opción del programado pisa la global.

### Editar el análisis de IA

Si el reporte incluye el análisis generado con IA, en el modal de revisión podés corregir el texto antes de aprobar. El texto original de la IA se conserva como registro interno. Mientras tengas cambios sin guardar, el botón **Aprobar y enviar** queda deshabilitado — guardá primero, después aprobá.

### Descartar un reporte

Si el contenido no corresponde (datos incompletos, período equivocado), podés **descartarlo** con un motivo opcional. El reporte no se envía y queda en el Historial como registro de la decisión. El próximo ciclo programado genera el período siguiente normalmente.

### Estados del Historial

| Estado | Qué significa |
|--------|---------------|
| **Generando…** | El sistema está armando el reporte |
| **En revisión** | Generado y esperando aprobación — todavía no se envió |
| **Enviando…** | Aprobado, el email está saliendo |
| **Enviado** | Llegó a los destinatarios (la fila muestra quién lo aprobó) |
| **Descargado** | Descarga manual desde la pestaña Generar |
| **Descartado** | Un administrador decidió no enviarlo (la fila muestra el motivo) |
| **Fallido** | La generación o el envío fallaron — usá **Reintentar** |

### Si algo falla

Los reportes **Fallidos** muestran el motivo del error y la acción **Reintentar** en su menú:

- Si falló el **envío** (por ejemplo, un problema con el servidor de email), el reintento reenvía el mismo contenido ya generado, sin recalcular nada.
- Si falló la **generación**, el reintento vuelve a generar el reporte usando el mismo período original — aunque hayan pasado días.

Si un reporte queda más de 2 horas en "Generando…" o "Enviando…" (por ejemplo, por un corte del servidor), el sistema lo marca automáticamente como Fallido para que puedas reintentarlo.

---

## Configuración del reporte

La pestaña **Configuración** define los datos institucionales de los PDFs y el comportamiento de los envíos.

| Sección | Descripción |
|---------|-------------|
| Nombre del municipio | Aparece en la portada y pie de página del reporte |
| Texto de presentación | Descripción de la empresa para la sección "Quiénes Somos" del reporte |
| Servicios destacados | Cards de servicios que aparecen en la sección "Quiénes Somos" (máximo 6) |
| Inteligencia Artificial | Genera automáticamente la sección de análisis del reporte PDF (requiere una API key de Google AI Studio) |
| Tipos de reporte activos | Qué tipos se pueden generar y programar: Reporte mensual y/o Alertas |
| Revisión de envíos | Si los reportes programados requieren aprobación manual antes de enviarse — **activado por defecto** |

Completar los datos institucionales antes de generar el primer PDF formal para el municipio.

---

## El reporte PDF vs el reporte Excel

**Reporte PDF:**
- Tiene formato fijo con logo y encabezado institucional
- No es editable
- Está pensado para ser firmado y entregado al municipio
- Incluye solo los totales y los indicadores principales

**Reporte Excel:**
- Incluye el detalle completo de cada pesaje individual
- Es editable para análisis adicionales
- Está pensado para uso interno o para cruzar con otros datos

---

## Preguntas frecuentes

**¿Puedo generar un reporte de un mes que ya fue generado antes?**
Sí. Podés generar el mismo período cuantas veces quieras. Cada generación refleja los datos actuales del sistema — si hubo correcciones de pesajes después de la última exportación, el nuevo reporte las incluye.

**¿Qué pasa si hay pesajes editados en el período del reporte?**
Se incluyen con los valores corregidos. El reporte siempre muestra el estado actual de los datos, no el estado al momento del registro original.

**¿El reporte incluye los pesajes con estado EN PREDIO (sin egreso registrado)?**
Sí. El peso neto se calcula al momento del ingreso y se incluye en el reporte independientemente de si el egreso fue registrado o no.

**¿Puedo generar un reporte de una zona específica?**
Sí. Aplicá el filtro de zona antes de generar el reporte (podés acotar primero por servicio). El PDF generado refleja solo los datos de esa zona.

**¿Hay un límite de períodos que puedo exportar?**
No. Podés generar reportes de cualquier período desde el inicio de la operación.

**¿Puedo programar el envío automático del reporte al municipio?**
Sí. En la pestaña **Programados** podés crear un reporte programado con frecuencia mensual y los emails del municipio como destinatarios. El sistema lo genera y envía automáticamente en la fecha configurada.

**¿Puedo hacer que el reporte corra el 1 de cada mes con los datos del mes anterior?**
Sí, ese es el caso típico. Al crear el programado elegí frecuencia **Mensual** y en **Primer envío** el día 1 del mes que viene: el reporte sale todos los 1 a las 08:00 cubriendo el mes calendario anterior completo. Lo mismo aplica a cualquier otro día — si elegís el 5, corre todos los 5 con el mes previo a esa fecha.

**¿Puedo elegir si el reporte programado llega en PDF o en Excel?**
Sí, en los programados de tipo **Reporte mensual**. Al crear o editar el programado marcás **PDF**, **Excel** o ambos en "Formatos del envío" (al menos uno). El email automático adjunta los formatos elegidos. Los programados de tipo **Alertas** se envían siempre en PDF.

**¿Puedo configurar el logo y los datos del municipio en el PDF?**
Los datos institucionales (nombre del municipio, texto de presentación, servicios destacados) se configuran en la pestaña **Configuración** del módulo de Reportes.

**¿Por qué mi reporte programado no llegó a los destinatarios?**
Lo más probable es que esté esperando tu aprobación: por defecto, los envíos programados quedan **En revisión** en la pestaña Historial hasta que alguien los apruebe. Revisá el banner de la pantalla de Reportes o el contador del tab Historial. Si en cambio figura como **Fallido**, abrí su menú y usá **Reintentar**.

**¿Puedo corregir el texto de la IA antes de que salga el reporte?**
Sí. Con la revisión activada, el reporte queda pendiente en el Historial: desde **Revisar** editás el análisis, lo guardás y recién entonces aprobás el envío. El texto original de la IA se conserva como registro.

**¿La re-descarga desde el Historial recalcula los datos?**
No. Cada entrada del Historial guarda su contenido congelado: re-descargar reproduce el reporte idéntico al que se generó o envió, aunque después se hayan corregido pesajes del período. Para un reporte con los datos actuales, generalo de nuevo desde la pestaña **Generar**.

**¿Qué pasa con un reporte pendiente de revisión si elimino el programado?**
Nada se pierde: el reporte pendiente sigue en el Historial con sus destinatarios y se puede aprobar, descartar o reintentar igual.

---

*Documento actualizado: 13/07/2026 | Versión: 1.4*
