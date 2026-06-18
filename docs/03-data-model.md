# Modelo de datos
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Motor:** SQL Server (driver `sqlsrv`)
**ORM:** Laravel Eloquent
**Versión:** 2.0 — 18/06/2026

> Diagrama visual de relaciones: [`04-der.md`](04-der.md).

---

## Principios de diseño

| Principio | Aplicación |
|-----------|------------|
| **Multi-tenant por columna** | El sistema sirve a varias organizaciones sobre la misma base. Casi toda tabla de dominio lleva `organizacion_id`. La unicidad (`patente`, `nombre`, etc.) se scopea por organización: `UNIQUE (organizacion_id, columna)`, nunca global. |
| **Baja lógica** | Ninguna entidad maestra se elimina físicamente en la operación normal. `activo bit` en todas las tablas maestras. |
| **Auditoría inmutable** | `pesajes_log` y `vehiculos_log` son append-only. Nunca se actualiza ni elimina un registro de log. |
| **Desnormalización controlada** | `pesajes.peso_tara_kg` copia la tara del padrón al momento del ingreso para preservar el historial si el padrón cambia. `reportes_generados.snapshot` congela el reporte tal como se generó. |
| **Enums como `nvarchar`** | SQL Server no tiene tipo ENUM. Se usan columnas `nvarchar` con valores acotados (validados en app; `CHECK` donde aplica). |
| **IDs `bigint`** | Todas las PKs son `bigint IDENTITY`. Algunas tablas exponen además un `uuid` público (`pesajes`, `alertas`). |
| **Unicode** | Columnas de texto en `nvarchar` — soporte completo para español. |
| **Timestamps con precisión** | `datetime2`. `pesajes` usa `datetime2(3)` (precisión a milisegundos) porque `datetime` redondea `.999` al segundo siguiente y desvía la atribución por fecha en dashboard/reportes. |
| **Pesos en enteros** | Todos los pesos en `int` (kilogramos enteros). La balanza opera en kg completos. |
| **Borrado SQL Server** | `cascadeOnDelete` solo en el camino primario del padrón; `noActionOnDelete` en toda FK secundaria que converja en `organizaciones`. Ver [`04-der.md`](04-der.md) §*Estrategia de borrado* y `CLAUDE.md` §*SQL Server*. |

---

## Entidades y definiciones completas

### `organizaciones`

Raíz del modelo multi-tenant. Cada organización es un predio/cliente con su propio padrón, operación y reportes. El `organizacion_id` de cada tabla de dominio apunta acá.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `nombre` | `nvarchar(150)` | NO | — | — | Nombre del predio / cliente |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

---

### `users`

Usuarios del sistema. Tres roles: `super_admin` (gestiona organizaciones, transversal), `admin` (acceso completo dentro de su organización) y `operador` (solo Balanza).

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `name` | `nvarchar(255)` | NO | — | — | Nombre completo |
| `email` | `nvarchar(255)` | NO | — | UNIQUE | Usado como nombre de usuario para el login |
| `email_verified_at` | `datetime2(0)` | SÍ | NULL | — | No usado activamente |
| `password` | `nvarchar(255)` | NO | — | — | Hash bcrypt |
| `role` | `nvarchar(20)` | NO | `'operador'` | IN (`'super_admin'`, `'admin'`, `'operador'`) | Determina el perfil de acceso |
| `onboarding_visto` | `bit` | NO | `0` | — | `1` después de cerrar el modal de bienvenida |
| `activo` | `bit` | NO | `1` | — | `0` = desactivado, no puede iniciar sesión |
| `remember_token` | `nvarchar(100)` | SÍ | NULL | — | Breeze |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Nota:** la pertenencia a organizaciones no es una columna en `users` — se modela como N:M en `organizacion_user`. `activo` NO usa `SoftDeletes` (necesitamos mostrar usuarios inactivos en el ABM).

---

### `organizacion_user`

Pivot N:M entre usuarios y organizaciones. Un usuario puede pertenecer a varias organizaciones; el contexto activo se resuelve en sesión.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `user_id` | `bigint` | NO | — | FK → `users.id`, CASCADE | — |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Único:** `UNIQUE (organizacion_id, user_id)`.

---

### `tipos_vehiculo`

