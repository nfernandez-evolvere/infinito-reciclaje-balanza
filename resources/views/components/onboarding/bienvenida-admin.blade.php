@props(['forzar' => false])

<div
    x-data="{
        abierto: {{ $forzar ? 'true' : 'false' }},
        async cerrar() {
            this.abierto = false;
            @if($forzar)
            await fetch('{{ route('admin.onboarding.visto') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' },
            });
            @endif
        }
    }"
    @abrir-onboarding-admin.window="abierto = true"
>
    <template x-teleport="body">
        <div
            x-show="abierto"
            x-cloak
            @keydown.escape.window="cerrar()"
            class="fixed inset-0 z-(--z-modal) flex items-center justify-center p-4"
        >
            {{-- Overlay --}}
            <div
                x-show="abierto"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="cerrar()"
                class="absolute inset-0 bg-surface-overlay"
            ></div>

            {{-- Panel --}}
            <div
                x-show="abierto"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                role="dialog"
                aria-modal="true"
                class="relative z-10 w-full max-w-lg rounded-xl border border-border bg-background shadow-xl overflow-hidden"
                @click.stop
            >
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-border">
                    <div class="flex items-center gap-3">
                        <div class="size-9 rounded-lg bg-primary flex items-center justify-center">
                            <x-lucide-layout-dashboard class="size-5 text-primary-foreground" />
                        </div>
                        <div>
                            <h2 class="text-h4">Bienvenido al panel de administración</h2>
                            <p class="text-xs text-muted-foreground">Infinito Reciclaje</p>
                        </div>
                    </div>
                </div>

                {{-- Pasos --}}
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-muted-foreground">Tres cosas para arrancar. Seguí el orden y el sistema va a estar listo para operar.</p>

                    <div class="space-y-3">
                        {{-- Paso 1 --}}
                        <div class="flex gap-3 rounded-lg border border-border p-3">
                            <div class="size-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-primary">1</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Cargá el padrón antes del día 1</p>
                                <p class="text-xs text-muted-foreground mt-0.5">El orden importa: tipos de vehículo → servicios → zonas → vehículos → usuarios. Sin estos datos el operador no puede registrar pesajes. La barra de progreso en el menú lateral te muestra cuántos pasos completaste.</p>
                            </div>
                        </div>
                        {{-- Paso 2 --}}
                        <div class="flex gap-3 rounded-lg border border-border p-3">
                            <div class="size-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-primary">2</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Seguí la operación desde el Dashboard</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Muestra en tiempo real los camiones en el predio, los KPIs del día y las alertas activas. En Pesajes podés editar cualquier registro si hay un error.</p>
                            </div>
                        </div>
                        {{-- Paso 3 --}}
                        <div class="flex gap-3 rounded-lg border border-border p-3">
                            <div class="size-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-primary">3</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Generá el reporte mensual para el municipio</p>
                                <p class="text-xs text-muted-foreground mt-0.5">En Reportes seleccionás el período, aplicás filtros y exportás en PDF o Excel. También podés programar el envío automático por email.</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-border">
                    <a href="{{ route('manual.show', 'onboarding-admin') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                        Ver guía completa
                    </a>
                    <x-ui.button @click="cerrar()">Entendido</x-ui.button>
                </div>
            </div>
        </div>
    </template>
</div>
