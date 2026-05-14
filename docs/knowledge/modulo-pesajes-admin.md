# Módulo de pesajes (vista administrador)
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador (Nacho)
**Cuándo usarlo:** Referencia de cómo revisar, filtrar, editar y auditar el log completo de pesajes

---

## Para qué sirve este módulo

El módulo de Pesajes es el log completo de todos los pesajes registrados en el sistema, sin límite de fecha. Desde acá podés:
- Buscar y filtrar cualquier pesaje
- Ver el detalle completo de cada registro
- Editar un pesaje cuando hay un error
- Ver el historial de cambios de cualquier pesaje

Es diferente al Historial del operador, que solo muestra el turno actual. Este log incluye todo.

---

## Cómo acceder

**Ruta:** Operación → Pesajes (en la barra lateral)

---

## La tabla de pesajes

La tabla muestra todos los pesajes ordenados del más reciente al más antiguo. Para cada pesaje se ve:

| Columna | Descripción |
|---------|-------------|
| Fecha y hora | Fecha y hora de entrada del camión |
| Patente | Patente del vehículo |
| N° interno | Número interno del vehículo |
| Servicio | Tipo de servicio registrado |
| Origen | Origen de recolección |
| Peso neto | Kilogramos netos del pesaje |
| Operador | Usuario que registró el pesaje |
| Estado | EN PREDIO o CERRADO |
| Editado | Indicador si el pesaje fue modificado después del registro original |

---

## Cómo filtrar los pesajes

Encima de la tabla hay un panel de filtros. Podés combinar cualquiera de estos criterios:

| Filtro | Opciones |
|--------|----------|
| Rango de fechas | Fecha desde / Fecha hasta |
| Patente o número interno | Texto libre |
| Tipo de servicio | Lista de todos los servicios activos |
| Origen | Lista de todos los orígenes activos |
| Operador | Lista de todos los usuarios operadores |
| Estado | EN PREDIO / CERRADO / Todos |
| Con alerta de peso | Solo los pesajes que generaron aviso naranja |
| Editados | Solo los pesajes que fueron modificados |

Los filtros se aplican en tiempo real. Para limpiar todos los filtros, usá el botón **Limpiar filtros**.

---

## Cómo ver el detalle de un pesaje

Hacé clic en cualquier fila para abrir el panel de detalle. Muestra:

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

### Cuándo intervenir en estados EN PREDIO

Si hay pesajes con estado EN PREDIO que son del día anterior o de hace muchas horas, probablemente el operador se olvidó de registrar el egreso. En ese caso podés editar el pesaje y completar la hora de salida manualmente.

---

## Alerta de peso

Los pesajes que generaron un aviso naranja al momento del registro aparecen marcados en la tabla con un indicador especial. Esto significa que el peso bruto ingresado estaba fuera del rango habitual para ese tipo de vehículo.

El aviso no bloqueó el guardado — el operador decidió que el peso era correcto. Pero si querés revisarlo, podés filtrarlo con el filtro **Con alerta de peso** y revisarlos en conjunto.

---

## Exportar el log de pesajes

Desde esta pantalla podés exportar la vista filtrada actual:
- **Excel** — todos los campos de cada pesaje, útil para análisis ad hoc
- Los filtros aplicados en pantalla se respetan en la exportación

Para reportes formales (mensuales o trimestrales) usá el módulo de Reportes, que genera documentos con formato definido para entregar al municipio.

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

**¿Puedo ordenar la tabla por cualquier columna?**
Sí. Hacé clic en el encabezado de cualquier columna para ordenar ascendente o descendente.

---

*Documento generado: 12/05/2026 | Versión: 1.0*
