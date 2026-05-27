<div x-show="alertas > 0" x-cloak class="flex items-center gap-4 rounded-lg border border-warning/40 bg-warning/10 px-4 py-3">
    <x-lucide-triangle-alert class="size-5 shrink-0 text-warning" />
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-warning-foreground">
            <span x-text="alertas + (alertas === 1 ? ' alerta activa' : ' alertas activas')"></span>
        </p>
        <p class="text-xs text-warning-foreground/70">Revisá las alarmas para ver el detalle.</p>
    </div>
    <x-ui.button variant="outline" size="sm" href="#" class="border-warning/40 text-warning-foreground hover:bg-warning/20 shrink-0">
        Revisar
    </x-ui.button>
</div>
