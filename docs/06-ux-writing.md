# UX Writing — Sistema de Gestión de Balanza
## Infinito Reciclaje × EVOLVERE 2026

---

## Contexto de usuarios

Antes de escribir cualquier texto en la UI, hay que tener presente quién lo va a leer y en qué condiciones.

### Operador — Roberto

- **Perfil tecnológico:** bajo. Usa el celular y WhatsApp, pero no está familiarizado con software de gestión.
- **Contexto físico:** caseta pequeña en el predio, con calor, ruido de camiones, luz fluorescente, pantalla de escritorio a distancia de brazo.
- **Presión operativa:** 8+ camiones por hora en picos (10:00–12:00). No puede detenerse a leer.
- **Tarea:** repetitiva y secuencial — siempre el mismo flujo. Después de una semana, lo hace de memoria.
- **Ante un error:** no puede llamar a soporte. Tiene un camión esperando. Necesita resolver solo.
- **Implicancia para el texto:** cada palabra que Roberto tiene que leer es tiempo perdido. El sistema tiene que hablar lo menos posible, y cuando habla, tiene que ser imposible malinterpretar.

### Administrador — Nacho

- **Perfil tecnológico:** medio-alto. Usa herramientas de gestión, Excel, email. Cómodo con formularios y tablas.
- **Contexto físico:** oficina, sentado, sin presión de tiempo inmediata.
- **Tarea:** variada — cargar datos maestros, revisar operación, generar reportes, configurar umbrales.
- **Ante un error:** puede leer el mensaje, pensar, y actuar. Tiene tiempo.
- **Implicancia para el texto:** puede recibir más información, textos de ayuda, confirmaciones con contexto. Pero igual: directo, sin relleno.

---

## Voz del sistema

**Registro:** español operativo argentino. El sistema habla como un colega en la caseta — directo, sin rodeos, sin teatro.

**Principio rector:** el sistema narra estado. No actúa *con* el usuario, no celebra, no da ánimo, no pide disculpas.

```
✅ Pesaje guardado.
❌ ¡Tu pesaje fue guardado con éxito!
❌ Estamos guardando tu pesaje...
❌ ¡Listo! Todo salió perfecto.
```

---

## Diferencias por rol

### En pantallas del operador

El texto tiene que ser **mínimo y de acción directa**. Roberto no lee — escanea. Si hay que leer más de una línea para entender qué hacer, el texto es demasiado largo.

| Situación | Texto para operador |
|-----------|-------------------|
| Hint contextual en la barra de acción | `Buscá el vehículo` · `Elegí el servicio` · `Ingresá el peso bruto` · `Listo para guardar` |
| Peso fuera de rango | `Fuera del rango habitual (10.000 – 26.500 kg). La validación no bloquea el guardado.` |
| Guardar exitoso | `Pesaje guardado` |
| Egreso registrado | `Egreso registrado.` |
| Vehículo no encontrado | `No se encontró ningún vehículo con esa patente o número interno.` |
| Form vacío al intentar guardar | No hay mensaje — el botón está deshabilitado. La UI lo comunica visualmente. |
| Sin pesajes en el turno | `Sin pesajes en este turno todavía.` |

**Regla clave para el operador:** si el mensaje requiere más de una lectura para entenderse, reescribir. Si puede reemplazarse por un cambio visual (color, ícono, estado del botón), hacerlo así.

### En pantallas del admin

El texto puede ser más informativo. Nacho puede leer, procesar contexto y tomar decisiones. Igual: sin relleno, sin marketing interno.

| Situación | Texto para admin |
|-----------|-----------------|
| Filtros sin resultados | `No hay pesajes que coincidan con los filtros aplicados.` |
| Reporte sin generar | `Aplicá los filtros y generá el reporte para ver la vista previa.` |
| Alerta de gap | `Gap en registros 12:30 – 12:45. 15 minutos sin pesajes durante turno activo.` |
| Alerta de peso inusual | `Peso inusual en ABC-123: 32.000 kg. Por encima del rango habitual para Compactador (10.000 – 26.500 kg).` |
| Confirmar desactivar usuario | `Al desactivar a Roberto Acosta no podrá ingresar al sistema. Sus pesajes registrados se conservan.` |
| Cambio guardado con motivo | `Cambios guardados. El historial fue actualizado.` |

