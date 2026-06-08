# Modelo de datos
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Motor:** SQL Server (driver `sqlsrv`)
**ORM:** Laravel Eloquent
**Versión:** 1.1 — 14/05/2026

---

## Principios de diseño

| Principio | Aplicación |
|-----------|------------|
| **Baja lógica** | Ninguna entidad se elimina físicamente. `activo bit` en todas las tablas maestras. |
| **Auditoría inmutable** | `pesajes_log` es append-only. Nunca se actualiza ni elimina un registro de log. |
| **Desnormalización controlada** | `pesajes.peso_tara_kg` copia la tara del padrón al momento del ingreso para preservar el historial si el padrón cambia. |
| **Enums como CHECK** | SQL Server no tiene tipo ENUM. Se usan columnas `nvarchar` con `CHECK` constraint. |
| **IDs bigint** | Todas las PKs son `bigint IDENTITY` — preparado para volumen alto. |
| **Unicode** | Columnas de texto en `nvarchar` — soporte completo para español y caracteres especiales. |
| **Timestamps con precisión** | `datetime2(0)` en lugar de `datetime` — mayor rango, precisión a segundos, menor almacenamiento. |
| **Pesos en enteros** | Todos los pesos en `int` (kilogramos enteros). Sin `decimal` ni `float` — la balanza opera en kg completos. |

---

## Entidades y definiciones completas

### `users`

Usuarios del sistema. Dos roles: `operador` (acceso a Balanza) y `admin` (acceso completo).

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `name` | `nvarchar(255)` | NO | — | — | Nombre completo |
| `email` | `nvarchar(255)` | NO | — | UNIQUE | Usado como nombre de usuario para el login |
| `email_verified_at` | `datetime2(0)` | SÍ | NULL | — | No usado activamente en Etapa 1 |
| `password` | `nvarchar(255)` | NO | — | — | Hash bcrypt |
| `role` | `nvarchar(10)` | NO | `'operador'` | CHECK IN (`'operador'`, `'admin'`) | Determina el perfil de acceso |
| `onboarding_visto` | `bit` | NO | `0` | — | `1` después de cerrar el modal de bienvenida |
| `activo` | `bit` | NO | `1` | — | `0` = desactivado, no puede iniciar sesión |
| `remember_token` | `nvarchar(100)` | SÍ | NULL | — | Breeze |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Índices:**
```sql
PK   id                          -- clustered
UQ   email                       -- login único
IX   role                        -- SetupChecklistService: COUNT WHERE role = 'operador'
IX   activo                      -- login: verificar usuario activo antes de autenticar
```

**Nota:** `activo` NO usa `SoftDeletes` de Laravel (que genera `deleted_at`). La columna `activo` es intencional — necesitamos mostrar usuarios inactivos en el ABM.

---

### `tipos_vehiculo`

Catálogo de tipos de camión con rangos de peso. Define los umbrales de `alerta_peso` en pesajes.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `nombre` | `nvarchar(100)` | NO | — | UNIQUE | Ej: `'Compactador'` |
| `peso_min_kg` | `int` | NO | — | CHECK > 0 | Límite inferior para alerta de peso |
| `peso_max_kg` | `int` | NO | — | CHECK > `peso_min_kg` | Límite superior para alerta de peso |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**CHECK constraint compuesta:**
```sql
CONSTRAINT CK_tipos_vehiculo_rango CHECK (peso_max_kg > peso_min_kg)
```

**Índices:**
```sql
PK   id
UQ   nombre
IX   activo     -- autocomplete y selects del ABM
```

**Datos iniciales (seeder):**
| nombre | peso_min_kg | peso_max_kg |
|--------|-------------|-------------|
| Compactador | 10000 | 26500 |
| Volcador | 13000 | 30000 |
| Volquete | 7000 | 20000 |
| Particular | 1000 | 5000 |

---

### `tipos_servicio`

Tipos de recolección disponibles. Cada servicio puede tener **varios tipos de vehículo sugeridos** (relación N:M vía `tipo_servicio_tipo_vehiculo`). La relación con orígenes y turnos se modela en `zona_servicios` y `zona_servicio_turnos` (también N:M).

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `nombre` | `nvarchar(100)` | NO | — | UNIQUE | Ej: `'Domiciliario'` |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Índices:**
```sql
PK   id
UQ   nombre
IX   activo
```

> **Histórico:** existió una FK única `tipo_vehiculo_sugerido_id` (un solo vehículo sugerido por servicio). Se dio de baja al migrar a N:M — un servicio sugiere varios tipos de vehículo. Ver `tipo_servicio_tipo_vehiculo`.

---

### `tipo_servicio_tipo_vehiculo`

