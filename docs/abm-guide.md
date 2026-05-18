# Guía de desarrollo ABM

Lineamientos para construir módulos de ABM en este proyecto. Cada módulo nuevo debe seguir exactamente esta estructura — el módulo `tipos-vehiculo` es la referencia canónica.

---

## Estructura de archivos

```
app/
├── Http/
│   ├── Controllers/Admin/{Modelo}Controller.php
│   └── Requests/
│       ├── Store{Modelo}Request.php
│       └── Update{Modelo}Request.php
├── Services/{Modelo}Service.php
├── Repositories/{Modelo}Repository.php
└── Models/{Modelo}.php

resources/
├── js/alpine/admin/{modulo}.js
└── views/modules/admin/{modulo}/
    ├── index.blade.php
    ├── _header.blade.php
    ├── _tabla.blade.php
    ├── _modal.blade.php           ← crear + editar (modo dinámico)
    ├── _modal-confirm.blade.php   ← confirmar toggle activo/inactivo
    ├── _modal-delete.blade.php    ← confirmar eliminación
    └── _drawer-filtros.blade.php

routes/web.php  ← grupo admin, prefix + name + middleware
```

---

## Prompt para crear un módulo nuevo

Copiar y completar los `{}` antes de enviarlo a Claude.

```
Crear el módulo ABM "{Nombre en plural}" siguiendo exactamente el patrón del módulo tipos-vehiculo
(referencia canónica en este proyecto).

Modelo: {Modelo}
Tabla:  {tabla}
Ruta base: admin/{ruta-base}
Nombre de ruta: admin.{ruta-base}

Campos del modelo:
{
  campo1: tipo | validación | label UI,
  campo2: tipo | validación | label UI,
  ...
}

Tiene campo `activo` (boolean, toggle): {sí / no}

Relaciones que pueden generar constraint al eliminar:
- {Modelo relacionado} a través de {columna FK} (describir qué pasa si hay registros)

Mensajes de toast — completar para cada acción:
- store:   message / description
- update:  message / description (description usa el nombre nuevo del record)
- toggle desactivar: message / description
- toggle activar:    message / description
- destroy success:   message / description
- destroy constraint: message / description (qué registros bloquean la eliminación)

Texto del modal de confirmación de eliminación:
"Al eliminar {record} {qué se pierde}. {Qué no se ve afectado}."

Filtros del drawer:
- {campo}: {tipo de input — text / number / select / date}

Columnas de la tabla:
- {campo}: {formato de presentación}

Empty state sin filtros:
- icon: {nombre lucide}
- title: "{texto}"
- description: "{texto}"

Archivos a generar (en orden):
1. Migration
2. Model
3. Repository
4. Service
5. StoreRequest + UpdateRequest
6. Controller (resource: index/store/update/destroy + toggle PATCH)
7. Alpine JS (resources/js/alpine/admin/{modulo}.js)
8. Registrar Alpine.data en app.js
9. Vistas: index + _header + _tabla + _modal + _modal-confirm + _modal-delete + _drawer-filtros
10. Rutas en routes/web.php
```

---

## Controller — patrón obligatorio

Todo controller de ABM debe seguir esta estructura sin excepciones.

