# Sistema de Gestión de Balanza — Infinito Reciclaje

## Documentación del proyecto

| Documento | Descripción |
|-----------|-------------|
| [`docs/roadmap.md`](docs/roadmap.md) | Plan de desarrollo completo: sprints, schema de DB, arquitectura de pantallas, criterios de go-live |
| [`docs/ux-writing.md`](docs/ux-writing.md) | Voz y tono del sistema, reglas de escritura diferenciadas por rol (operador vs admin), formatos, ejemplos |
| [`docs/Brief_Producto_Etapa1.md`](docs/Brief_Producto_Etapa1.md) | Requerimientos funcionales y no funcionales de Etapa 1 |
| [`docs/design-system.md`](docs/design-system.md) | Documentación del design system Blade (`x-ui.*`) |
| [`docs/knowledge/README.md`](docs/knowledge/README.md) | Base de conocimiento de usuario: onboarding, configuración inicial y referencia de cada módulo (preparada para RAG) |

> **Al escribir cualquier texto en vistas Blade, consultar `docs/ux-writing.md`.**
> Las reglas de escritura para el operador y el admin son distintas — aplicarlas según el perfil de la pantalla.

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

    <x-toast />
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
    <x-label>Email</x-label>
    <x-input type="email" />
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
| `sm` | `h-8` (32px) | `<x-button size="sm">` |
| `default` | `h-9` (36px) | `<x-button>`, `<x-input>`, `<x-select>` |
| `lg` | `h-10` (40px) | `<x-button size="lg">` |
| `icon` | `h-9 w-9` (36×36px) | `<x-button size="icon">` |

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
    <x-card>...</x-card>
</div>
```

---

## Catálogo de componentes

Todos los componentes viven en `resources/views/components/`.
Los sub-componentes usan carpetas con el mismo nombre: `accordion/item.blade.php` → `<x-accordion.item>`.

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
<x-button>Default</x-button>
<x-button variant="destructive">Eliminar</x-button>
<x-button variant="outline" size="sm">Cancelar</x-button>
<x-button variant="ghost">Volver</x-button>
<x-button variant="link">Ver más</x-button>
<x-button variant="outline" size="icon">
    <svg .../>
</x-button>
<x-button disabled>No disponible</x-button>
```

---

### Alert

Props: `variant` (default/destructive)
Sub-componentes: `<x-alert.title>`, `<x-alert.description>`
El SVG se posiciona automáticamente con CSS arbitrario (`[&>svg]:absolute [&>svg]:left-4`).

```blade
<x-alert>
    <x-alert.title>Título</x-alert.title>
    <x-alert.description>Descripción.</x-alert.description>
</x-alert>

<x-alert variant="destructive">
    <svg class="size-4" .../>
    <x-alert.title>Error</x-alert.title>
    <x-alert.description>Algo salió mal.</x-alert.description>
</x-alert>
```

---

### Badge

Props: `variant` (default/secondary/destructive/outline/warning/success)

```blade
<x-badge>Default</x-badge>
<x-badge variant="destructive">Error</x-badge>
<x-badge variant="success">Activo</x-badge>
<x-badge variant="warning">Pendiente</x-badge>
```

---

### Card

Sub-componentes: `header`, `title`, `description`, `content`, `footer`

```blade
<x-card>
    <x-card.header>
        <x-card.title>Título</x-card.title>
        <x-card.description>Descripción.</x-card.description>
    </x-card.header>
    <x-card.content>Contenido</x-card.content>
    <x-card.footer>
        <x-button size="sm">Acción</x-button>
    </x-card.footer>
</x-card>
```

---

### Inputs de formulario

```blade
{{-- Label + Input --}}
<div class="space-y-2">
    <x-label for="email">Email</x-label>
    <x-input id="email" type="email" placeholder="nombre@ejemplo.com" />
    <x-input :error="true" value="valor incorrecto" />  {{-- estado de error --}}
</div>

{{-- Textarea --}}
<x-textarea placeholder="Escribí tu mensaje..." rows="4" />

{{-- Select --}}
<x-select>
    <option value="">Seleccionar...</option>
    <option value="a">Opción A</option>
</x-select>

{{-- Checkbox --}}
<label class="flex items-center gap-2 text-sm">
    <x-checkbox name="terms" /> Acepto los términos
</label>

{{-- Radio --}}
<label class="flex items-center gap-2 text-sm">
    <x-radio name="plan" value="pro" :checked="true" /> Pro
</label>

{{-- Switch (Alpine) --}}
<label class="flex items-center gap-2 text-sm">
    <x-switch :checked="true" /> Notificaciones
</label>
```

---

### Separator

Props: `orientation` (horizontal/vertical)

```blade
<x-separator />
<x-separator orientation="vertical" class="h-5" />
```

---

### Avatar

Props: `src`, `alt`, `fallback`

```blade
<x-avatar src="https://..." alt="Nombre" />
<x-avatar fallback="JG" />
<x-avatar fallback="AB" class="h-12 w-12" />
```

---

### Skeleton

```blade
<x-skeleton class="h-4 w-full" />
<x-skeleton class="h-10 w-10 rounded-full" />
```

