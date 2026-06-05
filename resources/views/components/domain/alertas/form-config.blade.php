@props(['config' => []])

@php
    $labels = [
        'peso_fuera_rango'        => 'Peso fuera de rango',
        'volumen_diario_atipico'  => 'Volumen diario atípico',
        'gap_registro'            => 'Sin actividad en horario operativo',
        'frecuencia_zona_atipica' => 'Frecuencia por zona atípica',
    ];
    $iconos = [
        'peso_fuera_rango'        => 'scale',
        'volumen_diario_atipico'  => 'trending-up',
        'gap_registro'            => 'clock',
        'frecuencia_zona_atipica' => 'map-pin',
    ];
@endphp

<form method="POST" action="{{ route('admin.alertas.configuracion.update') }}">
    @csrf @method('PUT')
    <input type="hidden" name="tab" value="configuracion">

    <div class="flex flex-col gap-6">

        @foreach($config as $tipo => $cfg)
            <x-ui.card>
                <x-ui.card.header>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <x-ui.card.title>
                                <div class="flex items-center gap-2">
                                    {{ $labels[$tipo] ?? $tipo }}
                                </div>
                            </x-ui.card.title>
                            <x-ui.card.description class="mt-1">{{ $cfg['descripcion'] }}</x-ui.card.description>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <x-ui.label class="hidden sm:block">Activa</x-ui.label>
                            <input type="hidden" name="config[{{ $tipo }}][activo]" value="0">
                            <x-ui.switch
                                name="config[{{ $tipo }}][activo]"
                                :checked="$cfg['activo']"
                            />
                        </div>
                    </div>
                </x-ui.card.header>

                @if($cfg['umbral_label'] || isset($cfg['hora_inicio']))
                    <x-ui.card.content class="pt-0">
                        <x-ui.separator class="mb-4" />
                        <div class="flex flex-col gap-4">
                            @if($cfg['umbral_label'])
                                <x-ui.form-field class="max-w-xs">
                                    <x-ui.label>{{ $cfg['umbral_label'] }}</x-ui.label>
                                    <x-ui.input
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        name="config[{{ $tipo }}][umbral_valor]"
                                        :value="$cfg['umbral_valor'] ?? ''"
                                    />
                                </x-ui.form-field>
                            @endif

                            @isset($cfg['hora_inicio'])
                                @php
                                    $errorHorario = $errors->first('config.'.$tipo.'.hora_inicio')
                                        ?: $errors->first('config.'.$tipo.'.hora_fin');
                                @endphp
                                <div class="space-y-2">
                                    <div>
                                        <x-ui.label>Horario operativo</x-ui.label>
                                        <p class="text-caption mt-0.5">Rango horario en el que se evalúa la falta de actividad. Fuera de este horario, la ausencia de pesajes no genera alertas.</p>
                                    </div>
                                    <div class="flex items-end gap-3">
                                        <div class="grid gap-1.5">
                                            <x-ui.label class="text-muted-foreground font-normal">Desde</x-ui.label>
                                            <x-ui.input
                                                type="time"
                                                class="w-32"
                                                :state="$errorHorario ? 'destructive' : null"
                                                name="config[{{ $tipo }}][hora_inicio]"
                                                :value="old('config.'.$tipo.'.hora_inicio', $cfg['hora_inicio'])"
                                            />
                                        </div>
                                        <div class="grid gap-1.5">
                                            <x-ui.label class="text-muted-foreground font-normal">Hasta</x-ui.label>
                                            <x-ui.input
                                                type="time"
                                                class="w-32"
                                                :state="$errorHorario ? 'destructive' : null"
                                                name="config[{{ $tipo }}][hora_fin]"
                                                :value="old('config.'.$tipo.'.hora_fin', $cfg['hora_fin'])"
                                            />
                                        </div>
                                    </div>
                                    @if($errorHorario)
                                        <x-ui.helper-text state="destructive" :message="$errorHorario" />
                                    @endif
                                </div>
                            @endisset
                        </div>
                    </x-ui.card.content>
                @endif
            </x-ui.card>
        @endforeach

        <div class="flex justify-end">
            <x-ui.button type="submit">
                <x-lucide-save class="size-4" />
                Guardar configuración
            </x-ui.button>
        </div>

    </div>
</form>
