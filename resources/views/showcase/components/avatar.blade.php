<x-layouts.showcase title="Avatar — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Avatar</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Imagen de perfil circular con fallback automático a iniciales cuando la imagen falla o no se provee.</x-ui.typography>
    </div>

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="flex flex-wrap gap-4 items-end">
            <x-ui.avatar size="sm" alt="Ana García" />
            <x-ui.avatar size="md" alt="Ana García" />
            <x-ui.avatar size="lg" alt="Ana García" />
            <x-ui.avatar size="xl" alt="Ana García" />
        </div>
    </section>

    <x-ui.separator />

    {{-- Con imagen --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con imagen</x-ui.typography>
        <div class="flex flex-wrap gap-4 items-center">
            <x-ui.avatar src="https://i.pravatar.cc/150?img=1" alt="Carlos Mendoza" />
            <x-ui.avatar src="https://i.pravatar.cc/150?img=5" alt="Laura Torres" />
            <x-ui.avatar src="https://i.pravatar.cc/150?img=9" alt="Martín López" />
            {{-- Imagen rota → fallback a iniciales --}}
            <x-ui.avatar src="https://invalid.url/404.jpg" alt="Imagen Rota" />
        </div>
    </section>

    <x-ui.separator />

    {{-- Fallback con iniciales --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Fallback — iniciales</x-ui.typography>
        <div class="flex flex-wrap gap-4 items-center">
            <x-ui.avatar alt="Ana García" />
            <x-ui.avatar alt="Juan Carlos Pérez" />
            <x-ui.avatar alt="María" />
            <x-ui.avatar alt="X" />
        </div>
    </section>

    <x-ui.separator />

    {{-- Grupo apilado --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Grupo apilado</x-ui.typography>
        <div class="flex -space-x-2">
            <x-ui.avatar src="https://i.pravatar.cc/150?img=1" alt="Usuario 1" class="ring-2 ring-background" />
            <x-ui.avatar src="https://i.pravatar.cc/150?img=2" alt="Usuario 2" class="ring-2 ring-background" />
            <x-ui.avatar src="https://i.pravatar.cc/150?img=3" alt="Usuario 3" class="ring-2 ring-background" />
            <x-ui.avatar alt="+5" class="ring-2 ring-background" />
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — perfil</x-ui.typography>
        <div class="flex items-center gap-3 max-w-xs">
            <x-ui.avatar src="https://i.pravatar.cc/150?img=1" alt="Ana García" size="lg" />
            <div>
                <x-ui.typography as="p" class="text-sm font-semibold">Ana García</x-ui.typography>
                <x-ui.typography as="muted" class="text-xs">ana@ejemplo.com</x-ui.typography>
            </div>
        </div>
    </section>

</div>
</x-layouts.showcase>
