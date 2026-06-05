<x-ui.alert
    x-show="alertas > 0"
    x-cloak
    state="warning"
    description="Revisá las alarmas para ver el detalle."
>
    <x-slot:title>
        <span x-text="alertas + (alertas === 1 ? ' alerta activa' : ' alertas activas')"></span>
    </x-slot:title>

    <x-slot:action>
        <x-ui.button variant="ghost" state="warning" href="{{ route('admin.alertas.index') }}">
            Revisar
        </x-ui.button>
    </x-slot:action>
</x-ui.alert>
