<x-layouts.auth title="Configuración inicial">

    @php
    $steps = [
        ['label' => 'Tu cuenta',      'icon' => 'user'],
        ['label' => 'Preferencias',   'icon' => 'settings-2'],
        ['label' => 'Equipo',         'icon' => 'users'],
        ['label' => 'Confirmación',   'icon' => 'check-circle'],
    ];
    @endphp

    <div
        x-data="{
            step: 1,
            total: {{ count($steps) }},
            progress() { return ((this.step - 1) / (this.total - 1)) * 100 },
            next() { if (this.step < this.total) this.step++ },
            prev() { if (this.step > 1) this.step-- },
        }"
        class="space-y-6"
    >

        {{-- Encabezado + progreso --}}
        <div class="space-y-4">
            <div class="text-center space-y-1">
                <x-ui.typography as="h1">Configuración inicial</x-ui.typography>
                <x-ui.typography as="muted">
                    Paso <span x-text="step" class="font-semibold text-foreground"></span> de {{ count($steps) }}
                </x-ui.typography>
            </div>

            {{-- Progress bar manual (evita :value en Blade component) --}}
            <div class="relative w-full h-2 rounded-full bg-secondary overflow-hidden">
                <div
                    class="h-full rounded-full bg-primary transition-all duration-500 ease-out"
                    :style="`width: ${progress()}%`"
                ></div>
            </div>

            {{-- Step indicators --}}
            <div class="flex items-center justify-between">
                @foreach($steps as $i => $s)
                @php $n = $i + 1; @endphp
                <div class="flex flex-col items-center gap-1">
                    <div
                        class="size-7 rounded-full flex items-center justify-center text-xs font-medium transition-colors"
                        :class="step > {{ $n }}
                            ? 'bg-primary text-primary-foreground'
                            : step === {{ $n }}
                                ? 'bg-primary text-primary-foreground ring-4 ring-primary/20'
                                : 'bg-muted text-muted-foreground'"
                    >
                        <template x-if="step > {{ $n }}">
                            <x-lucide-check class="size-3.5" stroke-width="3" />
                        </template>
                        <template x-if="step <= {{ $n }}">
                            <span>{{ $n }}</span>
                        </template>
                    </div>
                    <span class="text-[10px] text-muted-foreground hidden sm:block">{{ $s['label'] }}</span>
                </div>
                @if($i < count($steps) - 1)
                <div class="flex-1 h-px bg-border mx-1"></div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Card con el contenido del paso --}}
        <x-ui.card>
            <x-ui.card.header>
                <template x-if="step === 1"><div>
                    <x-ui.card.title>Tu cuenta</x-ui.card.title>
                    <x-ui.card.description>Creá tus credenciales de acceso.</x-ui.card.description>
                </div></template>
                <template x-if="step === 2"><div>
                    <x-ui.card.title>Preferencias</x-ui.card.title>
                    <x-ui.card.description>Contanos sobre tu rol y organización.</x-ui.card.description>
                </div></template>
                <template x-if="step === 3"><div>
                    <x-ui.card.title>Tu equipo</x-ui.card.title>
                    <x-ui.card.description>Conectá herramientas y configurá notificaciones.</x-ui.card.description>
                </div></template>
                <template x-if="step === 4"><div>
                    <x-ui.card.title>Todo listo</x-ui.card.title>
                    <x-ui.card.description>Revisá y confirmá tu configuración.</x-ui.card.description>
                </div></template>
            </x-ui.card.header>

            <x-ui.card.content>
                <div x-show="step === 1" x-cloak>@include('onboarding._step-account')</div>
                <div x-show="step === 2" x-cloak>@include('onboarding._step-preferences')</div>
                <div x-show="step === 3" x-cloak>@include('onboarding._step-team')</div>
                <div x-show="step === 4" x-cloak>@include('onboarding._step-confirm')</div>
            </x-ui.card.content>

            <x-ui.card.footer class="justify-between">
                <x-ui.button
                    variant="ghost"
                    @click="prev()"
                    x-show="step > 1"
                    x-cloak
                >
                    <x-lucide-arrow-left class="size-4" />
                    Anterior
                </x-ui.button>
                <div x-show="step === 1" x-cloak></div>

                <template x-if="step < total">
                    <x-ui.button @click="next()">
                        Siguiente
                        <x-lucide-arrow-right class="size-4" />
                    </x-ui.button>
                </template>
                <template x-if="step === total">
                    <x-ui.button
                        @click="$store.toast.success('¡Configuración completada! Bienvenido.')"
                    >
                        <x-lucide-check class="size-4" />
                        Finalizar
                    </x-ui.button>
                </template>
            </x-ui.card.footer>
        </x-ui.card>

    </div>

</x-layouts.auth>
