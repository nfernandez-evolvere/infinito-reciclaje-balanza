@props(['filtros', 'operarios', 'hayFiltros'])

<x-ui.card variant="elevated" class="hidden sm:block">
    <x-ui.card.content class="p-4">
        <form method="GET" action="{{ route('historial') }}" class="flex flex-wrap gap-3 items-end">
            <div class="space-y-1.5">
                <x-ui.label>Desde</x-ui.label>
                <x-ui.date-picker name="desde" value="{{ $filtros['desde'] }}" size="sm" placeholder="Desde" />
            </div>
            <div class="space-y-1.5">
                <x-ui.label>Hasta</x-ui.label>
                <x-ui.date-picker name="hasta" value="{{ $filtros['hasta'] }}" size="sm" placeholder="Hasta" />
            </div>
            <div class="space-y-1.5 flex-1 min-w-35">
                <x-ui.label>Patente</x-ui.label>
                <x-ui.input type="text" name="patente" value="{{ $filtros['patente'] ?? '' }}" placeholder="ABC 123" size="sm" />
            </div>
            <div class="space-y-1.5 min-w-32.5">
                <x-ui.label>Estado</x-ui.label>
                <x-ui.select name="estado" value="{{ $filtros['estado'] ?? '' }}" size="sm">
                    <x-ui.select.trigger>
                        <x-ui.select.value placeholder="Todos" />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="">Todos</x-ui.select.item>
                        <x-ui.select.item value="En predio">En predio</x-ui.select.item>
                        <x-ui.select.item value="Cerrado">Cerrado</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </div>
            <div class="space-y-1.5 min-w-40">
                <x-ui.label>Operario</x-ui.label>
                <x-ui.select name="operario_id" value="{{ $filtros['operario_id'] ?? '' }}" size="sm">
                    <x-ui.select.trigger>
                        <x-ui.select.value placeholder="Todos" />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="">Todos</x-ui.select.item>
                        @foreach($operarios as $op)
                            <x-ui.select.item value="{{ $op->id }}">{{ $op->name }}</x-ui.select.item>
                        @endforeach
                    </x-ui.select.content>
                </x-ui.select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" size="sm">
                    <x-lucide-search class="size-3.5" />
                    Filtrar
                </x-ui.button>
                @if($hayFiltros)
                    <x-ui.button variant="secondary" size="sm" href="{{ route('historial') }}">
                        <x-lucide-x class="size-3.5" />
                        Limpiar
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card.content>
</x-ui.card>
