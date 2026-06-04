# Módulo de registro de pesaje
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Operador de balanza
**Cuándo usarlo:** Referencia completa del formulario de pesaje y sus comportamientos

---

## Para qué sirve este módulo

Este es el módulo principal del sistema. Acá es donde registrás cada camión que entra al predio: buscás el vehículo, elegís el tipo de servicio e ingresás el peso que muestra la balanza. El sistema hace el resto.

---

## Cómo está organizada la pantalla

La pantalla tiene tres zonas:

**Arriba — navegación**
Dos botones: **Pesaje** (la pantalla actual) e **Historial**. En el extremo derecho, tu nombre de usuario y el botón para cerrar sesión.

**Centro — formulario de pesaje**
Tres pasos en columna: vehículo, tipo de servicio, peso bruto. Debajo de los tres pasos, el resumen del pesaje en tiempo real.

**Abajo — barra de estado del turno**
Siempre visible, muestra:
- Último pesaje registrado (patente, hora, kg netos)
- Totales del turno (cantidad de pesajes y toneladas netas)
- Camiones en predio en este momento

---

## Paso 1 — Buscar el vehículo

Hacé clic en el campo **Patente o número interno** y empezá a escribir. Podés escribir:
- La patente del camión (por ejemplo: `ABC123` o `ABC-123`)
- El número interno asignado por la Municipalidad (por ejemplo: `45`)

A medida que escribís aparecen las sugerencias. Hacé clic en el camión correcto o presioná **Enter** para seleccionar el primero de la lista.

**Qué completa el sistema automáticamente:**
- Tara (peso del vehículo vacío, en kg)
- Tipo de vehículo
- Titular (Municipalidad o nombre del particular)
- Número interno

No tenés que escribir nada de eso — el sistema lo trae del padrón.

**Si el vehículo no aparece en las sugerencias:**
El vehículo no está cargado en el padrón. Avisale al administrador para que lo agregue. Mientras tanto podés registrar el pesaje usando el número interno en forma manual, pero aclaralo en el campo de observaciones.

---

## Paso 2 — Elegir el tipo de servicio

Hacé clic en el campo de servicio y elegí uno de los tipos disponibles:

| Tipo de servicio | Descripción |
|-----------------|-------------|
| Domiciliario | Recolección puerta a puerta en barrios |
| Voluminoso | Residuos de gran tamaño (muebles, electrodomésticos) |
| Barrido | Residuos de limpieza de calles |
| Servicios Especiales | Operativos puntuales o eventos |
| Centros de Transferencia | Traslados desde centros intermedios |

**Origen automático:** Cuando elegís el servicio, el sistema completa el origen sugerido. Si ese origen no es el correcto para ese viaje, podés cambiarlo tocando el campo de origen.

---

## Paso 3 — Ingresar el peso bruto

Hacé clic en el campo grande de peso e ingresá el número que muestra la balanza física (el display de la báscula).

El sistema calcula automáticamente:
- **Peso neto** = Peso bruto − Tara del vehículo

Estos valores aparecen en el resumen debajo del formulario antes de que guardes.

---

## El aviso naranja de peso inusual

Si el peso que ingresaste está fuera del rango habitual para ese tipo de vehículo, aparece un aviso naranja que dice algo como:

> *"El peso ingresado está fuera del rango habitual para este tipo de vehículo (mínimo 10.000 kg, máximo 26.500 kg). Verificá antes de guardar."*

Este aviso **no te impide guardar** — es solo para que revises si hubieras ingresado mal el peso (por ejemplo, 2.450 en lugar de 24.500). Si el peso es correcto, guardá normalmente.

---

## El resumen del pesaje

Antes de guardar, el resumen muestra:

```
Vehículo:         ABC-123 — Compactador
Titular:          Municipalidad de San Juan
Número interno:   45
Tara:             8.500 kg
Servicio:         Domiciliario
Origen:           Origen Norte
Peso bruto:       22.300 kg
─────────────────────────────
Peso neto:        13.800 kg
```

Cuando el formulario está completo, el borde del resumen cambia a verde. Ese es el indicador de que podés guardar.

---

## Cómo guardar el pesaje

Hay dos formas:
- Hacé clic en el botón **GUARDAR PESAJE**
- Presioná **Ctrl+S**

Después de guardar aparece una confirmación y el formulario se limpia solo para el siguiente camión.

---

## Atajos de teclado

| Tecla | Qué hace |
|-------|----------|
| **Enter** | Avanza al siguiente campo o selecciona la primera sugerencia del autocompletado |
| **Ctrl+S** | Guarda el pesaje (solo cuando el formulario está completo) |
| **Esc** | Limpia el formulario y empieza desde cero |

Con estos tres atajos podés registrar un pesaje sin usar el mouse.

---

## Cómo registrar el egreso de un camión

Cuando un camión sale del predio, tenés que registrar su salida para que el sistema sepa que ya no está en el predio.

1. Ir a **Historial** (botón en la barra de navegación).
2. En la lista, buscá el camión — aparece con el estado **EN PREDIO**.
3. Hacé clic en **Marcar egreso**.
4. Confirmá la hora de salida.

El estado pasa de **EN PREDIO** a **CERRADO**.

> Si no marcás el egreso, el camión queda como "en predio" y el administrador puede ver esa situación en el dashboard como alerta.

---

## Cómo corregir un pesaje del turno

Si te diste cuenta que ingresaste un dato incorrecto:

1. Ir a **Historial**.
2. Encontrá el pesaje a corregir.
3. Hacé clic en **Editar**.
4. Cambiá el dato incorrecto.
5. Escribí el **motivo de la corrección** (campo obligatorio). Ejemplo: *"Corrección de peso: ingresé 2.450 en lugar de 24.500"*.
6. Guardá.

La corrección queda registrada con tu nombre, la hora y el motivo. El administrador puede ver ese historial de cambios.

---

## La barra de estado del turno (siempre visible)

En la parte de abajo de la pantalla, siempre visible sin importar en qué pantalla estés:

- **Último pesaje:** patente, hora y kg netos del pesaje más reciente que registraste
- **Pesajes del turno:** cantidad total de pesajes registrados en el turno de hoy
- **Toneladas netas:** total acumulado de kg netos del turno, expresado en toneladas
- **En predio:** cantidad de camiones que entraron y todavía no salieron

---

## Preguntas frecuentes

**¿Qué pasa si cierro el navegador mientras estoy llenando un pesaje?**
Si el pesaje no fue guardado, se pierde. El sistema te avisa con un mensaje de confirmación antes de cerrar cuando hay un pesaje sin guardar.

**¿Qué pasa si le doy Esc sin querer?**
El formulario se limpia. El pesaje no se guardó, así que tenés que empezar de nuevo.

**¿Puedo ver pesajes de días anteriores?**
No desde esta pantalla. El Historial muestra solo el turno actual. Para ver pesajes de otros días, el administrador tiene acceso al log completo.

**¿El sistema guarda la hora automáticamente?**
Sí. La hora de entrada se registra en el momento en que guardás el pesaje. No tenés que ingresarla.

**¿Qué pasa si un camión entra dos veces en el mismo turno?**
Registrás dos pesajes por separado. El sistema permite múltiples registros del mismo vehículo en el mismo día.

**¿Puedo cambiar el origen sugerido?**
Sí. El origen que aparece es el predeterminado para ese servicio, pero podés cambiarlo para cada pesaje individual si ese viaje corresponde a otro origen.

---

*Documento actualizado: 04/06/2026 | Versión: 1.1*