```php
class {Modelo}Controller extends Controller
{
    public function __construct(
        protected {Modelo}Service $service,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only([/* campos de filtro */]);
        $items   = $this->service->listar($filters);

        return view('modules.admin.{modulo}.index', compact('items', 'filters'));
    }

    public function store(Store{Modelo}Request $request): RedirectResponse
    {
        try {
            $item = $this->service->crear($request->validated());

            return redirect()->route('admin.{modulo}.index')
                ->with('toast', [
                    'message'     => '{Entidad} creada.',
                    'description' => "\"{$item->nombre}\" quedó disponible.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function update(Update{Modelo}Request $request, {Modelo} ${modelo}): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->actualizar(${modelo}, $validated);

            return redirect()->route('admin.{modulo}.index')
                ->with('toast', [
                    'message'     => 'Cambios guardados.',
                    'description' => "\"{$validated['nombre']}\" fue actualizado correctamente.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function toggle({Modelo} ${modelo}): RedirectResponse   // solo si tiene campo activo
    {
        try {
            if (${modelo}->activo) {
                $this->service->desactivar(${modelo});
                $toast = [
                    'message'     => '{Entidad} desactivada.',
                    'description' => "\"{${modelo}->nombre}\" no aparecerá en nuevos registros.",
                    'variant'     => 'success',
                ];
            } else {
                $this->service->activar(${modelo});
                $toast = [
                    'message'     => '{Entidad} activada.',
                    'description' => "\"{${modelo}->nombre}\" volvió a estar disponible.",
                    'variant'     => 'success',
                ];
            }

            return redirect()->route('admin.{modulo}.index')->with('toast', $toast);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function destroy({Modelo} ${modelo}): RedirectResponse
    {
        try {
            $nombre = ${modelo}->nombre;
            $this->service->eliminar(${modelo});

            return redirect()->route('admin.{modulo}.index')
                ->with('toast', [
                    'message'     => '{Entidad} eliminada.',
                    'description' => '{Descripción de qué no se ve afectado}.',
                    'variant'     => 'success',
                ]);
        } catch (QueryException $e) {
            $isConstraint = in_array($e->getCode(), ['23000', '23503']);

            return redirect()->route('admin.{modulo}.index')
                ->with('toast', $isConstraint ? [
                    'message'     => 'No se puede eliminar.',
                    'description' => "\"{$nombre}\" {descripción del constraint}.",
                    'variant'     => 'destructive',
                ] : $this->toastErrorData());
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    private function toastError(): RedirectResponse
    {
        return redirect()->route('admin.{modulo}.index')
            ->with('toast', $this->toastErrorData());
    }

    private function toastErrorData(): array
    {
        return [
            'message'     => 'Error inesperado.',
            'description' => 'Si el problema persiste, revisá los logs del sistema.',
            'variant'     => 'destructive',
        ];
    }
}
```

> **Nota:** `toastError()` / `toastErrorData()` se repite en cada controller. Si hay más de 3 módulos conviene extraerlo a un trait `HasToastResponses`.

---

## Repository — patrón obligatorio

```php
class {Modelo}Repository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return {Modelo}::query()
            ->when(/* filtros con ->when() */)
            ->orderBy('nombre')
            ->paginate($perPage)
            ->appends(array_filter($filters, fn($v) => $v !== '' && $v !== null));
    }

    public function create(array $data): {Modelo}
    {
        return {Modelo}::create($data);
    }

    public function update({Modelo} ${modelo}, array $data): {Modelo}
    {
        ${modelo}->update($data);
        return ${modelo};
    }

    public function deactivate({Modelo} ${modelo}): void  // solo si tiene activo
    {
        ${modelo}->update(['activo' => false]);
    }

    public function activate({Modelo} ${modelo}): void    // solo si tiene activo
    {
        ${modelo}->update(['activo' => true]);
    }

    public function delete({Modelo} ${modelo}): void
    {
        ${modelo}->delete();
    }
}
```

---

## Form Requests — patrón obligatorio

```php
public function rules(): array
{
    return [
        'nombre' => ['required', 'string', 'max:100'],
        // otros campos...
    ];
}

public function attributes(): array
{
    return [
        'nombre' => 'nombre',
        // mapeo campo → label en español para mensajes de error
    ];
}

public function messages(): array
{
    return [
        // solo mensajes que no se expresan bien con el default de Laravel
        // ej: 'peso_max_kg.gt' => 'El peso máximo debe ser mayor al peso mínimo.'
    ];
}
```

`UpdateRequest` es idéntico a `StoreRequest` salvo que la regla `unique` (si existe) debe excluir el registro actual:
```php
'nombre' => ['required', 'string', 'max:100', Rule::unique('tabla')->ignore($this->route('{modelo}'))],
```

---

## Alpine.js — patrón obligatorio

