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
| **Inicio** | 12/05/2026 |
| **Go-live estimado** | 14/07/2026 |

Sistema web para el registro, control y análisis del ingreso y egreso de camiones de residuos en el predio de disposición final de la Municipalidad de Corrientes. Reemplaza un proceso manual con planillas y Excel por una operación automatizada, trazable y con inteligencia operacional en tiempo real.

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
- Mantiene datos maestros (padrón de vehículos, orígenes, servicios)
- Genera reportes mensuales para la Municipalidad
- Necesita dashboard en tiempo real, reportes automáticos y alertas proactivas

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

**Tipos de vehículos**: Compactador, Volcador, Volquete, Particulares

**Tipos de servicio**: Domiciliario, Voluminoso, Barrido, Servicios Especiales, Centros de Transferencia

**Orígenes**
| Campo | Uso |
|-------|-----|
| Nombre | Identificación |
| Servicio asociado | Autocompletado en Balanza |
| Tipo vehículo sugerido | Sugerencia en Balanza |
| Hectáreas | Cálculo densidad kg/ha |
| Habitantes | Reporte per cápita |

### Requerimientos funcionales
- CRUD completo para cada entidad (alta, baja lógica, modificación)
- Relaciones configurables: servicio → origen, servicio → tipo de vehículo sugerido
- Validaciones de campos obligatorios
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
- Desglose por origen (viajes, kg, densidad kg/hectárea)
- Reporte per cápita por origen: kg generados ÷ habitantes

**Filtros disponibles:**
- Por período (mes, trimestre, rango personalizado)
- Por origen
- Por tipo de servicio
- Por tipo de vehículo

**Exportación:**
- PDF con formato profesional (para entregar al municipio)
- Excel con datos crudos (para análisis adicional)

### Requerimientos no funcionales
- Generación del reporte en menos de 30 segundos
- Formato PDF consistente y presentable
- Sin intervención manual en el armado del reporte

---

## Módulo 5 — Alarmas
**Perfil**: Administrador

### Objetivo
Detección proactiva de anomalías sin esperar al fin de mes.

### Requerimientos funcionales

**Detecciones automáticas:**
- Volumen diario fuera de rango (por encima o por debajo del histórico)
- Kg por viaje inusual para el tipo de vehículo
- Frecuencia por origen atípica
- Gaps en el registro (cortes del sistema o períodos sin pesajes en horario operativo)

**Notificaciones:**
- Alerta visible en el dashboard (panel superior)
- Descripción clara: qué pasó, cuándo, en qué origen o vehículo

**Configuración:**
- El admin puede definir o ajustar los umbrales de detección

### Requerimientos no funcionales
- Monitoreo en tiempo real durante el horario operativo
- Sin falsos positivos excesivos (umbrales calibrados con histórico real)

---

## Perfiles y permisos

| Módulo | Operador | Admin |
|--------|----------|-------|
| Balanza — registro de pesajes | ✅ Lectura y escritura | ✅ Solo lectura |
| ABMs | ❌ Sin acceso | ✅ Lectura y escritura |
| Dashboard | ❌ Sin acceso | ✅ Lectura |
| Reportes automáticos | ❌ Sin acceso | ✅ Generación y exportación |
| Alarmas | ❌ Sin acceso | ✅ Configuración y recepción |

---

## Automatizaciones del sistema

| Automatización | Disparador | Resultado |
|----------------|-----------|-----------|
| Autocompletado por vehículo | Ingresar patente o número interno | Completa tara, tipo, titular, capacidad |
| Autocompletado por servicio | Seleccionar tipo de servicio | Completa origen asociado |
| Sugerencia de vehículo | Seleccionar servicio | Sugiere tipo de vehículo (editable) |
| Validación de peso | Ingresar peso bruto | Valida contra rango del tipo de vehículo |
| Cálculo de kg netos | Guardar pesaje | Calcula: peso bruto − tara |
| Generación de reporte | Acción del admin | Reporte generado automáticamente |
| Detección de anomalías | Monitoreo continuo | Alerta al admin si hay valores fuera de rango |

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
Vehículo → tara, tipo, titular (padrón maestro)
Tipo de servicio → origen asociado
Tipo de servicio → tipo de vehículo sugerido
Origen → hectáreas → densidad de generación (kg/ha)
Origen → habitantes → reporte per cápita (kg/habitante)
Registro de pesaje → Dashboard → Reportes → Alarmas
```

---

## Criterios de aceptación

| Módulo | Criterio |
|--------|---------|
| Balanza | Flujo completo de pesaje en < 10 segundos |
| Balanza | Autocompletado funciona para el 100% de los vehículos cargados |
| Balanza | Validación de peso detecta valores fuera de rango sin bloquear |
| ABMs | CRUD completo funcional para las 4 entidades |
| ABMs | Cambios en ABMs se reflejan inmediatamente en Balanza |
| Dashboard | KPIs correctos para cualquier período seleccionado |
| Dashboard | Gráficos renderizan en menos de 3 segundos |
| Reportes | Reporte generado correctamente con filtros aplicados |
| Reportes | Exportación PDF y Excel funcionales |
| Alarmas | Detección de gaps y valores fuera de rango |
| Alarmas | Alertas visibles en dashboard con descripción clara |
| General | Login con 2 perfiles diferenciados (operador / admin) |
| General | Sistema operativo el día 1 con padrón cargado |

---

## Fuera del alcance (Etapa 1)

- Integración automática con balanza física (se evalúa en Etapa 2)
- App mobile
- Integración con sistemas externos del municipio
- Multi-tenant (varios predios)
- API pública

---

## Stack técnico

| Capa | Tecnología |
|------|-----------|
| Frontend | Laravel Blade |
| Backend | Laravel (PHP) |
| Base de datos | SQL Server |
| Gráficos | Chart.js |
| Exportación PDF | A definir (DomPDF / Laravel Snappy) |
| Exportación Excel | A definir (Maatwebsite Laravel Excel) |

---

*Documento generado: 12/05/2026 | Versión: 1.0*
*Próxima revisión: Post-testing con usuarios (Semana 6)*
