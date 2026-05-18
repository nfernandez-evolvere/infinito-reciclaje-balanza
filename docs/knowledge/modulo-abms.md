# Módulo de gestión de padrones (ABMs)
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador (Nacho)
**Cuándo usarlo:** Referencia de cómo gestionar vehículos, zonas, servicios, tipos de vehículo y usuarios

---

## Para qué sirve este módulo

Los padrones son los datos maestros del sistema: la lista de camiones, las zonas de recolección, los tipos de servicio, los tipos de vehículo y los usuarios. Sin estos datos cargados, los operadores no pueden registrar pesajes.

Este módulo te permite agregar, editar, activar y desactivar registros en cada uno de esos padrones.

---

## Cómo funciona cada padrón

Todos los padrones siguen el mismo patrón de pantalla:

1. **Tabla de registros** — todos los registros existentes, activos e inactivos
2. **Botón Agregar** — abre el formulario para crear un registro nuevo
3. **Editar** — abre el formulario pre-llenado con los datos actuales del registro
4. **Desactivar / Activar** — cambia el estado del registro con confirmación previa

La baja es siempre **lógica**: los registros nunca se borran. Si un camión deja de operar, se desactiva — sus pesajes históricos se conservan y siguen apareciendo en los reportes.

---

## Padrón de tipos de vehículo

**Ruta:** Transporte → Tipos de vehículo

Los tipos de vehículo definen los rangos de **peso bruto** esperados para cada categoría de camión (vehículo + carga completa). El sistema usa estos rangos para detectar pesajes anómalos y alertar al operador cuando el peso registrado queda fuera de lo habitual.

---

### Para qué se usa este padrón

Cada vehículo del sistema tiene asignado un tipo. Cuando el operador registra un pesaje, el sistema compara el peso bruto contra el rango del tipo del vehículo. Si el valor está fuera de rango, aparece una advertencia — pero el pesaje se puede guardar igual.

Sin tipos de vehículo cargados con rangos correctos, el sistema no puede detectar anomalías.

---

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre del tipo (ej: Compactador, Volcador) | Sí |
| Peso bruto mínimo (kg) | Lo mínimo esperado en la balanza con este tipo de camión cargado | Sí |
| Peso bruto máximo (kg) | Lo máximo esperado incluyendo vehículo vacío más la carga habitual | Sí |

> **Importante:** estos rangos son de **peso bruto** — lo que marca la balanza con el camión adentro. No confundir con la tara, que es el peso del camión vacío y se configura por separado en cada vehículo.

**Validaciones del formulario:**
- El nombre es obligatorio y no puede superar los 100 caracteres.
- El peso mínimo no puede ser negativo.
- El peso máximo debe ser mayor al peso mínimo — el sistema no acepta rangos invertidos.

---

### Rangos configurados en el sistema

| Tipo | Bruto mínimo | Bruto máximo | Para qué se usa |
|------|-------------|-------------|-----------------|
| Compactador | 10.000 kg | 26.500 kg | Recolección domiciliaria y voluminosos |
| Volcador | 13.000 kg | 30.000 kg | Barrido y servicios especiales |
| Volquete | 7.000 kg | 20.000 kg | Residuos de obras y centros de transferencia |
| Particular | 1.000 kg | 5.000 kg | Vehículos livianos de monitoreo o traslado |

Estos valores son una referencia — cada municipio puede necesitar ajustarlos según su flota real.

---

### Cómo crear un tipo nuevo

1. Ir a Transporte → Tipos de vehículo.
2. Hacer clic en **Nuevo tipo**.
3. Completar el nombre y los rangos de peso.
4. Guardar.

El tipo queda disponible de inmediato para asignarlo a vehículos.

---

### Cómo editar un tipo existente

1. En la tabla, abrir el menú de acciones (⋯) del tipo a modificar.
2. Seleccionar **Editar**.
3. Modificar los campos necesarios.
4. Guardar.

