# Guía de inicio para el operador
## Sistema de Gestión de Balanza — Infinito Reciclaje

**Dirigido a:** Operador de balanza
**Objetivo:** Que puedas registrar pesajes desde el primer día, sin depender de nadie.

---

## Qué hace este sistema

El sistema reemplaza las planillas en papel. Cada vez que entra un camión al predio, vos registrás el peso en la computadora. El sistema completa solo los datos del camión y calcula los kg netos automáticamente.

Al final del turno, el administrador puede ver todo lo que se registró sin necesidad de que vos le entregues nada.

---

## Cómo ingresar al sistema

1. Abrí el navegador en la computadora de la caseta.
2. Ingresá a la dirección del sistema (pegada en la pantalla o en la caseta).
3. Escribí tu nombre de usuario y contraseña.
4. Hacé clic en **Ingresar**.

El sistema te va a llevar directo a la pantalla de pesaje.

> Si no recordás tu contraseña, avisale al administrador para que te la resetee.

---

## La pantalla principal — Registro de pesaje

Cuando entrás, vas a ver el formulario de pesaje. Tiene tres pasos que se completan de arriba hacia abajo:

```
[ 1 · Vehículo               ]
[ 2 · Tipo de servicio y zona ]
[ 3 · Peso bruto             ]
[ Resumen del pesaje         ]
```

Abajo de todo hay una barra con los botones para limpiar y para guardar.

---

## Cómo registrar un pesaje

### Paso 1 — Buscá el vehículo

Hacé clic en el campo que dice **"Patente o número interno"** y escribí la patente o el número interno del camión.

Mientras escribís, van a aparecer sugerencias. Hacé clic en el camión correcto o presioná **Enter** para seleccionar el primero.

Una vez seleccionado, el sistema completa automáticamente:
- La tara (peso del vehículo vacío)
- El tipo de vehículo
- El titular
- El número interno

No tenés que tipear nada de eso — ya está.

### Paso 2 — Elegí el tipo de servicio y la zona

Hacé clic en el campo de servicio y elegí el tipo que corresponde a ese camión (los que tenga configurados tu organización), por ejemplo:
- Domiciliario
- Voluminoso
- Barrido
- Servicios Especiales
- Centros de Transferencia

Al elegir el servicio aparece el campo **Zona**: elegí la zona que corresponde al viaje (no viene pre-seleccionada). Si esa zona opera con turnos, aparece también el campo **Turno**, que hay que completar.

### Paso 3 — Ingresá el peso bruto

Hacé clic en el campo grande de peso e ingresá el peso que muestra la balanza física (el número que aparece en el display de la báscula).

El sistema te muestra en tiempo real:
- La tara del vehículo
- El neto estimado (peso bruto − tara)

Si el peso está fuera del rango habitual para ese tipo de vehículo, aparece un aviso naranja. **Ese aviso no te impide guardar** — es solo una alerta para que lo revises.

### Guardá el pesaje

Cuando el formulario está completo, el resumen de abajo se pone en verde. Podés guardar de dos formas:
- Hacé clic en el botón **GUARDAR PESAJE**
- O presioná **Ctrl+S** en el teclado

Aparece una confirmación y el formulario se limpia solo para el próximo camión.

---

## Atajos de teclado

| Tecla | Qué hace |
|-------|----------|
| **Enter** | Avanza al siguiente campo / selecciona el primer vehículo sugerido |
| **Ctrl+S** | Guarda el pesaje (cuando está completo) |
| **Esc** | Limpia el formulario para empezar de nuevo |

Con estos tres atajos podés registrar un pesaje sin usar el mouse.

---

## Cómo registrar el egreso de un camión

Cuando un camión sale del predio, tenés que registrar su salida:

1. Hacé clic en **Historial** en la parte de arriba de la pantalla.
2. Buscá el camión en la lista (aparecen los que están "En predio").
3. Hacé clic en **Marcar egreso** en la fila del camión.
4. Confirmá la hora de salida.

