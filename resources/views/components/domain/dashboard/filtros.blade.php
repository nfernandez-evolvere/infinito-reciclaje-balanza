@props([])

{{--
    Filtro de período del dashboard, con el mismo patrón que historial: sheet en mobile
    (<md) + card colapsable inline en md+. A diferencia del resto, filtra por AJAX
    (applyRango/clearRango del store dashboardData), sin recargar la página — por eso
    usa los props submitHandler/clearHandler del filter-panel/filter-sheet.
--}}

{{-- Sheet mobile (<md) --}}
<x-ui.filter-sheet
    controlledBy="filterOpen"
    submitHandler="applyRango()"
    clearHandler="clearRango()"
>
    <x-domain.dashboard.filtros.campos />
</x-ui.filter-sheet>

{{-- Panel inline (md+) --}}
<x-ui.filter-panel
    storageKey="filtros:dashboard"
    title="Período"
    submitHandler="applyRango()"
    clearHandler="clearRango()"
    bodyClass="flex justify-end p-4"
>
    <x-slot:chips>
        <span x-show="!desdeRango" class="text-xs text-muted-foreground">Sin período seleccionado</span>
        <template x-if="desdeRango">
            <button
                type="button"
                @click="clearRango()"
                class="inline-flex items-center gap-1 rounded-full border border-primary/20 bg-primary/10 pl-2.5 pr-1.5 py-1 text-xs font-medium text-primary transition-colors hover:bg-primary/20"
            >
                <span class="max-w-40 truncate tabular-nums" x-text="rangoLabel()"></span>
                <x-lucide-x class="size-3 shrink-0" />
            </button>
        </template>
    </x-slot:chips>

    <x-domain.dashboard.filtros.campos />
</x-ui.filter-panel>