Catálogo de tipos de camión con rangos de peso. Define los umbrales de `alerta_peso` en pesajes.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `nombre` | `nvarchar(255)` | NO | — | — | Ej: `'Compactador'` |
| `peso_min_kg` | `int unsigned` | NO | — | — | Límite inferior para alerta de peso |
| `peso_max_kg` | `int unsigned` | NO | — | — | Límite superior para alerta de peso |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Validación de rango** (`peso_max_kg > peso_min_kg`) se aplica en el Form Request.

**Datos iniciales (seeder):**
| nombre | peso_min_kg | peso_max_kg |
|--------|-------------|-------------|
| Compactador | 10000 | 26500 |
| Volcador | 13000 | 30000 |
| Volquete | 7000 | 20000 |
| Particular | 1000 | 5000 |

---

### `tipos_servicio`

Tipos de recolección disponibles. Cada servicio puede sugerir **varios tipos de vehículo** (N:M vía `tipo_servicio_tipo_vehiculo`). La relación con zonas y turnos se modela en `zona_servicios` y `zona_servicio_turnos` (también N:M).

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `nombre` | `nvarchar(100)` | NO | — | — | Ej: `'Domiciliario'` |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Único:** `UNIQUE (organizacion_id, nombre)`.

> **Histórico:** existió una FK única `tipo_vehiculo_sugerido_id` (un solo vehículo sugerido por servicio). Se reemplazó por la N:M `tipo_servicio_tipo_vehiculo`.

---

### `tipo_servicio_tipo_vehiculo`

Pivot entre servicios y tipos de vehículo. Define qué tipos de vehículo se sugieren para cada servicio (hint en la balanza: si el vehículo elegido no es uno de los sugeridos, se muestra una advertencia suave, no bloqueante).

| Columna | Tipo | Nullable | Constraints |
|---------|------|----------|-------------|
| `tipo_servicio_id` | `bigint` | NO | PK compuesta, FK → `tipos_servicio.id`, CASCADE |
| `tipo_vehiculo_id` | `bigint` | NO | PK compuesta, FK → `tipos_vehiculo.id`, **noAction** |

**PK compuesta:** `(tipo_servicio_id, tipo_vehiculo_id)`. `tipo_vehiculo_id` usa `noAction` (segundo camino a `organizaciones`).

---

### `zonas`

Entidad geográfica. La asociación con servicios se modela en `zona_servicios`.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `nombre` | `nvarchar(150)` | NO | — | — | Ej: `'Zona Norte'` |
| `hectareas` | `decimal(10,2)` | SÍ | NULL | — | NULL = dato no disponible. 0 = verificado como cero. |
| `barrios` | `int` | SÍ | NULL | — | — |
| `habitantes` | `int` | SÍ | NULL | — | NULL = dato no disponible. Afecta cálculo per cápita en reportes. |
| `geojson` | `nvarchar(max)` | SÍ | NULL | — | Geometría del área como `FeatureCollection` GeoJSON (un polígono). NULL = zona sin área dibujada. Se dibuja/edita con Leaflet + Geoman. |
| `centro_lat` | `decimal(10,7)` | SÍ | NULL | — | Latitud del centro del polígono (derivado en el cliente). |
| `centro_lng` | `decimal(10,7)` | SÍ | NULL | — | Longitud del centro del polígono. |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Único:** `UNIQUE (organizacion_id, nombre)`.

**Geometría (`geojson`):** se guarda el `FeatureCollection` serializado tal cual lo emite Leaflet, sin cast en el modelo. Para consumirlo en PHP: `json_decode($zona->geojson, true)`. A este volumen (un puñado de zonas por organización) no se usa el tipo `geography` ni índices espaciales — el choropleth se arma en el cliente. La asignación de zona a cada pesaje sigue siendo manual (`zona_id`); el polígono es para visualización (mapas de calor en Dashboard y Reportes).

**Semántica NULL vs 0:** `hectareas = NULL` → no calculado; `= 0` → verificado sin área. El servicio de reportes distingue ambos (NULL produce `null`, 0 se maneja con `NULLIF` para evitar división por cero).

---

### `zona_servicios`

Pivot entre zonas y tipos de servicio. Define qué servicios operan en cada zona. Si no existe fila para una combinación zona+servicio, esa zona no aparece como opción cuando el operador elige ese servicio.

| Columna | Tipo | Nullable | Constraints |
|---------|------|----------|-------------|
| `zona_id` | `bigint` | NO | PK compuesta, FK → `zonas.id`, CASCADE |
| `tipo_servicio_id` | `bigint` | NO | PK compuesta, FK → `tipos_servicio.id`, **noAction** |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | — |

