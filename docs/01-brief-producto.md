# Brief del Producto — Etapa 1
## Sistema de Gestión de Balanza
### Infinito Reciclaje × EVOLVERE 2026

---

## Resumen del producto

| | |
|---|---|
| **Producto** | Sistema de Gestión de Balanza |
| **Cliente** | Infinito Reciclaje — Municipalidad de Corrientes |
| **Desarrollador** | EVOLVERE 2026 |
| **Stack** | Laravel Blade + SQL Server |
| **Arquitectura** | Multi-tenant (varias organizaciones sobre una base) |
| **Inicio** | 12/05/2026 |
| **Go-live estimado** | 14/07/2026 |

Sistema web para el registro, control y análisis del ingreso y egreso de camiones de residuos en el predio de disposición final de la Municipalidad de Corrientes. Reemplaza un proceso manual con planillas y Excel por una operación automatizada, trazable y con inteligencia operacional en tiempo real.

> **Nota de versión.** Este brief documenta el alcance vigente. Respecto del documento original de Etapa 1, el producto incorporó: arquitectura **multi-tenant** (rol `super_admin` y aislamiento por organización), módulo de **reportes** con programación, revisión manual y conclusiones por IA, y el renombre del módulo de alarmas a **alertas**. El modelo de datos al día está en [`03-data-model.md`](03-data-model.md).

---

## Problema que resuelve

| Usuario | Problema hoy | Solución |
|---------|-------------|----------|
| Roberto (Operador) | 45–60 seg/pesaje, errores de transcripción, presión en picos horarios | Autocompletado + validación + flujo en < 10 seg |
| Nacho (Admin) | 2–3 horas/mes en Excel manual, sin visibilidad diaria, anomalías no detectadas | Dashboard diario + reportes automáticos + alertas proactivas |

---

## Usuarios del sistema

### Operador — Roberto
- Registra pesajes de camiones en tiempo real
- Opera en caseta en el predio, picos de 8+ camiones/hora (10:00–12:00)
- Necesita velocidad, autocompletado y confirmación visual clara

### Administrador — Nacho
- Mantiene datos maestros (padrón de vehículos, zonas, servicios)
- Genera reportes mensuales para la Municipalidad
- Necesita dashboard en tiempo real, reportes automáticos y alertas proactivas

### Super Admin — EVOLVERE (plataforma)
- Da de alta y administra las organizaciones (predios/clientes) del sistema
- Rol transversal, no operativo: no registra pesajes ni gestiona el padrón de una organización
- Acceso al panel de super administración (gestión de organizaciones y sus admins)

---

## Módulo 1 — Balanza (Registro de pesajes)
**Perfil**: Operador

### Objetivo
Registrar ingreso y egreso de camiones con mínima intervención manual.

### Requerimientos funcionales
- Identificación de vehículo por patente O número interno (single input)
- Autocompletado de tara, tipo, titular, capacidad y observaciones desde el padrón
- Selección de tipo de servicio con autocompletado de origen asociado
- Sugerencia automática de tipo de vehículo según servicio (editable manualmente)
- Input de peso bruto con validación por rango en tiempo real según tipo de vehículo
- Alerta soft si el peso está fuera de rango (sin bloqueo — el operador puede confirmar)
- Cálculo automático de kg netos: peso bruto − tara
- Registro de egreso del camión
- Historial de pesajes del turno actual
- Feedback visual inmediato al guardar (color + mensaje)

### Requerimientos no funcionales
- Flujo completo en menos de 10 segundos
- Operación resiliente: si hay fallas en datos externos, el sistema sigue funcionando
- Interfaz sin distracciones, optimizada para escritorio (1280px)

> **Nota Etapa 1 vs Etapa 2**: El peso se tipea manualmente con validación por rango. La integración automática con la balanza física se evalúa para Etapa 2. Dejar dependencia 100% en esa conexión dejaría sin operación ante una falla.

---

