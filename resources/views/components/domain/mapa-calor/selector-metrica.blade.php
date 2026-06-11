{{-- Selector de métrica — usa el primitivo Tabs como segmented control.
     Vive bajo el x-data="mapaCalor" del padre: el $watch sincroniza la pestaña activa
     con setMetric(), que repinta el mapa. El valor inicial coincide con metric: 'toneladas'. --}}
<div class="flex flex-col gap-2">
    <x-ui.tabs value="toneladas" x-init="$watch('active', (key) => setMetric(key))">
        <x-ui.tabs.list>
            <x-ui.tabs.trigger value="toneladas">Toneladas</x-ui.tabs.trigger>
            <x-ui.tabs.trigger value="pesajes">Viajes</x-ui.tabs.trigger>
            <x-ui.tabs.trigger value="per_capita">Per cápita</x-ui.tabs.trigger>
            <x-ui.tabs.trigger value="densidad">Densidad</x-ui.tabs.trigger>
        </x-ui.tabs.list>
    </x-ui.tabs>
</div>
