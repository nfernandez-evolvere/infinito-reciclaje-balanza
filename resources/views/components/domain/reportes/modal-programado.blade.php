@props(['config'])

<x-ui.sheet controlled-by="modalOpen" side="right">

    <div class="flex flex-col h-full">

        {{-- Header --}}
        <div class="p-6 pr-12 border-b border-border shrink-0">
            <h2 class="text-h4" x-text="modalMode === 'create' ? 'Nuevo programado' : 'Editar programado'"></h2>
            <p class="text-caption mt-1">Configurá el envío automático de este reporte por email.</p>
        </div>

        {{-- Form --}}
        <form
            :action="modalMode === 'create' ? '{{ route('admin.reportes.programados.store') }}' : `/admin/reportes/programados/${form.id}`"
            method="POST"
            class="flex flex-col flex-1 overflow-y-auto"
        >
            @csrf
            <template x-if="modalMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="_mode" :value="modalMode">
            <input type="hidden" name="_editing_id" :value="form.id ?? ''">

            <div class="flex-1 p-6 space-y-4">

                <x-ui.form-field
                    for="m-nombre"
                    :state="$errors->has('nombre') ? 'destructive' : null"
                    :message="$errors->first('nombre')"
                >
                    <x-ui.label for="m-nombre">Nombre</x-ui.label>
                    <x-ui.input
                        id="m-nombre"
                        name="nombre"
                        x-model="form.nombre"
                        placeholder="Ej: Informe mensual municipio"
                        :state="$errors->has('nombre') ? 'destructive' : null"
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="m-tipo"
                    :state="$errors->has('tipo') ? 'destructive' : null"
                    :message="$errors->first('tipo')"
                >
                    <x-ui.label for="m-tipo">Tipo de reporte</x-ui.label>
                    <x-ui.select name="tipo" x-modelable="value" x-model="form.tipo">
                        <x-ui.select.trigger id="m-tipo" :state="$errors->has('tipo') ? 'destructive' : null">
                            <x-ui.select.value placeholder="Seleccionar tipo" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            @if($config->tipo_informe_mensual_activo ?? true)
                                <x-ui.select.item value="informe_mensual">Informe</x-ui.select.item>
                            @endif
                            @if($config->tipo_alertas_activo ?? false)
                                <x-ui.select.item value="alertas">Alertas</x-ui.select.item>
                            @endif
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>

                <x-ui.form-field
                    for="m-frecuencia"
                    :state="$errors->has('frecuencia') ? 'destructive' : null"
                    :message="$errors->first('frecuencia')"
                >
                    <x-ui.label for="m-frecuencia">Frecuencia</x-ui.label>
                    <x-ui.select name="frecuencia" x-modelable="value" x-model="form.frecuencia">
                        <x-ui.select.trigger id="m-frecuencia" :state="$errors->has('frecuencia') ? 'destructive' : null">
                            <x-ui.select.value placeholder="Seleccionar frecuencia" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="diaria">Diaria — último día</x-ui.select.item>
                            <x-ui.select.item value="semanal">Semanal — últimos 7 días</x-ui.select.item>
                            <x-ui.select.item value="quincenal">Quincenal — últimos 15 días</x-ui.select.item>
                            <x-ui.select.item value="mensual">Mensual — últimos 30 días</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>

                {{-- Formatos del envío — solo aplica al informe mensual (las alertas van siempre en PDF) --}}
                <div x-show="form.tipo === 'informe_mensual'" x-cloak class="space-y-2">
                    <x-ui.form-field
                        :state="$errors->has('formatos') ? 'destructive' : null"
                        :message="$errors->first('formatos')"
                    >
                        <x-ui.label>Formatos del envío</x-ui.label>
                        <div class="flex flex-col gap-2.5 pt-1">
                            @foreach (['pdf' => 'PDF', 'excel' => 'Excel'] as $value => $label)
                                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <button
                                        type="button"
                                        role="checkbox"
                                        :aria-checked="form.formatos.includes('{{ $value }}') ? 'true' : 'false'"
                                        @click="toggleFormato('{{ $value }}')"
                                        :class="form.formatos.includes('{{ $value }}') ? 'bg-primary border-primary text-primary-foreground' : 'bg-background border-input'"
                                        class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        <x-lucide-check class="size-3" stroke-width="3" x-show="form.formatos.includes('{{ $value }}')" x-cloak />
                                    </button>
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-caption">Se adjuntan al email. Elegí al menos uno.</p>
                    </x-ui.form-field>

                    <template x-for="f in form.formatos" :key="f">
                        <input type="hidden" name="formatos[]" :value="f">
                    </template>
                </div>

                <x-ui.form-field
                    :state="$errors->has('destinatarios') ? 'destructive' : null"
                    :message="$errors->first('destinatarios')"
                >
                    <x-ui.label>Destinatarios</x-ui.label>
                    <x-ui.tags-input
                        name="destinatarios"
                        placeholder="email@ejemplo.com"
                        fetch-url="{{ route('admin.reportes.destinatarios.index') }}"
                        :state="$errors->has('destinatarios') ? 'destructive' : null"
                    />
                    <p class="text-caption">Enter o coma para confirmar cada email.</p>
                </x-ui.form-field>

                <div class="flex items-center justify-between py-1">
                    <div>
                        <p class="text-label">Activo</p>
                        <p class="text-caption">El envío se ejecutará automáticamente.</p>
                    </div>
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="form.activo ? 'true' : 'false'"
                        :data-state="form.activo ? 'checked' : 'unchecked'"
                        @click="form.activo = !form.activo"
                        :class="form.activo ? 'bg-primary' : 'bg-input'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-ring"
                    >
                        <span
                            :data-state="form.activo ? 'checked' : 'unchecked'"
                            class="pointer-events-none inline-block size-5 rounded-full bg-background shadow-sm ring-0 transition-transform translate-x-0 data-[state=checked]:translate-x-5"
                        ></span>
                        <input type="hidden" name="activo" :value="form.activo ? '1' : '0'">
                    </button>
                </div>

            </div>

            {{-- Footer --}}
            <div class="shrink-0 flex gap-2 p-6 border-t border-border">
                <x-ui.button type="button" variant="ghost" @click="modalOpen = false" class="w-full">
                    <x-lucide-x class="size-4" />
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" class="w-full">
                    <x-lucide-save class="size-4" />
                    <span x-text="modalMode === 'create' ? 'Crear' : 'Guardar'"></span>
                </x-ui.button>
            </div>

        </form>

    </div>

</x-ui.sheet>