Tabla junction entre tipos de servicio y tipos de vehículo. Define qué tipos de vehículo se sugieren para cada servicio (hint en la balanza: si el vehículo elegido no es uno de los sugeridos, se muestra una advertencia suave, no bloqueante).

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `tipo_servicio_id` | `bigint` | NO | — | PK compuesta, FK → `tipos_servicio.id` | — |
| `tipo_vehiculo_id` | `bigint` | NO | — | PK compuesta, FK → `tipos_vehiculo.id` | — |

**PK compuesta:** `(tipo_servicio_id, tipo_vehiculo_id)`.

**FK behavior:**
- `tipo_servicio_id`: `ON DELETE CASCADE` — al eliminar un servicio, se eliminan sus vínculos.
- `tipo_vehiculo_id`: `ON DELETE CASCADE` — al eliminar un tipo de vehículo, se elimina del set de sugeridos de cada servicio (el servicio sigue existiendo).

**Decisión — relación N:M vía `zona_servicios`:**
Un origen puede operar bajo varios servicios y un servicio puede operar en varios orígenes. La asociación se modela en `zona_servicios`. Al registrar un pesaje, el formulario filtra los orígenes disponibles según el servicio elegido — el operador selecciona el origen de la lista filtrada. Los turnos aplicables se determinan por la combinación origen+servicio en `zona_servicio_turnos`.

---

### `zonas`

Entidad geográfica pura. No tiene relación directa con tipos de servicio — la asociación se modela en `zona_servicios`. Un origen puede estar asociado a varios servicios con distintas configuraciones de turno.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `nombre` | `nvarchar(150)` | NO | — | UNIQUE | Ej: `'Origen Norte'` |
| `hectareas` | `decimal(10,2)` | SÍ | NULL | CHECK >= 0 | NULL = dato no disponible. 0 = verificado como cero. |
| `barrios` | `int` | SÍ | NULL | CHECK >= 0 | — |
| `habitantes` | `int` | SÍ | NULL | CHECK >= 0 | NULL = dato no disponible. 0 = verificado como cero. Afecta cálculo per cápita en reportes. |
| `geojson` | `nvarchar(max)` | SÍ | NULL | — | Geometría del área de la zona como `FeatureCollection` GeoJSON (un polígono). NULL = zona sin área dibujada. Se dibuja/edita con Leaflet + Geoman en el ABM. |
| `centro_lat` | `decimal(10,7)` | SÍ | NULL | — | Latitud del centro del polígono (derivado en el cliente). Para centrar el mapa sin parsear la geometría. |
| `centro_lng` | `decimal(10,7)` | SÍ | NULL | — | Longitud del centro del polígono. |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Índices:**
```sql
PK   id
UQ   nombre
IX   activo    -- selects del formulario de pesaje
```

**Geometría (`geojson`):** se guarda el `FeatureCollection` serializado tal cual lo emite Leaflet, sin cast en el modelo (`Zona::$geojson` es `string|null`). Para consumirlo en PHP usar `json_decode($zona->geojson, true)`. A este volumen (un puñado de zonas por organización) no se usa el tipo `geography` ni índices espaciales — el choropleth se arma en el cliente. La asignación de zona a cada pesaje sigue siendo manual (`zona_id`); el polígono es para visualización (mapas de calor), no para point-in-polygon automático en Etapa 1.

**Semántica NULL vs 0:**
`hectareas = NULL` → dato no cargado, indicadores de densidad no se calculan.
`hectareas = 0` → origen verificado sin área (improbable en la operación real, pero válido).
El servicio de reportes debe distinguir ambos casos: NULL produce `null` en el resultado, 0 producería división por cero (manejar con `NULLIF`).

---

### `zona_servicios`

Tabla junction entre orígenes y tipos de servicio. Define qué servicios operan en cada origen. Si no existe fila para una combinación origen+servicio, ese origen no aparece como opción cuando el operador elige ese servicio. Los horarios detallados por día y franja se modelan en `zona_servicio_horarios`.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `zona_id` | `bigint` | NO | — | PK compuesta, FK → `zonas.id` | — |
| `tipo_servicio_id` | `bigint` | NO | — | PK compuesta, FK → `tipos_servicio.id` | — |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**PK compuesta:** `(zona_id, tipo_servicio_id)` — un origen puede tener el mismo servicio una sola vez.

**FK behavior:**
- `zona_id`: `ON DELETE CASCADE` — al desactivar/eliminar un origen, se eliminan sus asignaciones de servicio.
- `tipo_servicio_id`: `ON DELETE CASCADE` — al eliminar un tipo de servicio, se eliminan sus asignaciones de origen.