```js
// resources/js/alpine/admin/{modulo}.js
export default (initial = {}) => ({
    // — estado modal crear/editar —
    modalOpen: false,
    modalMode: 'create',
    form: { id: null, /* campos del formulario con defaults vacíos */ },

    // — estado drawer filtros —
    filterOpen: false,

    // — estado modal confirmar toggle (solo si tiene activo) —
    confirmOpen:   false,
    confirmId:     null,
    confirmNombre: '',
    confirmActivo: false,

    // — estado modal confirmar delete —
    deleteOpen:   false,
    deleteId:     null,
    deleteNombre: '',

    ...initial,  // sobrescribe con valores del servidor (errores de validación)

    openCreate() {
        this.modalMode = 'create';
        this.form      = { id: null, /* campos vacíos */ };
        this.modalOpen = true;
    },

    openEdit(id, /* campos */) {
        this.modalMode = 'edit';
        this.form      = { id, /* campos */ };
        this.modalOpen = true;
    },

    confirmToggle(id, nombre, activo) {   // solo si tiene activo
        this.confirmId     = id;
        this.confirmNombre = nombre;
        this.confirmActivo = activo;
        this.confirmOpen   = true;
    },

    executeToggle() {
        document.getElementById('toggle-' + this.confirmId).submit();
    },

    confirmDelete(id, nombre) {
        this.deleteId     = id;
        this.deleteNombre = nombre;
        this.deleteOpen   = true;
    },

    executeDelete() {
        document.getElementById('delete-' + this.deleteId).submit();
    },
});
```

Registrar en `resources/js/app.js`:
```js
import {modulo} from './alpine/admin/{modulo}.js';
// ...
Alpine.data('{modulo}', {modulo});
```

---

## index.blade.php — patrón obligatorio

```blade
@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'    => (int) old('_editing_id', 0) ?: null,
            // un campo por cada input del formulario:
            'campo' => old('campo', ''),
        ],
    ] : [];
@endphp

<x-layouts.app title="{Título de la página}">
<div x-data="{modulo}({{ Js::from($initial) }})" class="space-y-6">

    @include('modules.admin.{modulo}._header')
    @include('modules.admin.{modulo}._tabla')
    @include('modules.admin.{modulo}._drawer-filtros')
    @include('modules.admin.{modulo}._modal')
    @include('modules.admin.{modulo}._modal-confirm')   {{-- solo si tiene activo --}}
    @include('modules.admin.{modulo}._modal-delete')

</div>
</x-layouts.app>
```

---

## _modal.blade.php — patrón obligatorio

```blade
<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="sm">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('admin.{modulo}.store') }}'
                : '{{ url('admin/{ruta-base}') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nuevo {entidad}' : 'Editar {entidad}'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 space-y-4 pb-2">
                {{-- campos con x-ui.form-field + :state="$errors->has('campo') ? 'destructive' : null" --}}

                {{--
                    IMPORTANTE — x-ui.select con FK:
                    El componente x-ui.select inicializa su valor internamente desde el prop PHP `value`,
                    que es estático al momento de render. No admite x-model para sincronización reactiva.

                    Patrón correcto para un select ligado a form.{campo_id}:

                    <x-ui.select
                        name="{campo_id}"
                        x-effect="value = String(form.{campo_id} ?? '')"
                        @select-change="form.{campo_id} = $event.detail.value"
                    >

                    - x-effect corre en el scope del select (donde `value` es su estado interno),
                      y resuelve `form` desde el scope padre. Actualiza el label mostrado cada vez
                      que openEdit() cambia form.{campo_id}.
                    - @select-change escribe de vuelta en form.{campo_id} cuando el usuario elige.
                    - NO usar x-model en x-ui.select — no tiene efecto en el scope interno.
                --}}
            </div>

            <x-ui.dialog.footer>
                <x-ui.button type="button" variant="ghost" @click="open = false">
                    <x-lucide-x class="size-4" />
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit">
                    <x-lucide-save class="size-4" />
                    <span x-text="modalMode === 'create' ? 'Crear' : 'Guardar cambios'"></span>
                </x-ui.button>
            </x-ui.dialog.footer>
        </form>
    </x-ui.dialog.content>
</div>
```

---

## _modal-confirm.blade.php — patrón obligatorio (solo si tiene `activo`)

- El botón **Cancelar** usa `variant="ghost" state="destructive"` en modales destructivos.
- El botón de acción destructiva usa `state="destructive"`, el constructivo `state="success"`.
- La descripción cuando se desactiva debe decir exactamente qué impacto tiene en el sistema.