**PK compuesta:** `(zona_id, tipo_servicio_id)`. `tipo_servicio_id` usa `noAction` (segundo camino a `organizaciones`).

---

### `zona_servicio_turnos`

Turnos disponibles para una combinación zona+servicio. Sin filas → el formulario no muestra el campo turno; con filas → el operador debe seleccionar uno.

| Columna | Tipo | Nullable | Constraints |
|---------|------|----------|-------------|
| `zona_id` | `bigint` | NO | PK compuesta |
| `tipo_servicio_id` | `bigint` | NO | PK compuesta |
| `turno` | `nvarchar(10)` | NO | PK compuesta — `'Diurna'` \| `'Nocturna'` |

**PK compuesta:** `(zona_id, tipo_servicio_id, turno)`.
**FK compuesta** `(zona_id, tipo_servicio_id)` → `zona_servicios`, CASCADE.

---

### `zona_servicio_horarios`

Franjas horarias de recorrido por día para cada combinación zona+servicio. Múltiples franjas por día (ej: Lun 08:00–12:00 y 20:00–02:00). Informativas — no bloquean el registro.

| Columna | Tipo | Nullable | Constraints | Descripción |
|---------|------|----------|-------------|-------------|
| `zona_id` | `bigint` | NO | PK cuádruple | — |
| `tipo_servicio_id` | `bigint` | NO | PK cuádruple | — |
| `dia_semana` | `tinyint unsigned` | NO | PK cuádruple | 1=Lunes … 7=Domingo |
| `franja` | `tinyint unsigned` | NO | PK cuádruple | Orden de la franja dentro del día |
| `hora_inicio` | `time` | NO | — | — |
| `hora_fin` | `time` | NO | — | Puede ser menor que `hora_inicio` cuando cruza medianoche (ej: 20:00–02:00). |

**PK cuádruple:** `(zona_id, tipo_servicio_id, dia_semana, franja)`.
**FK compuesta** `(zona_id, tipo_servicio_id)` → `zona_servicios`, CASCADE.

---

### `vehiculos`

Padrón completo de vehículos. La tara se copia a cada pesaje al momento del ingreso.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `patente` | `nvarchar(20)` | NO | — | — | Sin espacios. Ej: `'ABC123'` |
| `numero_interno` | `nvarchar(20)` | SÍ | NULL | — | Alfanumérico, opcional. Algunos vehículos particulares no lo tienen. |
| `tara_kg` | `int` | NO | — | — | Peso vacío. **Campo crítico** — errores afectan todos los pesajes futuros. |
| `tipo_vehiculo_id` | `bigint` | NO | — | FK → `tipos_vehiculo.id`, **noAction** | Determina los rangos de alerta de peso |
| `titular` | `nvarchar(200)` | NO | — | — | Ej: `'Municipalidad de Corrientes'` |
| `capacidad_kg` | `int` | SÍ | NULL | — | Carga máxima. Informativo, no usado en validaciones. |
| `observaciones` | `nvarchar(500)` | SÍ | NULL | — | Se autocompleta en el formulario de pesaje, editable. |
| `activo` | `bit` | NO | `1` | — | Inactivo = no aparece en autocompletado |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Único:** `UNIQUE (organizacion_id, patente)` y `UNIQUE (organizacion_id, numero_interno)`.

**API de autocompletado** (`GET /api/vehiculos/buscar?q=`): busca con `LIKE` en `patente` y `numero_interno` donde `activo = 1`, dentro de la organización. Máximo 6 resultados.

---

### `vehiculos_log`

Audit trail de cambios del padrón de vehículos. Una fila por campo modificado. Hoy registra principalmente cambios de `tara_kg` (campo crítico). Append-only.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `vehiculo_id` | `bigint` | NO | — | FK → `vehiculos.id`, CASCADE | — |
| `campo` | `nvarchar(100)` | NO | — | — | Ej: `'tara_kg'` |
| `valor_anterior` | `nvarchar(500)` | SÍ | NULL | — | — |
| `valor_nuevo` | `nvarchar(500)` | SÍ | NULL | — | — |
| `motivo` | `nvarchar(500)` | NO | — | — | Para `tara_kg` lleva prefijo de intención (ver abajo). |
| `usuario_id` | `bigint` | NO | — | FK → `users.id`, **noAction** | Admin que hizo el cambio |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Semántica de la intención (corrección de tara):** al editar la tara de un vehículo con pesajes, el admin elige entre:
- **Corrección de dato mal cargado** → recalcula `peso_tara_kg` y `peso_neto_kg` en todos los pesajes **no cancelados** del vehículo, con una entrada en `pesajes_log` por campo y `pesajes.editado = 1`.
- **Cambio real de tara** → los pesajes anteriores no se modifican; solo los nuevos usan la tara nueva.

