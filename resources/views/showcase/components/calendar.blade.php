<x-layouts.showcase title="Calendar — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Calendar</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Selector de fechas con soporte para selección simple, múltiple y de rango. Internacionalizado vía <x-ui.typography as="code">Intl</x-ui.typography>.</x-ui.typography>
    </div>

    {{-- Single --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Selección simple</x-ui.typography>
        <div x-data="{ date: null }" class="flex flex-wrap gap-6 items-start">
            <x-ui.calendar @change="date = $event.detail.value" />
            <div class="text-sm text-muted-foreground pt-3">
                Seleccionado: <span x-text="date ?? '—'" class="font-mono text-foreground"></span>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Con valor inicial --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con fecha pre-seleccionada</x-ui.typography>
        <x-ui.calendar value="{{ date('Y-m-d') }}" />
    </section>

    <x-ui.separator />

    {{-- Multiple --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Selección múltiple</x-ui.typography>
        <div x-data="{ dates: [] }" class="flex flex-wrap gap-6 items-start">
            <x-ui.calendar mode="multiple" @change="dates = $event.detail.value" />
            <div class="text-sm text-muted-foreground pt-3">
                Seleccionados:
                <template x-for="d in dates" :key="d">
                    <span x-text="d" class="font-mono text-foreground block"></span>
                </template>
                <span x-show="!dates.length">—</span>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Range --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Selección de rango</x-ui.typography>
        <div x-data="{ range: null }" class="flex flex-wrap gap-6 items-start">
            <x-ui.calendar mode="range" @change="range = $event.detail.value" />
            <div class="text-sm text-muted-foreground pt-3 space-y-1">
                <div>Inicio: <span x-text="range?.start ?? '—'" class="font-mono text-foreground"></span></div>
                <div>Fin: <span x-text="range?.end ?? '—'" class="font-mono text-foreground"></span></div>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Con restricciones --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con fechas deshabilitadas y límites</x-ui.typography>
        <x-ui.calendar
            min-date="{{ date('Y-m-d') }}"
            :disabled-dates="[
                date('Y-m-d', strtotime('+3 days')),
                date('Y-m-d', strtotime('+7 days')),
            ]"
        />
        <x-ui.typography as="muted">Mínimo: hoy. Deshabilitados: +3 días y +7 días.</x-ui.typography>
    </section>

    <x-ui.separator />

    {{-- Semana desde domingo --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Semana desde domingo</x-ui.typography>
        <x-ui.calendar :week-starts-on="0" />
    </section>

</div>
</x-layouts.showcase>
