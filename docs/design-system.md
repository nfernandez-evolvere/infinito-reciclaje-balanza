# Design System — Laravel Blade + shadcn

Documentación de uso del sistema de diseño basado en shadcn/ui portado a Laravel Blade con Tailwind v4 y Alpine.js.

---

## Índice

1. [Arquitectura — Atomic Design](#1-arquitectura--atomic-design)
2. [Copiar a un nuevo proyecto](#2-copiar-a-un-nuevo-proyecto)
3. [Adaptar al branding del producto](#3-adaptar-al-branding-del-producto)
4. [Principios de generación de pantallas](#4-principios-de-generación-de-pantallas)
5. [Principios de formularios](#5-principios-de-formularios)
6. [Sistema de tokens](#6-sistema-de-tokens)
7. [Catálogo de componentes](#7-catálogo-de-componentes)
8. [Otras consideraciones](#8-otras-consideraciones)

---

## 1. Arquitectura — Atomic Design

El sistema sigue Atomic Design adaptado a Blade. Los niveles son:

```
Átomos       →  tokens CSS, typography, icon, badge, label, separator, skeleton
Moléculas    →  button, input, textarea, checkbox, switch, select, form-field
Organismos   →  card, dialog, drawer, sheet, data-table, sidebar, combobox
Plantillas   →  layouts/app.blade.php, layouts/showcase.blade.php
Páginas      →  vistas específicas del proyecto
```

### Cómo se relacionan los niveles

```
design-tokens.css          ← fuente de verdad (colores, escala, sombras, z-index)
       ↓
components/ui/             ← átomos, moléculas y organismos como Blade components
       ↓
resources/views/layouts/   ← plantillas que consumen los componentes
       ↓
resources/views/           ← páginas que usan las plantillas
```

### Convención de naming de componentes

Todos los componentes tienen el prefijo `x-ui.`:

```blade
{{-- Átomo --}}
<x-ui.badge variant="secondary">Nuevo</x-ui.badge>

{{-- Molécula --}}
<x-ui.button size="md" variant="outline">Guardar</x-ui.button>

{{-- Organismo compuesto (patrón sub-componente) --}}
<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title>Título</x-ui.card.title>
    </x-ui.card.header>
    <x-ui.card.content>
        Contenido
    </x-ui.card.content>
</x-ui.card>
```

### Patrón sub-componente

Los organismos complejos se dividen en sub-componentes ubicados en una carpeta con el mismo nombre:

```
ui/
├── card.blade.php           ← wrapper raíz
└── card/
    ├── header.blade.php
    ├── title.blade.php
    ├── description.blade.php
    ├── content.blade.php
    └── footer.blade.php
```

Esto aplica a: `card`, `dialog`, `drawer`, `sheet`, `alert-dialog`, `dropdown-menu`, `select`, `tabs`, `table`, `sidebar`, `combobox`, `breadcrumb`, `accordion`, `command`, `menubar`, `navigation-menu`, `pagination`.

---

## 2. Copiar a un nuevo proyecto

### Dependencias requeridas

**PHP / Composer:**

```json
{
    "require": {
        "php": "^8.3",
        "gehrisandro/tailwind-merge-laravel": "^1.4",
        "laravel/framework": "^13.7",
        "mallardduck/blade-lucide-icons": "^1.26"
    }
}
```

**Node / npm:**

```json
{
    "devDependencies": {
        "@tailwindcss/vite": "^4.3.0",
        "tailwindcss": "^4.3.0",
        "vite": "^8.0.0",
        "laravel-vite-plugin": "^3.1"
    },
    "dependencies": {
        "alpinejs": "^3.15.12"
    }
}
```

### Archivos a copiar

```bash
# Tokens y estilos base
laravel-app/resources/css/design-tokens.css
laravel-app/resources/css/app.css

# Todos los componentes Blade
laravel-app/resources/views/components/ui/

# Layout base
laravel-app/resources/views/components/layouts/app.blade.php

# JS (Alpine + inicialización)
laravel-app/resources/js/app.js

# Config de íconos
laravel-app/config/icons.php

# Instrucciones para Claude (reglas de diseño cargadas automáticamente)
CLAUDE.md

# Documentación del design system
docs/design-system.md
```

### Paso a paso

```bash
# 1. Crear proyecto Laravel
composer create-project laravel/laravel mi-proyecto

# 2. Instalar dependencias PHP
composer require gehrisandro/tailwind-merge-laravel mallardduck/blade-lucide-icons

# 3. Instalar dependencias Node
npm install tailwindcss @tailwindcss/vite alpinejs

# 4. Copiar recursos del design system
cp -r laravel-shadcn/laravel-app/resources/css/design-tokens.css  mi-proyecto/resources/css/
cp -r laravel-shadcn/laravel-app/resources/views/components/ui/   mi-proyecto/resources/views/components/ui/
cp    laravel-shadcn/laravel-app/resources/js/app.js               mi-proyecto/resources/js/

# 5. Referenciar design-tokens.css desde app.css
# En app.css, agregar:  @import "./design-tokens.css";

# 6. Publicar config de íconos
cp laravel-shadcn/laravel-app/config/icons.php mi-proyecto/config/
```

### Estructura mínima de `app.css`

```css
@import "tailwindcss";
@import "./design-tokens.css";

/* Habilitar modo dark con clase en <html> */
@variant dark (&:where(.dark, .dark *));
```

### Verificación rápida

Crear una vista con esto para confirmar que todo funciona:

```blade
<x-layouts.app>
    <div class="p-8 space-y-4">
        <x-ui.typography as="h1">Design System OK</x-ui.typography>
        <x-ui.button>Botón primario</x-ui.button>
        <x-ui.badge state="success">Funcionando</x-ui.badge>
    </div>
</x-layouts.app>
```

---

## 3. Adaptar al branding del producto

El sistema usa tokens semánticos en todos los componentes. Cambiar los tokens en `design-tokens.css` propaga el cambio a toda la interfaz. **Nunca tocar componentes individuales para un rebrand.**

Las cinco palancas, en orden de impacto visual:

---

### Palanca 1 — Color de marca

**Paso 1:** agregar la paleta primitiva de la marca en la sección "1. PALETA PRIMITIVA" de `design-tokens.css`. Usar OKLCH con la misma escala de 11 pasos que las paletas existentes. Convertir desde hex en [oklch.com](https://oklch.com):

```css
/* design-tokens.css — agregar en la sección de paleta primitiva */
--brand-50:  oklch(0.970 0.030 260);
--brand-100: oklch(0.932 0.060 260);
--brand-200: oklch(0.880 0.100 260);
--brand-300: oklch(0.810 0.150 260);
--brand-400: oklch(0.720 0.190 260);
--brand-500: oklch(0.620 0.220 260);
--brand-600: oklch(0.540 0.230 260);   /* ← color de marca principal */
--brand-700: oklch(0.460 0.210 260);
--brand-800: oklch(0.390 0.175 260);
--brand-900: oklch(0.330 0.135 260);
--brand-950: oklch(0.220 0.085 260);
```

**Paso 2:** redirigir los tokens semánticos en la sección "2. TEMA CLARO":

```css
/* design-tokens.css — sección 2, tema claro */
--primary:            var(--brand-600);
--primary-foreground: var(--neutral-50);
--ring:               var(--brand-500);

/* Si el sidebar usa el color de marca */
--sidebar-primary:               var(--brand-600);
--sidebar-primary-foreground:    var(--neutral-50);
```

**Paso 3:** ajustar el tema oscuro en la sección "3. TEMA OSCURO" (el tono claro de la escala funciona mejor sobre fondos oscuros):

```css
/* design-tokens.css — sección 3, tema oscuro */
--primary:            var(--brand-400);
--primary-foreground: var(--neutral-950);
--ring:               var(--brand-400);
```

---

### Palanca 2 — Tipografía

Cambiar `--font-sans` en la sección "4. TIPOGRAFÍA" e importar la fuente en `app.css`:

```css
/* app.css — antes del @import de design-tokens */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
```

```css
/* design-tokens.css — sección 4 */
--font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
```

Fuentes frecuentes por personalidad de producto:

| Personalidad | Fuente |
|---|---|
| Enterprise / Fintech | Inter |
| SaaS / Productividad | Plus Jakarta Sans |
| Creativo / Consumer | DM Sans |
| Técnico / Dev tools | IBM Plex Sans |

---

### Palanca 3 — Border radius

Toda la escala parte de `--radius`. Solo cambiar esa variable:

```css
/* design-tokens.css — sección 2, al final de :root */
--radius: 0.375rem;   /* cambiar este valor, el resto se deduce */
```

| Personalidad | `--radius` | Efecto |
|---|---|---|
| Enterprise / Fintech | `0` – `0.125rem` | Bordes rectos, muy formal |
| B2B / SaaS estándar | `0.375rem` (6px) | Ligeramente redondeado |
| Default del sistema | `0.5rem` (8px) | Equilibrado |
| App de consumo | `0.75rem` (12px) | Amigable, moderno |
| Playful / Consumer | `1rem` (16px) | Muy redondeado |

---

### Palanca 4 — Movimiento

Ajustar la velocidad y el easing en la sección "7. MOVIMIENTO":

```css
/* Producto eficiente / herramienta profesional */
--duration-fast:   75ms;
--duration-normal: 150ms;

/* Producto delightful / consumer */
--duration-fast:   150ms;
--duration-normal: 300ms;
/* Usar ease-spring en botones y dialogs para feedback táctil */
```

---

### Palanca 5 — Densidad por defecto

Setear `data-density` en el `<body>` o en el layout raíz (`app.blade.php`):

```html
<!-- Herramienta de datos / dashboard -->
<body data-density="compact">

<!-- App estándar — no necesita el atributo -->
<body>

<!-- B2C / onboarding / páginas de bienvenida -->
<body data-density="comfortable">
```

---

### Arquetipos de producto frecuentes

| Producto | Primary | Fuente | Radius | Movimiento | Densidad |
|---|---|---|---|---|---|
| Fintech / Banco | Neutral o azul oscuro | Inter | 4–6px | Rápido, sin spring | Compact |
| SaaS B2B | Brand color moderado | Plus Jakarta | 8px | Normal | Default |
| App consumer | Brand color vivo | DM Sans | 12–16px | Lento + spring | Comfortable |
| Dev tool | Neutral o verde | IBM Plex | 4px | Muy rápido | Compact |

---

### Lo que NO cambiar al rebrandear

- La estructura de la paleta primitiva — solo agregar colores, no modificar la escala de Neutral, Red, Green, etc.
- Los tokens de estados (`--destructive`, `--success`, `--warning`, `--info`) — tienen significado universal y no deben virar a la marca.
- Los archivos en `components/ui/` — el sistema funciona porque los componentes solo consumen tokens.

---

## 4. Principios de generación de pantallas

### Checklist antes de entregar una pantalla

- [ ] Un solo `<h1>` en la página
- [ ] Máximo 3 niveles de anidación visual
- [ ] Texto de párrafo con `max-w-prose` (sin esto es ilegible en pantallas anchas)
- [ ] Tokens de color semánticos — sin valores hardcodeados como `bg-white` o `text-gray-500`
- [ ] Todos los espaciados son múltiplos de 4px (usar clases Tailwind, no valores arbitrarios)
- [ ] Estados definidos: cargando, vacío, con datos
- [ ] Acciones al final del flujo (bottom-right en desktop, full-width en mobile)
- [ ] Verificado en mobile (< 640px) y desktop (≥ 1280px)

### Estructura de página estándar

```blade
<x-layouts.app>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Encabezado de sección --}}
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <x-ui.typography as="h1">Título de página</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">
                    Descripción opcional de la sección.
                </x-ui.typography>
            </div>
            <x-ui.button>Acción principal</x-ui.button>
        </div>

        {{-- Contenido principal --}}
        <x-ui.card>
            <x-ui.card.content class="p-6">
                {{-- contenido --}}
            </x-ui.card.content>
        </x-ui.card>

    </div>
</x-layouts.app>
```

### Grilla de 4px — regla más importante

Todo espaciado debe ser múltiplo de 4px usando clases Tailwind:

| px  | Tailwind    | Uso típico                          |
|-----|-------------|-------------------------------------|
| 4   | `gap-1`     | Micro: ícono + texto                |
| 8   | `gap-2`     | Dentro de un componente             |
| 12  | `gap-3`     | Padding sm                          |
| 16  | `gap-4`     | Separar componentes relacionados    |
| 24  | `gap-6`     | Entre secciones relacionadas        |
| 32  | `gap-8`     | Entre secciones de página           |
| 48  | `gap-12`    | Entre bloques principales           |
| 64  | `gap-16`    | Grandes separaciones de página      |

```blade
{{-- ✅ Correcto --}}
<div class="flex gap-2">...</div>

{{-- ❌ Incorrecto --}}
<div class="flex gap-[7px]">...</div>
```

### Jerarquía de espaciado (proximidad)

Lo relacionado está más cerca:

```
Micro   4–8px    → ícono+texto, label+input
Inset   8–20px   → padding interno de componentes
Stack   8–16px   → entre campos del mismo grupo
Section 24–40px  → entre secciones relacionadas
Block   48–96px  → entre bloques principales de página
```

### Responsive

```blade
{{-- Padding de página --}}
<div class="px-4 sm:px-6 lg:px-8 xl:px-10">

{{-- Formulario: 1 col mobile, 2 col desktop --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

{{-- Cards escalables --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

{{-- Stack → Row --}}
<div class="flex flex-col sm:flex-row gap-3">

{{-- Acciones de formulario --}}
<div class="flex flex-col sm:flex-row-reverse gap-2">
    <x-ui.button class="w-full sm:w-auto">Guardar</x-ui.button>
    <x-ui.button variant="outline" class="w-full sm:w-auto">Cancelar</x-ui.button>
</div>
```

### Densidad de pantalla

Setear en el contenedor raíz según el contexto:

```blade
{{-- Dashboards y tablas de datos --}}
<div data-density="compact">

{{-- App estándar (default, no requiere el atributo) --}}
<div data-density="default">

{{-- Onboarding, marketing --}}
<div data-density="comfortable">
```

### Estados de UI — siempre definir los tres

```blade
@if($loading)
    <x-ui.skeleton class="h-32 w-full rounded-lg" />

@elseif($items->isEmpty())
    <x-ui.empty-state
        title="Sin resultados"
        description="Agregá el primero para comenzar."
        icon="inbox"
    >
        <x-ui.button size="sm">Agregar</x-ui.button>
    </x-ui.empty-state>

@else
    @foreach($items as $item)
        {{-- contenido --}}
    @endforeach
@endif
```

### Tipografía — siempre `<x-ui.typography>`

Nunca usar tags HTML crudos. Siempre el componente:

```blade
<x-ui.typography as="h1">Título único de página</x-ui.typography>
<x-ui.typography as="h2">Título de sección</x-ui.typography>
<x-ui.typography as="h3">Sub-sección</x-ui.typography>
<x-ui.typography as="lead" class="max-w-prose">Subtítulo prominente</x-ui.typography>
<x-ui.typography as="p" class="max-w-prose">Párrafo normal</x-ui.typography>
<x-ui.typography as="muted" class="max-w-prose">Texto secundario</x-ui.typography>
<x-ui.typography as="small">Label compacto</x-ui.typography>
<x-ui.typography as="section-label">CATEGORÍA</x-ui.typography>
<x-ui.typography as="code">$variable</x-ui.typography>
```

| `as=`          | Cuándo usar                                     |
|----------------|-------------------------------------------------|
| `h1`           | Título único de página                          |
| `h2`           | Título de sección principal                     |
| `h3`           | Sub-sección dentro de una sección               |
| `h4`           | Nivel más bajo de jerarquía de títulos          |
| `lead`         | Subtítulo o intro prominente (color muted)      |
| `large`        | Énfasis visual sin jerarquía semántica          |
| `p`            | Cuerpo de texto, color `foreground` normal      |
| `muted`        | Helper text, descripciones, info secundaria     |
| `small`        | Label compacto, metadata (`font-medium`)        |
| `section-label`| Etiqueta de sección: xs, uppercase, tracking    |
| `code`         | Código inline, nombres de props, tokens         |
| `blockquote`   | Citas, fragmentos destacados                    |

---

## 5. Principios de formularios

### Estructura base de un formulario

```blade
<form method="POST" action="/ruta" class="space-y-6">
    @csrf

    {{-- Grupo de campos relacionados --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <x-ui.form-field
            for="nombre"
            :state="$errors->has('nombre') ? 'destructive' : null"
            :message="$errors->first('nombre')"
        >
            <x-ui.label for="nombre">Nombre</x-ui.label>
            <x-ui.input
                id="nombre"
                name="nombre"
                :value="old('nombre')"
                :state="$errors->has('nombre') ? 'destructive' : null"
                placeholder="Ej: Juan"
            />
        </x-ui.form-field>

        <x-ui.form-field
            for="email"
            :state="$errors->has('email') ? 'destructive' : null"
            :message="$errors->first('email')"
        >
            <x-ui.label for="email">Email</x-ui.label>
            <x-ui.input
                id="email"
                name="email"
                type="email"
                :value="old('email')"
                :state="$errors->has('email') ? 'destructive' : null"
            />
        </x-ui.form-field>

    </div>

    {{-- Acciones --}}
    <div class="flex flex-col sm:flex-row-reverse gap-2 pt-2">
        <x-ui.button type="submit">Guardar</x-ui.button>
        <x-ui.button as="a" href="/anterior" variant="outline">Cancelar</x-ui.button>
    </div>

</form>
```

### Regla de tamaños — no mezclar sizes en el mismo formulario

Todos los controles del mismo formulario usan el mismo `size`. El default es `md`:

| Prop     | `sm`             | `md` (default)   | `lg`             |
|----------|------------------|------------------|------------------|
| Altura   | 32px (`h-8`)     | 40px (`h-10`)    | 48px (`h-12`)    |
| Padding X| 12px (`px-3`)    | 16px (`px-4`)    | 20px (`px-5`)    |
| Font     | 13px             | 14px (`text-sm`) | 16px (`text-base`)|
| Ícono    | 14px (`size-3.5`)| 16px (`size-4`)  | 18px (`size-[18px]`)|

```blade
{{-- ✅ Correcto: todos md --}}
<x-ui.input size="md" />
<x-ui.button size="md">Guardar</x-ui.button>

{{-- ❌ Incorrecto: tamaños mezclados --}}
<x-ui.input size="sm" />
<x-ui.button size="lg">Guardar</x-ui.button>
```

### form-field — propagación de estado

`<x-ui.form-field>` coordina label + input + helper-text automáticamente. Pasarle el `state` y `message` es suficiente:

```blade
<x-ui.form-field
    state="destructive"
    message="Este campo es requerido"
    for="campo"
>
    <x-ui.label for="campo">Campo</x-ui.label>
    <x-ui.input id="campo" :state="$state" />
    {{-- helper-text se renderiza automáticamente desde $message --}}
</x-ui.form-field>
```

### Input con ícono

```blade
<x-ui.input placeholder="Buscar...">
    <x-slot:leading>
        <x-lucide-search />
    </x-slot:leading>
</x-ui.input>
```

### Select nativo vs Combobox

- **`<x-ui.native-select>`** — listas cortas (< 10 items), sin búsqueda, forms simples
- **`<x-ui.combobox>`** — listas largas, con búsqueda, selección dinámica con Alpine.js

### Touch targets en mobile

Todo elemento interactivo necesita mínimo 44×44px de área táctil:

```blade
{{-- Botón icónico con área de toque adecuada --}}
<button class="p-3 -m-1 rounded-md hover:bg-accent">
    <x-lucide-more-horizontal class="size-4" />
</button>

{{-- Checkbox con label clickeable --}}
<label class="flex items-center gap-3 py-2 cursor-pointer">
    <x-ui.checkbox id="opcion" name="opcion" />
    <span class="text-sm">Opción</span>
</label>
```

---

## 6. Sistema de tokens

### Fuente de verdad

`resources/css/design-tokens.css` define toda la paleta y los tokens semánticos. Cambiar un valor ahí lo propaga a todos los componentes.

### Regla de tokens — nunca colores hardcodeados

```blade
{{-- ✅ Correcto: tokens semánticos --}}
<div class="bg-background text-foreground">
<div class="bg-card text-card-foreground border border-border">
<p class="text-muted-foreground">
<div class="bg-primary text-primary-foreground">

{{-- ❌ Incorrecto: valores hardcodeados --}}
<div class="bg-white text-gray-900">
<div class="bg-zinc-100">
<p class="text-gray-500">
```

### Tokens disponibles

| Token                      | Uso                                      |
|----------------------------|------------------------------------------|
| `bg-background`            | Fondo de página                          |
| `bg-card`                  | Fondo de card/panel                      |
| `bg-muted`                 | Fondo sutil (inputs deshabilitados, etc.)|
| `bg-accent`                | Hover de items de menú/nav               |
| `bg-primary`               | Botón principal, CTA                     |
| `bg-secondary`             | Botón secundario                         |
| `text-foreground`          | Texto principal                          |
| `text-muted-foreground`    | Texto secundario, helper text            |
| `text-primary-foreground`  | Texto sobre fondo primario               |
| `border-border`            | Borde estándar                           |
| `border-input`             | Borde de inputs                          |

### Estados semánticos — 6 tokens por estado

Para `destructive`, `success`, `warning`, `info`:

| Uso                              | Token                                    |
|----------------------------------|------------------------------------------|
| Botón filled                     | `bg-{state}` + `text-{state}-foreground` |
| Alert / highlight sutil          | `bg-{state}-subtle` + `text-{state}-subtle-foreground` |
| Borde de input con error         | `border-{state}-border`                  |
| Texto de helper/estado           | `text-{state}`                           |

```blade
{{-- Alert de error --}}
<x-ui.alert state="destructive">
    <x-ui.alert.title>Error</x-ui.alert.title>
    <x-ui.alert.description>Algo salió mal.</x-ui.alert.description>
</x-ui.alert>

{{-- Badge de éxito sutil --}}
<x-ui.badge state="success" :subtle="true">Activo</x-ui.badge>

{{-- Botón destructivo --}}
<x-ui.button state="destructive">Eliminar</x-ui.button>

{{-- Botón outline de warning --}}
<x-ui.button variant="outline" state="warning">Atención</x-ui.button>
```

### Tema oscuro

El tema oscuro se activa con la clase `dark` en `<html>`. El layout lo gestiona automáticamente via Alpine.js + localStorage.

---

## 7. Catálogo de componentes

### Átomos

| Componente        | Descripción                              | Props clave                        |
|-------------------|------------------------------------------|------------------------------------|
| `typography`      | Todos los textos del sistema             | `as`, `element`                    |
| `icon`            | Wrapper de lucide-icons                  | `name`, `size`                     |
| `badge`           | Etiqueta de estado o categoría           | `variant`, `state`, `subtle`       |
| `separator`       | Línea divisora                           | `orientation`                      |
| `skeleton`        | Placeholder de carga                     | clases de Tailwind                 |
| `spinner`         | Indicador de carga animado               | `size`                             |
| `kbd`             | Atajos de teclado                        | —                                  |

### Moléculas

| Componente        | Descripción                              | Props clave                        |
|-------------------|------------------------------------------|------------------------------------|
| `button`          | Botón de acción                          | `variant`, `size`, `state`, `as`   |
| `button-group`    | Grupo de botones unidos                  | —                                  |
| `input`           | Campo de texto                           | `size`, `state`, slots `leading`/`trailing` |
| `textarea`        | Área de texto                            | `size`, `state`                    |
| `label`           | Etiqueta de campo                        | `for`                              |
| `checkbox`        | Casilla de verificación                  | `state`                            |
| `switch`          | Toggle on/off                            | —                                  |
| `radio-group`     | Grupo de opciones excluyentes            | —                                  |
| `slider`          | Control de rango                         | —                                  |
| `native-select`   | Select HTML nativo con estilos           | `size`, `state`                    |
| `form-field`      | Wrapper label + input + helper-text      | `state`, `message`, `for`          |
| `helper-text`     | Texto de ayuda bajo un campo             | `state`, `message`                 |
| `input-group`     | Input con addon/botón integrado          | —                                  |
| `input-otp`       | Input para códigos OTP                   | —                                  |
| `toggle`          | Botón toggle (on/off visual)             | `variant`, `size`                  |
| `toggle-group`    | Grupo de toggles                         | —                                  |

### Organismos

| Componente        | Sub-componentes                          | Descripción                        |
|-------------------|------------------------------------------|------------------------------------|
| `card`            | `header`, `title`, `description`, `content`, `footer` | Contenedor genérico |
| `dialog`          | `trigger`, `content`, `header`, `title`, `description`, `footer`, `close` | Modal |
| `drawer`          | `content`, `header`, `title`, `description`, `footer` | Panel lateral (mobile-first) |
| `sheet`           | `content`, `header`, `title`, `description`, `footer` | Panel lateral |
| `alert-dialog`    | `trigger`, `content`, `header`, `title`, `description`, `footer`, `action`, `cancel` | Confirmación |
| `alert`           | `title`, `description`                   | Mensaje de estado                  |
| `tabs`            | `list`, `trigger`, `content`             | Pestañas                           |
| `accordion`       | `item`, `header`, `trigger`, `content`   | Acordeón colapsable                |
| `select`          | `trigger`, `content`, `group`, `label`, `item`, `separator`, `value` | Select estilizado |
| `combobox`        | `input`, `list`, `item`, `group`, `label`, `empty`, `separator`, `content` | Select con búsqueda |
| `dropdown-menu`   | `trigger`, `content`, `item`, `group`, `label`, `separator`, `shortcut`, `checkbox-item`, `radio-group`, `radio-item`, `sub`, `sub-trigger`, `sub-content` | Menú contextual |
| `data-table`      | —                                        | Tabla de datos completa            |
| `table`           | `header`, `body`, `footer`, `row`, `head`, `cell`, `caption` | Tabla semántica |
| `sidebar`         | `provider`, `content`, `header`, `footer`, `group`, `group-label`, `group-content`, `menu`, `menu-item`, `menu-button`, `menu-action`, `menu-badge`, `menu-sub`, `menu-sub-item`, `menu-sub-button`, `separator`, `trigger`, `inset` | Sidebar completo |
| `breadcrumb`      | `list`, `item`, `link`, `page`, `separator`, `ellipsis` | Navegación migas de pan |
| `pagination`      | `content`, `item`, `link`, `previous`, `next`, `ellipsis` | Paginación |
| `avatar`          | `image`, `fallback`                      | Avatar de usuario                  |
| `tooltip`         | —                                        | Tooltip (Alpine.js)                |
| `popover`         | —                                        | Popover flotante                   |
| `hover-card`      | `trigger`, `content`                     | Card que aparece al hover          |
| `collapsible`     | `trigger`, `content`                     | Sección colapsable                 |
| `command`         | `input`, `list`, `group`, `item`, `empty`, `separator`, `shortcut`, `dialog` | Paleta de comandos |
| `navigation-menu` | `list`, `item`, `trigger`, `content`, `link` | Menú de navegación principal |
| `menubar`         | `menu`, `trigger`, `content`, `item`, `group`, `label`, `separator`, `shortcut`, `checkbox-item`, `radio-group`, `radio-item`, `sub`, `sub-trigger`, `sub-content` | Barra de menús |
| `calendar`        | —                                        | Calendario Alpine.js               |
| `date-picker`     | —                                        | Selector de fecha                  |
| `empty-state`     | —                                        | Estado vacío estilizado            |
| `scroll-area`     | —                                        | Área con scroll custom             |
| `progress`        | —                                        | Barra de progreso                  |
| `chart`           | —                                        | Wrapper de Chart.js                |
| `sonner`          | —                                        | Sistema de toasts                  |
| `toaster`         | —                                        | Contenedor de toasts               |

---

## 8. Otras consideraciones

### Alpine.js — inicialización de stores globales

El layout `app.blade.php` inicializa `$store.theme` globalmente. Para agregar stores propios:

```js
// resources/js/app.js
import Alpine from 'alpinejs'

Alpine.store('theme', {
    dark: false,
    toggle() { this.dark = !this.dark }
})

Alpine.start()
```

### twMerge — merge de clases Tailwind

Los componentes usan `$attributes->twMerge(...)` del paquete `gehrisandro/tailwind-merge-laravel`. Esto permite sobreescribir clases desde el exterior sin conflictos:

```blade
{{-- La clase "w-full" sobreescribe el ancho por defecto --}}
<x-ui.button class="w-full">Texto</x-ui.button>

{{-- Agregar clases adicionales sin conflictos --}}
<x-ui.card class="shadow-lg">...</x-ui.card>
```

### Íconos — blade-lucide-icons

Usar siempre el componente dinámico o el componente directo:

```blade
{{-- Ícono directo --}}
<x-lucide-search class="size-4" />

{{-- Ícono dinámico (cuando el nombre es variable) --}}
<x-dynamic-component :component="'lucide-' . $iconName" class="size-4" />
```

Los íconos en botones e inputs heredan el tamaño via `[&_svg]:size-4` del componente padre — no hace falta setear el size manualmente si están dentro de un botón.

### Modo dark — clase en `<html>`

El theme toggle del layout agrega/quita la clase `dark` en `<html>`. El script inline evita el flash al cargar:

```html
<script>
    (function() {
        const saved = localStorage.getItem('theme')
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        if (saved === 'dark' || (!saved && prefersDark)) {
            document.documentElement.classList.add('dark')
        }
    })()
</script>
```

Para tematizar un componente custom, usar el selector de Tailwind v4:

```css
/* app.css */
@variant dark (&:where(.dark, .dark *));
```

```blade
<div class="bg-white dark:bg-zinc-900">
```

### Generación con Claude — instrucciones para el agente

Al pedir a Claude que genere pantallas o componentes en este proyecto, las instrucciones del `CLAUDE.md` se cargan automáticamente. Lo más importante a recordar:

1. **Grilla de 4px** — nunca valores arbitrarios en espaciados
2. **Tokens semánticos** — nunca `bg-white`, `text-gray-500`, etc.
3. **`<x-ui.typography>`** — nunca tags HTML crudos para texto
4. **Mismo `size` en todo el formulario** — no mezclar `sm`, `md`, `lg`
5. **Tres estados de UI** — cargando, vacío, con datos
6. **`max-w-prose` en párrafos** — siempre en `p` y `muted`
7. **Acciones al final** — bottom-right desktop, full-width mobile

### Agregar un componente nuevo

1. Crear el archivo en `resources/views/components/ui/nombre.blade.php`
2. Usar `@props([])` para declarar las props con defaults
3. Usar `$attributes->twMerge(...)` en el elemento raíz para permitir override de clases
4. Si tiene sub-componentes, crear la carpeta `ui/nombre/`
5. Usar solo tokens semánticos — nunca colores primitivos directos

```blade
@props([
    'variant' => 'default',  // default | secondary
])

@php
$class = match($variant) {
    'secondary' => 'bg-secondary text-secondary-foreground',
    default     => 'bg-primary text-primary-foreground',
};
@endphp

<div {{ $attributes->twMerge('rounded-md px-4 py-2', $class) }}>
    {{ $slot }}
</div>
```