En ambos casos se registra fila en `vehiculos_log`, dentro de una transacción, exclusivo de admins.

**Limitación conocida:** una corrección retroactiva recalcula netos históricos pero **no** reemite reportes ya enviados (quedan como snapshot del envío).

---

### `pesajes`

Tabla operacional central. Cada fila es un camión que entró al predio. Se escribe al registrar el ingreso y puede modificarse con auditoría o cancelarse.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `uuid` | `uniqueidentifier` | NO | — | UNIQUE | Identificador público (URLs, no expone el id secuencial) |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, **noAction** | — |
| `vehiculo_id` | `bigint` | NO | — | FK → `vehiculos.id`, **noAction** | — |
| `operador_id` | `bigint` | NO | — | FK → `users.id`, **noAction** | Usuario que registró el pesaje |
| `tipo_servicio_id` | `bigint` | NO | — | FK → `tipos_servicio.id`, **noAction** | — |
| `zona_id` | `bigint` | NO | — | FK → `zonas.id`, **noAction** | — |
| `turno` | `nvarchar(10)` | SÍ | NULL | `'Diurna'` \| `'Nocturna'` | Obligatorio (en app) cuando la combinación zona+servicio tiene turnos. |
| `peso_bruto_kg` | `int` | NO | — | — | Peso que muestra la balanza al ingreso |
| `peso_tara_kg` | `int` | NO | — | — | Copiado de `vehiculos.tara_kg` al crear. Se recalcula solo ante corrección retroactiva de tara (ver `vehiculos_log`). |
| `peso_neto_kg` | `int` | NO | — | — | Calculado en el Service: `peso_bruto_kg - peso_tara_kg`. |
| `alerta_peso` | `bit` | NO | `0` | — | `1` si `peso_bruto_kg` estaba fuera del rango de `tipos_vehiculo` al registrar |
| `observaciones` | `nvarchar(500)` | SÍ | NULL | — | Autocompleta del padrón, editable |
| `estado` | `nvarchar(20)` | NO | `'En predio'` | `'En predio'` \| `'Cerrado'` \| `'Cancelado'` | — |
| `hora_salida` | `datetime2(3)` | SÍ | NULL | — | NULL = en predio. Se completa al marcar egreso. |
| `bruto_salida_kg` | `int` | SÍ | NULL | — | Peso de salida opcional. Solo trazabilidad. |
| `editado` | `bit` | NO | `0` | — | `1` si al menos un campo fue modificado post-registro |
| `motivo_cancelacion` | `nvarchar(500)` | SÍ | NULL | — | Obligatorio al cancelar |
| `cancelado_por_id` | `bigint` | SÍ | NULL | FK → `users.id`, **noAction** | Usuario que canceló |
| `cancelado_at` | `datetime2(3)` | SÍ | NULL | — | — |
| `created_at` / `updated_at` | `datetime2(3)` | SÍ | NULL | — | `created_at` = hora de entrada del camión |

**FK behavior:** todas `noActionOnDelete` — nunca se elimina un vehículo, usuario, servicio, zona u organización con pesajes asociados (SQL Server además rechazaría las cascadas múltiples convergentes en `organizaciones`).

**Estados:** `En predio → Cerrado` (egreso) o `En predio → Cancelado` (anulación con motivo). No hay reversión.

**Precisión `datetime2(3)`:** evita que `datetime` redondee `.999` al segundo siguiente y desvíe la atribución por fecha de dashboard/reportes; además parsea siempre ISO 8601 sin depender del `DATEFORMAT` del servidor.

**Índice de cobertura sugerido** para agregaciones de dashboard/reportes (las queries filtran primero por `organizacion_id`):
```sql
CREATE NONCLUSTERED INDEX IX_pesajes_org_fecha
ON pesajes (organizacion_id, created_at DESC)
INCLUDE (peso_neto_kg, peso_bruto_kg, peso_tara_kg, estado,
         zona_id, tipo_servicio_id, vehiculo_id, operador_id, alerta_peso)
```

