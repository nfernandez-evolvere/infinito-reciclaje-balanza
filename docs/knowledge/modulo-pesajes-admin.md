# Módulo de pesajes (vista administrador)
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador
**Cuándo usarlo:** Referencia de cómo revisar, filtrar, editar y auditar el log completo de pesajes

---

## Para qué sirve este módulo

El módulo de Pesajes es el log completo de todos los pesajes registrados en el sistema, sin límite de fecha. Desde acá podés:
- Buscar y filtrar cualquier pesaje
- Ver el detalle completo de cada registro
- Editar un pesaje cuando hay un error
- Ver el historial de cambios de cualquier pesaje

Ambas vistas (admin y operador) muestran todos los pesajes de la organización sin límite de fecha. La diferencia está en los filtros disponibles: el operador puede filtrar por fecha, patente, estado y operario; el admin tiene además filtros de zona, tipo de servicio, alertas de peso y pesajes editados.

---

## Cómo acceder

**Ruta:** Operación → Pesajes (en la barra lateral)

---

## Las dos pestañas de la pantalla

La pantalla de Pesajes del administrador tiene dos pestañas:

| Pestaña | Para qué sirve |
|---------|----------------|
| **Pesajes** | El log completo de pesajes, con los KPIs del día arriba. Es la vista por defecto. |
| **Modificaciones** | Solo los pesajes que fueron **editados** o **cancelados**, para auditar los cambios de un vistazo. Tiene su propio filtro por tipo de cambio (editado / cancelado). |

Cada pestaña tiene sus propios filtros y orden, independientes entre sí.

---

## La tabla de pesajes

La tabla muestra todos los pesajes ordenados del más reciente al más antiguo. Para cada pesaje se ve:

| Columna | Descripción |
|---------|-------------|
| Ingreso | Fecha y hora de entrada del camión (es la columna que ordena la tabla) |
| Patente / N.° interno | Patente y número interno del vehículo |
| Origen | Origen de recolección (con el turno, si corresponde) |
| Servicio | Tipo de servicio registrado |
| Peso neto | Kilogramos netos del pesaje (con el detalle bruto − tara al pasar el cursor) |
| Estado | Distintivos del pesaje: **Cancelado**, **Editado** y/o **Alerta** de peso |
| Acciones | Menú (⋯) con Detalles, Editar, Marcar egreso, Ver cambios y Cancelar |

> El operador, que NO tiene su propia tabla, ve estas mismas columnas. La diferencia con el admin son los filtros disponibles, no las columnas.

---

## Cómo filtrar los pesajes

Encima de la tabla hay un panel de filtros. Podés combinar cualquiera de estos criterios:

| Filtro | Opciones |
|--------|----------|
| Rango de fechas | Fecha desde / Fecha hasta |
| Patente o número interno | Texto libre |
| Tipo de servicio | Lista de todos los servicios activos |
| Zona | Lista de todas las zonas activas |
| Operador | Lista de todos los usuarios operadores |
| Estado | Todos / Activos / Cancelados |
| Con alerta de peso | Solo los pesajes que generaron aviso naranja |
| Editados | Solo los pesajes que fueron modificados |

> El filtro **Estado** separa los pesajes vigentes (**Activos** — en predio o cerrados) de los **Cancelados**. La distinción entre EN PREDIO y CERRADO se ve en cada fila, no en este filtro.

Los filtros se aplican desde un panel lateral y se reflejan como chips arriba de la tabla. Para limpiar todos los filtros, usá el botón **Limpiar filtros**.

---

## Cómo ver el detalle de un pesaje

Hacé clic en el menú de cualquier fila y selecciona la opción Detalles. Muestra:

- Todos los campos del pesaje (vehículo, servicio, origen, pesos, operador, fechas)
- Observaciones (si tiene)
- Indicador de alerta de peso (si la tuvo)
- Historial de cambios (si fue editado)

---

## Cómo editar un pesaje

Podés editar un pesaje cuando el operador cometió un error o cuando los datos necesitan corrección.

1. Encontrá el pesaje en la tabla (usá los filtros si hace falta).
2. Hacé clic en la fila para ver el detalle, o usá el botón **Editar** directamente en la fila.
3. Modificá los campos que corresponde corregir.
4. **Escribí el motivo de la corrección** — este campo es obligatorio. Sé específico: en lugar de "error", escribí qué estaba mal y qué se corrigió.
5. Guardá.

### Qué campos se pueden editar

- Tipo de servicio
- Origen
- Peso bruto
- Observaciones

### Qué no se puede editar

- Fecha y hora de entrada (es trazabilidad)
- Vehículo (patente / número interno)
- Operador que registró el pesaje
- Tara usada en el cálculo (se puede consultar pero no modificar)

