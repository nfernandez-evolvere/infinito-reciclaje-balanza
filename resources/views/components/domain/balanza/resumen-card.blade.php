<div
    class="rounded-xl border p-5 transition-all duration-300"
    x-bind:class="canSave
        ? 'bg-success-subtle border-success-border shadow-md shadow-success/10'
        : 'bg-card border-border shadow-lg'"
>
    <div class="flex items-start justify-between mb-4">
        <div>
            <div class="text-sm font-semibold text-foreground">Resumen</div>
            <div class="text-xs text-muted-foreground mt-0.5" x-text="fechaHoraActual"></div>
        </div>
        <div
            x-show="canSave"
            x-cloak
            class="inline-flex items-center gap-1.5 bg-success text-success-foreground rounded-full px-2.5 py-1 text-[11px] font-bold tracking-wide uppercase"
        >
            <x-lucide-circle-check class="size-3" />
            Listo
        </div>
    </div>

    <div class="grid grid-cols-2 gap-x-3 gap-y-4">
        @foreach([
            ['label' => 'Vehículo',   'key' => "vehiculo?.patente ?? '—'",              'num' => false],
            ['label' => 'Servicio',   'key' => "servicioNombre || '—'",                 'num' => false],
            ['label' => 'Origen',     'key' => "zonaNombre || '—'",                     'num' => false],
            ['label' => 'Turno',      'key' => "turno || '—'",                          'num' => false],
            ['label' => 'Tipo',       'key' => "vehiculo?.tipo ?? '—'",                 'num' => false],
            ['label' => 'Peso bruto', 'key' => "brutoN > 0 ? fmtKg(brutoN) : '—'",     'num' => true],
            ['label' => 'Tara',       'key' => "vehiculo ? fmtKg(vehiculo.tara) : '—'", 'num' => true],
        ] as $cell)
            <div>
                <div class="text-overline mb-1">{{ $cell['label'] }}</div>
                <div class="text-sm font-semibold text-foreground {{ $cell['num'] ? 'tabular-nums font-mono' : '' }}"
                     x-text="{{ $cell['key'] }}"></div>
            </div>
        @endforeach

        {{-- Neto — destacado --}}
        <div class="col-span-2 pt-3 mt-1 border-t border-border/60">
            <div class="text-overline mb-1">Neto estimado</div>
            <div
                class="tabular-nums font-mono font-bold transition-colors duration-200"
                style="font-size:28px;line-height:1.1"
                x-bind:class="canSave ? 'text-success' : 'text-foreground/40'"
                x-text="vehiculo && brutoN > 0 ? fmtKg(neto) : '—'"
            ></div>
        </div>

        {{-- Operador --}}
        <div class="col-span-2 pt-3 border-t border-border/60">
            <div class="text-overline mb-1">Operador</div>
            <div class="text-sm font-semibold text-foreground">{{ auth()->user()->name }}</div>
        </div>
    </div>
</div>
