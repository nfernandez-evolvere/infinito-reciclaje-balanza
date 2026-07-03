@props([])

{{--
    Campo del filtro del dashboard: un rango de fechas. Se reutiliza en el sheet mobile
    y en el panel inline (md+). No usa inputs ocultos ni submit nativo — el dashboard
    filtra por AJAX. El date-range-picker despacha `range-picked` (burbujea); lo capturo
    en un wrapper y lo guardo vía método (stageRango) para no chocar con el scope Alpine
    anidado del filter-panel/filter-sheet.
--}}

<div class="w-full md:w-80" @range-picked="stageRango($event.detail.start, $event.detail.end)">
    <x-ui.form-field>
        <x-ui.label>Período</x-ui.label>
        <x-ui.date-range-picker
            placeholder="Elegí un rango de fechas"
            max-date="{{ now()->format('Y-m-d') }}"
        />
    </x-ui.form-field>
</div>