---

### `pesajes_log`

Audit trail inmutable. Una fila por campo modificado en cada edición de un pesaje.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `pesaje_id` | `bigint` | NO | — | FK → `pesajes.id`, CASCADE | — |
| `campo` | `nvarchar(100)` | NO | — | — | Ej: `'peso_bruto_kg'` |
| `valor_anterior` | `nvarchar(500)` | SÍ | NULL | — | — |
| `valor_nuevo` | `nvarchar(500)` | SÍ | NULL | — | — |
| `motivo` | `nvarchar(500)` | NO | — | — | Obligatorio en toda edición. Nunca vacío. |
| `usuario_id` | `bigint` | NO | — | FK → `users.id`, **noAction** | Quién hizo el cambio (operador o admin) |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Índices:** `(pesaje_id, created_at DESC)` para el historial de un pesaje; `(usuario_id, created_at DESC)` para auditoría por usuario.

---

### `alertas`

Alertas generadas automáticamente por el sistema (detección de anomalías). Persisten hasta ser marcadas como leídas.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `uuid` | `uniqueidentifier` | NO | — | UNIQUE | Identificador público |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `user_id` | `bigint` | SÍ | NULL | FK → `users.id`, **noAction** | Operador destinatario (nullable) |
| `tipo` | `nvarchar(50)` | NO | — | IN (`'peso_fuera_rango'`, `'volumen_diario_atipico'`, `'gap_registro'`, `'frecuencia_zona_atipica'`) | — |
| `titulo` | `nvarchar(200)` | NO | — | — | Encabezado legible |
| `descripcion` | `nvarchar(max)` | SÍ | NULL | — | Detalle |
| `pesaje_id` | `bigint` | SÍ | NULL | FK → `pesajes.id`, **noAction** | Link al pesaje que disparó (`peso_fuera_rango`) |
| `zona_id` | `bigint` | SÍ | NULL | FK → `zonas.id`, **noAction** | Zona afectada (`gap_registro`, `frecuencia_zona_atipica`) |
| `fecha_deteccion` | `date` | NO | — | — | Día al que corresponde la alerta |
| `leida` | `bit` | NO | `0` | — | — |
| `leida_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Índices:** `(organizacion_id, leida)`, `(user_id, leida)`, `(organizacion_id, tipo, fecha_deteccion)`.

> **Histórico:** la tabla se llamó `alarmas` con tipos `gap_pesajes | peso_inusual | frecuencia_atipica` y campos `resuelta/resuelta_por`. Hoy es `alertas`, con `leida/leida_at` y los cuatro tipos de arriba.

---

### `config_alertas`

Configuración de detección por tipo de alerta. Una fila por `(organizacion_id, tipo)`.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `tipo` | `nvarchar(50)` | NO | — | — | Mismos valores que `alertas.tipo` |
| `activo` | `bit` | NO | `1` | — | `0` = tipo desactivado para la organización |
| `umbral_valor` | `decimal(8,2)` | SÍ | NULL | — | Significado según el tipo (ver abajo) |
| `hora_inicio` | `nvarchar(5)` | SÍ | NULL | — | Formato `'H:i'`. Solo `gap_registro`. |
| `hora_fin` | `nvarchar(5)` | SÍ | NULL | — | Formato `'H:i'`. Solo `gap_registro`. |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Único:** `UNIQUE (organizacion_id, tipo)`.

**Semántica de `umbral_valor` por tipo:**
| tipo | umbral_valor | Default | Unidad |
|------|-------------|---------|--------|
| `peso_fuera_rango` | `NULL` | — | Usa los rangos de `tipos_vehiculo`. |
| `volumen_diario_atipico` | % de desviación del promedio | 20 | porcentaje |
| `gap_registro` | minutos sin actividad en horario operativo | 120 | minutos (usa `hora_inicio`/`hora_fin`) |
| `frecuencia_zona_atipica` | % de desviación del promedio por zona | 30 | porcentaje |

---

## Módulo de reportes

Cuatro tablas. Una organización tiene **una** `reporte_configuraciones` (marca, IA y política de revisión), N `reportes_programados` (envíos automáticos), una libreta de `reporte_destinatarios`, y un historial de `reportes_generados` (cada generación manual o envío programado).

### `reporte_configuraciones`

Configuración 1:1 por organización: branding del PDF, integración de IA para conclusiones, y qué tipos de informe están habilitados.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | UNIQUE, FK → `organizaciones.id`, CASCADE | 1:1 con la organización |
| `municipalidad_nombre` | `nvarchar(200)` | NO | `'Municipalidad'` | — | Encabezado del reporte |
| `intro_empresa` | `nvarchar(max)` | SÍ | NULL | — | Texto introductorio del PDF |
| `servicios` | `json` | SÍ | NULL | — | Configuración de servicios para el reporte |
| `ai_enabled` | `bit` | NO | `0` | — | Genera conclusiones narrativas con IA |
| `ai_proveedor` | `nvarchar(50)` | NO | `'gemini'` | — | — |
| `ai_api_key` | `nvarchar(max)` | SÍ | NULL | — | Clave del proveedor |
| `ai_modelo` | `nvarchar(100)` | NO | `'gemini-2.0-flash-lite'` | — | — |
| `ai_prompt` | `nvarchar(max)` | SÍ | NULL | — | Prompt personalizado |
| `tipo_informe_mensual_activo` | `bit` | NO | `1` | — | Habilita el informe mensual |
| `tipo_alertas_activo` | `bit` | NO | `0` | — | Habilita el reporte de alertas |
| `revision_requerida` | `bit` | NO | `1` | — | Default de organización: los envíos programados quedan pendientes de aprobación. Cada programado puede sobreescribirlo vía `opciones['revision']` (`heredar` \| `revisar` \| `directo`). |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

---

### `reportes_programados`

Envíos automáticos por cron. El scheduler dispara la generación y el envío según `cron_expresion`.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `tipo` | `nvarchar(30)` | NO | `'informe_mensual'` | `'informe_mensual'` \| `'alertas'` | — |
| `nombre` | `nvarchar(150)` | NO | — | — | — |
| `frecuencia` | `nvarchar(20)` | NO | `'mensual'` | `'mensual'` \| `'semanal'` \| `'custom'` | — |
| `cron_expresion` | `nvarchar(50)` | NO | `'0 8 1 * *'` | — | Expresión cron del envío |
| `destinatarios` | `json` | NO | — | — | Emails de destino |
| `opciones` | `json` | SÍ | NULL | — | Filtros, formato, `revision` (heredar/revisar/directo) |
| `activo` | `bit` | NO | `1` | — | — |
| `ultimo_envio_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `proximo_envio_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

---

### `reporte_destinatarios`

Libreta de emails frecuentes por organización, con contador de uso para sugerir los más usados.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, CASCADE | — |
| `email` | `nvarchar(255)` | NO | — | — | — |
| `nombre` | `nvarchar(255)` | SÍ | NULL | — | — |
| `uso_count` | `int unsigned` | NO | `1` | — | Veces que se usó como destinatario |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Único:** `UNIQUE (organizacion_id, email)`. **Índice:** `(organizacion_id, uso_count)`.

---

### `reportes_generados`

Historial de cada reporte generado (manual) o enviado (programado). Conserva un `snapshot` congelado para re-descargar el reporte idéntico sin recalcular sobre los pesajes vivos.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `organizacion_id` | `bigint` | NO | — | FK → `organizaciones.id`, **noAction** | — |
| `usuario_id` | `bigint` | SÍ | NULL | FK → `users.id`, **noAction** | Quién generó. NULL en envíos programados (los dispara el job). |
| `reporte_programado_id` | `bigint` | SÍ | NULL | FK → `reportes_programados.id`, **nullOnDelete** | Si se borra el programado, el historial se conserva. |
| `origen` | `nvarchar(20)` | NO | — | `'manual'` \| `'programado'` | — |
| `tipo` | `nvarchar(30)` | NO | — | `'informe_mensual'` \| `'alertas'` | — |
| `formato` | `nvarchar(20)` | NO | — | `'pdf'` \| `'excel'` \| `'pdf+excel'` | — |
| `periodo_desde` | `date` | NO | — | — | — |
| `periodo_hasta` | `date` | NO | — | — | — |
| `filtros` | `json` | SÍ | NULL | — | `zona_id`, `tipo_servicio_id`, `tipo_vehiculo_id` |
| `destinatarios` | `json` | SÍ | NULL | — | Emails (solo envíos) |
| `estado` | `nvarchar(20)` | NO | `'generado'` | `'generado'` \| `'enviado'` \| `'fallido'` | — |
| `error` | `nvarchar(500)` | SÍ | NULL | — | Detalle si `estado = fallido` |
| `conclusiones` | `nvarchar(max)` | SÍ | NULL | — | Narrativa IA preservada del envío |
| `snapshot` | `json` | SÍ | NULL | — | Datos congelados: agregados, pivots, detalle, mapa de calor, alertas y branding. |
| `revisado_por_id` | `bigint` | SÍ | NULL | FK → `users.id`, **noAction** | Quién aprobó/descartó |
| `revisado_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `enviado_at` | `datetime2(0)` | SÍ | NULL | — | Cuándo salió el mail (puede diferir de `created_at` con revisión) |
| `motivo_descarte` | `nvarchar(500)` | SÍ | NULL | — | Si se descartó en revisión |
| `created_at` / `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**Índices:** `(organizacion_id, created_at)` (historial) y `(organizacion_id, estado)` (contador de pendientes de revisión).

**Flujo de revisión:** con `revision_requerida`, un envío programado se genera con `estado = generado` y queda pendiente; un admin lo aprueba (`enviado`, setea `revisado_por_id`/`enviado_at`) o lo descarta (`motivo_descarte`). Sin revisión, el job envía directo.

---

## Patrones de consulta y cobertura de índices

> Toda query de dominio filtra primero por `organizacion_id` (multi-tenant). Los índices de cobertura lo llevan como primera columna.

### Dashboard — KPIs del día

```sql
SELECT
    COUNT(*)                        AS pesajes,
    SUM(peso_neto_kg) / 1000.0      AS toneladas,
    AVG(peso_neto_kg) / 1000.0      AS promedio