```blade
<div x-data="{ get open() { return confirmOpen }, set open(v) { confirmOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title
                x-text="confirmActivo ? 'Desactivar {entidad}' : 'Activar {entidad}'"
            ></x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Confirmás que querés
                <span x-text="confirmActivo ? 'desactivar' : 'activar'"></span>
                <strong x-text="confirmNombre" class="text-foreground font-medium"></strong>?
                <span x-show="confirmActivo" class="block mt-1">
                    {Consecuencia de desactivar en el sistema.}
                </span>
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            {{-- El state del Cancelar refleja el contexto: destructivo al desactivar, default al activar --}}
            <x-ui.button type="button" variant="ghost" state="destructive" x-show="confirmActivo" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button type="button" variant="ghost" x-show="!confirmActivo" x-cloak @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button x-show="confirmActivo" state="destructive" @click="executeToggle(); open = false">
                <x-lucide-ban class="size-4" />
                Desactivar
            </x-ui.button>
            <x-ui.button x-show="!confirmActivo" state="success" @click="executeToggle(); open = false">
                <x-lucide-circle-check class="size-4" />
                Activar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
```

---

## _modal-delete.blade.php — patrón obligatorio

- El botón **Cancelar** usa `variant="ghost" state="destructive"`.
- La descripción **no dice** "Esta acción no se puede deshacer" — describe qué se pierde y qué no se ve afectado.

```blade
<div x-data="{ get open() { return deleteOpen }, set open(v) { deleteOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title>Eliminar {entidad}</x-ui.dialog.title>
            <x-ui.dialog.description>
                Al eliminar
                <strong x-text="deleteNombre" class="text-foreground font-medium"></strong>
                {qué se pierde}. {Qué no se ve afectado.}
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button state="destructive" @click="executeDelete(); open = false">
                <x-lucide-trash-2 class="size-4" />
                Eliminar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
```

---

## Rutas — patrón obligatorio

```php
// routes/web.php — dentro del grupo admin
Route::resource('{ruta-base}', {Modelo}Controller::class)
    ->only(['index', 'store', 'update', 'destroy']);

// Solo si tiene campo activo:
Route::patch('{ruta-base}/{{modelo}}/toggle', [{Modelo}Controller::class, 'toggle'])
    ->name('{ruta-base}.toggle');
```

Usar siempre el parámetro de ruta en singular y minúscula: `{tipo}`, `{vehiculo}`, `{zona}`.

---

## Toasts — reglas de UX writing

Aplicar según `docs/ux-writing.md`. Resumen para ABMs de admin:

| Acción | `message` | `description` |
|---|---|---|
| store | `{Entidad} creada.` | `"{nombre}" quedó disponible.` |
| update | `Cambios guardados.` | `"{nombre nuevo}" fue actualizado correctamente.` |
| toggle desactivar | `{Entidad} desactivada.` | `"{nombre}" {impacto en el sistema}.` |
| toggle activar | `{Entidad} activada.` | `"{nombre}" volvió a estar disponible.` |
| destroy | `{Entidad} eliminada.` | `{Qué no se ve afectado.}` |
| destroy constraint | `No se puede eliminar.` | `"{nombre}" {qué registros bloquean}. {Cómo resolverlo.}` |
| error inesperado | `Error inesperado.` | `Si el problema persiste, revisá los logs del sistema.` |

Reglas:
- Sentence case. Sin exclamaciones. Sin emoji.
- `message` = hecho consumado en dos o tres palabras.
- `description` = contexto o consecuencia. Siempre con punto final.
- `variant`: `success` para éxito, `destructive` para error.

---

## Checklist antes de considerar un ABM completo

- [ ] Migration con tipos correctos y FKs si corresponde
- [ ] Model con `$fillable`, `$casts` y `scopeActivos()` si tiene `activo`
- [ ] Repository con `paginate()` filtrando con `->when()` y `appends()`
- [ ] Service con métodos `listar / crear / actualizar / desactivar / activar / eliminar`
- [ ] StoreRequest + UpdateRequest con `rules()`, `attributes()`, `messages()`
- [ ] Controller con try/catch en todas las mutaciones
- [ ] Controller con `QueryException` diferenciado en `destroy` si hay relaciones
- [ ] Todos los toasts tienen `message` + `description` + `variant`
- [ ] Alpine JS registrado en `app.js`
- [ ] `index.blade.php` con `$initial` para reabrir modal tras error de validación
- [ ] `_modal.blade.php` con `_mode` y `_editing_id` hidden inputs
- [ ] `_modal-confirm.blade.php` con Cancelar usando `variant="ghost" state="destructive"`
- [ ] `_modal-delete.blade.php` con descripción específica (no genérica) y Cancelar usando `variant="ghost" state="destructive"`
- [ ] Empty state diferenciado: filtros activos vs. lista vacía
- [ ] Paginación con `->appends()` para preservar filtros en la URL
- [ ] Ruta `toggle` con método PATCH (solo si tiene `activo`)
- [ ] Rutas registradas en el grupo admin correspondiente
- [ ] Sección en `docs/knowledge/modulo-abms.md` completa con las subsecciones definidas abajo

