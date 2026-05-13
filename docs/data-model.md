# Modelo de datos
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Motor:** SQL Server (driver `sqlsrv`)
**ORM:** Laravel Eloquent
**Versión:** 1.0 — 13/05/2026

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

Tipos de recolección disponibles. Cada servicio tiene un tipo de vehículo sugerido. Los turnos disponibles por servicio se modelan en la tabla `tipos_servicio_turnos`. Las zonas asociadas se modelan en `zonas` (relación 1:N).

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `nombre` | `nvarchar(100)` | NO | — | UNIQUE | Ej: `'Domiciliario'` |
| `tipo_vehiculo_sugerido_id` | `bigint` | SÍ | NULL | FK → `tipos_vehiculo.id` | Sugerencia en el formulario de pesaje. Nullable. |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**FK behavior:**
- `tipo_vehiculo_sugerido_id`: `ON DELETE SET NULL` — si se desactiva un tipo de vehículo, el servicio pierde la sugerencia pero sigue activo.

**Índices:**
```sql
PK   id
UQ   nombre
IX   activo
IX   tipo_vehiculo_sugerido_id   -- JOIN al recuperar la sugerencia en el formulario
```

**Decisión — sin `zona_predeterminada_id`:**
Un servicio tiene varias zonas asociadas (1:N). La asociación se modela en `zonas.tipo_servicio_id`. Al registrar un pesaje, el formulario filtra las zonas disponibles según el servicio elegido — el operador selecciona la zona de la lista filtrada. No hay una única "zona predeterminada" por servicio.

---

### `tipos_servicio_turnos`

Turnos disponibles por tipo de servicio. Si un servicio no tiene filas en esta tabla, el formulario de pesaje no muestra el campo turno. Si tiene filas, el operador debe seleccionar un turno obligatoriamente.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `tipo_servicio_id` | `bigint` | NO | — | PK compuesta, FK → `tipos_servicio.id` | — |
| `turno` | `nvarchar(10)` | NO | — | PK compuesta, CHECK IN (`'Diurna'`, `'Nocturna'`) | — |

**PK compuesta:** `(tipo_servicio_id, turno)` — impide duplicados, no se necesita columna `id`.

**FK behavior:**
- `tipo_servicio_id`: `ON DELETE CASCADE` — al eliminar un tipo de servicio se eliminan sus turnos.

**Índices:**
```sql
PK   (tipo_servicio_id, turno)   -- lookup directo: ¿qué turnos tiene este servicio?
```

**Datos iniciales (seeder):**
| tipo_servicio | turno |
|---------------|-------|
| Domiciliario | Diurna |
| Domiciliario | Nocturna |

---

### `zonas`

Áreas geográficas de recolección. Cada zona pertenece a un tipo de servicio (relación N:1). Al registrar un pesaje, el formulario filtra las zonas según el servicio elegido.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `nombre` | `nvarchar(150)` | NO | — | UNIQUE | Ej: `'Zona Norte'` |
| `tipo_servicio_id` | `bigint` | SÍ | NULL | FK → `tipos_servicio.id` | Clasificación para reportes y filtrado en el formulario de pesaje. Nullable: zona puede existir sin servicio asignado. |
| `hectareas` | `decimal(10,2)` | SÍ | NULL | CHECK >= 0 | NULL = dato no disponible. 0 = verificado como cero. |
| `barrios` | `int` | SÍ | NULL | CHECK >= 0 | — |
| `habitantes` | `int` | SÍ | NULL | CHECK >= 0 | NULL = dato no disponible. 0 = verificado como cero. Afecta cálculo per cápita en reportes. |
| `activo` | `bit` | NO | `1` | — | — |
| `created_at` | `datetime2(0)` | SÍ | NULL | — | — |
| `updated_at` | `datetime2(0)` | SÍ | NULL | — | — |

**FK behavior:**
- `tipo_servicio_id`: `ON DELETE SET NULL` — si se desactiva un servicio, las zonas quedan sin clasificación pero no se pierden.

**Índices:**
```sql
PK   id
UQ   nombre
IX   tipo_servicio_id            -- filtrado en formulario de pesaje + agrupación en reportes
     WHERE activo = 1            -- filtered index: solo zonas activas en el formulario
```

