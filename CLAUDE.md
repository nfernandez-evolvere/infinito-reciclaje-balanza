# Sistema de Gestión de Balanza — Infinito Reciclaje

## Documentación del proyecto

| Documento | Descripción |
|-----------|-------------|
| [`docs/roadmap.md`](docs/roadmap.md) | Plan de desarrollo completo: sprints, schema de DB, arquitectura de pantallas, criterios de go-live |
| [`docs/ux-writing.md`](docs/ux-writing.md) | Voz y tono del sistema, reglas de escritura diferenciadas por rol (operador vs admin), formatos, ejemplos |
| [`docs/Brief_Producto_Etapa1.md`](docs/Brief_Producto_Etapa1.md) | Requerimientos funcionales y no funcionales de Etapa 1 |
| [`docs/design-system.md`](docs/design-system.md) | Documentación del design system Blade (`x-ui.*`) |
| [`docs/knowledge/README.md`](docs/knowledge/README.md) | Base de conocimiento de usuario: onboarding, configuración inicial y referencia de cada módulo (preparada para RAG) |
| [`docs/data-model.md`](docs/data-model.md) | Modelo de datos completo: tipos, constraints, índices, cardinalidades, patrones de consulta y decisiones de diseño |
| [`docs/sprints/`](docs/sprints/) | Plan detallado por sprint: sub-sprints, tareas granulares, tests unitarios, de integración y manuales |
| [`docs/testing-strategy.md`](docs/testing-strategy.md) | Estrategia y convenciones de testing: taxonomía de suites, naming, roadmap de cobertura, CI |

> **Al escribir cualquier texto en vistas Blade, consultar `docs/ux-writing.md`.**
> Las reglas de escritura para el operador y el admin son distintas — aplicarlas según el perfil de la pantalla.

---

## ⚠️ Base de datos — comandos PROHIBIDOS

La base de datos de producción es **compartida entre múltiples proyectos**. Cada proyecto usa un prefijo de tabla para separar sus datos. Ejecutar comandos destructivos sobre la base **elimina las tablas de todos los proyectos**, no solo de este.

**Nunca ejecutar bajo ninguna circunstancia:**

```bash
php artisan migrate:fresh          # elimina TODAS las tablas y vuelve a migrar
php artisan migrate:fresh --seed   # ídem + seeders
php artisan migrate:reset          # revierte TODAS las migraciones (drop de tablas)
php artisan db:wipe                # elimina TODAS las tablas, vistas y tipos
```

**Permitido:**

```bash
php artisan migrate                # solo aplica migraciones pendientes (seguro)
php artisan migrate --force        # ídem, sin prompt de confirmación (para scripts de deploy)
php artisan migrate:rollback       # revierte solo el último batch (usar con criterio)
php artisan migrate:status         # consulta el estado (solo lectura, siempre seguro)
```

> Si necesitás limpiar el entorno local usá una base SQLite local (`DB_CONNECTION=sqlite`) donde no hay riesgo de afectar datos de otros proyectos.

---

## ⚠️ SQL Server — Reglas de migración

El proyecto usa **SQL Server Express** como motor de base de datos. SQL Server tiene restricciones que MySQL/PostgreSQL no tienen. Aplicar siempre estas reglas al escribir migraciones.

### Variables de entorno requeridas

```dotenv
DB_PORT=            # dejar VACÍO para instancias nombradas (ej: localhost\SQLEXPRESS)
DB_SCHEMA=dbo       # nunca dejar vacío — genera "".tabla que es SQL inválido
```

### `noActionOnDelete()` en lugar de `restrictOnDelete()`

SQL Server **no soporta** `ON DELETE RESTRICT`. Usar siempre `noActionOnDelete()`:

```php
// ❌ SQL Server lanza syntax error
$table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculo')->restrictOnDelete();

// ✅ Correcto en SQL Server
$table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculo')->noActionOnDelete();
```

### Cascadas múltiples — el error más frecuente

SQL Server **rechaza** `ON DELETE CASCADE` en una FK si ya existe otro camino de cascada desde el mismo ancestro hasta esa tabla (directa o transitivamente). Esto aplica a tablas pivote y a cualquier tabla con dos FKs.

**Regla práctica:** si una tabla tiene dos FKs y ambas (directa o transitivamente) llegan al mismo ancestro con CASCADE, la FK "secundaria" debe usar `noActionOnDelete()`.

**Ejemplo — `vehiculos`:**
```
organizaciones ──cascade──► tipos_vehiculo ──cascade──► vehiculos   ← dos caminos a organizaciones
organizaciones ──cascade──────────────────────────────► vehiculos
```
```php
// ✅ organizacion_id cascadea (path directo, suficiente para limpiar al eliminar org)
$table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
// ✅ tipo_vehiculo_id NO cascadea (evita el segundo camino)
$table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculo')->noActionOnDelete();
```

**Patrón que siempre lo dispara — tabla pivote con dos FKs que comparten ancestro:**
```php
// ❌ Ambas cascadean y convergen en organizaciones → SQL Server lo rechaza
$table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();           // zonas → org
$table->foreignId('tipo_servicio_id')->constrained('tipos_servicio')->cascadeOnDelete(); // servicios → org

// ✅ Solo la FK primaria cascadea
$table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
$table->foreignId('tipo_servicio_id')->constrained('tipos_servicio')->noActionOnDelete();
```

**Checklist antes de crear una migración con FKs:**
1. ¿Alguna de las FKs apunta a una tabla que ya cascadea de `organizaciones`? (`tipos_vehiculo`, `tipos_servicio`, `zonas`, `vehiculos`, …)
2. ¿Hay otra FK en la misma tabla que también (directa o transitivamente) llega a `organizaciones`?
3. Si ambas respuestas son sí → la FK secundaria debe ser `noActionOnDelete()`.

### Límite de 2100 parámetros por query — bulk inserts

SQL Server acepta **máximo 2100 parámetros** por sentencia SQL. En inserciones masivas (`DB::table()->insert($batch)`), cada columna de cada fila cuenta como un parámetro.

```
máx_filas_por_batch = floor(2100 / cantidad_de_columnas)
```

| Columnas | Máx. filas seguras |
|----------|--------------------|
| 10       | 210                |
| 15       | 140                |
| 18       | 116 → usar **100** |
| 20       | 105 → usar **100** |

```php
// ❌ Con 18 cols y 500 filas → 9000 params → SQL Server lanza error
DB::table('pesajes')->insert($batch); // $batch con 500 elementos

// ✅ Cortar el batch a ≤ floor(2100 / n_cols) antes de insertar
foreach (array_chunk($batch, 100) as $chunk) {
    DB::table('pesajes')->insert($chunk);
}
```

