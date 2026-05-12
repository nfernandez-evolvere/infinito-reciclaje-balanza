# Roadmap de Desarrollo — Etapa 1
## Sistema de Gestión de Balanza — Infinito Reciclaje × EVOLVERE 2026

**Inicio:** 12/05/2026 | **Go-live:** 14/07/2026 | **Duración:** 9 semanas

---

## Stack técnico

| Capa | Tecnología | Notas |
|------|-----------|-------|
| Frontend | Laravel Blade + Tailwind v4 + Alpine.js | Design system `x-ui.*` existente |
| Backend | Laravel 13 (PHP 8.3) | Patrón Repository + Service + Resource Controller |
| Base de datos | SQL Server | Driver `sqlsrv` |
| Auth | Laravel Breeze (Blade) | Vistas reescritas con `x-ui.*` |
| Roles | Campo `role` en `users` (`operador` \| `admin`) | Middleware + Gates |
| Gráficos | Chart.js | Integrado en vistas Blade |
| PDF | `knplabs/snappy` + wkhtmltopdf | Ruta del binario vía `.env` |
| Excel | `maatwebsite/laravel-excel` | — |

---

## Arquitectura de información

### Pantallas y navegación

**Shell del operador** — header fijo con nav inline (sin sidebar):
```
Header: logo · reloj en vivo · estado conexión · chip usuario · logout
Nav:    [Pesaje]  [Historial]
Footer: último pesaje (patente · neto · hora) · totales del turno · camiones en predio
```

| Pantalla | Ruta | Descripción |
|----------|------|-------------|
| Login | `/login` | Redirección por rol al guardar |
| Balanza | `/balanza` | Formulario de 3 pasos + barra de acción sticky |
| Historial | `/historial` | Pesajes del turno con egreso, edición e historial |

**Shell del admin** — sidebar izquierdo en acordeón, 240px:
```
Grupo Operación:  Dashboard · Pesajes
Grupo Padrones:   Vehículos · Zonas · Tipos de servicio · Tipos de vehículo · Usuarios
Grupo Análisis:   Reportes
Footer sidebar:   avatar · nombre · rol · logout
```

| Pantalla | Ruta | Descripción |
|----------|------|-------------|
| Dashboard | `/admin/dashboard` | KPIs, gráficos, alertas, camiones en predio |
| Pesajes | `/admin/pesajes` | Log completo filtrable con edición y auditoría |
| Vehículos | `/admin/vehiculos` | ABM padrón |
| Zonas | `/admin/zonas` | ABM zonas |
| Tipos de servicio | `/admin/servicios` | ABM servicios con cascada |
| Tipos de vehículo | `/admin/tipos-vehiculo` | ABM tipos con rangos de peso |
| Usuarios | `/admin/usuarios` | ABM usuarios con rol y turno |
| Reportes | `/admin/reportes` | Filtros + preview + exportación PDF/Excel |

---

## Arquitectura de permisos

**3 capas:**

1. **Rutas** — Middleware `EnsureRole` agrupado por rol
2. **Lógica** — Gates en `AppServiceProvider`
3. **Vistas** — Directiva `@can` para ocultar elementos de UI

```
Operador → /balanza, /historial
Admin    → /admin/*
```

| Gate | Operador | Admin |
|------|----------|-------|
| `record-weighing` | ✅ | — |
| `view-own-historial` | ✅ | — |
| `edit-pesaje` | ✅ (propios del turno) | ✅ (todos) |
| `manage-masters` | — | ✅ |
| `view-dashboard` | — | ✅ |
| `manage-usuarios` | — | ✅ |

Helpers en `User`: `isAdmin()`, `isOperador()`

---

## Esquema de base de datos