**Índices:**
```sql
PK   (zona_id, tipo_servicio_id)
IX   tipo_servicio_id    -- filtrado en Balanza: dado un servicio, ¿qué orígenes lo tienen?
     WHERE origen activo = 1   -- (join con origenes; filtered index en la tabla origenes)
```

---

### `zona_servicio_turnos`

Turnos disponibles para una combinación específica origen+servicio. Si no hay filas, el formulario de pesaje no muestra el campo turno para esa combinación. Si hay filas, el operador debe seleccionar uno obligatoriamente.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `zona_id` | `bigint` | NO | — | PK compuesta, FK → `zona_servicios` | — |
| `tipo_servicio_id` | `bigint` | NO | — | PK compuesta, FK → `zona_servicios` | — |
| `turno` | `nvarchar(10)` | NO | — | PK compuesta, CHECK IN (`'Diurna'`, `'Nocturna'`) | — |

**PK compuesta:** `(zona_id, tipo_servicio_id, turno)`.

**FK behavior:**
- `(zona_id, tipo_servicio_id)`: FK compuesta → `zona_servicios(zona_id, tipo_servicio_id)` `ON DELETE CASCADE`.

**Índices:**
```sql
PK   (zona_id, tipo_servicio_id, turno)   -- lookup directo: ¿qué turnos tiene este origen+servicio?
```

**Datos iniciales (seeder):**
| origen | tipo_servicio | turno |
|--------|---------------|-------|
| Origen Norte | Domiciliario | Diurna |
| Origen Norte | Domiciliario | Nocturna |
| Origen Sur | Domiciliario | Diurna |
| Origen Sur | Domiciliario | Nocturna |
| *(etc. según configuración real)* | | |

---

### `zona_servicio_horarios`

Franjas horarias de recorrido por día de la semana para cada combinación origen+servicio. Una combinación puede tener múltiples franjas por día (ej: Lunes 08:00–12:00, 14:00–18:00 y 20:00–02:00). Las franjas son informativas — no bloquean el registro de pesajes fuera de horario.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `zona_id` | `bigint` | NO | — | PK cuádruple, FK → `zona_servicios` | — |
| `tipo_servicio_id` | `bigint` | NO | — | PK cuádruple, FK → `zona_servicios` | — |
| `dia_semana` | `tinyint` | NO | — | PK cuádruple, CHECK IN (1,2,3,4,5,6,7) | 1=Lunes … 7=Domingo |
| `franja` | `tinyint` | NO | — | PK cuádruple, CHECK > 0 | Orden de la franja dentro del día (1, 2, 3…) |
| `hora_inicio` | `time(0)` | NO | — | — | — |
| `hora_fin` | `time(0)` | NO | — | — | Puede ser menor que `hora_inicio` cuando la franja cruza medianoche (ej: 20:00–02:00). |

**PK cuádruple:** `(zona_id, tipo_servicio_id, dia_semana, franja)`.

**FK behavior:**
- `(zona_id, tipo_servicio_id)`: FK compuesta → `zona_servicios(zona_id, tipo_servicio_id)` `ON DELETE CASCADE`.

**Índices:**
```sql
PK   (zona_id, tipo_servicio_id, dia_semana, franja)
IX   (zona_id, tipo_servicio_id, dia_semana)   -- listado rápido de franjas del día
```

---

### `vehiculos`

Padrón completo de vehículos. La tara de este padrón se copia a cada pesaje al momento del ingreso.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `patente` | `nvarchar(20)` | NO | — | UNIQUE | Sin espacios. Ej: `'ABC123'` |
| `numero_interno` | `nvarchar(20)` | NO | — | UNIQUE | Alfanumérico — algunos municipios usan códigos compuestos |
| `tara_kg` | `int` | NO | — | CHECK > 0 | Peso vacío del vehículo. **Campo crítico** — errores afectan todos los pesajes futuros. |
| `tipo_vehiculo_id` | `bigint` | NO | — | FK → `tipos_vehiculo.id` NOT NULL | Determina los rangos de alerta de peso |
| `titular` | `nvarchar(200)` | NO | — | — | Ej: `'Municipalidad de Corrientes'` |
| `capacidad_kg` | `int` | SÍ | NULL | CHECK > 0 | Carga máxima. Informativo, no usado en validaciones. |
| `observaciones` | `nvarchar(500)` | SÍ | NULL | — | Se autocompleta en el formulario de pesaje, editable por el operador. |
| `activo` | `bit` | NO | `1` | — | Inactivo = no aparece en autocompletado del operador |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**FK behavior:**
- `tipo_vehiculo_id`: `ON DELETE RESTRICT` — no se puede desactivar un tipo de vehículo si tiene vehículos asignados. Primero reasignar o desactivar los vehículos.

