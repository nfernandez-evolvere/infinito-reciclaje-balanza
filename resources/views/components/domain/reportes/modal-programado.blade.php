@props([])

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
                            <x-ui.select.item value="informe_mensual">Informe mensual</x-ui.select.item>
                            <x-ui.select.item value="alertas">Alertas</x-ui.select.item>
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
                            <x-ui.select.item value="mensual">Mensual</x-ui.select.item>
                            <x-ui.select.item value="semanal">Semanal</x-ui.select.item>
                            <x-ui.select.item value="custom">Custom (cron)</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>

                <div x-show="form.frecuencia === 'custom'" x-cloak>
                    <x-ui.form-field
                        for="m-cron"
                        :state="$errors->has('cron_expresion') ? 'destructive' : null"
                        :message="$errors->first('cron_expresion')"
                    >
                        <x-ui.label for="m-cron">Expresión cron</x-ui.label>
                        <x-ui.input
                            id="m-cron"
                            name="cron_expresion"
                            x-model="form.cron_expresion"
                            placeholder="0 8 1 * *"
                            :state="$errors->has('cron_expresion') ? 'destructive' : null"
                        />
                        <p class="text-caption">Formato: minuto hora día-mes mes día-semana</p>
                    </x-ui.form-field>
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