```
users
    id, name, email, password,
    role    (operador | admin),
    turno   (Mañana | Tarde | Noche | null)   ← null para admins

tipos_vehiculo
    id, nombre, peso_min_kg, peso_max_kg, activo

tipos_servicio
    id, nombre, tipo_vehiculo_sugerido_id (FK → tipos_vehiculo), activo

zonas
    id, nombre, tipo_servicio_id (FK → tipos_servicio),
    hectareas, habitantes, barrios, activo

vehiculos
    id, patente, numero_interno, tara_kg,
    tipo_vehiculo_id (FK → tipos_vehiculo),
    titular, capacidad_kg, observaciones, activo

pesajes
    id,
    vehiculo_id (FK → vehiculos),
    operador_id (FK → users),
    tipo_servicio_id (FK → tipos_servicio),
    zona_id (FK → zonas),
    peso_bruto_kg,
    peso_tara_kg,        ← copiado del padrón al registrar (preserva historial)
    peso_neto_kg,        ← calculado al ingreso: peso_bruto - peso_tara
    alerta_peso (bool),
    observaciones,       ← autocompleta del padrón, editable por el operador
    estado               (En predio | Cerrado),
    hora_salida          (nullable datetime),   ← timestamp de egreso
    bruto_salida_kg      (nullable int),        ← peso de salida opcional, solo audit
    editado (bool),      ← true si fue editado post-registro
    created_at

pesajes_log
    id, pesaje_id (FK → pesajes),
    campo, valor_anterior, valor_nuevo,
    motivo,              ← obligatorio en toda edición
    usuario,             ← nombre del operador o admin que editó
    created_at

alarmas
    id, tipo, descripcion,
    zona_id (FK → zonas, nullable),
    vehiculo_id (FK → vehiculos, nullable),
    resuelta (bool), created_at

config_alarmas
    id, tipo, umbral_min, umbral_max, activo
```

### Decisiones de diseño confirmadas

| Decisión | Definición |
|----------|-----------|
| Integración balanza física | **Fuera de Etapa 1.** Ingreso de peso manual con validación de rango. A evaluar para Etapa 2. |
| `observaciones` en pesajes | Dos niveles: padrón (estático, autocompleta) + por viaje (editable por el operador). |
| Egreso | Solo trazabilidad: `hora_salida` + `bruto_salida_kg` opcional (guardado, no usado en cálculo del neto). Estado `En predio` → `Cerrado`. |
| `peso_tara_kg` en pesajes | Se copia del padrón al momento del ingreso para preservar historial si el padrón cambia. |
| Edición de pesajes | Ambos roles pueden editar con `motivo` obligatorio. Cada campo editado genera una entrada en `pesajes_log`. |
| Edición por operador | El operador puede editar sus propios pesajes del turno activo desde Historial. Misma lógica auditable que el admin. |

---

## UX Writing — reglas para todas las vistas Blade

Voz: **español operativo argentino** — directo, sin rodeos, como un colega en la caseta.

| Regla | Aplicación |
|-------|-----------|
| **Voseo solo en verbos imperativos** | `Seguí los tres pasos`, `Ingresá el peso`, `Generá el reporte` |
| **Labels y chrome en tercera persona** | `Peso bruto`, `Tipo de servicio`, `Último pesaje del turno` |
| **Sentence case** en todo | `Guardar pesaje`, no `Guardar Pesaje` ni `GUARDAR PESAJE` |
| **Excepción CTA principal de Balanza** | `GUARDAR PESAJE` en mayúsculas — Roberto lo usa 40+ veces por turno |
| **Status pills en mayúsculas** | `EN PREDIO`, `CERRADO`, `ACTIVO`, `INACTIVO` |
| **Sin exclamaciones** | El sistema narra estado, no celebra |
| **Sin microcopy relleno** | Nunca `¡Listo!`, `¡Genial!`, `¡Éxito!` |
| **Validación fuera de rango** | `Fuera del rango habitual para Compactador (10.000 – 26.500 kg). La validación no bloquea el guardado.` |
| **Save success** | `Pesaje guardado` + animación de check — dos palabras, sin más |
| **Empty state** | `Sin pesajes en este turno todavía.` — amigable, con punto, sin exclamación |
| **Formato numérico** | `8.500 kg`, `142,5 t`, `1,3 kg/ha` — siempre con unidad, separador de miles con punto |
| **Fechas y hora** | `dd/mm/yyyy` · hora `14:32` (24 h) |
| **Sin emoji en UI** | Siempre Lucide icons — nunca ⚠️ ✅ ⚫ |