---

## Voseo

Se usa **solo en verbos imperativos** dirigidos al usuario. Labels, encabezados y chrome usan sustantivos en tercera persona.

```
✅ Seguí los tres pasos.         → verbo imperativo → voseo
✅ Ingresá el peso bruto.        → verbo imperativo → voseo
✅ Generá el reporte.            → verbo imperativo → voseo
✅ Aplicá los filtros.           → verbo imperativo → voseo

✅ Peso bruto                    → label → tercera persona (sustantivo)
✅ Tipo de servicio              → label → tercera persona (sustantivo)
✅ Último pesaje del turno       → chrome → tercera persona

❌ Ingresá tu peso bruto acá.    → coloquial de más
❌ Igual podés guardar.          → permisivo / condescendiente
❌ Podés configurar los umbrales → describe una capacidad, no da una instrucción
```

---

## Casing

| Contexto | Regla | Ejemplo |
|----------|-------|---------|
| Labels, encabezados, botones | Sentence case | `Guardar pesaje`, `Tipos de servicio`, `Ver historial` |
| CTA principal de Balanza | Mayúsculas — excepción deliberada | `GUARDAR PESAJE` |
| Status pills | Mayúsculas | `EN PREDIO`, `CERRADO`, `ACTIVO`, `INACTIVO`, `EDITADO` |
| Nombres propios y siglas | Según convención | `SQL Server`, `PDF`, `Excel` |

**Por qué sentence case:** el Title Case En Español Siempre Se Siente Traducido Del Inglés. Genera distancia. El sistema tiene que sentirse local.

**Por qué `GUARDAR PESAJE` en mayúsculas:** Roberto ejecuta esa acción 40+ veces por turno. Tiene que encontrar ese botón sin mirar. Las mayúsculas crean una jerarquía visual que ningún otro elemento del formulario tiene.

---

## Validaciones

### Para el operador

La validación informa el hecho y la regla. No juzga ni bloquea.

```
✅ Fuera del rango habitual para Compactador (10.000 – 26.500 kg).
   La validación no bloquea el guardado.

❌ ¡Atención! El peso ingresado parece incorrecto.
❌ Error: valor fuera de rango permitido.
❌ Igual podés guardar si estás seguro.
```

Cuando el valor está en rango: **sin mensaje**. El borde verde es suficiente.

### Para el admin (formularios de ABMs)

```
✅ La patente ya existe en el padrón.
✅ La tara no puede ser mayor que la capacidad.
✅ Este campo es obligatorio.

❌ Hubo un error al procesar tu solicitud. Por favor intentá de nuevo.
❌ ¡Oops! Algo salió mal.
```

---

## Confirmaciones y feedback

```
✅ Pesaje guardado.              → dos palabras + animación de check
✅ Egreso registrado.
✅ Cambios guardados.
✅ Vehículo desactivado.
✅ Reporte generado.

❌ ¡Guardado con éxito!
❌ ¡Genial! El pesaje fue registrado correctamente.
❌ La operación se completó satisfactoriamente.
```

---

## Estados vacíos

Amigables, con punto final, sin exclamación. Cuando hay una acción posible, la nombramos.

```
✅ Sin pesajes en este turno todavía.
✅ Aplicá los filtros y generá el reporte para ver la vista previa.
✅ No hay alertas activas.
✅ Ningún vehículo coincide con la búsqueda.

❌ ¡No hay resultados!
❌ Ups, no encontramos nada.
❌ 🎉 Todo en orden, no hay alertas.
```

---

## Alertas del sistema

