<x-ui.alert x-show="alertas > 0" x-cloak state="warning" class="flex items-center gap-4 py-3">
    <div class="flex flex-1 items-center gap-4 min-w-0">
        <x-lucide-triangle-alert class="size-5 shrink-0 text-warning" />
        <div class="min-w-0">
            <x-ui.alert.title>
                <span x-text="alertas + (alertas === 1 ? ' alerta activa' : ' alertas activas')"></span>
            </x-ui.alert.title>
            <x-ui.alert.description>Revisá las alarmas para ver el detalle.</x-ui.alert.description>
        </div>
    </div>
    <x-ui.button variant="ghost" state="warning" href="{{ route('admin.alertas.index') }}" class="shrink-0">
        Revisar
    </x-ui.button>
</x-ui.alert>
