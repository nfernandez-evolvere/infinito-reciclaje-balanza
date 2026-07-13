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
Tres pasos en columna: vehículo, tipo de servicio y zona, peso bruto. A la derecha (en pantallas grandes) o en un panel desplegable (en celular), el **resumen del pesaje** en tiempo real.

**Abajo — barra de acciones**
Siempre visible, con los botones **Limpiar** (o Cancelar, al editar), **Resumen** (en celular) y **Guardar pesaje**, más un texto de ayuda que indica qué falta completar.

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

## Paso 2 — Elegir el tipo de servicio y la zona

Hacé clic en el campo de servicio y elegí uno de los tipos disponibles (los que tenga configurados tu organización). Por ejemplo:

| Tipo de servicio | Descripción |
|-----------------|-------------|
| Domiciliario | Recolección puerta a puerta en barrios |
| Voluminoso | Residuos de gran tamaño (muebles, electrodomésticos) |
| Barrido | Residuos de limpieza de calles |
| Servicios Especiales | Operativos puntuales o eventos |
| Centros de Transferencia | Traslados desde centros intermedios |

**Zona:** Al elegir el servicio, aparece el campo **Zona** con las zonas de ese servicio. Tenés que **elegir la zona** que corresponde al viaje — no viene pre-seleccionada.

**Turno:** Si la zona elegida opera con turnos, aparece un campo **Turno** que también hay que completar. Si la zona no usa turnos, ese campo no aparece.

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
Vehículo:         ABC-123
Servicio:         Domiciliario
Zona:             Zona Norte
Turno:            Diurna
Tipo:             Compactador
Peso bruto:       22.300 kg
Tara:             8.500 kg
─────────────────────────────
Neto estimado:    13.800 kg
Operador:         (tu nombre)
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

> Si no marcás el egreso, el camión queda con estado **EN PREDIO**. El administrador puede detectar esos camiones en el módulo de Pesajes filtrando por estado EN PREDIO.

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

## Cómo anular un pesaje cargado por error

Cuando un pesaje no corresponde y hay que dejarlo sin efecto (no solo corregir un dato):

1. Ir a **Historial**.
2. Abrí el menú de acciones (⋯) de la fila del pesaje.
3. Elegí **Cancelar pesaje** y escribí el motivo (obligatorio).
4. Confirmá.

El pesaje pasa al estado **CANCELADO**: ya no suma en los totales del turno ni en los reportes, pero no se borra — queda en el Historial con el motivo y tu nombre. Un pesaje cancelado no se puede editar ni reabrir. Si solo te equivocaste en un valor, usá **Editar**, no **Cancelar**.

---

## El resumen del turno

Los totales del día y los camiones en predio se consultan desde la pantalla de **Historial** (botón en la barra de navegación). Ahí, arriba de la tabla, se ven la cantidad de pesajes del día, las toneladas netas, el promedio por viaje y cuántos camiones están en predio en este momento.

La pantalla de pesaje en sí no tiene una barra fija con esos totales: se enfoca en cargar el pesaje actual. Para ver el acumulado del turno, entrá al Historial.

---

## Preguntas frecuentes

**¿Qué pasa si cierro el navegador mientras estoy llenando un pesaje?**
Si el pesaje no fue guardado, se pierde. El sistema te avisa con un mensaje de confirmación antes de cerrar cuando hay un pesaje sin guardar.

**¿Qué pasa si le doy Esc sin querer?**
El formulario se limpia. El pesaje no se guardó, así que tenés que empezar de nuevo.

**¿Puedo ver pesajes de días anteriores?**
Sí. El Historial muestra todos los pesajes de la organización. Podés usar el filtro de fecha para buscar registros de días anteriores.

**¿El sistema guarda la hora automáticamente?**
Sí. La hora de entrada se registra en el momento en que guardás el pesaje. No tenés que ingresarla.

**¿Qué pasa si un camión entra dos veces en el mismo turno?**
Registrás dos pesajes por separado. El sistema permite múltiples registros del mismo vehículo en el mismo día.

**¿La zona viene pre-seleccionada?**
No. Al elegir el servicio aparecen sus zonas, pero tenés que elegir vos la zona de cada pesaje. Si la zona opera con turnos, además tenés que elegir el turno.

---

*Documento actualizado: 13/07/2026 | Versión: 1.3*
