{{-- Mapa choropleth + leyenda. Vive bajo el x-data="mapaCalor" del padre. --}}
<div class="overflow-hidden rounded-xl bg-card shadow-lg">
    <div id="mapa-calor-map" class="h-[480px] w-full"></div>

    {{-- Leyenda --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-border p-4">
        <span class="text-xs font-medium text-muted-foreground">
            Escala · <span x-text="metricaActual().label"></span>
        </span>

        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
            <template x-for="(b, i) in buckets" :key="i">
                <div class="flex items-center gap-1.5">
                    <span class="size-3 rounded-sm" :style="{ backgroundColor: b.color }"></span>
                    <span class="text-xs tabular-nums text-muted-foreground" x-text="b.label"></span>
                </div>
            </template>
        </div>

        <div class="flex items-center gap-1.5">
            <span class="size-3 rounded-sm" style="background-color: #cbd5e1"></span>
            <span class="text-xs text-muted-foreground">Sin actividad</span>
        </div>
    </div>
</div>