---

### Progress

Props: `value` (0-100), `max` (default 100)

```blade
<x-progress :value="75" class="w-full" />
```

---

### Tabs (Alpine)

Props en `<x-tabs>`: `default` (valor inicial)

```blade
<x-tabs default="tab1">
    <x-tabs.list>
        <x-tabs.trigger value="tab1">Tab 1</x-tabs.trigger>
        <x-tabs.trigger value="tab2">Tab 2</x-tabs.trigger>
    </x-tabs.list>
    <x-tabs.content value="tab1">Contenido del tab 1</x-tabs.content>
    <x-tabs.content value="tab2">Contenido del tab 2</x-tabs.content>
</x-tabs>
```

---

### Accordion (Alpine + x-collapse)

```blade
<x-accordion>
    <x-accordion.item value="item-1">
        <x-accordion.trigger>¿Pregunta?</x-accordion.trigger>
        <x-accordion.content>Respuesta.</x-accordion.content>
    </x-accordion.item>
    <x-accordion.item value="item-2">
        <x-accordion.trigger>Otra pregunta</x-accordion.trigger>
        <x-accordion.content>Otra respuesta.</x-accordion.content>
    </x-accordion.item>
</x-accordion>
```

---

### Collapsible (Alpine + x-collapse)

Props: `open` (boolean, default false)

```blade
<x-collapsible>
    <x-collapsible.trigger>
        <x-button variant="ghost">Mostrar más</x-button>
    </x-collapsible.trigger>
    <x-collapsible.content>
        Contenido colapsable.
    </x-collapsible.content>
</x-collapsible>
```

---

### Toggle

Props: `variant` (default/outline), `size` (default/sm/lg), `pressed` (boolean)

```blade
<x-toggle>Negrita</x-toggle>
<x-toggle variant="outline" :pressed="true">Activo</x-toggle>
```

---

### Dropdown Menu (Alpine)

```blade
<x-dropdown-menu>
    <x-dropdown-menu.trigger>
        <x-button variant="outline">Opciones</x-button>
    </x-dropdown-menu.trigger>
    <x-dropdown-menu.content>
        <x-dropdown-menu.label>Mi cuenta</x-dropdown-menu.label>
        <x-dropdown-menu.separator />
        <x-dropdown-menu.item href="/perfil">Perfil</x-dropdown-menu.item>
        <x-dropdown-menu.item>Configuración</x-dropdown-menu.item>
        <x-dropdown-menu.separator />
        <x-dropdown-menu.item :destructive="true">Cerrar sesión</x-dropdown-menu.item>
    </x-dropdown-menu.content>
</x-dropdown-menu>
```

Props de `content`: `align` (start/end/center), `side` (bottom/top — default bottom). Usar `side="top"` cuando el trigger está al fondo de un contenedor fixed (ej: footer del sidebar).

> **Gotcha — triggers:** Todos los triggers (`dropdown-menu`, `popover`, `sheet`, `dialog`) son `<div>` con `@click.stop`, NO `<button>`. Un `<button>` wrapper genera botones anidados (HTML inválido) cuando el slot contiene `<x-button>`. El `.stop` evita que `@click.outside` cierre el menú al instante.

> **Gotcha — sheet panel:** Las clases de transformación (`translate-x-full`, etc.) NO deben estar en `$sides` como clases estáticas. Alpine las remueve del enter-end al terminar la transición y el panel vuelve a off-screen. Solo deben estar en `x-transition:enter-start` y `x-transition:leave-end`.

---

### Tooltip (Alpine)

Props: `text`, `side` (top/bottom/left/right)

```blade
<x-tooltip text="Texto del tooltip">
    <x-button variant="outline">Hover aquí</x-button>
</x-tooltip>

<x-tooltip text="Abajo" side="bottom">
    <x-button variant="ghost" size="icon"><svg .../></x-button>
</x-tooltip>
```

---

### Popover (Alpine)

```blade
<x-popover>
    <x-popover.trigger>
        <x-button variant="outline">Abrir</x-button>
    </x-popover.trigger>
    <x-popover.content>
        Contenido del popover.
    </x-popover.content>
</x-popover>
```

Props de `content`: `align` (start/end/center)

---

### Dialog (Alpine + x-teleport)

```blade
<x-dialog>
    <x-dialog.trigger>
        <x-button>Abrir Dialog</x-button>
    </x-dialog.trigger>
    <x-dialog.content>
        <x-dialog.header>
            <x-dialog.title>Título</x-dialog.title>
            <x-dialog.description>Descripción opcional.</x-dialog.description>
        </x-dialog.header>
        {{-- cuerpo --}}
        <x-dialog.footer>
            <x-button variant="outline" @click="open = false">Cancelar</x-button>
            <x-button @click="open = false">Confirmar</x-button>
        </x-dialog.footer>
    </x-dialog.content>
</x-dialog>
```

Cerrar desde dentro: `@click="open = false"`

---

### Sheet (Alpine + x-teleport)

Props de `content`: `side` (right/left/top/bottom)