---

## Datos maestros iniciales (seeders)

Deben estar listos desde el Sprint 2 para que el módulo Balanza funcione.

**Rangos de peso por tipo de vehículo:**

| Tipo | Peso mínimo (kg) | Peso máximo (kg) |
|------|-----------------|-----------------|
| Compactador | 10.000 | 26.500 |
| Volcador | 13.000 | 30.000 |
| Volquete | 7.000 | 20.000 |
| Particular | 1.000 | 5.000 |

**Tipos de servicio:** Domiciliario, Voluminoso, Barrido, Servicios Especiales, Centros de Transferencia

---

## Sprint 1 — Cimientos
**Semana 1 · 12–16 mayo**

### Objetivo
Base técnica funcional: conexión a SQL Server, autenticación real con 2 roles, layouts diferenciados por perfil.

### Tareas

**Configuración SQL Server**
- [ ] Configurar driver `sqlsrv` en `database.php`
- [ ] Variables en `.env` para conexión local y producción
- [ ] Verificar conexión con `php artisan db:show`

**Autenticación con Breeze**
- [ ] `composer require laravel/breeze --dev`
- [ ] `php artisan breeze:install blade`
- [ ] Migración `users`: campos `role` (enum) y `turno` (nullable)
- [ ] Reescribir vista `login` con componentes `x-ui.*` y UX Writing del sistema
- [ ] Eliminar vista `register` de Breeze — usuarios solo se crean desde ABM Usuarios

**Middleware y Gates**
- [ ] `app/Http/Middleware/EnsureRole.php`
- [ ] Registrar en `bootstrap/app.php` como `role`
- [ ] Gates en `AppServiceProvider`: `record-weighing`, `view-own-historial`, `edit-pesaje`, `manage-masters`, `view-dashboard`, `manage-usuarios`
- [ ] Helpers en `User`: `isAdmin()`, `isOperador()`

**Layouts**
- [ ] `layouts/operador.blade.php` — header con reloj en vivo, nav inline (Pesaje / Historial), footer de turno sticky
- [ ] `layouts/admin.blade.php` — sidebar acordeón (Operación · Padrones · Análisis), footer de usuario
- [ ] Grupos de rutas con middleware por rol

**Seeders base**
- [ ] `UserSeeder`: 1 operador (roberto, turno Tarde) + 1 admin (nacho) de prueba
- [ ] `DatabaseSeeder` orquestando el orden correcto

### Entregable
Login funcional → redirección al layout correcto según rol. Rutas protegidas. Layouts con navegación operativa.

---

## Sprint 2 — ABMs completos
**Semanas 2–3 · 19–30 mayo**

### Objetivo
Los 5 ABMs 100% funcionales. Condición crítica de go-live: sin padrón completo las automatizaciones del módulo Balanza no funcionan.

### Tareas

**Migraciones**
- [ ] `create_tipos_vehiculo_table`
- [ ] `create_tipos_servicio_table`
- [ ] `create_zonas_table`
- [ ] `create_vehiculos_table`

**ABM Tipos de vehículo**
- [ ] `TipoVehiculoRepository`, `TipoVehiculoService`
- [ ] `TipoVehiculoController` (resource)
- [ ] Form Requests: `StoreTipoVehiculoRequest`, `UpdateTipoVehiculoRequest`
- [ ] Vista index: tabla con rangos de peso + banner informativo (rangos son orientativos, nunca bloquean)
- [ ] Modal crear/editar inline (mismo patrón en todos los ABMs)
- [ ] Seeder con los 4 tipos y rangos del brief

**ABM Tipos de servicio**
- [ ] `TipoServicioRepository`, `TipoServicioService`
- [ ] `TipoServicioController` (resource)
- [ ] Form Requests correspondientes
- [ ] Vista index: tabla con `zonaPredeterminada` y `tipoSugerido` visibles y editables
- [ ] Modal crear/editar con select de tipo de vehículo sugerido
- [ ] Seeder con los 5 tipos de servicio