Los cambios en los rangos afectan la detección de anomalías **a partir del momento del cambio**. Los pesajes ya registrados no se recalculan.

---

### Cómo desactivar un tipo

Un tipo desactivado no desaparece del sistema — los vehículos que lo tienen asignado lo conservan, y los pesajes históricos siguen vinculados a él. Solo deja de estar disponible para asignar a vehículos nuevos.

1. Abrir el menú de acciones (⋯) del tipo.
2. Seleccionar **Desactivar**.
3. Confirmar en el modal.

Para volver a activarlo, repetir el proceso y seleccionar **Activar**.

---

### Cuándo desactivar vs. cuándo eliminar

**Desactivar:** cuando el tipo ya no se usa pero puede volver a necesitarse, o cuando hay vehículos que lo tienen asignado. Es la acción recomendada en casi todos los casos.

**Eliminar:** solo si el tipo fue creado por error y nunca se asignó a ningún vehículo. Si el tipo tiene vehículos asignados, el sistema no permite eliminarlo y muestra un mensaje explicando que hay que reasignar los vehículos primero.

---

### Cómo funciona la detección de anomalías

Cuando el operador registra un pesaje y el peso bruto queda fuera del rango del tipo del vehículo, el sistema:
1. Muestra una advertencia visual en el formulario de pesaje.
2. Indica el rango esperado para ese tipo.
3. Permite guardar el pesaje de todos modos.

La advertencia queda registrada en el pesaje. El dashboard y los reportes de alertas muestran estos casos para que el admin pueda revisarlos.

Los rangos **no bloquean** el guardado — son informativos. El criterio final es siempre del operador.

---

### Relación con el padrón de vehículos

Cada vehículo tiene un **tipo de vehículo** asignado. Sin un tipo asignado, el vehículo no puede guardarse en el sistema. Por eso conviene cargar todos los tipos antes de empezar a cargar los vehículos.

Si se necesita cambiar el tipo de un vehículo (por ejemplo, un camión fue reclasificado), hacerlo desde el padrón de Vehículos, no desde este padrón.

---

### Preguntas frecuentes sobre tipos de vehículo

**¿Qué pasa si elimino un tipo que tiene vehículos asignados?**
El sistema no lo permite. Aparece un mensaje indicando que el tipo tiene vehículos asignados y que hay que reasignarlos o desactivar el tipo en su lugar.

**¿Los rangos de peso bloquean el pesaje del operador?**
No. Son informativos. El operador ve una advertencia pero puede guardar el pesaje con cualquier valor.

**¿Puedo cambiar los rangos de un tipo que ya tiene pesajes registrados?**
Sí. El cambio afecta solo los pesajes futuros. Los pesajes ya registrados conservan el estado de anomalía que tenían al momento de guardarse.

**¿Cuántos tipos de vehículo puedo tener?**
No hay límite. Podés crear tantos como necesite la flota.

**¿Qué nombre le pongo a un tipo nuevo?**
Un nombre que identifique claramente la categoría del vehículo. Ejemplos: Compactador, Volcador, Volquete, Camión caja. Evitar nombres genéricos como "Tipo 1" — dificultan la interpretación en los reportes.

**¿Puedo tener dos tipos con el mismo nombre?**
El sistema lo permite técnicamente, pero no es recomendable — generaría confusión al asignar vehículos y al leer reportes.

**¿Un tipo inactivo sigue apareciendo en los pesajes del operador?**
No en nuevos pesajes. Pero si un vehículo tenía ese tipo asignado antes de desactivarlo, el tipo sigue visible en el historial de pesajes ya registrados.

---

## Padrón de vehículos

**Ruta:** Transporte → Vehículos

El padrón de vehículos registra todos los camiones habilitados para operar en la balanza. Cada vehículo tiene su tara (peso vacío) cargada, que el sistema usa automáticamente para calcular el peso neto en cada pesaje.

---

### Para qué se usa este padrón

