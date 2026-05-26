@props(['alertas'])

@if($alertas > 0)
<div class="flex items-center gap-4 rounded-lg border border-warning/40 bg-warning/10 px-4 py-3">
    <x-lucide-triangle-alert class="size-5 shrink-0 text-warning" />
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-warning-foreground">
            {{ $alertas }} {{ Str::plural('alerta activa', $alertas) }}
        </p>
        <p class="text-xs text-warning-foreground/70">Revisá las alarmas para ver el detalle.</p>
    </div>
    <x-ui.button variant="outline" size="sm" href="{{ route('admin.alarmas.index') }}" class="border-warning/40 text-warning-foreground hover:bg-warning/20 shrink-0">
        Revisar
    </x-ui.button>
</div>
@endif
