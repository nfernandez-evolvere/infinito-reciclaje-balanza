@props(['config'])

{{--
    Panel "Secciones" de la configuración de reportes: qué páginas del PDF y qué
    hojas del Excel incluye por defecto el informe mensual. Catálogo canónico en
    App\Support\ReporteSecciones.
--}}
@php
    $seccionesPdfActivas   = $config->seccionesPdf();
    $seccionesExcelActivas = $config->seccionesExcel();
@endphp
<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title>Secciones del reporte</x-ui.card.title>
        <x-ui.card.description>Elegí qué partes incluye el reporte mensual. Los reportes usan esta selección salvo que un programado la personalice.</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <div>
                    <p class="text-label">PDF · páginas</p>
                    <p class="text-caption">La portada y el cierre se incluyen siempre.</p>
                </div>
                @foreach (\App\Support\ReporteSecciones::pdf() as $clave => $meta)
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <x-ui.checkbox
                            name="secciones[pdf][]"
                            value="{{ $clave }}"
                            :checked="in_array($clave, old('secciones.pdf', $seccionesPdfActivas), true)"
                            class="mt-0.5"
                        />
                        <span>
                            <span class="block text-sm font-medium">{{ $meta['label'] }}</span>
                            <span class="block text-caption">{{ $meta['descripcion'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <x-ui.form-field
                class="content-start gap-3"
                :state="$errors->has('secciones.excel') ? 'destructive' : null"
                :message="$errors->first('secciones.excel')"
            >
                <div>
                    <p class="text-label">Excel · hojas</p>
                    <p class="text-caption">El archivo necesita al menos una hoja.</p>
                </div>
                @foreach (\App\Support\ReporteSecciones::excel() as $clave => $meta)
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <x-ui.checkbox
                            name="secciones[excel][]"
                            value="{{ $clave }}"
                            :checked="in_array($clave, old('secciones.excel', $seccionesExcelActivas), true)"
                            class="mt-0.5"
                        />
                        <span>
                            <span class="block text-sm font-medium">{{ $meta['label'] }}</span>
                            <span class="block text-caption">{{ $meta['descripcion'] }}</span>
                        </span>
                    </label>
                @endforeach
            </x-ui.form-field>
        </div>
    </x-ui.card.content>
</x-ui.card>