## Módulo 2 — ABMs (Administración de datos maestros)
**Perfil**: Administrador

### Objetivo
Mantener actualizados los datos que alimentan todas las automatizaciones del sistema.

### Entidades a gestionar

**Padrón de vehículos**
| Campo | Tipo | Notas |
|-------|------|-------|
| Patente | Texto | Identificador único |
| Número interno | Número | Alternativa para búsqueda |
| Tara | Número (kg) | Peso del vehículo vacío |
| Tipo de vehículo | Select | Compactador, Volcador, Volquete, Particular |
| Titular | Texto | Municipal o particular |
| Capacidad | Número (kg) | Peso máximo de carga |
| Observaciones | Texto | Notas libres |
| Estado | Boolean | Activo / Inactivo |

**Tipos de vehículos**: Compactador, Volcador, Volquete, Particulares (cada uno con rango de peso bruto mín/máx)

**Tipos de servicio**: Domiciliario, Voluminoso, Barrido, Servicios Especiales, Centros de Transferencia. Cada servicio puede sugerir **varios** tipos de vehículo (relación N:M, no un único sugerido).

**Zonas** (antes "Orígenes")
| Campo | Uso |
|-------|-----|
| Nombre | Identificación |
| Servicios asociados | Relación N:M con tipos de servicio; filtra las zonas disponibles en Balanza según el servicio elegido |
| Turnos y horarios por servicio | Configurables por zona: los turnos son texto libre (chips, sin catálogo — el admin escribe el nombre que necesite, ej. Diurna/Nocturna/Refuerzo); franjas horarias por día |
| Hectáreas | Cálculo densidad kg/ha |
| Barrios | Dato descriptivo |
| Habitantes | Reporte per cápita |
| Geometría (polígono) | Área dibujada en mapa (Leaflet + Geoman) para el mapa de calor del Dashboard y Reportes |

### Requerimientos funcionales
- CRUD completo para cada entidad (alta, baja lógica, modificación)
- Relaciones configurables: zona ↔ servicios (N:M con turnos/horarios), servicio → tipos de vehículo sugeridos (N:M)
- Validaciones de campos obligatorios (unicidad scopeada por organización)
- Búsqueda y filtrado en listados
- Baja lógica (no física) para mantener historial de pesajes

### Requerimientos no funcionales
- Interfaz simple, sin conocimientos técnicos requeridos
- Cambios reflejados inmediatamente en el módulo Balanza

> **Condición crítica de go-live**: Todos los ABMs deben estar 100% cargados antes del arranque operativo. Sin el padrón completo las automatizaciones no funcionan.

---

## Módulo 3 — Dashboard
**Perfil**: Administrador

### Objetivo
Visibilidad en tiempo real de la operación diaria. Reemplaza la necesidad de esperar al fin de mes para ver datos.

### Requerimientos funcionales

**KPIs principales** (período seleccionable):
- Días operativos
- Total de ingresos (viajes)
- Total kg / toneladas ingresadas
- Promedio kg/día
- Promedio kg/viaje

**Gráficos:**
- Evolución diaria de toneladas (barras) con línea de promedio, máximo y mínimo
- Distribución por tipo de vehículo: viajes, kg netos, % del total, kg/viaje
- KG netos por origen: tabla cruzada por tipo de vehículo (viajes + kg + %)
- Densidad de generación: kg/hectárea por origen

**Panel de alertas activas:**
- Visible en la parte superior si hay anomalías activas
- Descripción clara de cada alerta con fecha y hora

### Requerimientos no funcionales
- Actualización diaria
- Gráficos responsivos y legibles
- Selector de período (hoy, semana, mes, personalizado)
- Carga en menos de 3 segundos

---

## Módulo 4 — Reportes automáticos
**Perfil**: Administrador

### Objetivo
Reemplazar el proceso manual de Excel (2–3 horas/mes) por generación automática en menos de 5 minutos.

### Requerimientos funcionales

