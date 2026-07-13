@props(['config'])

{{--
    Panel "Identidad" de la configuración de reportes. Reúne la marca del informe
    (nombre del municipio + presentación) y los servicios destacados que arman la
    página "Quiénes somos" del PDF. Vive dentro del x-data reportesConfiguracion
    del orquestador (usa `servicios`, `addServicio`, `removeServicio`).
--}}
<div class="flex flex-col gap-6">
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Identidad del reporte</x-ui.card.title>
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
                    <p class="text-caption">Aparece en la segunda página del reporte PDF.</p>
                </x-ui.form-field>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Servicios destacados</x-ui.card.title>
            <x-ui.card.description>Aparecen como cards en la sección "Quiénes Somos" del reporte.</x-ui.card.description>
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
</div>