Dicen el hecho. Sin "Detectamos que...", sin primera persona del plural, sin dramatismo.

```
✅ Gap en registros 12:30 – 12:45.
✅ Peso inusual ABC-123: 32.000 kg — por encima del rango habitual para Compactador.
✅ Sin actividad registrada desde las 11:00.

❌ ¡Alerta! Hemos detectado una anomalía en los registros.
❌ Parece que hubo un problema con los pesajes de esta mañana.
❌ ⚠️ Atención: posible irregularidad detectada.
```

---

## Confirmaciones destructivas (admin)

Cuando una acción es irreversible o tiene consecuencias, el texto describe exactamente qué pasa.

```
✅ Al desactivar a Roberto Acosta no podrá ingresar al sistema.
   Sus pesajes registrados se conservan.

✅ Al eliminar este tipo de vehículo se perderá la configuración de rangos.
   Los vehículos y pesajes asociados no se ven afectados.

❌ ¿Estás seguro de que querés hacer esto?
❌ Esta acción no se puede deshacer.  ← genérico, no describe qué pasa
```

---

## Edición con motivo (operador y admin)

Cuando se edita un pesaje ya registrado, el campo `motivo` es obligatorio. El placeholder guía sin forzar.

```
✅ Placeholder: Ej.: corrección de patente, error en el peso bruto…
✅ Label del campo: Motivo de la edición
✅ Error si vacío: Describí el motivo antes de guardar.

❌ Placeholder: Ingresá el motivo aquí
❌ Error si vacío: Este campo es requerido.
```

---

## Formato de datos

Siempre consistente. La unidad siempre presente junto al valor — nunca solo en el encabezado de columna.

| Tipo de dato | Formato | Ejemplo |
|-------------|---------|---------|
| Peso en kg | Separador de miles con punto, unidad pegada | `8.500 kg` |
| Toneladas | Decimal con coma, una cifra | `142,5 t` |
| Densidad | Decimal con coma, unidad | `1,3 kg/ha` |
| Porcentaje | Decimal con coma | `68,9%` |
| Hora | 24 h, sin segundos | `14:32` |
| Fecha | dd/mm/yyyy | `12/05/2026` |
| Fecha y hora | Separadas por espacio | `12/05/2026 14:32` |

**Por qué unidad en el valor y no solo en el header:** Roberto compara pesos entre pantallas (caseta, dashboard, reportes). La unidad en el valor elimina toda ambigüedad, sin depender de que el usuario recuerde el contexto.

---

## Lo que la aplicación nunca hace

- **Emoji en la UI** — siempre Lucide icons. Los emoji renderizan distinto por OS y se ven poco profesionales en un sistema operativo municipal.
- **Exclamaciones** fuera de errores graves — el sistema es un colega, no un animador.
- **Primera persona del plural** — nunca `vamos a guardar tu pesaje`, `te ayudamos`, `estamos procesando`.
- **Microcopy de relleno** — nunca `¡Listo!`, `¡Perfecto!`, `¡Todo bien!`, `¡Éxito!`.
- **Jerga técnica en pantallas del operador** — nunca `timeout`, `error 500`, `excepción`, `null`.
- **Mensajes de error técnicos al operador** — si hay un error de sistema, el operador ve: `Algo salió mal. Intentá de nuevo o avisale a Nacho.`

---

## Mensajes de error técnico por rol

| Situación | Operador | Admin |
|-----------|----------|-------|
| Error de conexión a la DB | `El sistema no está disponible. Avisale a Nacho.` | `Error de conexión al servidor. Intentá recargar.` |
| Timeout al guardar | `No se pudo guardar. Intentá de nuevo.` | `La operación tardó demasiado. Intentá de nuevo o contactá a soporte.` |
| Error inesperado | `Algo salió mal. Intentá de nuevo o avisale a Nacho.` | `Error inesperado. Si el problema persiste, revisá los logs del sistema.` |

---

*Documento generado: 12/05/2026 | Versión: 1.0*
