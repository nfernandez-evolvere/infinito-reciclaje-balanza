@props(['kpis'])

<template x-teleport="body">
    <div
        x-show="metricasOpen"
        @keydown.escape.window="metricasOpen = false"
        class="fixed inset-0 z-(--z-modal)"
        x-cloak
    >
        <div
            x-show="metricasOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="metricasOpen = false"
            class="absolute inset-0 bg-surface-overlay"
        ></div>

        <div
            x-show="metricasOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="absolute inset-x-0 bottom-0 flex flex-col max-h-[80vh] rounded-t-xl border-t overflow-y-auto bg-background border-border shadow-xl"
        >
            <button
                @click="metricasOpen = false"
                class="absolute right-4 top-4 flex size-7 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors z-10"
                aria-label="Cerrar"
            >
                <x-lucide-x class="size-4" />
            </button>

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
        </div>
    </div>
</template>
