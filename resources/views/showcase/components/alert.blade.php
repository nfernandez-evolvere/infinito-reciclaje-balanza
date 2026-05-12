<x-layouts.showcase title="Alert — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Alert</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Mensaje contextual no interruptivo. El ícono debe ser el primer hijo del componente para que los selectores CSS de posicionamiento funcionen correctamente.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.alert>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
            <x-ui.alert.title>Heads up!</x-ui.alert.title>
            <x-ui.alert.description>Podés agregar componentes a tu app usando el CLI.</x-ui.alert.description>
        </x-ui.alert>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <div class="space-y-3">

            <x-ui.alert state="destructive">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <x-ui.alert.title>Error al procesar</x-ui.alert.title>
                <x-ui.alert.description>No se pudo completar la operación. Verificá tu conexión e intentá de nuevo.</x-ui.alert.description>
            </x-ui.alert>

            <x-ui.alert state="success">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <x-ui.alert.title>Cambios guardados</x-ui.alert.title>
                <x-ui.alert.description>Tu perfil fue actualizado correctamente.</x-ui.alert.description>
            </x-ui.alert>

            <x-ui.alert state="warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                <x-ui.alert.title>Acción requerida</x-ui.alert.title>
                <x-ui.alert.description>Tu suscripción vence en 3 días. Renovala para evitar interrupciones.</x-ui.alert.description>
            </x-ui.alert>

            <x-ui.alert state="info">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                <x-ui.alert.title>Nueva funcionalidad</x-ui.alert.title>
                <x-ui.alert.description>Ahora podés exportar reportes en formato PDF desde el panel de analytics.</x-ui.alert.description>
            </x-ui.alert>

        </div>
    </section>

    <x-ui.separator />

    {{-- Sin ícono --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Sin ícono</x-ui.typography>
        <div class="space-y-3">
            <x-ui.alert state="warning">
                <x-ui.alert.title>Modo de solo lectura</x-ui.alert.title>
                <x-ui.alert.description>No tenés permisos de edición en este recurso.</x-ui.alert.description>
            </x-ui.alert>
            <x-ui.alert state="info">
                <x-ui.alert.title>Solo texto</x-ui.alert.title>
                <x-ui.alert.description>Un alert sin ícono al que le falta el ancho de sangría al ser el primer hijo.</x-ui.alert.description>
            </x-ui.alert>
        </div>
    </section>

    <x-ui.separator />

    {{-- Solo descripción --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Solo descripción</x-ui.typography>
        <x-ui.alert state="destructive">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <x-ui.alert.description>La contraseña debe tener al menos 8 caracteres.</x-ui.alert.description>
        </x-ui.alert>
    </section>

</div>
</x-layouts.showcase>