Sin vehículos cargados, los operadores no pueden registrar pesajes. Al ingresar una patente o número interno en la balanza, el sistema busca el vehículo en este padrón, carga su tara automáticamente y sugiere las observaciones del registro. Si el vehículo no está en el padrón o está inactivo, no aparece en los resultados.

La **tara** es el campo más crítico: un error en ese valor afecta el cálculo del peso neto en todos los pesajes futuros de ese vehículo.

---

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Patente | Patente oficial del vehículo, sin espacios ni guiones (ej: ABC123) | Sí |
| N.° interno | Código interno asignado por la Municipalidad (ej: 042) | Sí |
| Tipo de vehículo | Compactador, Volcador, Volquete u otro tipo definido en el padrón de tipos | Sí |
| Titular | Propietario o responsable del vehículo (ej: Municipalidad de Corrientes) | Sí |
| Tara (kg) | Peso del vehículo completamente vacío, en kilogramos enteros | Sí |
| Capacidad (kg) | Carga máxima teórica del vehículo. Informativo, no afecta los cálculos | No |
| Observaciones | Texto libre visible para el operador al seleccionar el vehículo. Se autocompleta en el formulario de pesaje | No |

> **Importante:** la tara se copia al pesaje en el momento del ingreso y no se actualiza si el padrón cambia después. Si la tara de un vehículo cambia (por una modificación estructural, por ejemplo), corregirla en el padrón afecta solo los pesajes futuros — los históricos conservan la tara original.

**Validaciones:**
- Patente y número interno son únicos en el sistema. No pueden existir dos vehículos con el mismo valor en ninguno de esos dos campos.
- La tara debe ser mayor a cero.
- La capacidad, si se carga, debe ser mayor a cero.

---

### Cómo crear un vehículo nuevo

1. Ir a Transporte → Vehículos.
2. Hacer clic en **Nuevo vehículo**.
3. Completar patente, número interno, tipo, titular y tara.
4. Opcionalmente, cargar capacidad y observaciones.
5. Guardar.

El vehículo queda disponible de inmediato en el autocompletado del operador.

---

### Cómo editar un vehículo existente

1. En la tabla, abrir el menú de acciones (⋯) del vehículo a modificar.
2. Seleccionar **Editar**.
3. Modificar los campos necesarios.
4. Guardar.

Si corregís la tara, los pesajes ya registrados no cambian. Solo los pesajes nuevos usan la tara actualizada.

---

### Cómo desactivar un vehículo

1. Abrir el menú de acciones (⋯) del vehículo.
2. Seleccionar **Desactivar**.
3. Confirmar en el modal.

Un vehículo desactivado no aparece en el autocompletado del operador. Sus pesajes históricos se conservan y siguen incluidos en los reportes. Para volver a activarlo, repetir el proceso y elegir **Activar**.

---

### Cuándo desactivar vs. cuándo eliminar

**Desactivar:** cuando el vehículo dejó de operar, está temporalmente fuera de servicio, o salió del padrón de la Municipalidad. Es la acción correcta en casi todos los casos. Un vehículo desactivado puede reactivarse si vuelve al servicio.

**Eliminar:** solo si el vehículo fue cargado por error y nunca se usó en ningún pesaje. Si el vehículo tiene pesajes registrados, el sistema no permite eliminarlo y muestra un mensaje indicando que hay que desactivarlo en su lugar.

---

### Relación con el padrón de tipos de vehículo

Cada vehículo tiene asignado un tipo (Compactador, Volcador, etc.). El tipo define los rangos de peso bruto esperados. Cuando el operador registra un pesaje, el sistema compara el peso bruto ingresado con los rangos del tipo del vehículo y muestra una advertencia si queda fuera de rango.

Si necesitás reclasificar un vehículo a otro tipo, editá el vehículo y cambiá el campo "Tipo de vehículo". El cambio afecta la detección de anomalías a partir del próximo pesaje; los históricos conservan el tipo que tenían al registrarse.

