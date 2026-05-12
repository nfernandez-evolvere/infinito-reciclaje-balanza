{{--
Partial: toolbar de filtros de miembros.
Usa el scope Alpine del contenedor padre (search, roleFilter, statusFilter).
No tiene @props — accede a las variables del contexto directamente.
--}}
<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">

    {{-- Búsqueda --}}
    <div class="flex-1">
        <x-ui.input type="search" placeholder="Buscar por nombre o email..." x-model="search">
            <x-slot:leading>
                <x-lucide-search class="size-full" />
            </x-slot:leading>
        </x-ui.input>
    </div>

    {{-- Filtro rol --}}
    <div @select-change.stop="roleFilter = $event.detail.value" class="w-full sm:w-44">
        <x-ui.select value="">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos los roles" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="admin">Administrador</x-ui.select.item>
                <x-ui.select.item value="editor">Editor</x-ui.select.item>
                <x-ui.select.item value="member">Miembro</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </div>

    {{-- Filtro estado --}}
    <div @select-change.stop="statusFilter = $event.detail.value" class="w-full sm:w-44">
        <x-ui.select value="">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos los estados" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="active">Activo</x-ui.select.item>
                <x-ui.select.item value="pending">Pendiente</x-ui.select.item>
                <x-ui.select.item value="inactive">Inactivo</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </div>

    {{-- Sheet: filtros avanzados --}}
    <x-ui.sheet>
        <x-slot:trigger>
            <x-ui.button variant="outline" class="w-full sm:w-auto">
                <x-lucide-sliders-horizontal class="size-4" />
                Filtros avanzados
            </x-ui.button>
        </x-slot:trigger>

        <x-ui.sheet.header class="px-6 pt-6 pb-2">
            <x-ui.typography as="h3">Filtros avanzados</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">Refiná tu búsqueda con filtros adicionales.</x-ui.typography>
        </x-ui.sheet.header>

        <x-ui.sheet.content class="space-y-4">

            <x-ui.form-field for="sh-search">
                <x-ui.label for="sh-search">Buscar</x-ui.label>
                <x-ui.input id="sh-search" type="search" placeholder="Nombre o email..." x-model="search">
                    <x-slot:leading><x-lucide-search class="size-full" /></x-slot:leading>
                </x-ui.input>
            </x-ui.form-field>

            <x-ui.form-field>
                <x-ui.label>Rol</x-ui.label>
                <div @select-change.stop="roleFilter = $event.detail.value">
                    <x-ui.select value="">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Todos los roles" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="admin">Administrador</x-ui.select.item>
                            <x-ui.select.item value="editor">Editor</x-ui.select.item>
                            <x-ui.select.item value="member">Miembro</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </div>
            </x-ui.form-field>

            <x-ui.form-field>
                <x-ui.label>Estado</x-ui.label>
                <div @select-change.stop="statusFilter = $event.detail.value">
                    <x-ui.select value="">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Todos los estados" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="active">Activo</x-ui.select.item>
                            <x-ui.select.item value="pending">Pendiente</x-ui.select.item>
                            <x-ui.select.item value="inactive">Inactivo</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </div>
            </x-ui.form-field>

            <x-ui.separator />

            <x-ui.button variant="outline" class="w-full" @click="search = ''; roleFilter = ''; statusFilter = ''">
                Limpiar filtros
            </x-ui.button>

        </x-ui.sheet.content>
    </x-ui.sheet>

</div>