FROM pesajes
WHERE organizacion_id = ?
  AND estado <> 'Cancelado'
  AND created_at >= CAST(GETDATE() AS DATE)
  AND created_at < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
```

### Dashboard — Camiones en predio

```sql
SELECT p.*, v.patente, v.numero_interno, ts.nombre AS servicio, z.nombre AS zona
FROM pesajes p
JOIN vehiculos v       ON v.id = p.vehiculo_id
JOIN tipos_servicio ts ON ts.id = p.tipo_servicio_id
JOIN zonas z           ON z.id = p.zona_id
WHERE p.organizacion_id = ?
  AND p.estado = 'En predio'
ORDER BY p.created_at DESC
```

### Historial del turno (operador)

```sql
SELECT * FROM pesajes
WHERE organizacion_id = ?
  AND operador_id = ?
  AND created_at >= CAST(GETDATE() AS DATE)
ORDER BY created_at DESC
```

### Reporte — Detalle por zona en un período (per cápita)

```sql
SELECT
    z.nombre,
    COUNT(p.id)                    AS pesajes,
    SUM(p.peso_neto_kg) / 1000.0   AS toneladas,
    SUM(p.peso_neto_kg) * 1.0 / NULLIF(z.habitantes, 0) AS per_capita_kg
FROM pesajes p
JOIN zonas z ON z.id = p.zona_id
WHERE p.organizacion_id = ?
  AND p.estado <> 'Cancelado'
  AND p.created_at BETWEEN ? AND ?