---

### Preguntas frecuentes sobre el padrón de vehículos

**¿Qué pasa si intento eliminar un vehículo con pesajes registrados?**
El sistema no lo permite. Aparece un mensaje indicando que el vehículo tiene pesajes y que hay que desactivarlo en su lugar. Los pesajes históricos se conservan intactos.

**¿Puedo tener dos vehículos con la misma patente?**
No. Patente y número interno son únicos. Si al guardar aparece un error, verificá que no exista ya ese vehículo en el sistema, activo o inactivo.

**¿Qué pasa si corrijo la tara de un vehículo?**
Los pesajes futuros usan la tara nueva. Los pesajes ya registrados conservan la tara que tenían al momento del ingreso — este comportamiento es intencional para preservar el historial.

**¿Los vehículos inactivos aparecen en los reportes?**
Sus datos históricos sí. Los reportes incluyen todos los pesajes registrados, independientemente del estado actual del vehículo. Lo que cambia es que el vehículo inactivo no aparece en el autocompletado del operador para nuevos pesajes.

**¿El campo observaciones es lo que ve el operador en la balanza?**
Sí. Al seleccionar un vehículo, el campo observaciones del padrón se autocompleta en el formulario de pesaje. El operador puede modificarlo antes de guardar, pero el cambio no se guarda en el padrón — solo queda en ese pesaje puntual.

**¿Qué es la capacidad y para qué sirve?**
Es la carga máxima teórica del vehículo. No se usa en ningún cálculo automático — es un dato informativo para el admin. Puede dejarse en blanco sin afectar el funcionamiento del sistema.

---

## Padrón de tipos de servicio

**Ruta:** Padrón → Tipos de servicio

Los tipos de servicio definen las categorías de operación disponibles en la balanza — Domiciliario, Barrido, Voluminoso, etc. Cada tipo puede tener un tipo de vehículo habitual sugerido, que el sistema usa como referencia en el formulario de pesaje.

---

### Para qué se usa este padrón

Cuando el operador registra un pesaje, elige el tipo de servicio del camión que ingresa. Este dato clasifica el pesaje y permite al admin ver los reportes separados por operación (cuántas toneladas de Domiciliario, cuántas de Barrido, etc.).

Además, los tipos de servicio se usan para configurar las zonas: cada zona tiene asignados los servicios que operan en ella. Sin un tipo de servicio cargado, no se puede completar esa configuración ni registrar pesajes del tipo correspondiente.

---

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre del tipo de servicio (ej: Domiciliario, Barrido) | Sí |
| Vehículo habitual | Tipo de vehículo que suele prestar este servicio. Informativo — no bloquea el pesaje si el operador usa otro tipo | No |

> **Importante:** el campo "Vehículo habitual" es una sugerencia, no una restricción. Si se desactiva o elimina el tipo de vehículo asignado, el servicio pierde esa sugerencia pero sigue activo y disponible para el operador.

**Validaciones:**
- El nombre es obligatorio y no puede superar los 100 caracteres.
- No pueden existir dos tipos de servicio con el mismo nombre.

---

### Tipos de servicio configurados en el sistema

| Servicio | Descripción |
|----------|-------------|
| Domiciliario | Recolección puerta a puerta en barrios residenciales |
| Voluminoso | Residuos de gran tamaño: muebles, electrodomésticos |
| Barrido | Residuos de limpieza de calles y espacios públicos |
| Servicios Especiales | Operativos puntuales, eventos, situaciones de emergencia |
| Centros de Transferencia | Traslados de residuos desde centros intermedios de transferencia |

---

### Cómo crear un tipo nuevo

1. Ir a Padrón → Tipos de servicio.
2. Hacer clic en **Nuevo tipo**.
3. Completar el nombre y, opcionalmente, elegir el vehículo habitual.
4. Guardar.

El tipo queda disponible de inmediato para asignarlo a zonas y para que el operador lo use en el formulario de pesaje.