Regla práctica: usar **BATCH_SIZE = 100** en seeders de tablas con muchas columnas. Para tablas simples (≤10 cols) se puede usar 200.

### Fechas en inserciones y updates raw — usar ISO 8601 con `T`

SQL Server interpreta las fechas según el `SET DATEFORMAT` del servidor (puede ser `dmy` en servidores con locale en español). El formato `Y-m-d H:i:s` (`2026-01-13 08:23:06`) **es ambiguo** y puede fallar. El formato ISO 8601 con separador `T` es **siempre** aceptado sin importar el DATEFORMAT.

```php
// ❌ Ambiguo — SQL Server con DATEFORMAT=dmy interpreta 2026 como día → error
'created_at' => $carbon->format('Y-m-d H:i:s'),

// ✅ ISO 8601 con T — siempre unambiguo en SQL Server
'created_at' => $carbon->format('Y-m-d\TH:i:s'),
```

Aplica a todos los `DB::table()->insert()`, `DB::table()->update()`, y queries raw con fechas como strings.
Eloquent no tiene este problema — usa objetos Carbon/DateTime que el driver SQLSRV envía en formato nativo.

### Otras restricciones SQL Server en queries raw

Si alguna vez se necesita SQL raw, tener en cuenta:
- No existe `LIMIT` — usar query builder (`.limit()`, `.take()`) que genera `TOP`/`OFFSET FETCH` correcto
- No existe `DATE_FORMAT()` — usar Carbon o métodos del query builder
- No existe `GROUP_CONCAT()` — usar `STRING_AGG(columna, separador)`
- Booleanos en raw: usar `1`/`0`, no `true`/`false`
- `ISNULL()` en lugar de `IFNULL()`

---

# Laravel Design System — shadcn/ui en Blade

Stack: **Laravel 13 + Tailwind CSS v4 + Alpine.js + Blade components**
Inspirado en shadcn/ui (new-york style), sin React, sin librerías de componentes JS.

---

## Setup de proyecto nuevo

```bash
composer create-project laravel/laravel nombre-proyecto
cd nombre-proyecto
npm install
npm install alpinejs @alpinejs/collapse
composer require mallardduck/blade-lucide-icons
```

Tailwind v4 y `@tailwindcss/vite` ya vienen incluidos en Laravel 13.

---

## Iconos

