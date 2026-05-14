# Módulo de gestión de padrones (ABMs)
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador (Nacho)
**Cuándo usarlo:** Referencia de cómo gestionar vehículos, orígenes, servicios, tipos de vehículo y usuarios

---

## Para qué sirve este módulo

Los padrones son los datos maestros del sistema: la lista de camiones, los orígenes de recolección, los tipos de servicio, los tipos de vehículo y los usuarios. Sin estos datos cargados, los operadores no pueden registrar pesajes.

Este módulo te permite agregar, editar y desactivar registros en cada uno de esos padrones.

---

## Cómo funciona cada padrón

Todos los padrones siguen el mismo patrón de pantalla:

1. **Tabla de registros** — todos los registros existentes, activos e inactivos
2. **Barra de búsqueda** — filtra los registros mientras escribís
3. **Botón Agregar** — abre el formulario para crear un registro nuevo
4. **Acciones por fila** — cada registro tiene dos acciones: **Editar** y **Desactivar**

La baja es siempre **lógica**: los registros nunca se borran. Si un camión deja de operar, se desactiva — sus pesajes históricos se conservan y siguen apareciendo en los reportes.

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
| Capacidad | Peso máximo de carga en kg | No |
| Observaciones | Notas relevantes del vehículo | No |

### Reglas clave

- **La tara es crítica.** Se copia al pesaje en el momento del ingreso. Si la tara cambia después (por ejemplo, el camión tiene una modificación), los pesajes futuros usan la nueva tara; los históricos conservan la tara que tenían al momento del registro.
- **Patente sin espacios ni guiones.** El operador puede buscar con o sin guión, pero la patente debe cargarse en formato limpio para evitar errores de búsqueda.
- **Número interno único.** No pueden existir dos vehículos con el mismo número interno.

### Cuándo desactivar un vehículo

Cuando un camión deja de operar o sale del padrón de la Municipalidad. Una vez desactivado:
- No aparece en el autocompletado del operador
- Sus pesajes históricos se conservan
- Los reportes siguen incluyendo sus datos históricos

Para reactivarlo si el camión vuelve a operar, usá la acción **Activar** en la tabla (visible en los registros inactivos).

---

## Padrón de orígenes

**Ruta:** Orígenes → Orígenes

Los orígenes son las áreas geográficas de recolección. Se usan para agrupar pesajes en los reportes y para calcular indicadores de densidad y per cápita.

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre del origen (ej: Origen Norte, Barrio Belgrano) | Sí |
| Hectáreas | Superficie del origen en hectáreas | No |
| Cantidad de barrios | Barrios que componen el origen | No |
| Habitantes | Población estimada del origen | No |

### Servicios asignados

Después de crear el origen, podés asignarle uno o más tipos de servicio. Para cada asignación se define:

| Campo | Descripción |
|-------|-------------|
| Tipo de servicio | Cuál servicio opera en este origen |
| Turno | Si aplica: Diurna, Nocturna, ambos, o ninguno |
| Horario de recorrido | Informativo: hora de inicio y fin del recorrido |

Esto determina qué le aparece al operador en el formulario de pesaje: elige el servicio → ve los orígenes que tienen ese servicio asignado → si el origen tiene turno configurado para ese servicio, debe elegir turno.

### Sobre los datos demográficos

Los campos hectáreas, barrios y habitantes son opcionales al momento de la carga. Si no los tenés disponibles, podés dejarlos en cero y completarlos después. Los reportes de densidad (kg por hectárea) y per cápita (kg por habitante) quedarán en cero o no calculados hasta que estén cargados.

---

## Padrón de tipos de servicio

**Ruta:** Servicios → Tipos de servicio

Los tipos de servicio definen el nombre del servicio, el tipo de vehículo habitual y, para los servicios con horario fijo, el turno de operación. Los orígenes que pertenecen a cada servicio se cargan en el padrón de Orígenes.

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre del tipo de servicio | Sí |
| Tipo de vehículo sugerido | Tipo de vehículo que suele prestar este servicio | No |

### Tipos de servicio del sistema

| Servicio | Descripción |
|----------|-------------|
| Domiciliario | Recolección puerta a puerta en barrios residenciales |
| Voluminoso | Residuos de gran tamaño: muebles, electrodomésticos |
| Barrido | Residuos de limpieza de calles y espacios públicos |
| Servicios Especiales | Operativos puntuales, eventos, situaciones de emergencia |
| Centros de Transferencia | Traslados de residuos desde centros intermedios de transferencia |

### Cómo funciona el origen y el turno en el pesaje

