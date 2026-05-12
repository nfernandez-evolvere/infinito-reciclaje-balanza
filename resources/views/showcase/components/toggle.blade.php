<x-layouts.showcase title="Toggle — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Toggle / Toggle Group</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Botón de dos estados (on/off). Toggle Group gestiona la selección entre múltiples opciones con tipo <x-ui.typography as="code">single</x-ui.typography> o <x-ui.typography as="code">multiple</x-ui.typography>.</x-ui.typography>
    </div>

    {{-- Toggle default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Toggle</x-ui.typography>
        <div class="flex flex-wrap gap-3">
            <x-ui.toggle>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.744h-.753v8.25h7.125a4.125 4.125 0 0 0 0-8.25H6.75Zm0 0H6v8.25m0 0H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18v-9a2.25 2.25 0 0 0-2.25-2.25h-9.375H6v8.25m6.75-4.498h-1.5v4.498H12V6.75h1.5a4.5 4.5 0 0 1 0 9h-1.5V9.75Z"/></svg>
                Bold
            </x-ui.toggle>
            <x-ui.toggle>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.248 20.246H9.05m0 0h3.696m-3.696 0 5.893-16.502m0 0h-3.697m3.697 0h3.803"/></svg>
                Italic
            </x-ui.toggle>
            <x-ui.toggle :pressed="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z"/></svg>
                Saved (on)
            </x-ui.toggle>
            <x-ui.toggle disabled>Disabled</x-ui.toggle>
        </div>
    </section>

    <x-ui.separator />

    {{-- Variantes --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Variantes</x-ui.typography>
        <div class="flex flex-wrap gap-3">
            <x-ui.toggle variant="default">Default</x-ui.toggle>
            <x-ui.toggle variant="outline">Outline</x-ui.toggle>
            <x-ui.toggle variant="outline" :pressed="true">Outline On</x-ui.toggle>
        </div>
    </section>

    <x-ui.separator />

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.toggle size="sm">Small</x-ui.toggle>
            <x-ui.toggle size="md">Medium</x-ui.toggle>
            <x-ui.toggle size="lg">Large</x-ui.toggle>
        </div>
    </section>

    <x-ui.separator />

    {{-- Toggle Group single --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Toggle Group — single</x-ui.typography>
        <div class="space-y-3">
            <x-ui.toggle-group type="single" value="center">
                <x-ui.toggle-group.item value="left">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h10.5m-10.5 5.25h16.5"/></svg>
                </x-ui.toggle-group.item>
                <x-ui.toggle-group.item value="center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12H20.25m-16.5 5.25h16.5"/></svg>
                </x-ui.toggle-group.item>
                <x-ui.toggle-group.item value="right">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h12.75m-12.75 5.25h16.5"/></svg>
                </x-ui.toggle-group.item>
            </x-ui.toggle-group>

            <x-ui.toggle-group type="single" value="md" variant="outline">
                <x-ui.toggle-group.item value="sm">S</x-ui.toggle-group.item>
                <x-ui.toggle-group.item value="md">M</x-ui.toggle-group.item>
                <x-ui.toggle-group.item value="lg">L</x-ui.toggle-group.item>
                <x-ui.toggle-group.item value="xl">XL</x-ui.toggle-group.item>
            </x-ui.toggle-group>
        </div>
    </section>

    <x-ui.separator />

    {{-- Toggle Group multiple --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Toggle Group — multiple</x-ui.typography>
        <x-ui.toggle-group type="multiple" :value="['bold', 'underline']">
            <x-ui.toggle-group.item value="bold">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.744h-.753v8.25h7.125a4.125 4.125 0 0 0 0-8.25H6.75Zm0 0H6v8.25m0 0H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18v-9a2.25 2.25 0 0 0-2.25-2.25h-9.375H6v8.25m6.75-4.498h-1.5v4.498H12V6.75h1.5a4.5 4.5 0 0 1 0 9h-1.5V9.75Z"/></svg>
            </x-ui.toggle-group.item>
            <x-ui.toggle-group.item value="italic">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.248 20.246H9.05m0 0h3.696m-3.696 0 5.893-16.502m0 0h-3.697m3.697 0h3.803"/></svg>
            </x-ui.toggle-group.item>
            <x-ui.toggle-group.item value="underline">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.995 3.744v7.5a6 6 0 1 1-12 0v-7.5m-2.25 16.502h16.5"/></svg>
            </x-ui.toggle-group.item>
        </x-ui.toggle-group>
    </section>

</div>
</x-layouts.showcase>