Set: **Lucide Icons** — el mismo que usa shadcn/ui por defecto.
Paquete: [`mallardduck/blade-lucide-icons`](https://github.com/mallardduck/blade-lucide-icons)
Catálogo completo: [lucide.dev](https://lucide.dev)

```blade
<x-lucide-home class="size-4" />
<x-lucide-shopping-cart class="size-4" />
<x-lucide-bell class="size-4 text-muted-foreground" />
<x-lucide-chevron-down class="size-4" />
<x-lucide-settings class="size-4" />
<x-lucide-circle-alert class="size-4 text-destructive" />
```

El nombre del componente es `<x-lucide-{nombre-con-guiones}>`.
Buscar el nombre exacto en [lucide.dev](https://lucide.dev) — el ícono "Trash 2" se usa como `<x-lucide-trash-2>`.

---

## vite.config.js

Reemplazar Bunny Fonts por Inter. Editar `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: { ignored: ['**/storage/framework/views/**'] },
    },
});
```

---

## resources/js/app.js

```js
import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';

Alpine.plugin(Collapse);

window.Alpine = Alpine;
Alpine.start();
```

---

## Estructura CSS — Design System

Los archivos CSS están divididos por responsabilidad:

```
resources/css/
├── app.css          → solo imports (punto de entrada, aquí se activa el tema)
├── base.css         → @variant dark, @source, @layer base — nunca cambia
├── tokens.css       → tipografía, radios, sombras — compartido entre temas
└── themes/
    ├── zinc.css     → paleta neutral/oscura (default, shadcn new-york)
    ├── blue.css     → paleta azul eléctrico
    ├── rose.css     → paleta rose/pink
    └── emerald.css  → paleta verde esmeralda
```

### Cambiar de tema

Editar `resources/css/app.css` y descomentar el tema deseado:

```css
@import 'tailwindcss';
@import './base.css';
@import './tokens.css';

@import './themes/zinc.css';
/* @import './themes/blue.css'; */
/* @import './themes/rose.css'; */
/* @import './themes/emerald.css'; */
```

Los componentes Blade no necesitan cambios — todo se deriva de los tokens.

### Tokens disponibles en `tokens.css`

| Token | Utilidad Tailwind | Descripción |
|-------|-------------------|-------------|
| `--font-sans` | `font-sans` | Fuente principal (Inter) |
| `--font-mono` | `font-mono` | Fuente monospace |
| `--radius` | — | Radio base (0.5rem) |
| `--radius-sm/md/lg/xl` | — | Radios derivados |
| `--shadow-sm/md/lg/xl` | `shadow-sm/md/lg/xl` | Sombras suaves estilo shadcn |

### Tokens de color (en cada tema)

Todos los temas definen los mismos tokens semánticos — light en `@theme {}`, dark en `@layer base { .dark {} }`:

| Token | Uso |
|-------|-----|
| `--color-background / foreground` | Fondo y texto de la página |
| `--color-primary / primary-foreground` | Acción principal, botón default |
| `--color-secondary / secondary-foreground` | Acción secundaria |
| `--color-muted / muted-foreground` | Fondos apagados, texto de ayuda |
| `--color-accent / accent-foreground` | Hover, items activos |
| `--color-destructive / destructive-foreground` | Errores, eliminación |
| `--color-warning / warning-foreground` | Advertencias |
| `--color-success / success-foreground` | Estados exitosos |
| `--color-border` | Todos los bordes |
| `--color-input` | Borde de inputs |
| `--color-ring` | Focus ring |
| `--color-card / card-foreground` | Tarjetas |
| `--color-popover / popover-foreground` | Dropdowns, popovers |

> **Agregar un nuevo token de color**: declarar `--color-nombre` en `@theme {}` (light) y en `.dark {}` (dark) dentro del archivo de tema activo. Tailwind v4 genera automáticamente `bg-nombre`, `text-nombre`, `border-nombre`, `border-nombre/50`, etc.

> **Cambiar la fuente**: editar `--font-sans` en `tokens.css` y actualizar el `@import` del font en el `<head>` del layout.

> **Crear un tema nuevo**: copiar cualquier archivo de `themes/` y ajustar los valores de color. Activarlo en `app.css`.

---

## Layout principal

Archivo: `resources/views/components/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground antialiased">

    <nav class="bg-card border-b border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <span class="text-lg font-semibold">{{ config('app.name', 'Laravel') }}</span>
                <div class="flex items-center gap-4">{{ $nav ?? '' }}</div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{ $slot }}
    </main>

    <x-ui.sonner />
</body>
</html>
```

Uso: `<x-layouts.app title="Título">...</x-layouts.app>`

---

## Tipografía

### Escala de tamaños

La escala base es la de Tailwind. Los valores clave del proyecto:

| Clase Tailwind | Tamaño | Uso típico |
|----------------|--------|------------|
| `text-xs`      | 12px   | Badges, timestamps, metadata |
| `text-sm`      | 14px   | UI texto, botones, inputs, labels |
| `text-base`    | 16px   | Cuerpo principal |
| `text-lg`      | 18px   | Lead, subtítulos menores |
| `text-xl`      | 20px   | Heading 4 |
| `text-2xl`     | 24px   | Heading 3 |
| `text-3xl`     | 30px   | Heading 2 |
| `text-4xl`     | 36px   | Heading 1 |
| `text-5xl`     | 48px   | Display / hero |

### Roles semánticos

Clases de utilidad definidas en `tokens.css` que combinan size + weight + leading + tracking. Soportan variantes de Tailwind (`lg:text-h1`, `dark:text-caption`, etc.):

| Clase | Equivalente manual | Uso |
|-------|--------------------|-----|
| `text-display` | `text-5xl font-bold tracking-tight leading-none` | Hero, landing |
| `text-h1` | `text-4xl font-bold tracking-tight` | Título de página |
| `text-h2` | `text-3xl font-semibold tracking-tight` | Título de sección |
| `text-h3` | `text-2xl font-semibold` | Subtítulo de sección |
| `text-h4` | `text-xl font-semibold` | Subtítulo de card/panel |
| `text-lead` | `text-lg text-muted-foreground` | Bajada, intro |
| `text-body` | `text-base` | Cuerpo principal |
| `text-body-sm` | `text-sm` | Cuerpo secundario, descripciones |
| `text-label` | `text-sm font-medium` | Labels de formulario |
| `text-caption` | `text-xs text-muted-foreground` | Metadata, timestamps |
| `text-overline` | `text-xs font-semibold uppercase tracking-widest text-muted-foreground` | Eyebrow, categoría |

```blade
<h1 class="text-h1">Título de la página</h1>
<p class="text-lead">Descripción introductoria del contenido.</p>

<h2 class="text-h2">Sección</h2>
<p class="text-body">Cuerpo del texto principal...</p>

<span class="text-overline">Categoría</span>
<span class="text-caption">Hace 3 minutos</span>
```

### Pesos

Usar siempre los pesos definidos por el sistema. No usar `font-black` ni `font-extrabold`.

| Clase | Peso | Uso |
|-------|------|-----|
| `font-normal` | 400 | Cuerpo de texto |
| `font-medium` | 500 | Labels, botones, nav items |
| `font-semibold` | 600 | Headings h3–h4, card titles |
| `font-bold` | 700 | Headings h1–h2, display |

### Fuentes

Definidas en `tokens.css`:
- `--font-sans` → `font-sans` — Inter (fuente principal, para todo)
- `--font-heading` → `font-heading` — igual a `font-sans` por defecto; cambiarlo para dar personalidad a los headings (ej: una serif para editorial)
- `--font-mono` → `font-mono` — monospace para código

```blade
{{-- Cambiar fuente solo en headings (si --font-heading fue modificada) --}}
<h1 class="font-heading text-h1">Título con fuente de display</h1>

{{-- Código inline --}}
<code class="font-mono text-sm bg-muted px-1.5 py-0.5 rounded">npm run dev</code>
```

---

## Espaciado

El sistema usa la escala de Tailwind (base 4px = `spacing-1`). La clave es usar siempre los **múltiplos del sistema** y no valores arbitrarios.

### Escala de referencia

| Valor | px | Uso principal |
|-------|----|---------------|
| `1`   | 4px  | Espaciado mínimo, gaps internos |
| `1.5` | 6px  | Gap entre ícono y texto en botón |
| `2`   | 8px  | Gap entre elementos compactos |
| `3`   | 12px | Padding lateral en botón sm, items de lista |
| `4`   | 16px | Padding base, gap estándar entre elementos |
| `5`   | 20px | — |
| `6`   | 24px | Padding de cards, secciones de form |
| `8`   | 32px | Separación entre secciones |
| `10`  | 40px | — |
| `12`  | 48px | Separación grande entre bloques |
| `16`  | 64px | Márgenes de página, spacing de layout |

### Gaps entre elementos (flex / grid)

```blade
{{-- Compacto: íconos, chips, tags --}}
<div class="flex items-center gap-1.5">...</div>

{{-- Base: botones agrupados, form fields en horizontal --}}
<div class="flex items-center gap-2">...</div>

{{-- Cómodo: secciones de formulario, cards en fila --}}
<div class="flex items-center gap-4">...</div>

{{-- Amplio: grupos de secciones en layout --}}
<div class="grid grid-cols-3 gap-6">...</div>
```

### Stacks verticales (`space-y-*`)

```blade
{{-- Label → Input (dentro de un campo de formulario) --}}
<div class="space-y-2">
    <x-ui.label>Email</x-label>
    <x-ui.input type="email" />
</div>

{{-- Campos dentro de un formulario --}}
<form class="space-y-4">
    <div class="space-y-2">...</div>
    <div class="space-y-2">...</div>
</form>

{{-- Secciones dentro de una página --}}
<div class="space-y-8">
    <section>...</section>
    <section>...</section>
</div>
```

### Anatomía de padding en componentes

Los valores de padding siguen un patrón uniforme en todo el sistema:

| Contexto | Clases | px equivalente |
|----------|--------|----------------|
| Card (header, content, footer) | `p-6` | 24px todos los lados |
| Card content (sin top, ya lo da el header) | `p-6 pt-0` | 24px sin arriba |
| Card header interno (title → description) | `space-y-1.5` | 6px |
| Dialog / Sheet panel | `p-6` | 24px |
| Dialog footer (botones) | `flex justify-end gap-2` | 8px entre botones |
| Dropdown menu (contenedor) | `p-1` | 4px — lista de items |
| Dropdown item | `px-2 py-1.5` | 8px/6px |
| Tooltip | `px-2.5 py-1.5` | 10px/6px |

### Alturas de controles interactivos

Todos los controles tienen alturas fijas para alinearse en layouts mixtos:

| Tamaño | Altura | Usado en |
|--------|--------|----------|
| `sm` | `h-8` (32px) | `<x-ui.button size="sm">` |
| `default` | `h-9` (36px) | `<x-ui.button>`, `<x-ui.input>`, `<x-ui.select>` |
| `lg` | `h-10` (40px) | `<x-ui.button size="lg">` |
| `icon` | `h-9 w-9` (36×36px) | `<x-ui.button size="icon">` |

### Convenciones de layout

```blade
{{-- Padding lateral de página (hereda del layout) --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

{{-- Sección con encabezado --}}
<div class="space-y-6">
    <div>
        <h1 class="text-h2">Usuarios</h1>
        <p class="text-lead mt-1">Administrá los usuarios del sistema.</p>
    </div>
    {{-- contenido --}}
</div>

{{-- Grid de cards responsive --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <x-ui.card>...</x-ui.card>
</div>
```

---

## Estructura de vistas y componentes

```
resources/views/
├── components/
│   ├── ui/         → primitivos atómicos · prefijo <x-ui.*>
│   ├── layouts/    → layouts de página  · prefijo <x-layouts.*>
│   └── domain/     → componentes de dominio · prefijo <x-domain.[módulo].[nombre]>
├── modules/        → vistas por módulo del sistema
│   ├── auth/       → resources/views/modules/auth/login.blade.php
│   ├── admin/      → resources/views/modules/admin/dashboard.blade.php
│   └── operador/   → resources/views/modules/operador/pesaje.blade.php
└── welcome.blade.php
```

### Regla de capas

| Capa | Carpeta | Prefijo Blade | Qué contiene |
|------|---------|---------------|--------------|
| Primitivos | `components/ui/` | `<x-ui.*>` | Button, Card, Input, Dialog… |
| Layouts | `components/layouts/` | `<x-layouts.*>` | app, dashboard, auth |
| Dominio | `components/domain/[módulo]/` | `<x-domain.[módulo].*>` | Componentes reutilizables con lógica del negocio |
| Vistas | `modules/[módulo]/` | `view('modules.[módulo].[vista]')` | Páginas completas del sistema |

### Crear un módulo nuevo

Al comenzar un módulo (ej: `pesajes`):

1. Crear carpeta de vistas: `resources/views/modules/pesajes/`
2. Crear carpeta de dominio si hay componentes reutilizables: `resources/views/components/domain/pesajes/`
3. Agregar rutas en `routes/web.php` apuntando a `modules.pesajes.*`
4. Usar solo `<x-ui.*>` y `<x-layouts.*>` dentro de las vistas y componentes de dominio

---

### Patrón de desacoplamiento con subcomponentes de dominio

Las vistas complejas se desacoplan en subcomponentes de dominio. La vista principal queda como orquestadora: solo declara el `x-data` de Alpine, calcula las variables PHP necesarias con `@php`, y delega cada sección a un `<x-domain.[módulo].*>`.

**Cuándo extraer a subcomponente:**
- La sección tiene más de ~20 líneas de Blade
- Se repite en más de un lugar (mobile + desktop)
- Es una unidad semántica independiente (filtros, tabla, dialog)

**Qué va en la vista principal:**
- `x-data` de Alpine (fuente de verdad del estado JS)
- Slots del layout (`footerTurno`, etc.)
- Variables PHP de configuración/derivadas (`@php ... @endphp`)
- Solo llamadas a `<x-domain.[módulo].*>` con sus props

**Qué va en el subcomponente:**
- El HTML completo de la sección
- `@props([])` para todo lo que viene del controller
- Acceso al estado Alpine del padre directamente (sin `x-data` propio, a menos que sea un sub-árbol aislado)

**Ejemplo — módulo `historial`:**

```
resources/views/
├── modules/operador/historial.blade.php     → orquestadora (36 líneas)
└── components/domain/historial/
    ├── kpis.blade.php                        → grid desktop de KPIs
    ├── filtros.blade.php                     → card desktop de filtros
    ├── mobile-drawers.blade.php              → drawers mobile (métricas + filtros)
    ├── tabla.blade.php                       → tabla + empty states
    ├── dialog-egreso.blade.php               → dialog marcar egreso
    └── dialog-cambios.blade.php              → dialog historial de cambios
```

```blade
{{-- historial.blade.php — la vista solo orquesta --}}
<div class="flex flex-col gap-6" x-data="historial()">
    @php $hayFiltros = ...; @endphp

    <x-domain.historial.mobile-drawers :kpis="$kpis" :filtros="$filtros" :operarios="$operarios" :hayFiltros="$hayFiltros" />
    <x-domain.historial.kpis :kpis="$kpis" />
    <x-domain.historial.filtros :filtros="$filtros" :operarios="$operarios" :hayFiltros="$hayFiltros" />
    <x-domain.historial.tabla :pesajes="$pesajes" :hayFiltros="$hayFiltros" />
    <x-domain.historial.dialog-egreso />
    <x-domain.historial.dialog-cambios />
</div>
```

Los subcomponentes que dependen de Alpine (dialogs, acciones de tabla) no reciben props para el estado JS — lo acceden directamente por pertenecer al mismo ámbito `x-data` del padre.

Este patrón está aplicado en: `balanza` y `historial`.

---

## Catálogo de componentes

Todos los primitivos viven en `resources/views/components/ui/` y se usan con el prefijo `<x-ui.*>`.
Los sub-componentes usan carpetas con el mismo nombre: `ui/accordion/item.blade.php` → `<x-ui.accordion.item>`.

### Convenciones

- Props con `@props([])` siempre al inicio del archivo.
- Usar `$attributes->merge(['class' => '...'])` para permitir clases adicionales desde fuera.
- Usar siempre tokens semánticos (`bg-primary`, `text-muted-foreground`) nunca colores hardcoded (`bg-zinc-900`).
- Interactividad con Alpine.js directamente en el componente (sin archivos JS separados).
- `x-collapse` requiere el plugin `@alpinejs/collapse` (ya registrado en `app.js`).

---

### Button

Props: `variant` (default/secondary/destructive/outline/ghost/link/warning/success), `size` (default/sm/lg/icon), `type`, `disabled`

```blade
<x-ui.button>Default</x-ui.button>
<x-ui.button variant="destructive">Eliminar</x-ui.button>
<x-ui.button variant="outline" size="sm">Cancelar</x-ui.button>
<x-ui.button variant="ghost">Volver</x-ui.button>
<x-ui.button variant="link">Ver más</x-ui.button>
<x-ui.button variant="outline" size="icon">
    <svg .../>
</x-ui.button>
<x-ui.button disabled>No disponible</x-ui.button>
```

---

### Alert

Props: `variant` (default/destructive)
Sub-componentes: `<x-ui.alert.title>`, `<x-ui.alert.description>`
El SVG se posiciona automáticamente con CSS arbitrario (`[&>svg]:absolute [&>svg]:left-4`).

```blade
<x-ui.alert>
    <x-ui.alert.title>Título</x-ui.alert.title>
    <x-ui.alert.description>Descripción.</x-ui.alert.description>
</x-ui.alert>

<x-ui.alert variant="destructive">
    <svg class="size-4" .../>
    <x-ui.alert.title>Error</x-ui.alert.title>
    <x-ui.alert.description>Algo salió mal.</x-ui.alert.description>
</x-ui.alert>
```

---

### Badge

Props: `variant` (default/secondary/destructive/outline/warning/success)

```blade
<x-ui.badge>Default</x-ui.badge>
<x-ui.badge variant="destructive">Error</x-ui.badge>
<x-ui.badge variant="success">Activo</x-ui.badge>
<x-ui.badge variant="warning">Pendiente</x-ui.badge>
```

---

### Card

Props: `variant` (default/elevated)
Sub-componentes: `header`, `title`, `description`, `content`, `footer`

| Variant | Estilo | Cuándo usarla |
|---------|--------|---------------|
| `default` | `border border-border` — sin sombra | Patrón estándar: listas, tablas, formularios |
| `elevated` | `shadow-lg` — sin border | Tarjetas flotantes, KPIs, contenido destacado |

```blade
{{-- Default: con border --}}
<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title>Título</x-ui.card.title>
        <x-ui.card.description>Descripción.</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content>Contenido</x-ui.card.content>
    <x-ui.card.footer>
        <x-ui.button size="sm">Acción</x-ui.button>
    </x-ui.card.footer>
</x-ui.card>

{{-- Elevated: sin border, elevación por shadow --}}
<x-ui.card variant="elevated">
    <x-ui.card.content class="pt-6">Contenido destacado</x-ui.card.content>
</x-ui.card>
```

---

### Inputs de formulario

```blade
{{-- Label + Input --}}
<div class="space-y-2">
    <x-ui.label for="email">Email</x-label>
    <x-ui.input id="email" type="email" placeholder="nombre@ejemplo.com" />
    <x-ui.input :error="true" value="valor incorrecto" />  {{-- estado de error --}}
</div>

{{-- Textarea --}}
<x-ui.textarea placeholder="Escribí tu mensaje..." rows="4" />

{{-- Select --}}
<x-ui.select>
    <option value="">Seleccionar...</option>
    <option value="a">Opción A</option>
</x-select>

{{-- Checkbox --}}
<label class="flex items-center gap-2 text-sm">
    <x-ui.checkbox name="terms" /> Acepto los términos
</label>

{{-- Radio --}}
<label class="flex items-center gap-2 text-sm">
    <x-ui.radio name="plan" value="pro" :checked="true" /> Pro
</label>

{{-- Switch (Alpine) --}}
<label class="flex items-center gap-2 text-sm">
    <x-ui.switch :checked="true" /> Notificaciones
</label>
```

---

### Separator

Props: `orientation` (horizontal/vertical)

```blade
<x-ui.separator />
<x-ui.separator orientation="vertical" class="h-5" />
```

---

### Avatar

Props: `src`, `alt`, `fallback`

```blade
<x-ui.avatar src="https://..." alt="Nombre" />
<x-ui.avatar fallback="JG" />
<x-ui.avatar fallback="AB" class="h-12 w-12" />
```

---

### Skeleton

```blade
<x-ui.skeleton class="h-4 w-full" />
<x-ui.skeleton class="h-10 w-10 rounded-full" />
```

---

### Progress

Props: `value` (0-100), `max` (default 100)

```blade
<x-ui.progress :value="75" class="w-full" />
```

---

### Tabs (Alpine)

Props en `<x-ui.tabs>`: `default` (valor inicial)

```blade
<x-ui.tabs default="tab1">
    <x-ui.tabs.list>
        <x-ui.tabs.trigger value="tab1">Tab 1</x-ui.tabs.trigger>
        <x-ui.tabs.trigger value="tab2">Tab 2</x-ui.tabs.trigger>
    </x-ui.tabs.list>
    <x-ui.tabs.content value="tab1">Contenido del tab 1</x-ui.tabs.content>
    <x-ui.tabs.content value="tab2">Contenido del tab 2</x-ui.tabs.content>
</x-ui.tabs>
```

---

### Accordion (Alpine + x-collapse)

```blade
<x-ui.accordion>
    <x-ui.accordion.item value="item-1">
        <x-ui.accordion.trigger>¿Pregunta?</x-ui.accordion.trigger>
        <x-ui.accordion.content>Respuesta.</x-ui.accordion.content>
    </x-ui.accordion.item>
    <x-ui.accordion.item value="item-2">
        <x-ui.accordion.trigger>Otra pregunta</x-ui.accordion.trigger>
        <x-ui.accordion.content>Otra respuesta.</x-ui.accordion.content>
    </x-ui.accordion.item>
</x-ui.accordion>
```

---

### Collapsible (Alpine + x-collapse)

Props: `open` (boolean, default false)

```blade
<x-ui.collapsible>
    <x-ui.collapsible.trigger>
        <x-ui.button variant="ghost">Mostrar más</x-ui.button>
    </x-ui.collapsible.trigger>
    <x-ui.collapsible.content>
        Contenido colapsable.
    </x-ui.collapsible.content>
</x-ui.collapsible>
```

---

### Toggle

Props: `variant` (default/outline), `size` (default/sm/lg), `pressed` (boolean)

```blade
<x-ui.toggle>Negrita</x-ui.toggle>
<x-ui.toggle variant="outline" :pressed="true">Activo</x-ui.toggle>
```

---

### Dropdown Menu (Alpine)

```blade
<x-ui.dropdown-menu>
    <x-ui.dropdown-menu.trigger>
        <x-ui.button variant="outline">Opciones</x-ui.button>
    </x-ui.dropdown-menu.trigger>
    <x-ui.dropdown-menu.content>
        <x-ui.dropdown-menu.label>Mi cuenta</x-ui.dropdown-menu.label>
        <x-ui.dropdown-menu.separator />
        <x-ui.dropdown-menu.item href="/perfil">Perfil</x-ui.dropdown-menu.item>
        <x-ui.dropdown-menu.item>Configuración</x-ui.dropdown-menu.item>
        <x-ui.dropdown-menu.separator />
        <x-ui.dropdown-menu.item :destructive="true">Cerrar sesión</x-ui.dropdown-menu.item>
    </x-ui.dropdown-menu.content>
</x-ui.dropdown-menu>
```

Props de `content`: `align` (start/end/center), `side` (bottom/top — default bottom). Usar `side="top"` cuando el trigger está al fondo de un contenedor fixed (ej: footer del sidebar).

> **Gotcha — triggers:** Todos los triggers (`dropdown-menu`, `popover`, `sheet`, `dialog`) son `<div>` con `@click.stop`, NO `<button>`. Un `<button>` wrapper genera botones anidados (HTML inválido) cuando el slot contiene `<x-ui.button>`. El `.stop` evita que `@click.outside` cierre el menú al instante.

> **Gotcha — sheet panel:** Las clases de transformación (`translate-x-full`, etc.) NO deben estar en `$sides` como clases estáticas. Alpine las remueve del enter-end al terminar la transición y el panel vuelve a off-screen. Solo deben estar en `x-transition:enter-start` y `x-transition:leave-end`.

---

### Tooltip (Alpine)

Props: `text`, `side` (top/bottom/left/right)

```blade
<x-ui.tooltip text="Texto del tooltip">
    <x-ui.button variant="outline">Hover aquí</x-ui.button>
</x-ui.tooltip>

<x-ui.tooltip text="Abajo" side="bottom">
    <x-ui.button variant="ghost" size="icon"><svg .../></x-ui.button>
</x-ui.tooltip>
```

---

### Popover (Alpine)

```blade
<x-ui.popover>
    <x-ui.popover.trigger>
        <x-ui.button variant="outline">Abrir</x-ui.button>
    </x-ui.popover.trigger>
    <x-ui.popover.content>
        Contenido del popover.
    </x-ui.popover.content>
</x-ui.popover>
```

Props de `content`: `align` (start/end/center)

---

### Dialog (Alpine + x-teleport)

```blade
<x-ui.dialog>
    <x-ui.dialog.trigger>
        <x-ui.button>Abrir Dialog</x-ui.button>
    </x-ui.dialog.trigger>
    <x-ui.dialog.content>
        <x-ui.dialog.header>
            <x-ui.dialog.title>Título</x-ui.dialog.title>
            <x-ui.dialog.description>Descripción opcional.</x-ui.dialog.description>
        </x-ui.dialog.header>
        {{-- cuerpo --}}
        <x-ui.dialog.footer>
            <x-ui.button variant="outline" @click="open = false">Cancelar</x-ui.button>
            <x-ui.button @click="open = false">Confirmar</x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</x-ui.dialog>
```

Cerrar desde dentro: `@click="open = false"`

---

### Sheet (Alpine + x-teleport)

Props de `content`: `side` (right/left/top/bottom)

```blade
<x-ui.sheet>
    <x-ui.sheet.trigger>
        <x-ui.button variant="outline">Abrir panel</x-ui.button>
    </x-ui.sheet.trigger>
    <x-ui.sheet.content side="right">
        Contenido del panel lateral.
    </x-ui.sheet.content>
</x-ui.sheet>
```

---

### Breadcrumb

```blade
<x-ui.breadcrumb>
    <x-ui.breadcrumb.item>
        <x-ui.breadcrumb.link href="/">Inicio</x-ui.breadcrumb.link>
    </x-ui.breadcrumb.item>
    <x-ui.breadcrumb.separator />
    <x-ui.breadcrumb.item>
        <x-ui.breadcrumb.link href="/seccion">Sección</x-ui.breadcrumb.link>
    </x-ui.breadcrumb.item>
    <x-ui.breadcrumb.separator />
    <x-ui.breadcrumb.item>
        <x-ui.breadcrumb.page>Página actual</x-ui.breadcrumb.page>
    </x-ui.breadcrumb.item>
</x-ui.breadcrumb>
```

---

### Pagination

```blade
<x-ui.pagination>
    <x-ui.pagination.content>
        <x-ui.pagination.item>
            <x-ui.pagination.link href="?page=1" :disabled="true">« Anterior</x-ui.pagination.link>
        </x-ui.pagination.item>
        <x-ui.pagination.item>
            <x-ui.pagination.link href="?page=1" :active="true">1</x-ui.pagination.link>
        </x-ui.pagination.item>
        <x-ui.pagination.item>
            <x-ui.pagination.link href="?page=2">2</x-ui.pagination.link>
        </x-ui.pagination.item>
        <x-ui.pagination.item>
            <x-ui.pagination.link href="?page=2">Siguiente »</x-ui.pagination.link>
        </x-ui.pagination.item>
    </x-ui.pagination.content>
</x-ui.pagination>
```

Integración con paginación de Laravel:
```blade
@if ($items->hasPages())
    <x-ui.pagination>
        <x-ui.pagination.content>
            @if ($items->onFirstPage())
                <x-ui.pagination.item>
                    <x-ui.pagination.link :disabled="true">« Anterior</x-ui.pagination.link>
                </x-ui.pagination.item>
            @else
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="{{ $items->previousPageUrl() }}">« Anterior</x-ui.pagination.link>
                </x-ui.pagination.item>
            @endif
            @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="{{ $url }}" :active="$page === $items->currentPage()">
                        {{ $page }}
                    </x-ui.pagination.link>
                </x-ui.pagination.item>
            @endforeach
            @if ($items->hasMorePages())
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="{{ $items->nextPageUrl() }}">Siguiente »</x-ui.pagination.link>
                </x-ui.pagination.item>
            @else
                <x-ui.pagination.item>
                    <x-ui.pagination.link :disabled="true">Siguiente »</x-ui.pagination.link>
                </x-ui.pagination.item>
            @endif
        </x-ui.pagination.content>
    </x-ui.pagination>
@endif
```

---

### Table

Sub-componentes: `header`, `body`, `footer`, `row`, `head`, `cell`, `caption`

#### Reglas de mobile (siempre aplicar)

En mobile cada `<x-ui.table.row>` se convierte en una tarjeta (`flex flex-col`). El `<thead>` se oculta (`hidden sm:table-header-group`). Para que las celdas muestren su etiqueta de columna en mobile se usa el pseudo-elemento `before:content-[attr(data-label)]` definido en `cell.blade.php`.

**Reglas que nunca omitir:**

1. **Toda `<x-ui.table.cell>` de datos lleva `data-label` con el texto exacto del `<x-ui.table.head>` correspondiente.**  
   Sin este atributo el usuario no sabe a qué columna pertenece el valor en mobile.

2. **La celda de acciones (botones, dropdown de opciones) NO lleva `data-label`.**  
   Se agrega `order-first sm:order-0 justify-end border-b border-border sm:border-b-0` para que aparezca **primero** en la tarjeta mobile con un separador inferior, sin etiqueta. En desktop `order` no aplica a celdas de tabla y el borde se oculta.

```blade
<x-ui.table>
    <x-ui.table.header>
        <x-ui.table.row>
            <x-ui.table.head>Nombre</x-ui.table.head>
            <x-ui.table.head>Email</x-ui.table.head>
            <x-ui.table.head>Estado</x-ui.table.head>
            <x-ui.table.head class="text-right">Acciones</x-ui.table.head>
        </x-ui.table.row>
    </x-ui.table.header>
    <x-ui.table.body>
        @foreach ($usuarios as $usuario)
        <x-ui.table.row>
            <x-ui.table.cell class="font-medium" data-label="Nombre">{{ $usuario->name }}</x-ui.table.cell>
            <x-ui.table.cell data-label="Email">{{ $usuario->email }}</x-ui.table.cell>
            <x-ui.table.cell data-label="Estado">
                <x-ui.badge variant="success">Activo</x-ui.badge>
            </x-ui.table.cell>
            {{-- Celda de acciones: sin data-label, primera en mobile, separador inferior --}}
            <x-ui.table.cell class="order-first sm:order-0 justify-end border-b border-border sm:border-b-0">
                <x-ui.dropdown-menu align="end">
                    ...
                </x-ui.dropdown-menu>
            </x-ui.table.cell>
        </x-ui.table.row>
        @endforeach
    </x-ui.table.body>
</x-ui.table>
```

---

### Toast

Agregar `<x-ui.sonner />` una sola vez en el layout (ya incluido en `layouts/app.blade.php`).
Disparar desde cualquier vista con Alpine `$dispatch`:

```blade
{{-- Desde un botón --}}
<x-ui.button @click="$dispatch('toast', { message: 'Guardado con éxito', variant: 'success' })">
    Guardar
</x-ui.button>

{{-- Desde JS puro --}}
<script>
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message: 'Operación completada', variant: 'default', duration: 5000 }
    }));
</script>
```

Variantes: `default` | `success` | `destructive`
Props: `message` (string), `variant` (string), `duration` (ms, default 4000)

---

## Agregar un componente de dominio

Los componentes de dominio encapsulan UI con lógica del negocio y se construyen sobre `<x-ui.*>`.

1. Crear `resources/views/components/domain/[módulo]/nombre.blade.php`
2. Definir props con `@props([])`
3. Usar solo `<x-ui.*>` internamente — nunca colores hardcoded
4. Para sub-componentes: `domain/[módulo]/nombre/sub.blade.php` → `<x-domain.[módulo].nombre.sub>`

Ejemplo — `domain/pesajes/fila.blade.php` → `<x-domain.pesajes.fila>`:

```blade
@props(['pesaje', 'destacado' => false])

<x-ui.table.row :class="$destacado ? 'bg-warning/10' : ''">
    <x-ui.table.cell>{{ $pesaje->ticket }}</x-ui.table.cell>
    <x-ui.table.cell>{{ $pesaje->material }}</x-ui.table.cell>
    <x-ui.table.cell class="text-right font-mono">
        {{ number_format($pesaje->peso_neto, 2) }} kg
    </x-ui.table.cell>
</x-ui.table.row>
```

---

## Dark Mode

Implementado con clase `.dark` en `<html>` + CSS variables override en `@layer base`.

### Reglas clave

- `@variant dark (&:where(.dark, .dark *))` en `app.css` activa el modo clase (en lugar de media query).
- Los tokens se sobreescriben en `.dark { ... }` dentro de `@layer base`.
- Se agrega `x-cloak` a los elementos que no deben mostrarse antes de que Alpine inicialice.

### Anti-flash (en el `<head>` del layout)

```html
<script>
    (function () {
        const theme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (!theme && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
```

### Estado Alpine (en `<body x-data="...">`)

```js
{
    dark: localStorage.getItem('theme') === 'dark' ||
          (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),

    toggleDark() {
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', this.dark);
    }
}
```

### Botón toggle en el topbar

```blade
<x-ui.button variant="ghost" size="icon" @click="toggleDark()">
    <svg x-show="dark" x-cloak ...>  {{-- ícono sol --}}</svg>
    <svg x-show="!dark" ...>          {{-- ícono luna --}}</svg>
</x-ui.button>
```

---

## Sidebar Collapse

El sidebar del layout `layouts/dashboard.blade.php` soporta colapso a íconos (ancho `w-16`) y expansión (`w-64`). El estado persiste en `localStorage`.

### Estado Alpine adicional (mismo `x-data` del body)

```js
{
    collapsed: localStorage.getItem('sidebar') === 'collapsed',

    toggleCollapse() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar', this.collapsed ? 'collapsed' : 'expanded');
    }
}
```

### Sidebar dinámico

```html
<aside :class="[collapsed ? 'w-16' : 'w-64', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
       class="fixed inset-y-0 left-0 z-50 flex flex-col bg-card border-r transition-all duration-300">
```

### Main content dinámico

```html
<div :class="collapsed ? 'lg:pl-16' : 'lg:pl-64'" class="transition-all duration-300">
```

### Nav items con tooltip al colapsar

```html
<div class="relative group/nav">
    <a :class="collapsed ? 'justify-center px-0 w-10 mx-auto' : 'px-3'" class="flex items-center gap-3 ...">
        <svg .../>  {{-- ícono siempre visible --}}
        <span x-show="!collapsed" x-cloak>Label</span>
    </a>
    {{-- Tooltip solo cuando está colapsado --}}
    <div x-show="collapsed" x-cloak
         class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-popover border px-2.5 py-1.5 text-xs shadow-md opacity-0 transition-opacity group-hover/nav:opacity-100">
        Label
    </div>
</div>
```

---

## Comandos frecuentes

```bash
npm run dev      # servidor Vite con hot reload
npm run build    # build de producción
php artisan serve # servidor PHP (si no usás Herd)
```

---

## Arquitectura Laravel

### Patrón: Repository + Service + Resource Controller

```
app/
├── Http/
│   ├── Controllers/      # Delgados: solo llaman Services
│   └── Requests/         # Validación siempre aquí, nunca validate() en controller
├── Services/             # Lógica de negocio
├── Repositories/         # Acceso a datos (Eloquent)
└── Models/               # Solo relaciones, scopes, accessors/mutators
```

### Reglas que siempre aplicar

**Controllers — siempre Resource, siempre delgados**
```bash
php artisan make:controller UserController --resource
```
```php
// CORRECTO
public function store(StoreUserRequest $request): RedirectResponse
{
    $this->userService->create($request->validated());
    return redirect()->route('users.index');
}

// INCORRECTO — lógica en el controller
public function store(Request $request): RedirectResponse
{
    $user = User::create([...]);
    Mail::to($user)->send(new WelcomeMail($user));
    // ...
}
```

**Controllers por dominio — nunca single-action (`__invoke`)**

**Prohibido crear controllers de acción única (`__invoke`).** Toda la lógica de un dominio vive en un único controller por dominio (`PesajeController`, `VehiculoController`, etc.) con métodos con nombre del vocabulario resource (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) o un verbo claro para acciones fuera del CRUD (`toggle`, `export`, `data`). Las rutas siempre referencian el método de forma declarativa.

```php
// CORRECTO — método con nombre, ruta declarativa
public function index(): View { ... }
```
```php
// routes/*.php
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
```
```php
// INCORRECTO — acción única invocable
public function __invoke(): View { ... }
Route::get('/dashboard', DashboardController::class)->name('dashboard');
```

**Por qué un controller por dominio (y no muchos single-action):**

- Concentrar el dominio en un solo controller hace que toda la superficie del dominio sea visible de un vistazo. Antes de agregar un método, se ve qué ya existe y se evita **duplicar lógica o crear un método que ya estaba** — un fallo típico cuando se trabaja con asistentes de IA y la lógica está dispersa en muchos archivos chicos.
- El nombre del método documenta la acción y encaja con el resto de rutas resource.
- Evita el falso "0 references" que muestran los analizadores estáticos (el IDE no detecta la invocación mágica de `__invoke`).

> **Decisión de etapa:** por ahora todo va en controllers por dominio. Cuando el proyecto crezca, se desacoplarán los controllers grandes (ej: extraer acciones a controllers más específicos). No anticipar ese desacople todavía.

**Antes de agregar un método a un controller, service o repository — analizar primero el dominio**

Es **obligatorio**, antes de escribir un método nuevo en cualquier capa, revisar lo que ya existe en ese dominio para reutilizar en lugar de duplicar:

1. **Identificar el dominio** que se está trabajando (pesaje, vehículo, tipo de servicio, etc.).
2. **Leer el controller del dominio** completo y verificar si ya hay un método que cubre (total o parcialmente) lo que se necesita.
3. **Leer el service del dominio** (`app/Services/[Dominio]Service.php`) — la lógica de negocio puede ya estar implementada ahí.
4. **Leer el repository del dominio** (`app/Repositories/[Dominio]Repository.php`) — la consulta o acceso a datos puede ya existir.
5. Reutilizar o extender lo existente. Solo crear un método nuevo si no hay nada reaprovechable; si hay algo parecido pero no idéntico, preferir generalizar el método existente antes que duplicar.

Esto aplica a las tres capas: **Controller → Service → Repository**. Nunca crear un método sin antes confirmar que no existe ya uno equivalente en su capa.

**Form Requests — siempre, sin excepción**
```bash
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
```
```php
// Nunca esto en un controller:
$request->validate([...]);

// Siempre esto:
public function store(StoreUserRequest $request): RedirectResponse
```

**Services — toda la lógica de negocio**
```bash
php artisan make:class Services/UserService
```
```php
class UserService
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function create(array $data): User
    {
        $user = $this->userRepository->create($data);
        // eventos, notificaciones, etc.
        return $user;
    }
}
```

**Repositories — todo acceso a datos**
```bash
php artisan make:class Repositories/UserRepository
```
```php
class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
```

### Naming conventions

| Clase | Ejemplo | Comando |
|-------|---------|---------|
| Controller | `UserController` | `make:controller UserController --resource` |
| Form Request | `StoreUserRequest`, `UpdateUserRequest` | `make:request StoreUserRequest` |
| Service | `UserService` | `make:class Services/UserService` |
| Repository | `UserRepository` | `make:class Repositories/UserRepository` |
| Policy | `UserPolicy` | `make:policy UserPolicy --model=User` |
| Model | `User` (singular) | `make:model User -m` |
| Migration | `create_users_table` | auto con `-m` |
| Test | `UserTest`, `UserServiceTest` | `make:test UserTest`, `make:test UserServiceTest --unit` |

> **Tests — nombres siempre en inglés.** Las clases y métodos de test se escriben en inglés, sin prefijo `test_` (se usa el atributo `#[Test]`). Los nombres de dominio que son clases reales del código (`Pesaje`, `Vehiculo`, `Organizacion`, `Zona`, `TipoServicio`, etc.) se mantienen tal cual — son nombres propios, no se traducen. Ej: `public function admin_can_create_pesaje(): void`. Ver [`docs/testing-strategy.md`](docs/testing-strategy.md) para la estrategia completa.

> **Tests — estándar de calidad A (obligatorio).** Todo test nuevo debe cumplir el estándar definido en [`docs/testing-strategy.md §2.5`](docs/testing-strategy.md). En resumen: (1) probar el **borde exacto** de cada condición (`<`, `>`, `>=`, `<=`); (2) **assert completo** de todos los campos relevantes del resultado (valor_anterior, motivo, usuario_id en logs; metadatos en cancelar/egreso); (3) excepción con **clave de error validada** (no solo `expectException`, también `assertArrayHasKey` en `$e->errors()` o `assertSessionHasErrors('campo')`); (4) **datos controlados** — factories con valores explícitos para todo campo que el test afirma; (5) tests Feature verifican que los **datos persisten correctamente** además del redirect. No se mergea un test que solo verifica redirect + count.

### Inyección de dependencias en Controllers

```php
class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
    ) {}
}
```

### Autorización — siempre Policies

```bash
php artisan make:policy UserPolicy --model=User
```
```php
// En el controller
public function update(UpdateUserRequest $request, User $user): RedirectResponse
{
    $this->authorize('update', $user);
    // ...
}
```

### Lo que NUNCA hacer

- Lógica de negocio en Controllers (solo coordinación)
- `validate()` directo en Controllers (usar Form Requests)
- Queries Eloquent en Controllers (usar Repositories)
- Controllers de acción única (`__invoke`) — siempre controller por dominio con métodos con nombre
- Crear un método nuevo (en Controller, Service o Repository) sin antes revisar si ya existe uno reutilizable en ese dominio
- Lógica de negocio en Models (solo scopes, relaciones, accessors)
- Lógica en Blade (solo presentación)
- Ejecutar `migrate:fresh`, `migrate:reset` o `db:wipe` — la BD es compartida entre proyectos y estos comandos eliminan tablas de todos ellos (ver sección ⚠️ Base de datos arriba)