---

### Cómo editar un tipo existente

1. En la tabla, abrir el menú de acciones (⋯) del tipo a modificar.
2. Seleccionar **Editar**.
3. Modificar los campos necesarios.
4. Guardar.

Los cambios toman efecto de inmediato en el formulario de pesaje. Los pesajes ya registrados conservan el nombre del servicio que tenían al momento del ingreso.

---

### Cómo desactivar un tipo

Un tipo desactivado no desaparece del sistema — los pesajes históricos siguen vinculados a él y siguen apareciendo en los reportes. Solo deja de estar disponible para asignar a zonas nuevas y para registrar nuevos pesajes.

1. Abrir el menú de acciones (⋯) del tipo.
2. Seleccionar **Desactivar**.
3. Confirmar en el modal.

Para volver a activarlo, repetir el proceso y seleccionar **Activar**.

---

### Cuándo desactivar vs. cuándo eliminar

**Desactivar:** cuando el tipo ya no se usa en la operación actual pero puede volver a necesitarse, o cuando tiene pesajes registrados. Es la acción recomendada en casi todos los casos.

**Eliminar:** solo si el tipo fue creado por error y nunca se usó en ningún pesaje. Si el tipo tiene pesajes registrados, el sistema no permite eliminarlo y muestra un mensaje indicando que hay que desactivarlo en su lugar. Las zonas que tenían asignado ese servicio pierden esa configuración automáticamente al eliminar.

---

### Cómo funciona la zona y el turno en el pesaje

Los turnos **no** se configuran a nivel de tipo de servicio, sino a nivel de **zona + servicio**. Esto significa que Domiciliario puede tener turno Diurna y Nocturna en Zona Norte, pero ningún turno en Zona Industrial.

La configuración se hace desde el padrón de Zonas: para cada zona, se define qué servicios operan en ella y, para cada uno, si aplican turnos.

---

### Relación con otros padrones

**Con zonas:** cada zona tiene asignados uno o más tipos de servicio. El operador primero elige el servicio, y luego el sistema filtra las zonas disponibles según esa combinación. Si un servicio no está asignado a ninguna zona, el operador no puede usarlo en el formulario de pesaje.

**Con tipos de vehículo:** el campo "Vehículo habitual" es solo una referencia. No afecta qué vehículo puede ingresar — cualquier camión activo puede registrar un pesaje de cualquier servicio.

---

### Preguntas frecuentes sobre tipos de servicio

**¿Qué pasa si elimino un tipo que tiene pesajes registrados?**
El sistema no lo permite. Aparece un mensaje indicando que el tipo tiene pesajes y que hay que desactivarlo en su lugar. Los pesajes históricos se conservan intactos.

**¿Los cambios en el nombre del servicio afectan los pesajes ya registrados?**
No. Los pesajes existentes conservan el nombre que tenían al registrarse. El cambio afecta solo los pesajes nuevos.

**¿Puedo tener dos tipos con el mismo nombre?**
No. El sistema no permite duplicados de nombre. Si al guardar aparece un error, verificá que no exista ya ese tipo de servicio activo o inactivo.

**¿Si desactivo un tipo, las zonas que lo tienen asignado lo pierden?**
No se pierden automáticamente. Las zonas conservan la configuración existente, pero el tipo desactivado no aparece como opción para asignar a nuevas zonas. Los pesajes futuros tampoco pueden registrarse con ese tipo.

**¿Qué pasa si elimino el tipo de vehículo asignado como "habitual" de un servicio?**
El servicio pierde esa sugerencia (el campo queda vacío) pero sigue activo y operativo. No hay impacto en la operación.

**¿Cuántos tipos de servicio puedo tener?**
No hay límite. Podés crear tantos como necesite la operación.

---

## Padrón de zonas

**Ruta:** Padrón → Zonas