**ABM Zonas**
- [ ] `ZonaRepository`, `ZonaService`
- [ ] `ZonaController` (resource)
- [ ] Form Requests correspondientes
- [ ] Vista index: tabla con hectáreas, barrios y total en el header
- [ ] Modal crear/editar con campos: nombre, servicio asociado, hectáreas, barrios, habitantes

**ABM Padrón de vehículos**
- [ ] `VehiculoRepository`, `VehiculoService`
- [ ] `VehiculoController` (resource)
- [ ] Form Requests: `StoreVehiculoRequest`, `UpdateVehiculoRequest`
- [ ] Vista index: tabla con búsqueda (patente / número interno) + filtro activo/inactivo + pills de estado
- [ ] Modal crear/editar con todos los campos del padrón
- [ ] Baja lógica (`activo`) — nunca DELETE físico para preservar historial de pesajes

**ABM Usuarios**
- [ ] `UsuarioRepository`, `UsuarioService`
- [ ] `UsuarioController` (resource)
- [ ] Form Requests: `StoreUsuarioRequest`, `UpdateUsuarioRequest`
- [ ] Vista index: tabla con avatar+nombre, pill de rol, turno, estado
- [ ] Modal crear: usuario, nombre completo, rol, turno, contraseña inicial
- [ ] Acciones por fila: editar, resetear contraseña, activar/desactivar
- [ ] Baja lógica — nunca eliminar un usuario que tiene pesajes registrados

### Entregable
Admin puede cargar el padrón completo. 5 ABMs funcionales. Usuarios gestionables sin acceso a la DB.

---

## Sprint 3 — Módulo Balanza
**Semanas 4–5 · 2–13 junio**

### Objetivo
Pantalla principal del operador: flujo completo de pesaje en menos de 10 segundos, y Historial del turno con egreso y edición auditada.

### Tareas

**Migraciones y modelos**
- [ ] `create_pesajes_table` (con todos los campos definidos en el schema)
- [ ] `create_pesajes_log_table`
- [ ] `PesajeRepository`, `PesajeService`
- [ ] `PesajeLogRepository`
- [ ] `PesajeController`
- [ ] Form Requests: `StorePesajeRequest`, `UpdatePesajeRequest`, `EgresoPesajeRequest`

**API de autocompletado**
- [ ] `GET /api/vehiculos/buscar?q={patente_o_numero}` → devuelve tara, tipo, titular, capacidad, observaciones (solo vehículos activos)
- [ ] `GET /api/servicios/{id}/zona` → devuelve zona predeterminada y tipo de vehículo sugerido

**Formulario Balanza — 3 pasos secuenciales (Alpine.js)**
- [ ] Indicadores de paso numerados (1→2→3) con check verde al completar; pasos futuros dimmed
- [ ] **Paso 1 — Vehículo:** input libre patente/número interno, popper de autocompletado (hasta 6 matches), Enter selecciona primer match, badges de solo lectura: Tara · Tipo · Titular · Interno
- [ ] **Paso 2 — Tipo de servicio:** select nativo, cascade a zona + badge tipo habitual (azul); warning naranja si el tipo del vehículo no coincide con el sugerido (nunca override)
- [ ] **Paso 3 — Peso bruto:** input numérico 72px estilo display, Tara y Neto estimado a la derecha, borde verde si en rango / naranja si fuera de rango, hint de rango siempre visible
- [ ] Campo `observaciones` autocompleta desde el padrón, editable
- [ ] Summary card final (verde suave cuando el form está completo): vehículo, servicio, zona, tipo, bruto, tara, neto, operador
- [ ] Barra de acción sticky: `Limpiar (Esc)` · hint contextual · `GUARDAR PESAJE (Ctrl+S)`
- [ ] Atajos de teclado: `↵` avanza campo, `Ctrl+S` guarda, `Esc` limpia — chips visibles en la pantalla
- [ ] Overlay de éxito post-guardado: check animado, `Pesaje guardado`, auto-dismiss 1,1 s, foco vuelve al input de vehículo
- [ ] Confirmación de logout si hay form sucio (pesaje sin guardar)