**Reporte mensual exportable:**
- Resumen general del período (total pesajes, toneladas, promedios)
- Evolución diaria de toneladas
- Desglose por tipo de vehículo (viajes, kg, %)
- Desglose por zona (viajes, kg, densidad kg/hectárea)
- Reporte per cápita por zona: kg generados ÷ habitantes
- Mapa de calor por zona (choropleth con la geometría cargada)
- Conclusiones narrativas generadas por IA (opcional, configurable por organización)

**Filtros disponibles:**
- Por período (mes, trimestre, rango personalizado)
- Por zona
- Por tipo de servicio
- Por tipo de vehículo

**Exportación y envío:**
- PDF con formato profesional (para entregar al municipio)
- Excel con datos crudos (para análisis adicional) — o ambos (`pdf+excel`)
- Envío por email a destinatarios (libreta de contactos frecuentes por organización)

**Reportes programados:**
- Envíos automáticos por cron (mensual / semanal / personalizado)
- Flujo de revisión manual opcional: un envío programado puede quedar pendiente de aprobación del admin antes de salir (configurable por organización y por programado)
- Historial de reportes generados/enviados con `snapshot` congelado: cada reporte se puede re-descargar idéntico aunque los datos vivos cambien después

### Requerimientos no funcionales
- Generación del reporte en menos de 30 segundos
- Formato PDF consistente y presentable (render headless con Chromium)
- Generación manual asistida; los envíos programados corren sin intervención (salvo la aprobación, si está activada)

---

## Módulo 5 — Alertas
**Perfil**: Administrador

### Objetivo
Detección proactiva de anomalías sin esperar al fin de mes.

### Requerimientos funcionales

**Detecciones automáticas (tipos):**
- `peso_fuera_rango` — peso bruto fuera del rango del tipo de vehículo
- `volumen_diario_atipico` — volumen diario desviado del promedio histórico (umbral en %)
- `gap_registro` — período sin pesajes durante el horario operativo (umbral en minutos)
- `frecuencia_zona_atipica` — frecuencia de pesajes por zona desviada del promedio (umbral en %)

**Notificaciones:**
- Alerta visible en el dashboard (panel superior)
- Descripción clara: qué pasó, cuándo, en qué zona o vehículo
- Se marcan como **leídas** (no requieren comentario de resolución)

**Configuración:**
- El admin puede activar/desactivar cada tipo y ajustar su umbral
- El horario operativo (usado por `gap_registro`) es configurable por organización

### Requerimientos no funcionales
- Monitoreo durante el horario operativo
- Sin falsos positivos excesivos (umbrales calibrados con histórico real)

---

## Perfiles y permisos

| Módulo | Operador | Admin | Super Admin |
|--------|----------|-------|-------------|
| Balanza — registro de pesajes | ✅ Lectura y escritura | ✅ Solo lectura | ❌ Sin acceso |
| ABMs | ❌ Sin acceso | ✅ Lectura y escritura | ❌ Sin acceso |
| Dashboard | ❌ Sin acceso | ✅ Lectura | ❌ Sin acceso |
| Reportes (manuales y programados) | ❌ Sin acceso | ✅ Generación, envío y revisión | ❌ Sin acceso |
| Alertas | ❌ Sin acceso | ✅ Configuración y recepción | ❌ Sin acceso |
| Gestión de organizaciones | ❌ Sin acceso | ❌ Sin acceso | ✅ Alta y administración |

> El acceso del admin y el operador está siempre acotado a su organización (multi-tenant). El super admin opera por encima de las organizaciones.

---

## Automatizaciones del sistema