Los turnos no se configuran a nivel de tipo de servicio, sino a nivel de **origen + servicio**. Esto significa que Domiciliario puede tener turno Diurna y Nocturna en Origen Norte, pero ningún turno en Origen Industrial.

La configuración se hace desde el padrón de Orígenes: para cada origen, se define qué servicios operan en él y, para cada uno, si aplican turnos.

Al registrar un pesaje: el operador elige el servicio → el sistema muestra los orígenes que tienen ese servicio asignado → al elegir un origen, si esa combinación tiene turnos configurados, aparece el selector de turno (obligatorio).

---

## Padrón de tipos de vehículo

**Ruta:** Transporte → Tipos de vehículo

Los tipos de vehículo definen los rangos de peso válidos. El sistema usa estos rangos para alertar al operador cuando el peso ingresado parece inusual para ese tipo de camión.

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| Nombre | Nombre del tipo (ej: Compactador) | Sí |
| Peso mínimo (kg) | Peso bruto mínimo esperado para este tipo | Sí |
| Peso máximo (kg) | Peso bruto máximo esperado para este tipo | Sí |

### Rangos configurados en el sistema

| Tipo | Peso mínimo | Peso máximo |
|------|-------------|-------------|
| Compactador | 10.000 kg | 26.500 kg |
| Volcador | 13.000 kg | 30.000 kg |
| Volquete | 7.000 kg | 20.000 kg |
| Particular | 1.000 kg | 5.000 kg |

Los rangos se pueden ajustar si la flota cambia o si los valores actuales generan alertas falsas con demasiada frecuencia.

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

### Acciones disponibles por usuario

- **Editar** — modificar cualquier campo excepto la contraseña
- **Resetear contraseña** — generar una nueva contraseña temporal para el usuario
- **Desactivar** — el usuario deja de poder ingresar al sistema; sus registros históricos se conservan

### Reglas clave

- Un usuario por persona. Nunca compartir credenciales: si dos operadores usan el mismo usuario, no se puede saber quién registró cada pesaje.
- Para crear un admin nuevo, elegir rol **Admin** en el formulario.

---

## Cómo editar un registro

1. Encontrá el registro en la tabla (usá la barra de búsqueda si hay muchos).
2. Hacé clic en **Editar** en la fila correspondiente.
3. Modificá los campos necesarios.
4. Guardá.

Los cambios toman efecto inmediatamente.

---

## Cómo desactivar un registro

1. Encontrá el registro en la tabla.
2. Hacé clic en **Desactivar** en la fila correspondiente.
3. Confirmá la acción.

El registro queda marcado como inactivo y deja de aparecer en los autocompletados del operador. Los registros inactivos siguen visibles en la tabla con una indicación visual diferente.

---

## Filtros y búsqueda en las tablas

Cada tabla tiene una barra de búsqueda que filtra en tiempo real. Podés buscar por cualquier campo visible: patente, número interno, nombre de origen, nombre de usuario, etc.

En la tabla de vehículos también podés filtrar por:
- Estado (Activos / Inactivos / Todos)
- Tipo de vehículo
- Titular

---

## Preguntas frecuentes

**¿Puedo borrar un registro en lugar de desactivarlo?**
No. El sistema no permite eliminar registros. Esto es intencional: si se pudiera borrar un vehículo, los pesajes históricos de ese vehículo quedarían sin referencia. La baja lógica (desactivar) preserva la integridad de los datos históricos.

**¿Qué pasa si edito la tara de un vehículo?**
Los pesajes futuros usan la nueva tara. Los pesajes ya registrados conservan la tara que tenían al momento del ingreso — ese valor se copió al registro del pesaje y no cambia con ediciones posteriores al padrón.

**¿Puedo agregar un tipo de servicio nuevo?**
Sí. Ir a Padrones → Tipos de servicio y usar el botón Agregar. El tipo de servicio nuevo aparece disponible para los operadores inmediatamente después de guardarlo.

**¿Qué pasa si desactivo un origen que tiene pesajes activos?**
Los pesajes "en predio" (con estado EN PREDIO) que pertenecen a ese origen no se ven afectados. La desactivación solo impide que el origen aparezca como sugerencia en nuevos pesajes.

**¿Puedo cambiar el rol de un usuario (de operador a admin)?**
Sí, editando el usuario. El cambio de rol toma efecto en el próximo inicio de sesión del usuario.

**¿Cómo sé si un usuario inició sesión recientemente?**
La tabla de usuarios no muestra el último acceso en esta versión del sistema.

---

*Documento generado: 12/05/2026 | Versión: 1.0*