**Índices:**
```sql
PK   id
UQ   patente
UQ   numero_interno
IX   activo, tipo_vehiculo_id       -- autocomplete: solo activos
IX   tipo_vehiculo_id               -- JOIN al calcular alerta de peso
```

**API de autocompletado** (`GET /api/vehiculos/buscar?q=`):
Busca con `LIKE '%q%'` en `patente` y `numero_interno` donde `activo = 1`. Máximo 6 resultados, ordenados por `numero_interno ASC`.

---

### `vehiculos_log`

Audit trail de cambios del padrón de vehículos. Una fila por campo modificado. Hoy solo registra cambios de `tara_kg` (campo crítico), pero la tabla es genérica y admite auditar cualquier campo en el futuro. Append-only, como `pesajes_log`.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `vehiculo_id` | `bigint` | NO | — | FK → `vehiculos.id` | — |
| `campo` | `nvarchar(100)` | NO | — | — | Nombre del campo modificado. Ej: `'tara_kg'` |
| `valor_anterior` | `nvarchar(500)` | SÍ | NULL | — | Valor antes de la edición. |
| `valor_nuevo` | `nvarchar(500)` | SÍ | NULL | — | Valor después de la edición. |
| `motivo` | `nvarchar(500)` | NO | — | — | Motivo del cambio. Para `tara_kg` lleva el prefijo de intención: `'Corrección de dato mal cargado — …'` o `'Cambio real de tara — …'`. |
| `usuario_id` | `bigint` | NO | — | FK → `users.id` | Admin que hizo el cambio |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Semántica de la intención (corrección de tara):**
Al editar la tara de un vehículo que ya tiene pesajes, el admin elige entre:
- **Corrección de dato mal cargado** → se recalcula `peso_tara_kg` y `peso_neto_kg` en todos los pesajes **no cancelados** del vehículo, con una entrada en `pesajes_log` por campo modificado. Marca `pesajes.editado = 1`.
- **Cambio real de tara** → los pesajes anteriores no se modifican; solo los nuevos usan la tara nueva.

En ambos casos se registra la fila en `vehiculos_log`. La operación corre dentro de una transacción y es exclusiva de admins.

**Limitación conocida (Etapa 1):** una corrección retroactiva recalcula los netos históricos, pero **no** reemite ni ajusta reportes ya enviados por mail. Los PDF entregados quedan como snapshot del momento del envío; la discrepancia con los datos corregidos no se resuelve automáticamente.

**FK behavior:**
- `vehiculo_id`: `ON DELETE CASCADE`.
- `usuario_id`: `ON DELETE RESTRICT` — el log conserva la referencia al admin que actuó.

**Índices:**
```sql
PK   id
IX   vehiculo_id, created_at DESC    -- historial de cambios de un vehículo
```

---

### `pesajes`

Tabla operacional central. Cada fila es un camión que entró al predio. Se escribe una vez (al registrar el ingreso) y puede modificarse con auditoría.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `vehiculo_id` | `bigint` | NO | — | FK → `vehiculos.id` | — |
| `operador_id` | `bigint` | NO | — | FK → `users.id` | Usuario que registró el pesaje |
| `tipo_servicio_id` | `bigint` | NO | — | FK → `tipos_servicio.id` | — |
| `zona_id` | `bigint` | NO | — | FK → `zonas.id` | — |
| `turno` | `nvarchar(10)` | SÍ | NULL | CHECK IN (`'Diurna'`, `'Nocturna'`) | NULL cuando la combinación origen+servicio no tiene turnos. Obligatorio (validado en app) cuando `zona_servicio_turnos` tiene filas para esa combinación. |
| `peso_bruto_kg` | `int` | NO | — | CHECK > 0 | Peso que muestra la balanza al ingreso |
| `peso_tara_kg` | `int` | NO | — | CHECK > 0 | Copiado de `vehiculos.tara_kg` al crear. No se recalcula ante un cambio normal del padrón. **Excepción:** si el admin edita la tara del vehículo y declara que corrige un dato mal cargado, se recalcula retroactivamente en todos los pesajes no cancelados del vehículo (ver `vehiculos_log`). |
| `peso_neto_kg` | `int` | NO | — | — | Calculado en la capa de servicio: `peso_bruto_kg - peso_tara_kg`. Se actualiza si se edita `peso_bruto_kg`. |
| `alerta_peso` | `bit` | NO | `0` | — | `1` si `peso_bruto_kg` estaba fuera del rango de `tipos_vehiculo` al registrar |
| `observaciones` | `nvarchar(500)` | SÍ | NULL | — | Autocompleta del padrón, editable por el operador |
| `estado` | `nvarchar(10)` | NO | `'En predio'` | CHECK IN (`'En predio'`, `'Cerrado'`) | Máquina de estados: solo avanza, nunca retrocede |
| `hora_salida` | `datetime2(0)` | SÍ | NULL | — | NULL = en predio. Se completa al marcar egreso. |
| `bruto_salida_kg` | `int` | SÍ | NULL | CHECK > 0 | Peso de salida opcional. Solo trazabilidad, no afecta cálculos. |
| `editado` | `bit` | NO | `0` | — | `1` si al menos un campo fue modificado post-registro |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | Hora de entrada del camión (momento del registro) |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**FK behavior (todas las FKs de pesajes):**
`ON DELETE RESTRICT` en todas — nunca se puede eliminar un vehículo, usuario, servicio u origen que tenga pesajes asociados. Esto garantiza la integridad del historial.

