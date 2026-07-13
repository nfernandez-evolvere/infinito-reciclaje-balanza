<x-layouts.app title="Pesaje registrado">

<div class="flex flex-col gap-4">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <x-ui.typography as="h2">Pesaje registrado</x-ui.typography>
            <x-ui.typography as="muted">
                {{ $pesaje->created_at->format('d/m/Y · H:i') }} · #{{ strtoupper(substr($pesaje->uuid, 0, 8)) }}
            </x-ui.typography>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="flex items-center gap-3 flex-1 justify-end">
        <x-ui.button variant="outline" href="{{ $routeHistorial }}" class="w-full md:w-auto">
            <x-lucide-history />
            <span>Ver historial</span>
        </x-ui.button>
        @unless($isAdmin)
            <x-ui.button href="{{ route('balanza') }}" class="w-full md:w-auto">
                <x-lucide-plus />
                <span>Registrar otro pesaje</span>
            </x-ui.button>
        @endunless
    </div>

    {{-- Alerta de peso fuera de rango --}}
    @if($pesaje->alerta_peso)
        <x-ui.alert
            state="warning"
            title="Peso fuera del rango habitual"
            description="El peso bruto registrado está fuera del rango esperado para este tipo de vehículo. El pesaje se guardó igualmente."
        />
    @endif

    {{-- Hero: Peso neto --}}
    <x-ui.card variant="elevated">
        <x-ui.card.content class="pt-6">
            <div class="text-overline mb-2">Peso neto</div>
            <div class="text-4xl sm:text-5xl font-bold font-mono tabular-nums {{ $pesaje->alerta_peso ? 'text-warning' : 'text-success' }}">
                {{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg
            </div>
            <div class="flex items-center gap-4 mt-4 text-sm text-muted-foreground">
                <span>Bruto: <b class="font-mono text-foreground">{{ number_format($pesaje->peso_bruto_kg, 0, ',', '.') }} kg</b></span>
                <span class="text-border">·</span>
                <span>Tara: <b class="font-mono text-foreground">{{ number_format($pesaje->peso_tara_kg, 0, ',', '.') }} kg</b></span>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Detalles --}}
    <x-ui.card>
        <x-ui.card.content class="pt-6">
            <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <div class="text-overline mb-1">Vehículo</div>
                    <div class="text-sm font-semibold">{{ $pesaje->vehiculo->patente }}</div>
                    <div class="text-xs text-muted-foreground">
                        Int. {{ $pesaje->vehiculo->numero_interno }} · {{ $pesaje->vehiculo->titular }}
                    </div>
                </div>
                <div>
                    <div class="text-overline mb-1">Tipo</div>
                    <div class="text-sm font-semibold">{{ $pesaje->vehiculo->tipoVehiculo?->nombre ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-overline mb-1">Servicio</div>
                    <div class="text-sm font-semibold">{{ $pesaje->tipoServicio->nombre }}</div>
                </div>
                <div>
                    <div class="text-overline mb-1">Zona</div>
                    <div class="text-sm font-semibold">{{ $pesaje->zona->nombre }}</div>
                </div>
                @if($pesaje->turno)
                    <div class="col-span-2">
                        <div class="text-overline mb-1">Turno</div>
                        <div class="text-sm font-semibold">{{ $pesaje->turno }}</div>
                    </div>
                @endif
                <div class="col-span-2 pt-3 border-t border-border/60">
                    <div class="text-overline mb-1">Operador</div>
                    <div class="text-sm font-semibold">{{ $pesaje->operador->name }}</div>
                </div>
            </div>
        </x-ui.card.content>
        <x-ui.card.footer class="flex items-center justify-end gap-4">
            <x-ui.button href="{{ route('pesajes.edit', $pesaje) }}" class="w-full md:w-auto">
                <x-lucide-pencil />
                <span>Editar pesaje</span>
            </x-ui.button>
        </x-ui.card.footer>
    </x-ui.card>
</div>

</x-layouts.app>