```blade
<x-sheet>
    <x-sheet.trigger>
        <x-button variant="outline">Abrir panel</x-button>
    </x-sheet.trigger>
    <x-sheet.content side="right">
        Contenido del panel lateral.
    </x-sheet.content>
</x-sheet>
```

---

### Breadcrumb

```blade
<x-breadcrumb>
    <x-breadcrumb.item>
        <x-breadcrumb.link href="/">Inicio</x-breadcrumb.link>
    </x-breadcrumb.item>
    <x-breadcrumb.separator />
    <x-breadcrumb.item>
        <x-breadcrumb.link href="/seccion">Sección</x-breadcrumb.link>
    </x-breadcrumb.item>
    <x-breadcrumb.separator />
    <x-breadcrumb.item>
        <x-breadcrumb.page>Página actual</x-breadcrumb.page>
    </x-breadcrumb.item>
</x-breadcrumb>
```

---

### Pagination

```blade
<x-pagination>
    <x-pagination.content>
        <x-pagination.item>
            <x-pagination.link href="?page=1" :disabled="true">« Anterior</x-pagination.link>
        </x-pagination.item>
        <x-pagination.item>
            <x-pagination.link href="?page=1" :active="true">1</x-pagination.link>
        </x-pagination.item>
        <x-pagination.item>
            <x-pagination.link href="?page=2">2</x-pagination.link>
        </x-pagination.item>
        <x-pagination.item>
            <x-pagination.link href="?page=2">Siguiente »</x-pagination.link>
        </x-pagination.item>
    </x-pagination.content>
</x-pagination>
```

Integración con paginación de Laravel:
```blade
@if ($items->hasPages())
    <x-pagination>
        <x-pagination.content>
            @if ($items->onFirstPage())
                <x-pagination.item>
                    <x-pagination.link :disabled="true">« Anterior</x-pagination.link>
                </x-pagination.item>
            @else
                <x-pagination.item>
                    <x-pagination.link href="{{ $items->previousPageUrl() }}">« Anterior</x-pagination.link>
                </x-pagination.item>
            @endif
            @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                <x-pagination.item>
                    <x-pagination.link href="{{ $url }}" :active="$page === $items->currentPage()">
                        {{ $page }}
                    </x-pagination.link>
                </x-pagination.item>
            @endforeach
            @if ($items->hasMorePages())
                <x-pagination.item>
                    <x-pagination.link href="{{ $items->nextPageUrl() }}">Siguiente »</x-pagination.link>
                </x-pagination.item>
            @else
                <x-pagination.item>
                    <x-pagination.link :disabled="true">Siguiente »</x-pagination.link>
                </x-pagination.item>
            @endif
        </x-pagination.content>
    </x-pagination>
@endif
```

---

### Table

Sub-componentes: `header`, `body`, `footer`, `row`, `head`, `cell`, `caption`

```blade
<x-table>
    <x-table.header>
        <x-table.row>
            <x-table.head>Nombre</x-table.head>
            <x-table.head>Email</x-table.head>
            <x-table.head class="text-right">Estado</x-table.head>
        </x-table.row>
    </x-table.header>
    <x-table.body>
        @foreach ($usuarios as $usuario)
        <x-table.row>
            <x-table.cell class="font-medium">{{ $usuario->name }}</x-table.cell>
            <x-table.cell>{{ $usuario->email }}</x-table.cell>
            <x-table.cell class="text-right">
                <x-badge variant="success">Activo</x-badge>
            </x-table.cell>
        </x-table.row>
        @endforeach
    </x-table.body>
</x-table>
```

---

### Toast

Agregar `<x-toast />` una sola vez en el layout (ya incluido en `layouts/app.blade.php`).
Disparar desde cualquier vista con Alpine `$dispatch`:

```blade
{{-- Desde un botón --}}
<x-button @click="$dispatch('toast', { message: 'Guardado con éxito', variant: 'success' })">
    Guardar
</x-button>

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

## Agregar un nuevo componente

1. Crear `resources/views/components/nombre.blade.php`
2. Definir props con `@props([])`
3. Usar `$attributes->merge(['class' => '...'])` para clases externas
4. Usar solo tokens semánticos (`bg-primary`, `text-muted-foreground`, etc.)
5. Para sub-componentes: crear carpeta `components/nombre/sub.blade.php` → `<x-nombre.sub>`

Ejemplo mínimo:

```blade
@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-background text-foreground border',
        'filled'  => 'bg-primary text-primary-foreground',
    ];
@endphp

<div {{ $attributes->merge(['class' => "rounded-md px-4 py-2 {$variants[$variant]}"]) }}>
    {{ $slot }}
</div>
```

---

## Showcase

Ruta `/showcase` — muestra todos los componentes disponibles.
Archivo: `resources/views/showcase.blade.php`

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
<x-button variant="ghost" size="icon" @click="toggleDark()">
    <svg x-show="dark" x-cloak ...>  {{-- ícono sol --}}</svg>
    <svg x-show="!dark" ...>          {{-- ícono luna --}}</svg>
</x-button>
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
- Lógica de negocio en Models (solo scopes, relaciones, accessors)
- Lógica en Blade (solo presentación)