**Invariante de estado:**
```
En predio → Cerrado    (solo avanza)
Cerrado   → En predio  (prohibido — no hay operación de reversión)
```

**Índices:**

```sql
-- Clustered (PK)
PK_pesajes   id

-- Historial del turno (operador): WHERE operador_id = X AND CAST(created_at AS DATE) = TODAY
IX_pesajes_operador_fecha
    (operador_id, created_at DESC)

-- Dashboard KPIs del día / camiones en predio
IX_pesajes_fecha_estado
    (created_at DESC, estado)
    INCLUDE (peso_neto_kg, zona_id, tipo_servicio_id, operador_id)

-- Reportes: agrupación por origen y por servicio en un rango de fechas
IX_pesajes_origen_fecha
    (zona_id, created_at DESC)
    INCLUDE (peso_neto_kg, tipo_servicio_id)

IX_pesajes_servicio_fecha
    (tipo_servicio_id, created_at DESC)
    INCLUDE (peso_neto_kg, zona_id)

-- Historial de un vehículo
IX_pesajes_vehiculo
    (vehiculo_id, created_at DESC)

-- Alarmas de peso: solo pesajes con alerta (filtered index — bajo volumen)
IX_pesajes_alerta
    (created_at DESC)
    WHERE alerta_peso = 1
```

**Índice de cobertura principal** para las queries del dashboard y reportes:
```sql
CREATE NONCLUSTERED INDEX IX_pesajes_kpis
ON pesajes (created_at DESC)
INCLUDE (peso_neto_kg, peso_bruto_kg, peso_tara_kg, estado,
         zona_id, tipo_servicio_id, vehiculo_id, operador_id, alerta_peso)
```
Este índice cubre la mayoría de las consultas de agregación sin necesidad de acceder al clustered index.

---

### `pesajes_log`

Audit trail inmutable. Una fila por campo modificado en cada edición de un pesaje.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `pesaje_id` | `bigint` | NO | — | FK → `pesajes.id` | — |
| `campo` | `nvarchar(50)` | NO | — | — | Nombre del campo modificado. Ej: `'peso_bruto_kg'` |
| `valor_anterior` | `nvarchar(500)` | SÍ | NULL | — | Valor antes de la edición. NULL si el campo no tenía valor. |
| `valor_nuevo` | `nvarchar(500)` | SÍ | NULL | — | Valor después de la edición. |
| `motivo` | `nvarchar(1000)` | NO | — | CHECK LEN > 0 | Obligatorio en toda edición. Nunca vacío. |
| `usuario_id` | `bigint` | NO | — | FK → `users.id` | Quién hizo el cambio (operador o admin) |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | Cuándo se hizo el cambio |

**Sin `updated_at`** — los registros de log son inmutables por diseño.

**FK behavior:**
- `pesaje_id`: `ON DELETE RESTRICT` — aunque no se eliminen pesajes, esta restricción lo garantiza.
- `usuario_id`: `ON DELETE RESTRICT` — el log debe conservar la referencia al usuario que actuó.

**Índices:**
```sql
PK   id
IX   pesaje_id, created_at DESC    -- historial de cambios de un pesaje (query más frecuente)
IX   usuario_id, created_at DESC   -- auditoría por usuario
```

---

### `alarmas`

