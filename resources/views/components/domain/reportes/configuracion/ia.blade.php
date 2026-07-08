@props(['config'])

{{--
    Panel "Inteligencia artificial": conclusiones automáticas del informe con
    Gemini. Vive dentro del x-data reportesConfiguracion (usa `aiEnabled` para
    mostrar/ocultar los campos de la integración).
--}}
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
                    <p class="text-caption">Genera conclusiones automáticas en el reporte PDF.</p>
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