---

## Documentación de conocimiento — patrón obligatorio

Cada módulo ABM debe tener su sección en `docs/knowledge/modulo-abms.md`. Esta documentación alimenta el agente de ayuda al usuario y debe estar escrita en lenguaje no técnico (sin mencionar controllers, migraciones, SQL ni términos de programación).

La referencia canónica es la sección **Padrón de tipos de vehículo** del mismo archivo.

### Subsecciones obligatorias por módulo

```markdown
## Padrón de {nombre}

**Ruta:** {Sección del sidebar} → {Nombre en el menú}

{Párrafo de introducción: qué define este padrón y para qué lo usa el sistema.}

---

### Para qué se usa este padrón

{Explicar el impacto operativo real. ¿Qué no puede funcionar sin este padrón cargado?
¿Cómo afecta al operador? ¿Cómo afecta a los reportes?}

---

### Campos del formulario

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| {campo} | {descripción en lenguaje de usuario} | Sí / No |

> **Importante:** {aclaración sobre algún campo que genera confusión frecuente, si aplica}

**Validaciones:**
- {regla en lenguaje de usuario — ej: "El peso máximo debe ser mayor al mínimo."}

---

### {Tabla de valores de referencia del sistema} ← solo si el módulo tiene datos de ejemplo cargados

| Columna | ... |
|---------|-----|

---

### Cómo crear un registro nuevo

{Pasos numerados desde la navegación hasta guardar.}

---

### Cómo editar un registro existente

{Pasos. Aclarar si los cambios son retroactivos o solo afectan registros futuros.}

---

### Cómo desactivar un registro

{Pasos. Describir qué deja de funcionar al desactivarlo y qué se conserva.}

---

### Cuándo desactivar vs. cuándo eliminar

**Desactivar:** {cuándo es la acción correcta}
**Eliminar:** {cuándo está permitido. Si hay constraint, describir qué lo bloquea y cómo resolverlo.}

---

### {Sección de impacto en otras partes del sistema} ← título según el módulo

{Describir relaciones con otros padrones o con la operación diaria.
Ej: "Relación con el padrón de vehículos", "Cómo afecta al formulario de pesaje".}

---

### Preguntas frecuentes sobre {nombre del padrón}

**¿{Pregunta sobre el constraint de eliminación}?**
{Respuesta.}

**¿{Pregunta sobre retroactividad de cambios}?**
{Respuesta.}

**¿{Pregunta sobre el impacto de desactivar}?**
{Respuesta.}

**¿{Pregunta sobre límites o unicidad}?**
{Respuesta.}

**¿{Pregunta sobre nomenclatura o convención}?**
{Respuesta.}

**¿{Pregunta sobre estado inactivo en la operación}?**
{Respuesta.}
```

### Prompt para generar la sección de conocimiento

Usar este prompt luego de tener el módulo técnico completo:

```
Generar la sección de conocimiento para el padrón "{Nombre}" en docs/knowledge/modulo-abms.md,
siguiendo exactamente el patrón de la sección "Padrón de tipos de vehículo" de ese archivo.

Ruta en el sistema: {Sección sidebar} → {Nombre en menú}

Campos del modelo:
{
  campo1: tipo | descripción para el usuario,
  campo2: tipo | descripción para el usuario,
  ...
}

Tiene campo `activo`: {sí / no}

Relaciones con otros padrones:
- {qué otro padrón depende de este, y cómo}
- {en qué parte de la operación aparece este padrón}

Constraint de eliminación:
- {qué registros bloquean el delete y cómo se resuelve}

Impacto de los cambios en datos existentes:
- {los cambios son retroactivos / solo afectan registros futuros}

Valores de referencia cargados en el sistema (si existen):
{tabla con ejemplos reales}

Preguntas frecuentes esperadas:
- {pregunta 1}
- {pregunta 2}
- ...

Escribir en español operativo argentino, sin jerga técnica, en lenguaje de usuario (Nacho — admin).
Seguir las reglas de docs/ux-writing.md.
```