Las zonas son las áreas geográficas de recolección. Se usan para agrupar pesajes en los reportes y para calcular indicadores de densidad y per cápita.

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre de la zona (ej: Norte, Costanera) | Sí |
| Hectáreas | Superficie de la zona en hectáreas | No |
| Cantidad de barrios | Barrios que componen la zona | No |

### Servicios asignados

Después de crear la zona, asignale uno o más tipos de servicio. Para cada asignación podés configurar:

| Campo | Descripción |
|-------|-------------|
| Tipo de servicio | Cuál servicio opera en esta zona |
| Turnos | Si el servicio opera en turnos: **Diurna**, **Nocturna**, ambos, o ninguno |
| Horarios de recorrido | Optativo: días y franjas horarias del recorrido |

**Cómo configurar los turnos:** usá el switch "Opera con turnos". Si está apagado, el operador no ve selector de turno para esa combinación. Si está encendido, podés activar Diurna, Nocturna o ambas.

**Cómo configurar los horarios:** seleccioná los días activos (chips Lun–Dom) y cargá las franjas horarias para cada día. Podés agregar más de una franja por día.

Esta configuración determina qué le aparece al operador en el formulario de pesaje: elige el servicio → ve las zonas que tienen ese servicio asignado → si la combinación tiene turnos, debe elegir turno.

### Sobre los datos geográficos

Los campos hectáreas y barrios son opcionales al momento de la carga. Los reportes de densidad (kg por hectárea) quedarán en cero hasta que estén cargados.

---

## Padrón de usuarios

**Ruta:** Sistema → Usuarios

Los usuarios son las personas que acceden al sistema. Hay dos roles:
- **Operador** — accede solo a las pantallas de pesaje e historial del turno
- **Admin** — accede a todas las pantallas, incluyendo dashboard, pesajes, padrones y reportes

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre de usuario | Identificador para el login (sin espacios) | Sí |
| Nombre completo | Nombre real del usuario | Sí |
| Rol | Operador o Admin | Sí |
| Contraseña inicial | Se entrega al usuario para que la cambie | Sí |

### Reglas clave

- Un usuario por persona. Nunca compartir credenciales: si dos operadores usan el mismo usuario, no se puede saber quién registró cada pesaje.
- Para crear un admin nuevo, elegir rol **Admin** en el formulario.

---

## Cómo editar un registro

1. Encontrá el registro en la tabla.
2. Hacé clic en **Editar** en la fila correspondiente.
3. Modificá los campos necesarios.
4. Guardá.

Los cambios toman efecto inmediatamente.

---

## Cómo desactivar un registro

1. Encontrá el registro en la tabla.
2. Hacé clic en **Desactivar** en la fila correspondiente.
3. Confirmá en el modal de confirmación.

El registro queda marcado como inactivo y deja de aparecer en los autocompletados del operador. Los registros inactivos siguen visibles en la tabla.

---

## Preguntas frecuentes

**¿Puedo borrar un registro en lugar de desactivarlo?**
No. El sistema no permite eliminar registros. La baja lógica (desactivar) preserva la integridad de los datos históricos.

**¿Qué pasa si edito la tara de un vehículo?**
Los pesajes futuros usan la nueva tara. Los pesajes ya registrados conservan la tara original.

**¿Puedo agregar un tipo de servicio nuevo?**
Sí. Ir a Padrón → Tipos de servicio y usar el botón Agregar.

**¿Qué pasa si desactivo una zona que tiene pesajes activos?**
Los pesajes "en predio" no se ven afectados. La desactivación solo impide que la zona aparezca en nuevos pesajes.

**¿Puedo cambiar el rol de un usuario (de operador a admin)?**
Sí, editando el usuario. El cambio de rol toma efecto en el próximo inicio de sesión.

**¿Puedo modificar los turnos u horarios de un servicio ya asignado a una zona?**
Sí. En el padrón de Zonas, en la fila del servicio asignado, usá el botón **Editar** para cambiar turnos y horarios.

---

*Documento actualizado: 18/05/2026 | Versión: 1.2*