| Automatización | Disparador | Resultado |
|----------------|-----------|-----------|
| Autocompletado por vehículo | Ingresar patente o número interno | Completa tara, tipo, titular, capacidad |
| Filtrado de zonas por servicio | Seleccionar tipo de servicio | Muestra solo las zonas que operan ese servicio |
| Sugerencia de vehículo | Seleccionar servicio | Sugiere los tipos de vehículo del servicio (editable) |
| Validación de peso | Ingresar peso bruto | Valida contra rango del tipo de vehículo |
| Cálculo de kg netos | Guardar pesaje | Calcula: peso bruto − tara |
| Generación de reporte | Acción del admin o cron programado | Reporte generado (con conclusiones IA si está habilitada) |
| Envío de reporte programado | Cron + revisión opcional | Email a destinatarios vía Resend |
| Detección de anomalías | Monitoreo durante horario operativo | Genera alerta si hay valores fuera de umbral |

---

## Datos maestros iniciales (Onboarding)

Deben estar cargados antes del go-live. Sin esto el sistema no puede operar.

**Rangos de peso válido por tipo de vehículo:**

| Tipo | Mínimo (kg bruto) | Máximo (kg bruto) |
|------|-------------------|-------------------|
| Compactador | 10.000 | 26.500 |
| Volcador | 13.000 | 30.000 |
| Volquete | 7.000 | 20.000 |
| Particular | 1.000 | 5.000 |

---

## Relaciones clave entre entidades

```
Organización → (padrón, operación y reportes propios, aislados)
Vehículo → tara, tipo, titular (padrón maestro)
Zona ↔ Tipos de servicio (N:M, con turnos y horarios por combinación)
Tipo de servicio → tipos de vehículo sugeridos (N:M)
Zona → hectáreas → densidad de generación (kg/ha)
Zona → habitantes → reporte per cápita (kg/habitante)
Zona → geometría → mapa de calor (Dashboard y Reportes)
Registro de pesaje → Dashboard → Reportes → Alertas
```

---

## Criterios de aceptación

| Módulo | Criterio |
|--------|---------|
| Balanza | Flujo completo de pesaje en < 10 segundos |
| Balanza | Autocompletado funciona para el 100% de los vehículos cargados |
| Balanza | Validación de peso detecta valores fuera de rango sin bloquear |
| ABMs | CRUD completo funcional para todas las entidades maestras (tipos de vehículo, tipos de servicio, zonas, vehículos, usuarios) |
| ABMs | Cambios en ABMs se reflejan inmediatamente en Balanza |
| Dashboard | KPIs correctos para cualquier período seleccionado |
| Dashboard | Gráficos renderizan en menos de 3 segundos |
| Reportes | Reporte generado correctamente con filtros aplicados |
| Reportes | Exportación PDF y Excel funcionales; envío programado por email |
| Alertas | Detección de gaps y valores fuera de umbral |
| Alertas | Alertas visibles en dashboard con descripción clara |
| General | Login con perfiles diferenciados (operador / admin / super admin) |
| General | Aislamiento multi-tenant: cada organización ve solo sus datos |
| General | Sistema operativo el día 1 con padrón cargado |

---

## Fuera del alcance (Etapa 1)

- Integración automática con balanza física (se evalúa en Etapa 2)
- App mobile
- Integración con sistemas externos del municipio
- API pública

> El **multi-tenant** (varias organizaciones) estaba originalmente fuera del alcance de Etapa 1; se incorporó durante el desarrollo y hoy es parte del producto.

---

## Stack técnico

| Capa | Tecnología |
|------|-----------|
| Frontend | Laravel Blade + Tailwind v4 + Alpine.js |
| Backend | Laravel 13 (PHP 8.4) |
| Base de datos | SQL Server (driver `sqlsrv`) |
| Gráficos (web) | Chart.js |
| Gráficos (PDF) | SVG server-side (`SvgChartService`) |
| Mapas | Leaflet + Geoman (geometría de zonas, mapa de calor) |
| Exportación PDF | Spatie Browsershot (Chromium headless) · mPDF |
| Exportación Excel | PhpSpreadsheet |
| Envío de email | Resend |
| Conclusiones IA | Gemini (proveedor configurable por organización) |

---

*Documento original: 12/05/2026 · Actualizado: 18/06/2026 — alcance vigente (multi-tenant, reportes y alertas).*