Alertas generadas automáticamente por el sistema. Persisten hasta ser marcadas como resueltas por un admin.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `tipo` | `nvarchar(30)` | NO | — | CHECK IN (`'gap_pesajes'`, `'peso_inusual'`, `'frecuencia_atipica'`) | — |
| `descripcion` | `nvarchar(500)` | NO | — | — | Mensaje legible. Ej: `'Sin pesajes en Origen Norte hace 90 minutos'` |
| `zona_id` | `bigint` | SÍ | NULL | FK → `zonas.id` | Origen afectado (para `gap_pesajes` y `frecuencia_atipica`) |
| `vehiculo_id` | `bigint` | SÍ | NULL | FK → `vehiculos.id` | Vehículo que generó la alerta (para `peso_inusual`) |
| `pesaje_id` | `bigint` | SÍ | NULL | FK → `pesajes.id` | Link directo al pesaje que disparó la alarma (`peso_inusual`) |
| `resuelta` | `bit` | NO | `0` | — | — |
| `resuelta_por` | `bigint` | SÍ | NULL | FK → `users.id` | Admin que marcó como resuelta |
| `comentario_resolucion` | `nvarchar(1000)` | SÍ | NULL | CHECK: requerido si `resuelta = 1` (validado en app) | — |
| `resuelta_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**FK behavior:**
- `zona_id`, `vehiculo_id`, `pesaje_id`: `ON DELETE SET NULL` — si se desactiva el recurso, la alarma histórica se conserva pero pierde el link.
- `resuelta_por`: `ON DELETE RESTRICT` — el admin que resolvió no se puede eliminar si tiene alarmas vinculadas.

**Anti-duplicado:** El servicio de detección verifica antes de crear una alarma:
```sql
SELECT 1 FROM alarmas
WHERE tipo = ? AND zona_id = ? AND resuelta = 0
```
Si ya existe una alarma activa del mismo tipo para el mismo origen, no crea una nueva.

**Índices:**
```sql
PK   id
IX   resuelta, tipo                             -- dashboard: COUNT alarmas activas por tipo
IX   tipo, zona_id, resuelta                  -- anti-duplicado en detección
IX   pesaje_id                                  -- link desde log de pesajes
IX   created_at DESC                            -- historial ordenado
```

---

### `config_alarmas`

Configuración de umbrales de detección. Una fila por tipo de alarma.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `tipo` | `nvarchar(30)` | NO | — | UNIQUE, CHECK IN (mismos que `alarmas.tipo`) | — |
| `umbral_valor` | `int` | NO | — | CHECK > 0 | Unidad varía por tipo (ver tabla abajo) |
| `descripcion_umbral` | `nvarchar(200)` | NO | — | — | Texto explicativo del umbral para mostrar en la UI de configuración |
| `activo` | `bit` | NO | `1` | — | `0` = tipo de alarma desactivado globalmente |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Semántica de `umbral_valor` por tipo:**

| tipo | umbral_valor | Unidad | Descripción |
|------|-------------|--------|-------------|
| `gap_pesajes` | `60` | minutos | Tiempo sin pesajes durante horario operativo para disparar alarma |
| `frecuencia_atipica` | `50` | porcentaje | Desviación respecto al promedio histórico por origen para disparar alarma |
| `peso_inusual` | — | — | El umbral viene de `tipos_vehiculo.peso_min_kg` y `peso_max_kg`. Esta fila solo controla si el tipo está activo. `umbral_valor = 0` cuando el tipo usa fuente externa. |

**Nota:** `peso_inusual` no usa `umbral_valor` propio — los rangos están en `tipos_vehiculo`. La fila existe únicamente para el toggle `activo` y la coherencia de la tabla.

**Índices:**
```sql
PK   id
UQ   tipo      -- lookup directo por tipo (frecuente en detección)
```

---

## Diagrama de relaciones

El DER completo con Mermaid está en [`der.md`](der.md).

Resumen de relaciones:

| Relación | Cardinalidad |
|----------|-------------|
| `users` → `pesajes` | 1:N — un operador registra muchos pesajes |
| `users` → `pesajes_log` | 1:N — un usuario puede tener muchas entradas de log |
| `vehiculos` → `pesajes` | 1:N — un vehículo tiene muchos pesajes históricos |
| `tipos_vehiculo` → `vehiculos` | 1:N — un tipo tiene muchos vehículos |
| `tipos_servicio` → `pesajes` | 1:N — un servicio aparece en muchos pesajes |
| `zonas` ↔ `tipos_servicio` (vía `zona_servicios`) | N:M — una zona puede tener varios servicios; un servicio opera en varias zonas |
| `zona_servicios` → `zona_servicio_turnos` | 1:0..N — una combinación zona+servicio tiene 0, 1 o 2 turnos disponibles |
| `zonas` → `pesajes` | 1:N — una zona aparece en muchos pesajes |
| `pesajes` → `pesajes_log` | 1:N — un pesaje puede tener muchas entradas de auditoría |
| `pesajes` → `alarmas` | 1:0..1 — un pesaje puede tener a lo sumo una alarma `peso_inusual` |

**Cardinalidades:**

| Relación | Cardinalidad |
|----------|-------------|
| `users` → `pesajes` | 1:N — un operador registra muchos pesajes |
| `users` → `pesajes_log` | 1:N — un usuario puede tener muchas entradas de log |
| `vehiculos` → `pesajes` | 1:N — un vehículo tiene muchos pesajes históricos |
| `tipos_vehiculo` → `vehiculos` | 1:N — un tipo tiene muchos vehículos |
| `tipos_servicio` → `pesajes` | 1:N — un servicio aparece en muchos pesajes |
| `zonas` ↔ `tipos_servicio` (vía `zona_servicios`) | N:M — un origen puede tener varios servicios; un servicio opera en varios orígenes |
| `zona_servicios` → `zona_servicio_turnos` | 1:N — una combinación origen+servicio tiene 0, 1 o 2 turnos disponibles |
| `zonas` → `pesajes` | 1:N — un origen aparece en muchos pesajes |
| `pesajes` → `pesajes_log` | 1:N — un pesaje puede tener muchas entradas de auditoría |
| `pesajes` → `alarmas` | 1:0..1 — un pesaje puede tener a lo sumo una alarma `peso_inusual` |

---

## Patrones de consulta y cobertura de índices

### Dashboard — KPIs del día

```sql
SELECT
    COUNT(*)                        AS pesajes,
    SUM(peso_neto_kg) / 1000.0      AS toneladas,
    AVG(peso_neto_kg) / 1000.0      AS promedio,
    COUNT(DISTINCT CAST(created_at AS DATE)) AS dias_operativos
