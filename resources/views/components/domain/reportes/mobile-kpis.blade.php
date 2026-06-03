@props(['kpis'])

{{-- Mobile y tablet: los KPIs del período viven dentro de un drawer (bottom sheet) --}}
<div class="xl:hidden">
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button variant="ghost" size="icon" aria-label="Ver resumen del período">
                <x-lucide-chart-bar class="size-4" />
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-4 overflow-y-auto max-h-[85vh]">
            <p class="text-label text-base">Resumen del período</p>
            <x-domain.reportes.kpis :kpis="$kpis" gridClass="grid grid-cols-1 sm:grid-cols-2 gap-3" />
        </div>
    </x-ui.sheet>
</div>
