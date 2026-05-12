# Guía de inicio para el administrador
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador (Nacho y cualquier admin nuevo)
**Objetivo:** Conocer el sistema completo, saber qué configurar antes del día 1 y cómo usarlo en el día a día.

---

## Qué hace este sistema

El sistema digitaliza el registro de pesajes de camiones en el predio. Reemplaza las planillas en papel y el trabajo manual en Excel que hoy lleva 2 a 3 horas por mes.

Como administrador, tu rol tiene dos momentos:

1. **Antes del go-live:** cargar todos los datos maestros (padrón de vehículos, zonas, servicios, usuarios). Sin esto, el sistema no puede funcionar.
2. **En el uso diario:** revisar el dashboard, gestionar el log de pesajes, generar reportes y atender alarmas.

---

## El panel de administración

Cuando ingresás como admin, ves el panel con una barra lateral izquierda organizada en tres grupos:

**Operación**
- **Dashboard** — vista en tiempo real de lo que está pasando hoy
- **Pesajes** — log completo de todos los pesajes registrados

**Padrones**
- **Vehículos** — padrón de todos los camiones
- **Zonas** — zonas geográficas de recolección
- **Tipos de servicio** — servicios con sus configuraciones de cascada
- **Tipos de vehículo** — tipos de camión con rangos de peso
- **Usuarios** — operadores y administradores del sistema

**Análisis**
- **Reportes** — generación y exportación de reportes mensuales

---

## Qué configurar antes del día 1

Seguí el checklist en [`configuracion-inicial.md`](configuracion-inicial.md). El orden es:

1. Tipos de vehículo (con rangos de peso)
2. Tipos de servicio (con zona y tipo de vehículo sugerido)
3. Zonas (con hectáreas y habitantes si están disponibles)
4. Padrón de vehículos (completo, con taras verificadas)
5. Usuarios operadores

> No saltes pasos. Si cargás vehículos antes que los tipos de vehículo, vas a tener que editar cada vehículo después.

---

## Cómo gestionar los datos maestros (Padrones)

Cada sección de Padrones sigue el mismo patrón:
- Una tabla con todos los registros y una barra de búsqueda
- Un botón **Agregar** para crear un registro nuevo
- Acciones por fila: editar, desactivar

**Baja lógica:** nunca se borra un registro del sistema. Si un camión deja de operar, se desactiva — sus pesajes históricos se conservan. Lo mismo con usuarios, zonas y servicios.

Para más detalle, ver [`modulo-abms.md`](modulo-abms.md).

---

## Cómo leer el Dashboard

El Dashboard es tu vista de control en tiempo real. Muestra:

- **Alertas activas** (si las hay) — en la parte superior, con un botón para revisarlas
- **Camiones en el predio ahora** — los que entraron y todavía no salieron
- **KPIs del día** — pesajes, toneladas, promedio por viaje, horas operativas
- **KPIs del mes** — acumulados del mes en curso
- **Evolución diaria** — gráfico de los últimos 7 días
- **Por zona** y **por tipo de vehículo** — desgloses de la operación

Para más detalle, ver [`modulo-dashboard.md`](modulo-dashboard.md).

---

## Cómo revisar y corregir pesajes

En **Operación → Pesajes** podés ver todos los pesajes registrados, filtrarlos y editarlos si hay un error.

Toda edición requiere escribir un motivo. Cada cambio queda registrado en un historial que muestra quién cambió qué, cuándo y por qué.

Para más detalle, ver [`modulo-pesajes-admin.md`](modulo-pesajes-admin.md).

---

## Cómo generar el reporte mensual

En **Análisis → Reportes**:

1. Seleccioná el período (mes, trimestre o rango personalizado).
2. Aplicá los filtros que necesites (zona, servicio, tipo de vehículo).
3. Hacé clic en **Generar reporte**.
4. Revisá la vista previa en pantalla.
5. Exportá en PDF (para entregar al municipio) o Excel (para análisis adicional).

Para más detalle, ver [`modulo-reportes.md`](modulo-reportes.md).

---

## Cómo funcionan las alarmas

El sistema monitorea la operación automáticamente y genera alertas cuando detecta situaciones inusuales:
- Períodos sin pesajes durante el horario operativo (gaps)
- Pesos muy por encima o por debajo del rango habitual
- Frecuencias atípicas por zona

Las alertas aparecen en el Dashboard. Podés configurar los umbrales de detección y marcar cada alerta como resuelta una vez atendida.

Para más detalle, ver [`modulo-alarmas.md`](modulo-alarmas.md).

---

## Preguntas frecuentes

**¿Qué pasa si un operador registra un pesaje con datos incorrectos?**
Podés editarlo desde Pesajes. El operador también puede editarlo desde su Historial dentro del turno activo. Toda edición queda registrada con el motivo.

**¿Puedo ver en tiempo real qué están registrando los operadores?**
Sí. El Dashboard muestra los camiones en predio en este momento y los KPIs del día se actualizan con cada nuevo pesaje.

**¿Qué pasa si desactivo un vehículo que tiene pesajes históricos?**
El vehículo deja de aparecer en el autocompletado del operador, pero todos sus pesajes anteriores se conservan en el historial. Los reportes siguen incluyendo esos datos.

**¿Puedo crear más de un usuario administrador?**
Sí. Ir a Padrones → Usuarios y crear el usuario con rol **Admin**.

**¿Qué hago si un operador olvida su contraseña?**
Ir a Padrones → Usuarios, buscar al operador y usar la acción **Resetear contraseña**.

**¿Con qué frecuencia debo revisar el dashboard?**
Durante el horario operativo (8:00–18:00) se recomienda revisarlo al menos una vez por hora. Las alertas aparecen automáticamente, pero el dashboard no envía notificaciones por fuera de la pantalla (en Etapa 1).

---

*Documento generado: 12/05/2026 | Versión: 1.0*
