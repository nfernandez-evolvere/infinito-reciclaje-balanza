# Configuración inicial del sistema
## Checklist pre-go-live

**Dirigido a:** Administrador

**Cuándo usarlo:** Antes de que el sistema entre en operación real. Este checklist debe completarse en orden — cada paso depende del anterior.

---

## Por qué este orden importa

El sistema está diseñado para que el operador no tenga que tipear nada que ya esté pre-cargado. Si el padrón no está completo antes del día 1, el operador va a tener que ingresar datos manualmente en cada pesaje — exactamente lo que el sistema viene a eliminar.

**Regla:** ningún camión puede pesar si su patente o número interno no está en el sistema.

---

## Paso 1 — Ingresar al sistema por primera vez

1. Abrí el navegador e ingresá a la dirección del sistema (te la provee el equipo de EVOLVERE).
2. Ingresá con el usuario y contraseña que te entregaron o te llegó por email.
3. El sistema te va a mostrar el panel de administración.

Si no podés ingresar, contactá al equipo de soporte de EVOLVERE.

---

## Paso 2 — Cargar los tipos de vehículo

Los tipos de vehículo definen los rangos de **peso bruto** esperados (vehículo + carga). El sistema los usa para detectar pesajes anómalos.

Ir a **Configuración → Vehículos** (pestaña **Tipos de vehículo**) y verificar que estén cargados:

| Tipo | Bruto mínimo | Bruto máximo |
|------|------------|------------|
| Compactador | 10.000 kg | 26.500 kg |
| Volcador | 13.000 kg | 30.000 kg |
| Volquete | 7.000 kg | 20.000 kg |
| Particular | 1.000 kg | 5.000 kg |

> Estos rangos son orientativos — si el operador registra un peso fuera de rango, el sistema lo avisa pero no bloquea el guardado. Son de peso bruto (lo que marca la balanza), no de tara.

---

## Paso 3 — Cargar los tipos de servicio

Los tipos de servicio definen el nombre del servicio y el tipo de vehículo habitual.

Ir a **Configuración → Servicios** y verificar que estén cargados:

| Servicio | Tipo de vehículo sugerido |
|----------|--------------------------|
| Domiciliario | Compactador, Volcador |
| Voluminoso | Volquete |
| Barrido | Volcador |
| Servicios Especiales | Compactador, Volquete |
| Centros de Transferencia | Compactador |

> Los turnos no se configuran acá. Se configuran en el Paso 4 cuando asignás cada servicio a sus zonas.

---

## Paso 4 — Cargar las zonas de cada servicio

Cada servicio tiene **sus propias zonas** de operación. Las zonas son las áreas geográficas de recolección; los datos de hectáreas se usan para calcular indicadores de densidad en los reportes.

Ir a **Configuración → Servicios**, expandir el servicio (botón "Ver zonas") y usar **Agregar zona**. Para cada zona definir:

**1. Datos geográficos:**
- Nombre de la zona (único dentro de ese servicio)
- Hectáreas y barrios (opcionales, completar después si no están disponibles)
- Área en el mapa (opcional): dibujá el polígono para los mapas de calor

**2. Turnos y horarios de esa zona:**
- Turnos: usá el switch "Opera con turnos" y escribí el nombre de cada uno (Enter para agregarlo como chip; no hay una lista fija, podés cargar los que necesites)
- Horarios de recorrido (opcional): seleccioná los días activos y cargá las franjas horarias

Esta configuración determina qué zonas y turnos le aparecen al operador al registrar un pesaje del servicio elegido.

> Si la misma área (ej: "Zona Norte") opera bajo dos servicios, se carga una zona en cada servicio — son zonas independientes con su propia configuración.

**Ejemplo — servicio Domiciliario:**
| Zona | Turnos | Horario |
|------|--------|---------|
| Zona Norte | Diurna, Nocturna | Lun–Vie 08:00–12:00 / 20:00–02:00 |
| Zona Sur | Diurna | — |

> Si no tenés los datos de hectáreas al momento de la carga, podés dejarlos en cero y actualizarlos después.

---

## Paso 5 — Cargar el padrón de vehículos

Este es el paso más importante y el que más tiempo lleva. **Todos los camiones que van a ingresar al predio deben estar cargados antes del día 1.**

Ir a **Configuración → Vehículos** y cargar cada vehículo con:

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| Patente | Patente oficial del vehículo | ABC-123 |
| Número interno | Número asignado por la Municipalidad | 45 |
| Tara | Peso del vehículo vacío en kg | 8.500 |
| Tipo de vehículo | Compactador, Volcador, Volquete o Particular | Compactador |
| Titular | Municipalidad o nombre del particular responsable del vehículo | Municipalidad de San Juan |

> La tara es crítica — se usa para calcular los kg netos en cada pesaje. Si la tara está mal, todos los pesajes de ese camión van a quedar mal calculados.

**Verificar antes de continuar:**
- [ ] Todos los camiones que operan actualmente están cargados
- [ ] La tara de cada vehículo fue verificada (no estimada)
- [ ] Ningún vehículo tiene la tara en cero

---

## Paso 6 — Crear los usuarios operadores

Ir a **Sistema → Usuarios** y crear un usuario para cada operador que va a usar el sistema.

Para cada usuario cargar:
- Nombre de usuario (para el login)
- Nombre completo
- Rol: **Operador**
- Contraseña inicial (el operador puede cambiarla después)

> Crear un usuario por persona, nunca compartir credenciales. Si dos operadores comparten usuario, no se puede saber quién registró cada pesaje.

---

## Paso 7 — Verificación final

Antes de habilitar el sistema para operación real:

- [ ] Tipos de vehículo cargados con rangos de peso bruto correctos
- [ ] Tipos de servicio cargados con tipo de vehículo sugerido
- [ ] Cada servicio con sus zonas cargadas (al menos nombre y turno si corresponde)
- [ ] Padrón de vehículos completo y con taras verificadas
- [ ] Usuarios operadores creados y con contraseñas entregadas
- [ ] Hacer un pesaje de prueba con un vehículo real para verificar el autocompletado
- [ ] Confirmar que el peso neto calculado es correcto (peso bruto ingresado − tara del padrón)

---

## Si algo no funciona

| Problema | Qué hacer |
|----------|----------|
| No aparece el autocompletado de un vehículo | Verificar que la patente o número interno estén cargados exactamente como el operador los ingresa |
| El peso neto parece incorrecto | Verificar la tara del vehículo en Configuración → Vehículos |
| Un operador no puede ingresar | Verificar que su usuario esté activo en Sistema → Usuarios |
| Un servicio no muestra zonas en el select | Verificar que la zona tenga asignado el tipo de servicio correcto en **Configuración → Zonas** |

---

*Documento actualizado: 18/06/2026 | Versión: 1.3*
