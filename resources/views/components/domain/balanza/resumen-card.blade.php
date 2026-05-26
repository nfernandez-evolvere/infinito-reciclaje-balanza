<div
    class="rounded-xl border p-5 transition-all duration-300"
    x-bind:class="canSave
        ? 'bg-success-subtle border-success-border shadow-md shadow-success/10'
        : 'bg-card border-border shadow-lg'"
>
    <div class="flex items-start justify-between mb-4">
        <div>
            <div class="text-lg font-bold text-foreground uppercase">Resumen</div>
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
            ['label' => 'Vehículo',   'icon' => 'truck',          'key' => "vehiculo?.patente ?? '—'",               'num' => false],
            ['label' => 'Servicio',   'icon' => 'clipboard-list', 'key' => "servicioNombre || '—'",                  'num' => false],
            ['label' => 'Origen',     'icon' => 'map-pin',        'key' => "zonaNombre || '—'",                      'num' => false],
            ['label' => 'Turno',      'icon' => 'clock',          'key' => "turno || '—'",                           'num' => false],
            ['label' => 'Tipo',       'icon' => 'tag',            'key' => "vehiculo?.tipo ?? '—'",                  'num' => false],
            ['label' => 'Peso bruto', 'icon' => 'scale',          'key' => "brutoN > 0 ? fmtKg(brutoN) : '—'",      'num' => true],
            ['label' => 'Tara',       'icon' => 'package',        'key' => "vehiculo ? fmtKg(vehiculo.tara) : '—'",  'num' => true],
        ] as $cell)
            <div>
                <div class="flex items-center gap-1 mb-1.5">
                    <x-dynamic-component :component="'lucide-' . $cell['icon']" class="size-3 text-primary" />
                    <span class="text-xs font-semibold uppercase tracking-widest text-primary">{{ $cell['label'] }}</span>
                </div>
                <div class="text-sm font-semibold text-foreground {{ $cell['num'] ? 'tabular-nums font-mono' : '' }}"
                     x-text="{{ $cell['key'] }}"></div>
            </div>
        @endforeach

        {{-- Neto — destacado --}}
        <div class="col-span-2 pt-3 mt-1 border-t border-border/60">
            <div class="flex items-center gap-1 mb-1.5">
                <x-lucide-calculator class="size-3 text-primary" />
                <span class="text-xs font-semibold uppercase tracking-widest text-primary">Neto estimado</span>
            </div>
            <div
                class="tabular-nums font-mono font-bold transition-colors duration-200"
                x-bind:class="canSave ? 'text-success' : 'text-foreground/40'"
                x-text="vehiculo && brutoN > 0 ? fmtKg(neto) : '—'"
            ></div>
        </div>

        {{-- Operador --}}
        <div class="col-span-2 pt-3 border-t border-border/60">
            <div class="flex items-center gap-1 mb-1.5">
                <x-lucide-user class="size-3 text-primary" />
                <span class="text-xs font-semibold uppercase tracking-widest text-primary">Operador</span>
            </div>
            <div class="text-sm font-semibold text-foreground">{{ auth()->user()->name }}</div>
        </div>
    </div>
</div>
