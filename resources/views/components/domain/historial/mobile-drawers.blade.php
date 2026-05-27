@props(['kpis'])

<div class="sm:hidden">
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button variant="outline" size="sm" class="w-full">
                <x-lucide-chart-bar class="size-3.5" />
                Métricas
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-4">
            <p class="text-label text-base">Resumen del turno</p>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-scale class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">Pesajes</p>
                        <p class="text-2xl font-bold leading-tight">{{ $kpis['total'] }}</p>
                    </div>
                </div>
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-weight class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">Toneladas netas</p>
                        <p class="text-2xl font-bold leading-tight">
                            {{ number_format($kpis['toneladas_netas'], 1, ',', '.') }}
                            <span class="text-sm font-normal text-muted-foreground">t</span>
                        </p>
                    </div>
                </div>
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-chart-bar class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">Promedio neto</p>
                        <p class="text-2xl font-bold leading-tight">
                            {{ number_format($kpis['promedio_kg'], 0, ',', '.') }}
                            <span class="text-sm font-normal text-muted-foreground">kg</span>
                        </p>
                    </div>
                </div>
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-truck class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">En predio</p>
                        <p class="text-2xl font-bold leading-tight">{{ $kpis['en_predio'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.sheet>
</div>
