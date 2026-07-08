@props(['reporte', 'filters', 'zonas', 'tiposServicio', 'tiposVehiculo', 'activeFilters', 'config'])

@if($reporte)
    @php
        $exportParams = array_filter(request()->only(['desde', 'hasta', 'zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']));
        $pills = array_filter([
            $filters['zona_id']          ? $zonas->firstWhere('id', $filters['zona_id'])?->nombre           : null,
            $filters['tipo_servicio_id'] ? $tiposServicio->firstWhere('id', $filters['tipo_servicio_id'])?->nombre : null,
            $filters['tipo_vehiculo_id'] ? $tiposVehiculo->firstWhere('id', $filters['tipo_vehiculo_id'])?->nombre : null,
        ]);
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-overline">Reporte de recolección</p>
            <h2 class="text-h3 mt-1">
                {{ $reporte['desde']->translatedFormat('d M') }} — {{ $reporte['hasta']->translatedFormat('d M Y') }}
            </h2>
            @if(!empty($pills))
                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                    @foreach($pills as $pill)
                        <x-ui.badge variant="secondary">{{ $pill }}</x-ui.badge>
                    @endforeach
                </div>
            @endif
        </div>

        <div
            class="flex items-center justify-end gap-2 sm:shrink-0"
            x-data="seccionesExport({
                catalogo: {{ Js::from(['pdf' => \App\Support\ReporteSecciones::pdfKeys(), 'excel' => \App\Support\ReporteSecciones::excelKeys()]) }},
                general:  {{ Js::from($config->secciones()) }},
                urls:     {{ Js::from(['pdf' => route('admin.reportes.pdf-v2', $exportParams), 'excel' => route('admin.reportes.excel-v2', $exportParams)]) }},
            })"
        >
            {{-- KPIs (mobile/tablet) + cambiar período: ghost solo-ícono, estilo dashboard --}}
            <x-domain.reportes.mobile-kpis :kpis="$reporte['kpis']" />

            <x-ui.button variant="ghost" size="icon" @click="filterOpen = true" class="relative md:hidden" aria-label="Cambiar período">
                <x-lucide-calendar-days class="size-4" />
                @if($activeFilters > 0)
                    <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground leading-none">
                        {{ $activeFilters }}
                    </span>
                @endif
            </x-ui.button>

            {{-- Secciones de la descarga: ajuste ad-hoc sobre la configuración general --}}
            <x-ui.popover align="end" width="w-80">
                <x-slot:trigger>
                    <x-ui.tooltip content="Secciones de la descarga">
                        <x-ui.button variant="ghost" size="icon" class="relative" aria-label="Secciones de la descarga">
                            <x-lucide-sliders-horizontal class="size-4" />
                            <span x-show="ajustado()" x-cloak class="absolute -top-0.5 -right-0.5 size-2.5 rounded-full bg-primary ring-2 ring-background"></span>
                        </x-ui.button>
                    </x-ui.tooltip>
                </x-slot:trigger>

                <div class="flex flex-col gap-4">
                    <div>
                        <p class="text-label">Secciones de la descarga</p>
                        <p class="text-caption mt-0.5">Solo para esta descarga — no cambia la configuración general.</p>
                    </div>

                    <div class="space-y-2.5">
                        <p class="text-caption font-semibold uppercase tracking-widest">PDF · páginas</p>
                        @foreach (\App\Support\ReporteSecciones::pdf() as $clave => $meta)
                            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                <button
                                    type="button"
                                    role="checkbox"
                                    :aria-checked="pdf.includes('{{ $clave }}') ? 'true' : 'false'"
                                    @click="toggleSeccion('pdf', '{{ $clave }}')"
                                    :class="pdf.includes('{{ $clave }}') ? 'bg-primary border-primary text-primary-foreground' : 'bg-background border-input'"
                                    class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <x-lucide-check class="size-3" stroke-width="3" x-show="pdf.includes('{{ $clave }}')" x-cloak />
                                </button>
                                <span class="text-sm">{{ $meta['label'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <x-ui.separator />

                    <div class="space-y-2.5">
                        <p class="text-caption font-semibold uppercase tracking-widest">Excel · hojas</p>
                        @foreach (\App\Support\ReporteSecciones::excel() as $clave => $meta)
                            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                <button
                                    type="button"
                                    role="checkbox"
                                    :aria-checked="excel.includes('{{ $clave }}') ? 'true' : 'false'"
                                    @click="toggleSeccion('excel', '{{ $clave }}')"
                                    :class="excel.includes('{{ $clave }}') ? 'bg-primary border-primary text-primary-foreground' : 'bg-background border-input'"
                                    class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <x-lucide-check class="size-3" stroke-width="3" x-show="excel.includes('{{ $clave }}')" x-cloak />
                                </button>
                                <span class="text-sm">{{ $meta['label'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between gap-2" x-show="ajustado()" x-cloak>
                        <p class="text-caption">Selección ajustada para esta descarga.</p>
                        <x-ui.button variant="ghost" size="sm" @click="restablecer()">Restablecer</x-ui.button>
                    </div>
                </div>
            </x-ui.popover>

            {{-- Exportar: descarga directa del formato v2 con las secciones elegidas --}}
            <x-ui.button
                variant="outline"
                as="a"
                x-bind:href="url('excel')"
                x-bind:class="excel.length === 0 ? 'pointer-events-none opacity-50' : ''"
                x-bind:aria-disabled="excel.length === 0 ? 'true' : 'false'"
            >
                <x-lucide-table class="size-4" />
                <span>Excel</span>
            </x-ui.button>

            <x-ui.button
                as="a"
                x-bind:href="url('pdf')"
                x-bind:class="pdf.length === 0 ? 'pointer-events-none opacity-50' : ''"
                x-bind:aria-disabled="pdf.length === 0 ? 'true' : 'false'"
            >
                <x-lucide-file-text class="size-4" />
                <span>PDF</span>
            </x-ui.button>
        </div>
    </div>
@endif
