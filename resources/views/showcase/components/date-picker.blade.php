<x-layouts.showcase title="Date Picker — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Date Picker</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Selector de fecha compuesto: Calendar + Popover. La fecha seleccionada se formatea en el idioma del navegador.</x-ui.typography>
    </div>

    {{-- Básico --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Básico</x-ui.typography>
        <div x-data="{ date: null }" class="flex flex-wrap gap-6 items-center">
            <x-ui.date-picker class="max-w-xs" @change="date = $event.detail.value" />
            <span class="text-sm text-muted-foreground">
                Valor: <span x-text="date ?? '—'" class="font-mono text-foreground"></span>
            </span>
        </div>
    </section>

    <x-ui.separator />

    {{-- Con valor inicial --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con fecha pre-seleccionada</x-ui.typography>
        <x-ui.date-picker value="{{ date('Y-m-d') }}" class="max-w-xs" />
    </section>

    <x-ui.separator />

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="flex flex-col gap-3 max-w-xs">
            <x-ui.date-picker size="sm" placeholder="Fecha (sm)" />
            <x-ui.date-picker size="md" placeholder="Fecha (md)" />
            <x-ui.date-picker size="lg" placeholder="Fecha (lg)" />
        </div>
    </section>

    <x-ui.separator />

    {{-- Con restricciones --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con límites de fechas</x-ui.typography>
        <x-ui.date-picker
            min-date="{{ date('Y-m-d') }}"
            max-date="{{ date('Y-m-d', strtotime('+30 days')) }}"
            placeholder="Solo los próximos 30 días"
            class="max-w-xs"
        />
    </section>

    <x-ui.separator />

    {{-- Deshabilitado --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Deshabilitado</x-ui.typography>
        <x-ui.date-picker disabled value="{{ date('Y-m-d') }}" class="max-w-xs" />
    </section>

    <x-ui.separator />

    {{-- En formulario --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En formulario</x-ui.typography>
        <form @submit.prevent="$store.toast.success('Fecha enviada: ' + $el.querySelector('[name=fecha]').value)" class="max-w-xs space-y-3">
            <div class="space-y-1.5">
                <x-ui.label for="fecha">Fecha de nacimiento</x-ui.label>
                <x-ui.date-picker name="fecha" placeholder="Seleccioná tu fecha" />
            </div>
            <x-ui.button type="submit" class="w-full">Enviar</x-ui.button>
        </form>
    </section>

</div>
</x-layouts.showcase>