El estado del registro pasa de **EN PREDIO** a **CERRADO**.

---

## El Historial

La pantalla de **Historial** muestra todos los pesajes registrados en la organización. Por defecto aparecen los más recientes primero, pero podés filtrar por fecha, patente, estado u operario para buscar cualquier registro.

Arriba ves un resumen del turno actual:
- Cuántos pesajes se registraron hoy
- Total de toneladas netas
- Promedio por viaje
- Cuántos camiones están en el predio en este momento

En la tabla podés ver cada registro con la hora de entrada, el estado, la patente, el servicio, la zona y el peso neto.

---

## Cómo corregir un pesaje del turno

Si te equivocaste en algún dato (por ejemplo, pusiste mal el peso), podés corregirlo:

1. Ir a **Historial**.
2. Buscá el pesaje que querés corregir.
3. Hacé clic en **Editar**.
4. Cambiá el dato incorrecto.
5. **Escribí el motivo de la corrección** — este campo es obligatorio. Ejemplo: *"Corrección de peso: ingresé 2.450 en lugar de 24.500"*.
6. Guardá.

Cada corrección queda registrada con tu nombre, la fecha y el motivo. El administrador puede ver el historial de cambios de cada pesaje.

---

## Cómo anular un pesaje cargado por error

Si un pesaje se cargó por error y no corresponde corregirlo sino dejarlo sin efecto:

1. Ir a **Historial**.
2. Abrí el menú de acciones (⋯) de la fila del pesaje.
3. Elegí **Cancelar pesaje**.
4. Escribí el motivo de la cancelación (es obligatorio).
5. Confirmá.

El pesaje pasa al estado **CANCELADO**: deja de sumar en los totales y en los reportes, pero no se borra — queda en el Historial con el motivo y tu nombre. Un pesaje cancelado no se puede editar ni reabrir. Si solo te equivocaste en un dato, usá **Editar** en lugar de cancelar.

---

## Qué hacer si el vehículo no aparece en el autocompletado

Si escribís la patente o el número interno y no aparece ninguna sugerencia:

1. Verificá que lo estés escribiendo bien (patente sin espacios extras, número sin ceros de más).
2. Si sigue sin aparecer, el vehículo no está cargado en el sistema.
3. **Avisale al administrador** para que lo agregue al padrón.
4. Mientras tanto, podés registrar el pesaje de todas formas usando el número interno manualmente, pero aclaralo en las observaciones.

---

## Preguntas frecuentes

**¿Qué pasa si la conexión se corta?**
El sistema necesita conexión para guardar un pesaje. Si la conexión se cae justo cuando vas a guardar, el pesaje no se registra: esperá a que vuelva y guardá de nuevo. Mientras no cierres ni recargues la pantalla, los datos que cargaste siguen en el formulario. Si la caída es larga, anotá los pesajes en papel para cargarlos después y no perder ninguno.

**¿Qué significa el aviso naranja en el peso?**
Que el peso que ingresaste está fuera del rango habitual para ese tipo de vehículo. No bloquea el guardado — es solo para que lo revises. Si el peso es correcto, guardá normalmente.

**¿Puedo modificar el tipo de vehículo sugerido?**
Sí. Si el sistema sugiere un tipo de vehículo diferente al que tiene el camión, podés dejarlo como está o cambiarlo. El sistema siempre respeta el dato real del vehículo del padrón.

**¿Qué pasa si cierro el navegador sin guardar?**
El pesaje en curso se pierde. Antes de cerrar, el sistema te pregunta si estás seguro cuando hay un pesaje sin guardar.

**¿Cómo sé cuántos camiones quedan en el predio?**
En la pantalla de **Historial**, arriba de la tabla, aparece el número de camiones "en predio". También podés filtrar la tabla por estado EN PREDIO para verlos.

---

*Documento actualizado: 13/07/2026 | Versión: 1.2*
