# Configuración inicial del sistema
## Checklist pre-go-live

**Dirigido a:** Administrador (Nacho)
**Cuándo usarlo:** Antes de que el sistema entre en operación real. Este checklist debe completarse en orden — cada paso depende del anterior.

---

## Por qué este orden importa

El sistema está diseñado para que el operador no tenga que tipear nada que ya esté pre-cargado. Si el padrón no está completo antes del día 1, el operador va a tener que ingresar datos manualmente en cada pesaje — exactamente lo que el sistema viene a eliminar.

**Regla:** ningún camión puede pesar si su patente o número interno no está en el sistema.

---

## Paso 1 — Ingresar al sistema por primera vez

1. Abrí el navegador e ingresá a la dirección del sistema (te la provee el equipo de EVOLVERE).
2. Ingresá con el usuario y contraseña que te entregaron.
3. El sistema te va a mostrar el panel de administración.

Si no podés ingresar, contactá al equipo de soporte de EVOLVERE.

---

## Paso 2 — Cargar los tipos de vehículo

Los tipos de vehículo definen los rangos de peso válidos. El sistema los usa para alertar al operador si un peso parece inusual.

Ir a **Padrones → Tipos de vehículo** y verificar que estén cargados:

| Tipo | Peso mínimo | Peso máximo |
|------|------------|------------|
| Compactador | 10.000 kg | 26.500 kg |
| Volcador | 13.000 kg | 30.000 kg |
| Volquete | 7.000 kg | 20.000 kg |
| Particular | 1.000 kg | 5.000 kg |

> Estos rangos son orientativos — si el operador registra un peso fuera de rango, el sistema lo avisa pero no bloquea el guardado.

---

## Paso 3 — Cargar los tipos de servicio

Los tipos de servicio definen el tipo de vehículo habitual y, para los servicios con horario fijo, el turno de operación.

Ir a **Padrones → Tipos de servicio** y verificar que estén cargados:

| Servicio | Turnos disponibles | Tipo de vehículo sugerido |
|----------|--------------------|--------------------------|
| Domiciliario | Diurna, Nocturna | Compactador |
| Voluminoso | — | Volquete |
| Barrido | — | Volcador |
| Servicios Especiales | — | Compactador |
| Centros de Transferencia | — | Compactador |

> Los turnos determinan si el operador debe elegir Diurna o Nocturna al registrar un pesaje de ese servicio. Domiciliario opera en ambos turnos, por eso el operador siempre debe indicar cuál es.
>
> Las zonas no se asignan acá. Cada zona se asocia a su tipo de servicio en el Paso 4 (carga de zonas). Al registrar un pesaje, el operador elige el servicio y el sistema muestra solo las zonas de ese servicio.

---

## Paso 4 — Cargar las zonas

Las zonas son las áreas geográficas de recolección. Los datos de hectáreas y habitantes se usan para calcular indicadores en los reportes.

Ir a **Padrones → Zonas** y cargar cada zona con:
- Nombre de la zona
- **Servicio al que pertenece** — este campo es clave: determina qué zonas le aparecen al operador cuando elige un tipo de servicio en el formulario de pesaje
- Hectáreas de la zona
- Cantidad de barrios
- Cantidad de habitantes

> Si no tenés los datos de hectáreas o habitantes al momento de la carga, podés dejarlos en cero y actualizarlos después. Los reportes de densidad y per cápita quedarán en cero hasta que estén cargados.

---

## Paso 5 — Cargar el padrón de vehículos

Este es el paso más importante y el que más tiempo lleva. **Todos los camiones que van a ingresar al predio deben estar cargados antes del día 1.**

Ir a **Padrones → Vehículos** y cargar cada vehículo con:

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| Patente | Patente oficial del vehículo | ABC-123 |
| Número interno | Número asignado por la Municipalidad | 45 |
| Tara | Peso del vehículo vacío en kg | 8.500 |
| Tipo de vehículo | Compactador, Volcador, Volquete o Particular | Compactador |
| Titular | Municipalidad de Corrientes o nombre del particular | Municipalidad de Corrientes |
| Capacidad | Peso máximo de carga en kg | 18.000 |
| Observaciones | Cualquier nota relevante del vehículo | Eje trasero reforzado |

> La tara es crítica — se usa para calcular los kg netos en cada pesaje. Si la tara está mal, todos los pesajes de ese camión van a quedar mal calculados.

**Verificar antes de continuar:**
- [ ] Todos los camiones que operan actualmente están cargados
- [ ] La tara de cada vehículo fue verificada (no estimada)
- [ ] Ningún vehículo tiene la tara en cero

---

## Paso 6 — Crear los usuarios operadores

Ir a **Padrones → Usuarios** y crear un usuario para cada operador que va a usar el sistema.

Para cada usuario cargar:
- Nombre de usuario (para el login)
- Nombre completo
- Rol: **Operador**
- Contraseña inicial (el operador puede cambiarla después)

> Crear un usuario por persona, nunca compartir credenciales. Si dos operadores comparten usuario, no se puede saber quién registró cada pesaje.

---

## Paso 7 — Verificación final

Antes de habilitar el sistema para operación real:

- [ ] Tipos de vehículo cargados con rangos correctos
- [ ] Tipos de servicio cargados con turno (si corresponde) y tipo de vehículo sugerido
- [ ] Zonas cargadas con su tipo de servicio asignado (al menos nombre y servicio; hectáreas y habitantes si están disponibles)
- [ ] Padrón de vehículos completo y con taras verificadas
- [ ] Usuarios operadores creados y con contraseñas entregadas
- [ ] Hacer un pesaje de prueba con un vehículo real para verificar el autocompletado
- [ ] Confirmar que el peso neto calculado es correcto (peso bruto ingresado − tara del padrón)

---

## Si algo no funciona

| Problema | Qué hacer |
|----------|----------|
| No aparece el autocompletado de un vehículo | Verificar que la patente o número interno estén cargados exactamente como el operador los ingresa |
| El peso neto parece incorrecto | Verificar la tara del vehículo en el padrón |
| Un operador no puede ingresar | Verificar que su usuario esté activo en Padrones → Usuarios |
| Un servicio no muestra zonas en el select | Verificar que la zona tenga asignado el tipo de servicio correcto en Padrones → Zonas |

---

*Documento generado: 12/05/2026 | Versión: 1.0*
