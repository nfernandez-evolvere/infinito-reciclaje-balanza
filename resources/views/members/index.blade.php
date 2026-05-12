<x-layouts.app-sidebar title="Miembros">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Miembros</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    @php
    $members = [
        ['id' => '1',  'name' => 'María García',     'email' => 'maria@ejemplo.com',     'role' => 'admin',  'role_label' => 'Administrador', 'status' => 'active',   'status_label' => 'Activo',    'joined' => '15 ene 2024'],
        ['id' => '2',  'name' => 'Carlos López',     'email' => 'carlos@ejemplo.com',    'role' => 'editor', 'role_label' => 'Editor',        'status' => 'active',   'status_label' => 'Activo',    'joined' => '03 feb 2024'],
        ['id' => '3',  'name' => 'Ana Rodríguez',    'email' => 'ana@ejemplo.com',       'role' => 'member', 'role_label' => 'Miembro',       'status' => 'pending',  'status_label' => 'Pendiente', 'joined' => '28 feb 2024'],
        ['id' => '4',  'name' => 'Pedro Martínez',   'email' => 'pedro@ejemplo.com',     'role' => 'member', 'role_label' => 'Miembro',       'status' => 'inactive', 'status_label' => 'Inactivo',  'joined' => '12 mar 2024'],
        ['id' => '5',  'name' => 'Laura Sánchez',    'email' => 'laura@ejemplo.com',     'role' => 'editor', 'role_label' => 'Editor',        'status' => 'active',   'status_label' => 'Activo',    'joined' => '05 abr 2024'],
        ['id' => '6',  'name' => 'Diego Fernández',  'email' => 'diego@ejemplo.com',     'role' => 'member', 'role_label' => 'Miembro',       'status' => 'active',   'status_label' => 'Activo',    'joined' => '20 abr 2024'],
        ['id' => '7',  'name' => 'Sofía González',   'email' => 'sofia@ejemplo.com',     'role' => 'admin',  'role_label' => 'Administrador', 'status' => 'active',   'status_label' => 'Activo',    'joined' => '02 may 2024'],
        ['id' => '8',  'name' => 'Martín Torres',    'email' => 'martin@ejemplo.com',    'role' => 'member', 'role_label' => 'Miembro',       'status' => 'pending',  'status_label' => 'Pendiente', 'joined' => '18 may 2024'],
        ['id' => '9',  'name' => 'Valentina Ruiz',   'email' => 'valentina@ejemplo.com', 'role' => 'editor', 'role_label' => 'Editor',        'status' => 'active',   'status_label' => 'Activo',    'joined' => '30 may 2024'],
        ['id' => '10', 'name' => 'Sebastián Castro', 'email' => 'sebastian@ejemplo.com', 'role' => 'member', 'role_label' => 'Miembro',       'status' => 'inactive', 'status_label' => 'Inactivo',  'joined' => '14 jun 2024'],
    ];

    $roleBadge   = ['admin' => 'info', 'editor' => 'warning', 'member' => null];
    $statusBadge = ['active' => 'success', 'pending' => 'warning', 'inactive' => 'destructive'];
    @endphp

    <div
        data-density="compact"
        class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-6"
        x-data="memberTable(@js(array_column($members, 'id')))"
        x-effect="filterRows()"
    >

        {{-- Page header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <x-ui.typography as="h1">Miembros</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">
                    Gestioná los miembros de tu equipo y sus permisos.
                </x-ui.typography>
            </div>
            <x-members.invite-dialog />
        </div>

        {{-- Toolbar: búsqueda + filtros + sheet --}}
        @include('members._toolbar')

        {{-- Bulk actions bar --}}
        @include('members._bulk-actions')

        {{-- Tabla --}}
        <x-ui.card>
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <x-ui.typography as="muted" class="text-sm">
                    <span x-text="visibleCount" class="font-semibold text-foreground"></span>
                    miembro(s)
                </x-ui.typography>
            </div>

            <x-ui.card.content class="p-0">

                <div x-show="visibleCount > 0">
                    <x-ui.table>
                        <x-ui.table.header>
                            <x-ui.table.row class="hover:bg-transparent">
                                <x-ui.table.head class="w-12 pl-4">
                                    <button
                                        type="button"
                                        role="checkbox"
                                        :aria-checked="allSelected ? 'true' : (someSelected ? 'mixed' : 'false')"
                                        @click="toggleAll()"
                                        :class="allSelected || someSelected
                                            ? 'bg-primary border-primary text-primary-foreground'
                                            : 'bg-background border-input'"
                                        class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors cursor-pointer"
                                    >
                                        <x-lucide-check x-show="allSelected" class="size-3" stroke-width="3" />
                                        <x-lucide-minus x-show="someSelected && !allSelected" class="size-3" stroke-width="3" />
                                    </button>
                                </x-ui.table.head>
                                <x-ui.table.head>Miembro</x-ui.table.head>
                                <x-ui.table.head>Rol</x-ui.table.head>
                                <x-ui.table.head>Estado</x-ui.table.head>
                                <x-ui.table.head class="hidden sm:table-cell">Ingresó</x-ui.table.head>
                                <x-ui.table.head class="w-12"></x-ui.table.head>
                            </x-ui.table.row>
                        </x-ui.table.header>
                        <x-ui.table.body>
                            @foreach($members as $member)
                                <x-members.row
                                    :member="$member"
                                    :role-badge="$roleBadge"
                                    :status-badge="$statusBadge"
                                />
                            @endforeach
                        </x-ui.table.body>
                    </x-ui.table>
                </div>

                <div x-show="visibleCount === 0" x-cloak class="p-6">
                    <x-ui.empty-state
                        title="Sin resultados"
                        description="No encontramos miembros que coincidan con los filtros aplicados."
                        icon="users"
                    >
                        <x-ui.button
                            variant="outline"
                            size="sm"
                            @click="search = ''; roleFilter = ''; statusFilter = ''"
                        >
                            Limpiar filtros
                        </x-ui.button>
                    </x-ui.empty-state>
                </div>

            </x-ui.card.content>
        </x-ui.card>

    </div>

</x-layouts.app-sidebar>