GROUP BY z.id, z.nombre, z.habitantes
```

### Autocomplete vehículos (operador)

```sql
SELECT TOP 6
    v.id, v.patente, v.numero_interno, v.tara_kg, v.titular,
    tv.nombre AS tipo_vehiculo, v.observaciones
FROM vehiculos v
JOIN tipos_vehiculo tv ON tv.id = v.tipo_vehiculo_id
WHERE v.organizacion_id = ?
  AND v.activo = 1
  AND (v.patente LIKE '%' + ? + '%' OR v.numero_interno LIKE '%' + ? + '%')
ORDER BY v.numero_interno
```

### Detección de alerta — Gap de registro por zona

```sql
SELECT zona_id, MAX(created_at) AS ultimo_pesaje
FROM pesajes
WHERE organizacion_id = ?
  AND created_at >= DATEADD(HOUR, -4, GETDATE())
GROUP BY zona_id
HAVING DATEDIFF(MINUTE, MAX(created_at), GETDATE()) > (
    SELECT umbral_valor FROM config_alertas
    WHERE organizacion_id = ? AND tipo = 'gap_registro'
)
```

---

## Decisiones de diseño documentadas

| Decisión | Alternativa descartada | Razón |
|----------|----------------------|-------|
| Multi-tenant por `organizacion_id` | Base por cliente / esquema por cliente | Un solo despliegue, padrón aislado por columna. La unicidad se scopea por organización. Simplifica operación y deploy. |
| FKs `noAction` salvo el camino primario | `cascade` en todas | SQL Server rechaza cascadas múltiples que convergen en el mismo ancestro (`organizaciones`). Solo el camino primario cascadea; el resto es `noAction`. |
| `peso_neto_kg` como columna regular | PERSISTED computed column | El computed impide log granular: al editar `peso_bruto_kg` necesitamos registrar el cambio derivado en `pesajes_log`. Se calcula en el Service. |
| `activo bit` en lugar de `SoftDeletes` | `deleted_at` (Laravel SoftDeletes) | El ABM admin necesita mostrar registros inactivos sin `withTrashed()` en cada query. |
| `tipo_servicio_tipo_vehiculo` (N:M) | FK única `tipo_vehiculo_sugerido_id` | Un servicio puede sugerir varios tipos de vehículo. |
| `zona_servicios` + turnos/horarios (N:M) | FK directa `zonas.tipo_servicio_id` | Una zona opera bajo varios servicios, cada uno con su config de turnos/horarios. Evita duplicar zonas. |
| `datetime2(3)` en `pesajes` | `datetime` | `datetime` redondea `.999` al segundo siguiente (desvía atribución por fecha) y parsea según `DATEFORMAT`. `datetime2` es exacto y siempre ISO. |
| `uuid` público en `pesajes`/`alertas` | Exponer el `id` IDENTITY | No filtrar volumen ni permitir enumeración secuencial en URLs. |
| `reportes_generados.snapshot` (json congelado) | Recalcular sobre pesajes vivos | La tara de un vehículo puede cambiar después; el reporte entregado debe poder re-descargarse idéntico. |
| `pesajes_log` / `vehiculos_log` sin uso de `updated_at` | Log editable | Los registros de log son inmutables por definición. |

---

## Consideraciones de volumen y escalabilidad

**Volumen estimado por organización:**
- ~20 vehículos activos · ~30–60 pesajes/día operativo · ~1.000–1.500 pesajes/mes · ~15.000–20.000 pesajes/año.

Con este volumen, los índices definidos son suficientes; no se requiere particionado ni sharding. El `organizacion_id` como primera columna de los índices de cobertura aísla cada organización en el plan de ejecución.

**Punto de revisión para escalar** (500.000+ pesajes): particionado de `pesajes` por año/mes, archivado de logs antiguos, índices columnstore para analítica histórica.

---

## Referencia de migraciones Laravel

Orden de ejecución (respetar dependencias de FK):

```
0.  organizaciones                (raíz multi-tenant)
1.  users + organizacion_user     (pivot N:M con organizaciones)
2.  tipos_vehiculo                (FK → organizaciones)
3.  tipos_servicio                (FK → organizaciones) + tipo_servicio_tipo_vehiculo
4.  zonas                         (FK → organizaciones)  (+ geojson en migración posterior)
5.  vehiculos                     (FK → organizaciones, tipos_vehiculo) + vehiculos_log
6.  zona_servicios              (FK compuesta base)
7.  zona_servicio_turnos        (FK compuesta → zona_servicios)
8.  zona_servicio_horarios      (FK compuesta → zona_servicios)
9.  reporte_configuraciones       (FK → organizaciones)
10. reportes_programados          (FK → organizaciones)
11. reporte_destinatarios         (FK → organizaciones)
12. pesajes                       (FK → organizaciones, vehiculos, users, tipos_servicio, zonas)
13. pesajes_log                   (FK → pesajes, users)
14. alertas                       (FK → organizaciones, users, pesajes, zonas)
15. config_alertas                (FK → organizaciones)  (+ horario operativo posterior)
16. reportes_generados            (FK → organizaciones, users, reportes_programados)  (+ snapshot/revisión posterior)
```

**Rollback:** orden inverso.

---

*Documento actualizado: 18/06/2026 · v2.0 — multi-tenant (`organizaciones`), módulo de reportes (4 tablas), renombre `alarmas`→`alertas`, FKs `noAction`, `uuid` y cancelación en pesajes. Diagrama: [`04-der.md`](04-der.md).*