**Historial del turno**
- [ ] KPIs pequeños en el header: pesajes, toneladas netas, promedio por viaje, camiones en predio
- [ ] Tabla del turno: entrada · salida · estado (pill) · patente · servicio · zona · bruto · tara · neto; pill azul `Editado` en filas modificadas
- [ ] Empty state: `Sin pesajes en este turno todavía.`
- [ ] **Acción Marcar egreso** (solo en filas `En predio`): modal que captura hora actual y `bruto_salida_kg` opcional; confirmar → estado `Cerrado`
- [ ] **Acción Editar** (propios del turno): modal con campos editables y `motivo` obligatorio; cada campo modificado genera entrada en `pesajes_log`
- [ ] **Acción Ver historial**: modal read-only con el log de cambios (campo · anterior → nuevo · motivo · usuario · fecha)

### Entregable
Operador registra pesaje completo en < 10 seg. Historial con egreso, edición auditada e historial de cambios.

---

## Sprint 4 — Pesajes admin + Dashboard
**Semanas 6–7 · 16–27 junio**

### Objetivo
Visibilidad completa de la operación para el admin: log filtrable de todos los pesajes y panel de análisis en tiempo real.

### Tareas

**Pesajes (admin) — log filtrable**
- [ ] `PesajesAdminController`
- [ ] Filtros: búsqueda (patente / ID), estado (Todos / En predio / Cerrado), zona, servicio, operador
- [ ] Header con conteo filtrado + toneladas netas totales de la vista actual
- [ ] Tabla: ID · entrada · salida · estado · patente · servicio · zona · bruto · tara · neto · operador; pill `Editado` en filas modificadas
- [ ] Mismas acciones que el operador: Marcar egreso · Editar (con motivo) · Ver historial
- [ ] Exportar Excel de la vista filtrada

**Dashboard**
- [ ] `DashboardController` con lógica de agregación
- [ ] Banners de alertas activas en la parte superior (con botón `Revisar`)
- [ ] Widget `Camiones en el predio`: solo aparece si hay registros `En predio`; tabla con patente, tipo, servicio, zona, hora entrada, neto, operador
- [ ] KPIs del día (4 cards): pesajes, toneladas, promedio por viaje, horas operativas — cada una con delta vs. promedio histórico
- [ ] KPIs del mes (3 cards): pesajes acumulados, toneladas acumuladas, días operativos
- [ ] Gráfico evolución diaria (barras, 7 días): hoy destacado en verde oscuro, días anteriores en verde claro, línea de promedio punteada
- [ ] Tabla por zona: pesajes, toneladas, kg/ha
- [ ] Tabla por tipo de vehículo: viajes, toneladas, barra horizontal de % del total

### Entregable
Admin ve log completo de pesajes editable y panel de análisis con KPIs, gráficos y alertas.

---

## Sprint 5 — Reportes automáticos
**Semana 7–8 · 30 junio – 4 julio**

### Objetivo
Reemplazar 2–3 horas de Excel manual por generación en menos de 5 minutos.

### Tareas

**Filtros y generación**
- [ ] `ReporteController` con formulario de filtros
- [ ] Filtros: período (desde/hasta), zona, tipo de servicio, tipo de vehículo
- [ ] Pills de filtros activos bajo el formulario
- [ ] `ReporteService` con lógica de agregación y cálculos
- [ ] Estado vacío antes de generar: `Aplicá los filtros y generá el reporte para ver la vista previa.`

**Preview del reporte**
- [ ] 4 KPIs de resumen
- [ ] Gráfico de barras de evolución diaria (Chart.js)
- [ ] Tabla por zona (pesajes, toneladas, densidad kg/ha)
- [ ] Tabla por tipo de vehículo (viajes, toneladas, % — barra visual)
- [ ] Sección densidad de generación (kg/ha por zona)
- [ ] Reporte per cápita por zona: kg ÷ habitantes

**Exportación PDF**
- [ ] Instalar y configurar `knplabs/snappy`
- [ ] Variable `.env`: `WKHTMLTOPDF_BINARY` (ruta diferente en Windows dev vs Linux prod)
- [ ] Config: `'options' => ['no-sandbox' => true]` para servidores Linux headless
- [ ] Template Blade del reporte con diseño profesional (para entregar al municipio)
- [ ] Ruta `GET /admin/reportes/pdf` con `Content-Disposition: attachment`