FROM pesajes
WHERE created_at >= CAST(GETDATE() AS DATE)
  AND created_at < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
```
**Índice usado:** `IX_pesajes_kpis` (cobertura total — no accede al clustered index)

---

### Dashboard — Camiones en predio

```sql
SELECT p.*, v.patente, v.numero_interno, ts.nombre AS servicio, o.nombre AS origen
FROM pesajes p
JOIN vehiculos v ON v.id = p.vehiculo_id
JOIN tipos_servicio ts ON ts.id = p.tipo_servicio_id
JOIN zonas z ON z.id = p.zona_id
WHERE p.estado = 'En predio'
ORDER BY p.created_at DESC
```
**Índice usado:** `IX_pesajes_fecha_estado` (filtered on estado)

---

### Historial del turno (operador)

```sql
SELECT * FROM pesajes
WHERE operador_id = ?
  AND created_at >= CAST(GETDATE() AS DATE)
ORDER BY created_at DESC
```
**Índice usado:** `IX_pesajes_operador_fecha`

---

### Reporte — Detalle por origen en un período

```sql
SELECT
    o.nombre,
    COUNT(p.id)                    AS pesajes,
    SUM(p.peso_neto_kg) / 1000.0   AS toneladas,
    SUM(p.peso_neto_kg) * 1.0 / NULLIF(o.habitantes, 0) AS per_capita_kg
FROM pesajes p
JOIN zonas z ON z.id = p.zona_id
WHERE p.created_at BETWEEN ? AND ?
GROUP BY o.id, o.nombre, o.habitantes
```
**Índice usado:** `IX_pesajes_origen_fecha` con INCLUDE de `peso_neto_kg`

---

### Autocomplete vehículos (operador)

```sql
SELECT TOP 6
    id, patente, numero_interno, tara_kg, titular,
    tv.nombre AS tipo_vehiculo, observaciones
FROM vehiculos v
JOIN tipos_vehiculo tv ON tv.id = v.tipo_vehiculo_id
WHERE v.activo = 1
  AND (v.patente LIKE '%' + ? + '%' OR v.numero_interno LIKE '%' + ? + '%')
