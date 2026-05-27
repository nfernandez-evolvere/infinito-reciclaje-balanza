@props(['kpisDia', 'kpisMes'])

<div class="sm:hidden">
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button variant="outline" size="sm" class="w-full">
                <x-lucide-chart-bar class="size-3.5" />
                Métricas
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-6 overflow-y-auto max-h-[85vh]">

            {{-- Hoy --}}
            <div class="space-y-3">
                <p class="text-label text-base">Hoy · {{ now()->format('d/m/Y') }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                        <x-lucide-scale class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                        <div>
                            <p class="text-overline">Pesajes</p>
                            <p class="text-2xl font-bold leading-tight">{{ number_format($kpisDia['total']) }}</p>
                            @if($kpisDia['delta'] !== null)
                                <p class="text-xs mt-0.5 {{ $kpisDia['delta'] >= 0 ? 'text-success' : 'text-destructive' }}">
                                    {{ $kpisDia['delta'] >= 0 ? '+' : '' }}{{ $kpisDia['delta'] }}%
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                        <x-lucide-weight class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                        <div>
                            <p class="text-overline">Toneladas netas</p>
                            <p class="text-2xl font-bold leading-tight">
                                {{ number_format($kpisDia['toneladas'], 1, ',', '.') }}
                                <span class="text-sm font-normal text-muted-foreground">t</span>
                            </p>
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                        <x-lucide-trending-up class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                        <div>
                            <p class="text-overline">Promedio / viaje</p>
                            <p class="text-2xl font-bold leading-tight">
                                {{ number_format($kpisDia['promedio'], 2, ',', '.') }}
                                <span class="text-sm font-normal text-muted-foreground">t</span>
                            </p>
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                        <x-lucide-timer class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                        <div>
                            <p class="text-overline">Último pesaje</p>
                            @if($kpisDia['ultimo_hace_min'] === null)
                                <p class="text-base font-medium text-muted-foreground leading-tight">Sin actividad</p>
                            @elseif($kpisDia['ultimo_hace_min'] < 15)
                                <p class="text-2xl font-bold leading-tight text-success">
                                    {{ $kpisDia['ultimo_hace_min'] }}<span class="text-sm font-normal"> min</span>
                                </p>
                            @elseif($kpisDia['ultimo_hace_min'] < 60)
                                <p class="text-2xl font-bold leading-tight text-warning">
                                    {{ $kpisDia['ultimo_hace_min'] }}<span class="text-sm font-normal"> min</span>
                                </p>
                            @else
                                @php $h = intdiv($kpisDia['ultimo_hace_min'], 60); $m = $kpisDia['ultimo_hace_min'] % 60; @endphp
                                <p class="text-2xl font-bold leading-tight text-destructive">
                                    {{ $h }}h{{ $m > 0 ? ' ' . $m . 'min' : '' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <x-ui.separator />

            {{-- Este mes --}}
            <div class="space-y-3">
                <p class="text-label text-base">{{ now()->translatedFormat('F Y') }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                        <x-lucide-calendar-check class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                        <div>
                            <p class="text-overline">Pesajes</p>
                            <p class="text-2xl font-bold leading-tight">{{ number_format($kpisMes['total']) }}</p>
                            @if($kpisMes['delta'] !== null)
                                <p class="text-xs mt-0.5 {{ $kpisMes['delta'] >= 0 ? 'text-success' : 'text-destructive' }}">
                                    {{ $kpisMes['delta'] >= 0 ? '+' : '' }}{{ $kpisMes['delta'] }}%
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                        <x-lucide-package class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                        <div>
                            <p class="text-overline">Toneladas</p>
                            <p class="text-2xl font-bold leading-tight">
                                {{ number_format($kpisMes['toneladas'], 1, ',', '.') }}
                                <span class="text-sm font-normal text-muted-foreground">t</span>
                            </p>
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                        <x-lucide-calendar-days class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                        <div>
                            <p class="text-overline">Días op.</p>
                            <p class="text-2xl font-bold leading-tight">{{ $kpisMes['dias_op'] }}</p>
                            <p class="text-xs mt-0.5 text-muted-foreground">de {{ now()->day }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </x-ui.sheet>
</div>