**Exportación Excel**
- [ ] Instalar `maatwebsite/laravel-excel`
- [ ] `ReporteExport` class con datos crudos
- [ ] Ruta `GET /admin/reportes/excel`

### Entregable
Admin genera y descarga reporte en PDF y Excel en menos de 5 minutos, con preview en pantalla antes de exportar.

---

## Sprint 6 — Alarmas + QA
**Semanas 8–9 · 7–14 julio**

### Objetivo
Detección proactiva de anomalías. QA end-to-end con datos reales. Buffer para correcciones previas al go-live.

### Tareas

**Migración y modelos**
- [ ] `create_alarmas_table`
- [ ] `create_config_alarmas_table`
- [ ] `AlarmaRepository`, `AlarmaService`

**Lógica de detección**
- [ ] Volumen diario fuera de rango histórico (por encima o por debajo)
- [ ] Kg por viaje inusual para el tipo de vehículo
- [ ] Frecuencia por zona atípica
- [ ] Gaps en el registro: períodos sin pesajes en horario operativo (8:00–18:00)

**Scheduler Laravel**
- [ ] `DetectarAnomalias` command
- [ ] Registro en `routes/console.php`: cada hora en horario operativo
- [ ] Crear entrada en `alarmas` si se detecta condición

**UI de alarmas**
- [ ] Banners en dashboard: surface naranja/roja con borde izquierdo semántico + botón `Revisar`
- [ ] `AlarmaController` (index + configuración)
- [ ] Vista: listado de alarmas con tipo, descripción, fecha, zona/vehículo afectado, estado
- [ ] Vista: configurar umbrales por tipo (editable por admin)
- [ ] Acción: marcar alarma como resuelta

**QA**
- [ ] Cargar padrón completo real antes del go-live
- [ ] Test del flujo de pesaje completo con operador real (Roberto)
- [ ] Test de edición con motivo y verificación del log de auditoría
- [ ] Test del dashboard y pesajes admin con datos reales de los últimos meses
- [ ] Verificar generación de PDF y Excel en servidor Linux
- [ ] Buffer de 2 días para correcciones

### Entregable
Sistema completo y operativo. Alarmas detectando anomalías reales. Listo para go-live el 14/07/2026.

---

## Criterios de go-live

| Módulo | Criterio |
|--------|---------|
| Balanza | Flujo completo de pesaje en < 10 segundos |
| Balanza | Autocompletado funciona para el 100% del padrón cargado |
| Balanza | Validación de peso detecta valores fuera de rango sin bloquear |
| Historial | Egreso, edición con motivo e historial de cambios operativos |
| ABMs | 5 ABMs funcionales (incluyendo Usuarios) |
| ABMs | Padrón de vehículos 100% cargado con datos reales |
| Pesajes admin | Log filtrable con edición auditada operativo |
| Dashboard | KPIs correctos, gráficos en menos de 3 segundos, alertas visibles |
| Reportes | Generación correcta con filtros + exportación PDF y Excel en Linux |
| Alarmas | Detección de gaps y valores fuera de rango, visibles en dashboard |
| General | Login con 2 perfiles diferenciados (operador / admin) |
| General | Sistema operativo el día 1 con padrón cargado |

---

## Fuera del alcance (Etapa 1)

- Integración automática con balanza física (se evalúa en Etapa 2)
- Doble pesaje para cálculo de neto (bruto entrada − bruto salida): `bruto_salida_kg` se captura pero no se usa
- App mobile
- Integración con sistemas externos del municipio
- Multi-tenant (varios predios)
- API pública

---

*Documento generado: 12/05/2026 | Versión: 1.2 — Actualizado 12/05/2026*
*Cambios v1.2: IA alineada al prototipo — arquitectura de pantallas completa, tabla pesajes_log nueva, schema actualizado (estado, hora_salida, bruto_salida_kg, editado, turno en users, barrios en zonas), ABM Usuarios agregado, pantalla Pesajes admin agregada, Sprint 4 expandido, UX Writing incorporado como sección.*