**Semántica NULL vs 0:**
`hectareas = NULL` → dato no cargado, indicadores de densidad no se calculan.
`hectareas = 0` → zona verificada sin área (improbable en la operación real, pero válido).
El servicio de reportes debe distinguir ambos casos: NULL produce `null` en el resultado, 0 producería división por cero (manejar con `NULLIF`).

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

### `pesajes`

Tabla operacional central. Cada fila es un camión que entró al predio. Se escribe una vez (al registrar el ingreso) y puede modificarse con auditoría.

| Columna | Tipo | Nullable | Default | Constraints | Descripción |
|---------|------|----------|---------|-------------|-------------|
| `id` | `bigint` | NO | IDENTITY | PK | — |
| `vehiculo_id` | `bigint` | NO | — | FK → `vehiculos.id` | — |
| `operador_id` | `bigint` | NO | — | FK → `users.id` | Usuario que registró el pesaje |
| `tipo_servicio_id` | `bigint` | NO | — | FK → `tipos_servicio.id` | — |
| `zona_id` | `bigint` | NO | — | FK → `zonas.id` | — |
| `turno` | `nvarchar(10)` | SÍ | NULL | CHECK IN (`'Diurna'`, `'Nocturna'`) | NULL cuando el servicio no tiene turnos. Obligatorio (validado en app) cuando `tipos_servicio_turnos` tiene filas para el servicio. |
| `peso_bruto_kg` | `int` | NO | — | CHECK > 0 | Peso que muestra la balanza al ingreso |
| `peso_tara_kg` | `int` | NO | — | CHECK > 0 | Copiado de `vehiculos.tara_kg` al crear. **No se recalcula si el padrón cambia.** |
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
`ON DELETE RESTRICT` en todas — nunca se puede eliminar un vehículo, usuario, servicio o zona que tenga pesajes asociados. Esto garantiza la integridad del historial.

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

-- Reportes: agrupación por zona y por servicio en un rango de fechas
IX_pesajes_zona_fecha
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
| `descripcion` | `nvarchar(500)` | NO | — | — | Mensaje legible. Ej: `'Sin pesajes en Zona Norte hace 90 minutos'` |
| `zona_id` | `bigint` | SÍ | NULL | FK → `zonas.id` | Zona afectada (para `gap_pesajes` y `frecuencia_atipica`) |
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
Si ya existe una alarma activa del mismo tipo para la misma zona, no crea una nueva.

**Índices:**
```sql
PK   id
IX   resuelta, tipo                             -- dashboard: COUNT alarmas activas por tipo
IX   tipo, zona_id, resuelta                    -- anti-duplicado en detección
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
| `frecuencia_atipica` | `50` | porcentaje | Desviación respecto al promedio histórico por zona para disparar alarma |
| `peso_inusual` | — | — | El umbral viene de `tipos_vehiculo.peso_min_kg` y `peso_max_kg`. Esta fila solo controla si el tipo está activo. `umbral_valor = 0` cuando el tipo usa fuente externa. |

**Nota:** `peso_inusual` no usa `umbral_valor` propio — los rangos están en `tipos_vehiculo`. La fila existe únicamente para el toggle `activo` y la coherencia de la tabla.

**Índices:**
```sql
PK   id
UQ   tipo      -- lookup directo por tipo (frecuente en detección)
```

---

## Diagrama de relaciones

```
users
  │
  ├──(operador_id)──────────────────────── pesajes ─┬──(vehiculo_id)────── vehiculos
  │                                                   │                         │
  ├──(usuario_id)──────────────────── pesajes_log     ├──(tipo_servicio_id)─ tipos_servicio ──(tipo_vehiculo_sugerido_id)── tipos_vehiculo
  │                                                   │                         │                                               │
  └──(resuelta_por)──────────────────── alarmas ──────├──(zona_id)────────── zonas ──(tipo_servicio_id)──────────────────────────┤
                                             │         │                         │                                               │
                                             ├─(zona_id)                         └── tipos_servicio_turnos                       │
                                             ├─(vehiculo_id)               tipos_vehiculo ◄──(tipo_vehiculo_id)─── vehiculos ───┘
                                             └─(pesaje_id)