> Si el peso bruto se edita, el sistema recalcula automáticamente el peso neto con la misma tara original del registro.

---

## El historial de cambios (auditoría)

Cada pesaje que fue editado tiene un historial completo de todos los cambios. Para verlo, abrí el detalle del pesaje y buscá la sección **Historial de cambios**.

Por cada cambio se registra:

| Dato | Descripción |
|------|-------------|
| Campo | Qué campo se modificó |
| Valor anterior | El dato que había antes |
| Valor nuevo | El dato que quedó después |
| Motivo | El texto que escribió quien editó |
| Usuario | Nombre del usuario que hizo el cambio |
| Fecha y hora | Cuándo se realizó el cambio |

Este historial es inmutable — no se puede editar ni borrar.

---

## El estado de los pesajes

Cada pesaje tiene un estado que indica si el camión todavía está en el predio o ya salió.

| Estado | Descripción |
|--------|-------------|
| **EN PREDIO** | El camión entró y no se registró su salida todavía |
| **CERRADO** | El egreso fue registrado por el operador |
| **CANCELADO** | El pesaje fue anulado (con motivo). No suma en KPIs ni reportes y no se puede editar ni reabrir |

### Cuándo intervenir en estados EN PREDIO

Si hay pesajes con estado EN PREDIO que son del día anterior o de hace muchas horas, probablemente el operador se olvidó de registrar el egreso. En ese caso podés editar el pesaje y completar la hora de salida manualmente.

---

## Cómo cancelar un pesaje

Cancelar es distinto de editar: editar corrige un dato y el pesaje sigue contando; cancelar anula el pesaje completo. Usá cancelar cuando el registro no corresponde (un pesaje duplicado, cargado por error o que nunca debió existir).

1. Encontrá el pesaje en la tabla.
2. Abrí el menú de acciones (⋯) y elegí **Cancelar pesaje**.
3. Escribí el motivo de la cancelación — es obligatorio.
4. Confirmá.

El pesaje pasa a estado **CANCELADO**: deja de sumar en los KPIs y en los reportes, pero **no se borra** — queda en el Historial y en la pestaña **Modificaciones** con el motivo y el usuario que lo canceló. La cancelación no se puede revertir y un pesaje cancelado no se puede editar. Tanto el admin como el operador pueden cancelar.

---

## Alerta de peso

Los pesajes que generaron un aviso naranja al momento del registro aparecen marcados en la tabla con un indicador especial. Esto significa que el peso bruto ingresado estaba fuera del rango habitual para ese tipo de vehículo.

El aviso no bloqueó el guardado — el operador decidió que el peso era correcto. Pero si querés revisarlo, podés filtrarlo con el filtro **Con alerta de peso** y revisarlos en conjunto.

---

## Exportar pesajes

Esta pantalla no tiene exportación propia: es para consultar, filtrar y auditar en línea. Para llevarte los datos a un archivo usá el **módulo de Reportes**, que exporta en Excel (detalle de cada pesaje) y en PDF (reporte formal para el municipio), con sus propios filtros de período, origen, servicio y tipo de vehículo.

Ver [`modulo-reportes.md`](modulo-reportes.md).

---

## Preguntas frecuentes

**¿Puedo ver los pesajes de todos los operadores o solo los míos?**
Como administrador, ves todos los pesajes de todos los operadores.

**¿Qué pasa si edito un pesaje que ya fue incluido en un reporte exportado?**
El pesaje editado queda actualizado en el sistema. El reporte ya exportado (PDF o Excel) no cambia — es una foto del momento en que se generó. Si necesitás un reporte corregido, volvé a generarlo.

**¿Puedo agregar un pesaje que el operador se olvidó de registrar?**
No hay un formulario para crear pesajes desde el panel de administración. Si hay un pesaje faltante, la mejor práctica es registrarlo a través del operador en la pantalla de pesaje. Si el operador ya terminó el turno, contactá al equipo de soporte de EVOLVERE.

**¿Se puede ver quién accedió al sistema y cuándo?**
El sistema registra los pesajes y las ediciones, pero no tiene un log de accesos de sesión en esta versión.

**¿El log tiene límite de registros?**
No. El log es completo e histórico desde el inicio de la operación.

**¿Puedo ordenar la tabla?**
Sí, por fecha de **Ingreso**. Hacé clic en el encabezado de la columna *Ingreso* para alternar entre más reciente primero y más antiguo primero (también podés elegirlo en el panel de filtros, en *Orden de fecha*). Las demás columnas no son ordenables.

---

*Documento actualizado: 18/06/2026 | Versión: 1.1*
