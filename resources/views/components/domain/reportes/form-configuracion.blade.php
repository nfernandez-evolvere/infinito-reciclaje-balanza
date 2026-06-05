@props(['config'])

<form method="POST" action="{{ route('admin.reportes.configuracion.update') }}">
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-6">

        {{-- Identidad --}}
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Identidad del informe</x-ui.card.title>
                <x-ui.card.description>Datos que aparecen en la portada y pie de cada página.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <div class="space-y-4">
                    <x-ui.form-field for="municipalidad_nombre">
                        <x-ui.label for="municipalidad_nombre">Nombre del municipio</x-ui.label>
                        <x-ui.input
                            id="municipalidad_nombre"
                            name="municipalidad_nombre"
                            :value="old('municipalidad_nombre', $config->municipalidad_nombre)"
                            placeholder="Ej: Municipalidad de Corrientes"
                        />
                    </x-ui.form-field>
                    <x-ui.form-field for="intro_empresa">
                        <x-ui.label for="intro_empresa">Texto de presentación (Quiénes Somos)</x-ui.label>
                        <x-ui.textarea
                            id="intro_empresa"
                            name="intro_empresa"
                            rows="4"
                            placeholder="Describí brevemente la empresa y su propósito..."
                        >{{ old('intro_empresa', $config->intro_empresa) }}</x-ui.textarea>
                        <p class="text-caption">Aparece en la segunda página del informe PDF.</p>
                    </x-ui.form-field>
                </div>
            </x-ui.card.content>
        </x-ui.card>

        {{-- Servicios --}}
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Servicios destacados</x-ui.card.title>
                <x-ui.card.description>Aparecen como cards en la sección "Quiénes Somos" del informe.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <div class="space-y-3">
                    <template x-for="(s, i) in servicios" :key="i">
                        <div class="flex gap-3 items-start p-3 border border-border rounded-lg">
                            <div class="flex-1 space-y-2">
                                <x-ui.input
                                    x-bind:name="`servicios[${i}][titulo]`"
                                    x-model="s.titulo"
                                    placeholder="Título del servicio"
                                />
                                <x-ui.textarea
                                    x-bind:name="`servicios[${i}][descripcion]`"
                                    x-model="s.descripcion"
                                    placeholder="Descripción breve"
                                    size="sm"
                                    rows="3"
                                />
                            </div>
                            <x-ui.button type="button" variant="ghost" size="sm" @click="removeServicio(i)" class="shrink-0 mt-1 w-8 px-0">
                                <x-lucide-x class="size-4" />
                            </x-ui.button>
                        </div>
                    </template>
                    <x-ui.button type="button" @click="addServicio()" x-show="servicios.length < 6">
                        <x-lucide-plus class="size-4" />
                        Agregar servicio
                    </x-ui.button>
                </div>
            </x-ui.card.content>
        </x-ui.card>

        {{-- Inteligencia Artificial --}}
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Inteligencia Artificial</x-ui.card.title>
                <x-ui.card.description>Generación automática de conclusiones usando Gemini. La API key se guarda encriptada.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-label">Habilitar IA</p>
                            <p class="text-caption">Genera conclusiones automáticas en el informe PDF.</p>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <x-ui.switch name="ai_enabled" :checked="$config->ai_enabled" x-model="aiEnabled" />
                        </label>
                    </div>
                    <div x-show="aiEnabled" x-cloak class="space-y-4 pt-2 border-t border-border">
                        <x-ui.form-field for="ai_modelo">
                            <x-ui.label for="ai_modelo">Modelo Gemini</x-ui.label>
                            <x-ui.select name="ai_modelo" :value="old('ai_modelo', $config->ai_modelo ?? 'gemini-2.5-flash')">
                                <x-ui.select.trigger id="ai_modelo">
                                    <x-ui.select.value placeholder="Seleccionar modelo" />
                                </x-ui.select.trigger>
                                <x-ui.select.content>
                                    <x-ui.select.item value="gemini-2.5-flash">gemini-2.5-flash (20 rpd)</x-ui.select.item>
                                    <x-ui.select.item value="gemini-2.5-flash-lite">gemini-2.5-flash-lite (20 rpd)</x-ui.select.item>
                                    <x-ui.select.item value="gemini-3.1-flash-lite">gemini-3.1-flash-lite (500 rpd)</x-ui.select.item>
                                </x-ui.select.content>
                            </x-ui.select>
                        </x-ui.form-field>
                        <x-ui.form-field for="ai_api_key">
                            <x-ui.label for="ai_api_key">API Key de Google AI Studio</x-ui.label>
                            <x-ui.input
                                id="ai_api_key"
                                name="ai_api_key"
                                type="password"
                                placeholder="{{ $config->ai_api_key ? '••••••••••••••••••••••••••' : 'AIza...' }}"
                                autocomplete="off"
                            />
                            <p class="text-caption">Dejá vacío para mantener la key actual. Obtené una gratis en <strong>aistudio.google.com</strong>.</p>
                        </x-ui.form-field>

                        <div class="grid gap-1.5">
                            <label for="ai_prompt" class="text-sm font-medium">Prompt de análisis</label>
                            <textarea
                                id="ai_prompt"
                                name="ai_prompt"
                                rows="6"
                                placeholder="Dejar vacío para usar el prompt por defecto."
                                class="flex w-full rounded-2xl border border-input bg-background text-foreground shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-y min-h-20 px-3 py-2 text-base sm:text-sm"
                            >{{ old('ai_prompt', $config->ai_prompt ?? '') }}</textarea>
                            <p class="text-xs text-muted-foreground">
                                Variables disponibles: {{ implode(', ', \App\Services\ConclusionesAIService::variablesDisponibles()) }}
                            </p>
                        </div>

                    </div>
                </div>
            </x-ui.card.content>
        </x-ui.card>

        {{-- Tipos de reporte --}}
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Tipos de reporte activos</x-ui.card.title>
                <x-ui.card.description>Controlá qué tipos de reportes están disponibles para generar y programar.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-label">Informe mensual (PDF)</p>
                            <p class="text-caption">Presentación con gráficos, tablas y conclusiones para el municipio.</p>
                        </div>
                        <x-ui.switch name="tipo_informe_mensual_activo" :checked="$config->tipo_informe_mensual_activo ?? true" />
                    </div>
                    <x-ui.separator />
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-label">Reporte de alertas</p>
                            <p class="text-caption">Lista de pesajes con alertas de peso y gaps operativos.</p>
                        </div>
                        <x-ui.switch name="tipo_alertas_activo" :checked="$config->tipo_alertas_activo ?? false" />
                    </div>
                </div>
            </x-ui.card.content>
        </x-ui.card>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            @if(app()->isLocal())
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.reportes.preview') }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-dashed border-border px-3 py-1 text-xs font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                        <x-lucide-eye class="size-3" />
                        Preview estilos
                    </a>
                    <a href="{{ route('admin.reportes.preview-pdf') }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-full border border-dashed border-border px-3 py-1 text-xs font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                        <x-lucide-file-text class="size-3" />
                        Preview PDF
                    </a>
                </div>
            @else
                <span></span>
            @endif
            <x-ui.button type="submit" class="self-end sm:self-auto">
                <x-lucide-save class="size-4" />
                Guardar configuración
            </x-ui.button>
        </div>

    </div>
</form>
