<x-layouts.showcase title="Typography — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Typography</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Sistema tipográfico del design system. Un único componente <x-ui.typography as="code">x-ui.typography</x-ui.typography> con prop <x-ui.typography as="code">as</x-ui.typography> para cada variante.</x-ui.typography>
    </div>

    <section class="space-y-6">
        <x-ui.typography as="section-label">Jerarquía de títulos</x-ui.typography>
        <div class="space-y-4">
            <x-ui.typography as="h1">El conocimiento es poder</x-ui.typography>
            <x-ui.typography as="h2">Diseño con intención</x-ui.typography>
            <x-ui.typography as="h3">Componentes accesibles</x-ui.typography>
            <x-ui.typography as="h4">Detalles que importan</x-ui.typography>
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Texto de cuerpo</x-ui.typography>
        <x-ui.typography>
            Un sistema de diseño bien construido acelera el desarrollo y garantiza consistencia visual.
            Cada componente sigue las mismas reglas de espaciado, color y tipografía.
        </x-ui.typography>
        <x-ui.typography as="lead">
            El lead es un párrafo introductorio más grande, ideal para descripciones de página.
        </x-ui.typography>
        <x-ui.typography as="large">Texto grande para destacar información clave.</x-ui.typography>
        <x-ui.typography as="small">Texto pequeño para metadatos o notas al pie.</x-ui.typography>
        <x-ui.typography as="muted">Texto silenciado para contexto secundario.</x-ui.typography>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Inline y especiales</x-ui.typography>
        <x-ui.typography as="p">
            Instalá el paquete con <x-ui.typography as="code" element="span">npm install @shadcn/ui</x-ui.typography> o usá
            <x-ui.typography as="code" element="span">pnpm add</x-ui.typography> si usás pnpm.
        </x-ui.typography>
        <x-ui.typography as="blockquote">
            "La simplicidad es la sofisticación máxima." — Leonardo da Vinci
        </x-ui.typography>
        <x-ui.typography as="list">
            <li>Tokens CSS semánticos (light + dark)</li>
            <li>Componentes Blade con Alpine.js</li>
            <li>Grilla de 4px para todos los espaciados</li>
            <li>Totalmente responsive sin breakpoints hardcodeados</li>
        </x-ui.typography>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Uso completo — artículo</x-ui.typography>
        <article class="max-w-prose space-y-4">
            <x-ui.typography as="h1">Introducción al design system</x-ui.typography>
            <x-ui.typography as="lead">
                Este sistema está diseñado para maximizar la consistencia y acelerar el desarrollo de interfaces.
            </x-ui.typography>
            <x-ui.typography>
                Cada componente expone una API predecible basada en la nomenclatura de shadcn/ui,
                adaptada para el ecosistema Laravel + Blade + Alpine.js.
            </x-ui.typography>
            <x-ui.typography as="h2">Principios de diseño</x-ui.typography>
            <x-ui.typography>
                La base del sistema es una grilla de 4px. Todos los espaciados son múltiplos de 4,
                lo que garantiza alineación perfecta en cualquier viewport.
            </x-ui.typography>
            <x-ui.typography as="blockquote">
                "Un buen sistema de diseño no se nota — simplemente funciona."
            </x-ui.typography>
            <x-ui.typography as="h3">Cómo instalar</x-ui.typography>
            <x-ui.typography>
                Ejecutá <x-ui.typography as="code" element="span">php artisan ui:install</x-ui.typography> para publicar todos los componentes en tu proyecto.
            </x-ui.typography>
        </article>
    </section>

</div>
</x-layouts.showcase>
