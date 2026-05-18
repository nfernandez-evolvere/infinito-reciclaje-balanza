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

Los tipos de vehículo definen los rangos de **peso bruto** esperados (vehículo + carga completa). El sistema usa estos rangos para detectar pesajes anómalos.

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre del tipo (ej: Compactador) | Sí |
| Peso bruto mínimo (kg) | Lo mínimo que debería marcar la balanza con este tipo cargado | Sí |
| Peso bruto máximo (kg) | Lo máximo esperado, incluyendo vehículo vacío más la carga | Sí |

> **Importante:** estos rangos son de peso bruto (lo que marca la balanza), no de tara. La tara de cada vehículo se configura por separado en el padrón de Vehículos.

### Rangos configurados en el sistema

| Tipo | Bruto mínimo | Bruto máximo |
|------|-------------|-------------|
| Compactador | 10.000 kg | 26.500 kg |
| Volcador | 13.000 kg | 30.000 kg |
| Volquete | 7.000 kg | 20.000 kg |
| Particular | 1.000 kg | 5.000 kg |

Los rangos son informativos — si el peso registrado queda fuera de rango, el sistema lo avisa como anomalía pero el operador puede guardar el pesaje igual.

---

## Padrón de vehículos

**Ruta:** Transporte → Vehículos

Los vehículos son el centro del sistema. Cada camión que ingresa al predio debe estar cargado acá con su tara correcta — ese valor se usa para calcular los kg netos en cada pesaje.

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Patente | Patente oficial del vehículo (sin espacios) | Sí |
| Número interno | Número asignado por la Municipalidad | Sí |
| Tara | Peso del vehículo vacío en kg | Sí |
| Tipo de vehículo | Compactador, Volcador, Volquete o Particular | Sí |
| Titular | Municipalidad de Corrientes u otro titular | Sí |

### Reglas clave

- **La tara es crítica.** Se copia al pesaje en el momento del ingreso. Si la tara cambia después (por ejemplo, el camión tiene una modificación), los pesajes futuros usan la nueva tara; los históricos conservan la tara que tenían al momento del registro.
- **Patente sin espacios ni guiones.** El operador puede buscar con o sin guión, pero la patente debe cargarse en formato limpio para evitar errores de búsqueda.
- **Número interno único.** No pueden existir dos vehículos con el mismo número interno.

### Cuándo desactivar un vehículo

Cuando un camión deja de operar o sale del padrón de la Municipalidad. Una vez desactivado:
- No aparece en el autocompletado del operador
- Sus pesajes históricos se conservan
- Los reportes siguen incluyendo sus datos históricos

Para reactivarlo, usá la acción **Activar** en la tabla.

---

## Padrón de tipos de servicio

**Ruta:** Padrón → Tipos de servicio

Los tipos de servicio definen el nombre del servicio y el tipo de vehículo habitual. Las zonas donde opera cada servicio se configuran en el padrón de Zonas.

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre del tipo de servicio | Sí |
| Vehículo habitual | Tipo de vehículo que suele prestar este servicio | Si |

### Tipos de servicio del sistema

| Servicio | Descripción |
|----------|-------------|
| Domiciliario | Recolección puerta a puerta en barrios residenciales |
| Voluminoso | Residuos de gran tamaño: muebles, electrodomésticos |
| Barrido | Residuos de limpieza de calles y espacios públicos |
| Servicios Especiales | Operativos puntuales, eventos, situaciones de emergencia |
| Centros de Transferencia | Traslados de residuos desde centros intermedios de transferencia |

### Cómo funciona la zona y el turno en el pesaje

Los turnos **no** se configuran a nivel de tipo de servicio, sino a nivel de **zona + servicio**. Esto significa que Domiciliario puede tener turno Diurna y Nocturna en Zona Norte, pero ningún turno en Zona Industrial.

La configuración se hace desde el padrón de Zonas: para cada zona, se define qué servicios operan en ella y, para cada uno, si aplican turnos.

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

*Documento actualizado: 14/05/2026 | Versión: 1.1*