```

**Cardinalidades:**

| Relación | Cardinalidad |
|----------|-------------|
| `users` → `pesajes` | 1:N — un operador registra muchos pesajes |
| `users` → `pesajes_log` | 1:N — un usuario puede tener muchas entradas de log |
| `vehiculos` → `pesajes` | 1:N — un vehículo tiene muchos pesajes históricos |
| `tipos_vehiculo` → `vehiculos` | 1:N — un tipo tiene muchos vehículos |
| `tipos_servicio` → `pesajes` | 1:N — un servicio aparece en muchos pesajes |
| `tipos_servicio` → `tipos_servicio_turnos` | 1:N — un servicio tiene 0, 1 o 2 turnos disponibles |
| `tipos_servicio` → `zonas` | 1:N — un servicio tiene varias zonas |
| `zonas` → `pesajes` | 1:N — una zona aparece en muchos pesajes |
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
SELECT p.*, v.patente, v.numero_interno, ts.nombre AS servicio, z.nombre AS zona
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

### Reporte — Detalle por zona en un período

```sql
SELECT
    z.nombre,
    COUNT(p.id)                    AS pesajes,
    SUM(p.peso_neto_kg) / 1000.0   AS toneladas,
    SUM(p.peso_neto_kg) * 1.0 / NULLIF(z.habitantes, 0) AS per_capita_kg
FROM pesajes p
JOIN zonas z ON z.id = p.zona_id
WHERE p.created_at BETWEEN ? AND ?
GROUP BY z.id, z.nombre, z.habitantes
```
**Índice usado:** `IX_pesajes_zona_fecha` con INCLUDE de `peso_neto_kg`

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

### Detección de alarma — Gap de pesajes por zona

```sql
SELECT zona_id, MAX(created_at) AS ultimo_pesaje
FROM pesajes
WHERE created_at >= DATEADD(HOUR, -4, GETDATE())
GROUP BY zona_id
HAVING DATEDIFF(MINUTE, MAX(created_at), GETDATE()) > (
    SELECT umbral_valor FROM config_alarmas WHERE tipo = 'gap_pesajes'
)
```
**Índice usado:** `IX_pesajes_zona_fecha`

---

## Decisiones de diseño documentadas

| Decisión | Alternativa descartada | Razón |
|----------|----------------------|-------|
| `peso_neto_kg` como columna regular | PERSISTED computed column en SQL Server | El computed column impide log granular de cambio: si se edita `peso_bruto_kg`, necesitamos registrar también el cambio derivado en `peso_neto_kg` en `pesajes_log`. Mejor calcularlo en el Service. |
| `activo bit` en lugar de `SoftDeletes` | `deleted_at datetime` (Laravel SoftDeletes) | El ABM admin necesita mostrar registros inactivos en la tabla. SoftDeletes oculta los registros por defecto y requiere `withTrashed()` en cada query. `activo` es explícito y no requiere scopes globales. |
| `tipos_servicio_turnos` tabla separada + `pesajes.turno` | `tipos_servicio.turno` campo único | Un servicio puede tener varios turnos disponibles (Domiciliario: Diurna y Nocturna). La tabla separada modela la cardinalidad correcta. `pesajes.turno` persiste qué turno eligió el operador al registrar. Validación en app: si el servicio tiene turnos, el campo es obligatorio; si no, queda NULL. |
| Sin `zona_predeterminada_id` en `tipos_servicio`; filtrado por `zonas.tipo_servicio_id` | FK circular `tipos_servicio ↔ zonas` con zona predeterminada única | Un servicio tiene varias zonas (1:N). No existe "una zona predeterminada" — el operador elige de la lista filtrada por servicio. Esto simplifica el modelo: FK unidireccional `zonas → tipos_servicio`, sin ciclos, sin migración en 3 pasos. |
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
1.  users                      (sin FK externas)
2.  tipos_vehiculo              (sin FK externas)
3.  tipos_servicio              (FK → tipos_vehiculo)
4.  tipos_servicio_turnos       (FK → tipos_servicio)
5.  zonas                       (FK → tipos_servicio)
6.  vehiculos                   (FK → tipos_vehiculo)
7.  pesajes                     (FK → vehiculos, users, tipos_servicio, zonas)
8.  pesajes_log                 (FK → pesajes, users)
9.  config_alarmas              (sin FK externas)
10. alarmas                     (FK → zonas, vehiculos, pesajes, users)
```

**Rollback:** el orden inverso (9 → 1).

---

*Documento generado: 13/05/2026 | Versión: 1.0*
