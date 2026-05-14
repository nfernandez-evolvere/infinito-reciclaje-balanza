# Módulo de reportes
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Administrador (Nacho)
**Cuándo usarlo:** Referencia de cómo generar, interpretar y exportar reportes de la operación

---

## Para qué sirve este módulo

El módulo de Reportes genera los informes formales de la operación de recolección. Reemplaza la tarea manual de armar los reportes mensuales en Excel — algo que antes llevaba entre 2 y 3 horas por mes.

El reporte principal es el **reporte mensual** que se entrega a la Municipalidad de Corrientes. También podés generar reportes trimestrales o por rango de fechas personalizado para análisis internos.

---

## Cómo acceder

**Ruta:** Análisis → Reportes (en la barra lateral)

---

## Cómo generar un reporte

### Paso 1 — Elegir el período

Seleccioná el rango de fechas que querés incluir en el reporte:

| Opción | Descripción |
|--------|-------------|
| Mes | Un mes completo (seleccionás el mes y el año) |
| Trimestre | Tres meses consecutivos (seleccionás el trimestre y el año) |
| Rango personalizado | Cualquier rango: elegís fecha de inicio y fecha de fin |

Para el reporte mensual de la Municipalidad, usá la opción **Mes**.

### Paso 2 — Aplicar filtros (opcional)

Podés acotar el reporte por cualquier combinación de:

| Filtro | Opciones |
|--------|----------|
| Origen | Un origen específico o todos |
| Tipo de servicio | Un servicio específico o todos |
| Tipo de vehículo | Un tipo específico o todos |

Si no aplicás ningún filtro, el reporte incluye todos los datos del período seleccionado.

### Paso 3 — Generar

Hacé clic en **Generar reporte**. El sistema procesa los datos y muestra una vista previa en pantalla.

### Paso 4 — Revisar la vista previa

Antes de exportar, revisá:
- Que el período sea correcto
- Que los totales tengan sentido
- Que no haya orígenes o servicios faltantes

### Paso 5 — Exportar

Elegí el formato de salida:

| Formato | Cuándo usarlo |
|---------|---------------|
| **PDF** | Para entregar al municipio — formato fijo, con logo y encabezado oficial |
| **Excel** | Para análisis adicional — todos los datos en columnas editables |

---

## Qué incluye el reporte

El reporte mensual incluye las siguientes secciones:

### Resumen ejecutivo
- Período del reporte
- Total de pesajes registrados
- Total de toneladas netas
- Promedio diario de toneladas
- Días operativos del período

### Detalle por origen
Para cada origen:
- Cantidad de pesajes
- Toneladas netas totales
- Promedio por pesaje
- Indicadores per cápita y por hectárea (si los datos demográficos están cargados)

### Detalle por tipo de servicio
Para cada tipo de servicio:
- Cantidad de pesajes
- Toneladas netas totales
- Porcentaje del total del período

### Detalle por tipo de vehículo
Para cada tipo de vehículo:
- Cantidad de pesajes
- Toneladas netas totales
- Cantidad de vehículos únicos que operaron

### Evolución diaria
Tabla con un registro por día del período, mostrando pesajes y toneladas netas de cada jornada.

### Pesajes con alerta de peso
Listado de los pesajes que generaron aviso por peso inusual durante el período. Útil para detectar patrones o errores de registro sistemáticos.

---

## Cuándo generar el reporte mensual

El reporte mensual se genera **después del último día del mes**, cuando ya están registrados todos los pesajes. El flujo habitual:

1. El 1° de cada mes (o los primeros días hábiles), generar el reporte del mes anterior.
2. Revisar la vista previa y verificar que los totales sean coherentes.
3. Exportar en PDF para el municipio.
4. Exportar en Excel para el archivo interno.

---

## Indicadores per cápita y de densidad

Si los orígenes tienen cargados los datos de habitantes y hectáreas, el reporte incluye:

| Indicador | Cálculo | Descripción |
|-----------|---------|-------------|
| kg per cápita | Toneladas netas × 1000 ÷ Habitantes | Kg recolectados por habitante en el período |
| kg por hectárea | Toneladas netas × 1000 ÷ Hectáreas | Kg recolectados por hectárea en el período |

Si un origen tiene habitantes o hectáreas en cero, estos indicadores no se calculan para ese origen.

---

## El reporte PDF vs el reporte Excel

**Reporte PDF:**
- Tiene formato fijo con logo y encabezado institucional
- No es editable
- Está pensado para ser firmado y entregado al municipio
- Incluye solo los totales y los indicadores principales

**Reporte Excel:**
- Incluye el detalle completo de cada pesaje individual
- Es editable para análisis adicionales
- Está pensado para uso interno o para cruzar con otros datos

---

## Preguntas frecuentes

**¿Puedo generar un reporte de un mes que ya fue generado antes?**
Sí. Podés generar el mismo período cuantas veces quieras. Cada generación refleja los datos actuales del sistema — si hubo correcciones de pesajes después de la última exportación, el nuevo reporte las incluye.

**¿Qué pasa si hay pesajes editados en el período del reporte?**
Se incluyen con los valores corregidos. El reporte siempre muestra el estado actual de los datos, no el estado al momento del registro original.

**¿El reporte incluye los pesajes con estado EN PREDIO (sin egreso registrado)?**
Sí. El peso neto se calcula al momento del ingreso y se incluye en el reporte independientemente de si el egreso fue registrado o no.

**¿Puedo generar un reporte de un origen específico?**
Sí. Aplicá el filtro de origen antes de generar el reporte. El PDF generado refleja solo los datos de ese origen.

**¿Hay un límite de períodos que puedo exportar?**
No. Podés generar reportes de cualquier período desde el inicio de la operación.

**¿Puedo programar el envío automático del reporte al municipio?**
No en esta versión. El reporte se genera manualmente y se envía por los canales habituales (email, sistema del municipio, etc.).

---

*Documento generado: 12/05/2026 | Versión: 1.0*