ORDER BY v.numero_interno
```
**Índice usado:** `IX_vehiculos_activo_tipo` — scan parcial sobre `activo = 1`

---

### Detección de alarma — Gap de pesajes por origen

```sql
SELECT zona_id, MAX(created_at) AS ultimo_pesaje
FROM pesajes
WHERE created_at >= DATEADD(HOUR, -4, GETDATE())
GROUP BY zona_id
HAVING DATEDIFF(MINUTE, MAX(created_at), GETDATE()) > (
    SELECT umbral_valor FROM config_alarmas WHERE tipo = 'gap_pesajes'
)
```
**Índice usado:** `IX_pesajes_origen_fecha`

---

## Decisiones de diseño documentadas

| Decisión | Alternativa descartada | Razón |
|----------|----------------------|-------|
| `peso_neto_kg` como columna regular | PERSISTED computed column en SQL Server | El computed column impide log granular de cambio: si se edita `peso_bruto_kg`, necesitamos registrar también el cambio derivado en `peso_neto_kg` en `pesajes_log`. Mejor calcularlo en el Service. |
| `activo bit` en lugar de `SoftDeletes` | `deleted_at datetime` (Laravel SoftDeletes) | El ABM admin necesita mostrar registros inactivos en la tabla. SoftDeletes oculta los registros por defecto y requiere `withTrashed()` en cada query. `activo` es explícito y no requiere scopes globales. |
| `zona_servicios` + `zona_servicio_turnos` en lugar de `tipo_servicio_id` en origenes | FK directa `origenes.tipo_servicio_id` | Un origen puede operar bajo distintos servicios, cada uno con su propia configuración de turnos y horarios. Modelar la relación como N:M evita duplicar orígenes (ej: "Origen Sur Diurno" y "Origen Sur Nocturno" serían el mismo origen con dos configuraciones). `pesajes.turno` persiste el turno elegido por el operador; es obligatorio si `zona_servicio_turnos` tiene filas para esa combinación. |
| Sin `origen_predeterminado_id` en `tipos_servicio` | FK circular `tipos_servicio ↔ origenes` con origen predeterminado único | La relación es N:M vía `zona_servicios`. No existe "origen predeterminado" — el operador elige de la lista filtrada por servicio. Sin ciclos de FK, sin migración en 3 pasos. |
| `numero_interno nvarchar(20)` | `int` | Algunos municipios usan códigos alfanuméricos. Más seguro como string. La búsqueda por LIKE funciona igual. |
| Pesos en `int` (kg enteros) | `decimal(8,2)` (kg con decimales) | La balanza opera en kg enteros. Usar int simplifica comparaciones, suma y validaciones sin pérdida de precisión. |
| Un único índice de cobertura `IX_pesajes_kpis` | Múltiples índices específicos | Para las queries de agregación del dashboard y reportes, un índice ancho con INCLUDEs evita lookups al clustered y reduce I/O. El trade-off es mayor tamaño del índice, aceptable dado el volumen esperado. |
| `pesajes_log` sin `updated_at` | Con `updated_at` | Los registros de log son inmutables por definición. Tener `updated_at` induciría a error (podría sugerir que el log es editable). |
| `config_alarmas.umbral_valor int` | `umbral_min + umbral_max` | El diseño original con dos campos era redundante: gap_pesajes solo necesita un umbral, frecuencia_atipica también. `umbral_valor` con `descripcion_umbral` documenta la unidad por tipo. |

---

## Consideraciones de volumen y escalabilidad

**Volumen estimado para Etapa 1:**
- ~20 vehículos activos
- ~30–60 pesajes por día operativo (8:00–18:00)
- ~1.000–1.500 pesajes por mes
- ~15.000–20.000 pesajes al año

Con este volumen, los índices definidos son más que suficientes. No se requiere particionado ni sharding.

**Punto de revisión para escalar:**
Si el volumen supera los 500.000 pesajes (10+ años de operación o expansión del sistema), considerar:
- Particionado de `pesajes` por año/mes en SQL Server
- Archivado de `pesajes_log` antiguo a tabla histórica
- Índices columnstore para reportes analíticos sobre datos históricos

**Conexiones concurrentes:**
En Etapa 1, máximo 2–3 usuarios simultáneos (1 operador activo + 1 admin consultando). El pool de conexiones por defecto de Laravel es suficiente. No se requiere configuración especial de SQL Server.

---

## Referencia de migraciones Laravel

Orden de ejecución requerido (respetar dependencias de FK):

```
1.  users                         (sin FK externas)
2.  tipos_vehiculo                (sin FK externas)
3.  tipos_servicio                (FK → tipos_vehiculo)
4.  zonas                         (sin FK externas)
5.  zona_servicios              (FK → origenes, tipos_servicio)
6.  zona_servicio_turnos        (FK compuesta → zona_servicios)
7.  zona_servicio_horarios      (FK compuesta → zona_servicios)
8.  vehiculos                     (FK → tipos_vehiculo)
9.  pesajes                       (FK → vehiculos, users, tipos_servicio, origenes)
10. pesajes_log                   (FK → pesajes, users)
11. config_alarmas                (sin FK externas)
12. alarmas                       (FK → origenes, vehiculos, pesajes, users)
```

**Rollback:** el orden inverso (12 → 1).

---

*Documento generado: 13/05/2026 | Versión: 1.1 — Actualizado 14/05/2026*
*Cambios v1.1: Nomenclatura unificada a `zonas` en todas las tablas relacionadas (`zona_servicios`, `zona_servicio_turnos`, `zona_servicio_horarios`). Columna de FK `zona_id`. Rutas API `/api/servicios/{id}/zonas`.*
