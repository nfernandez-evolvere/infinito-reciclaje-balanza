@props(['filtros', 'operarios', 'hayFiltros', 'zonas' => collect(), 'tiposServicio' => collect(), 'sortDirection' => 'desc', 'control' => 'filterOpen'])

@php
    // Tab «Modificaciones» de la pantalla de Pesajes: usa parámetros con prefijo `m_`
    // para no colisionar con el tab «Pesajes». Las claves de $filtros siguen siendo
    // canónicas (las arma el controller); sólo los names de inputs y de la URL son `m_*`.
    $route = route('admin.pesajes.index', ['tab' => 'modificaciones']);

    // Quita un filtro preservando el resto del query string (incluido el estado del tab Pesajes).
    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge($overrides, ['m_page' => null]));

    $chips = [];

    if (!empty($filtros['tipo'])) {
        $chips[] = [
            'label' => $filtros['tipo'] === 'editado' ? 'Editados' : 'Cancelados',
            'url'   => $removeUrl(['m_tipo' => null]),
        ];
    }

    if ($filtros['desde'] || $filtros['hasta']) {
        $label = match(true) {
            (bool) $filtros['desde'] && (bool) $filtros['hasta'] && $filtros['desde'] === $filtros['hasta']
                => \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y'),
            (bool) $filtros['desde'] && (bool) $filtros['hasta']
                => \Carbon\Carbon::parse($filtros['desde'])->format('d/m') . ' – ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m'),
            (bool) $filtros['desde']
                => 'Desde ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m'),
            default
                => 'Hasta ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m'),
        };
        $chips[] = [
            'label' => $label,
            'url'   => $removeUrl(['m_desde' => null, 'm_hasta' => null]),
        ];
    }

    if (!empty($filtros['patente'])) {
        $chips[] = ['label' => strtoupper($filtros['patente']), 'url' => $removeUrl(['m_patente' => null])];
    }

    if (!empty($filtros['operario_id'])) {
        $op = $operarios->firstWhere('id', $filtros['operario_id']);
        $chips[] = ['label' => $op?->name ?? 'Operario', 'url' => $removeUrl(['m_operario_id' => null])];
    }

    if (!empty($filtros['zona_id'])) {
        $zona = $zonas->firstWhere('id', $filtros['zona_id']);
        $chips[] = ['label' => $zona?->nombre ?? 'Origen', 'url' => $removeUrl(['m_zona_id' => null])];
    }

    if (!empty($filtros['tipo_servicio_id'])) {
        $ts = $tiposServicio->firstWhere('id', $filtros['tipo_servicio_id']);
        $chips[] = ['label' => $ts?->nombre ?? 'Servicio', 'url' => $removeUrl(['m_tipo_servicio_id' => null])];
    }

    $storageKey = 'filtros-mod:' . (request()->route()?->getName() ?? 'modificaciones');
@endphp

{{-- ── Mobile (<md): botón que abre el sheet ─────────────────────── --}}
<div class="md:hidden">
    <div class="relative inline-flex">
        <x-ui.button variant="outline" class="hover:bg-accent hover:text-accent-foreground" @click="{{ $control }} = true">
            <x-lucide-sliders-horizontal class="size-4" />
            Filtros
        </x-ui.button>
        @if($hayFiltros)
            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-background text-[10px] font-semibold leading-none">
                {{ count($chips) }}
            </span>
        @endif
    </div>
</div>

<x-ui.filter-sheet controlledBy="{{ $control }}" :action="$route" :resetUrl="$route">
    <x-domain.modificaciones.filtros.campos :filtros="$filtros" :operarios="$operarios" :zonas="$zonas" :tiposServicio="$tiposServicio" :sortDirection="$sortDirection" />
</x-ui.filter-sheet>

{{-- ── Tablet / Desktop (md+): toggle + card de filtros ─────────────── --}}
<x-ui.filter-panel :action="$route" :resetUrl="$route" :storageKey="$storageKey" :hasFilters="(bool) $hayFiltros">
    @if(count($chips))
        <x-slot:chips>
            @foreach($chips as $chip)
                <a
                    href="{{ $chip['url'] }}"
                    class="inline-flex items-center gap-1 rounded-full border border-border bg-muted/40 pl-2.5 pr-1.5 py-1 text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                >
                    <span class="truncate max-w-40">{{ $chip['label'] }}</span>
                    <x-lucide-x class="size-3 shrink-0" />
                </a>
            @endforeach
        </x-slot:chips>
    @endif

    <x-domain.modificaciones.filtros.campos :filtros="$filtros" :operarios="$operarios" :zonas="$zonas" :tiposServicio="$tiposServicio" :sortDirection="$sortDirection" />
</x-ui.filter-panel>
