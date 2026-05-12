@props([
    'member',
    'roleBadge',
    'statusBadge',
])

{{--
    Fila de la tabla de miembros.
    Las expresiones Alpine (selected, toggleSelect) resuelven en el scope
    del x-data padre — no necesita su propio x-data.
--}}
<tr
    class="member-row border-b border-border transition-colors hover:bg-muted/50"
    :class="selected.includes('{{ $member['id'] }}') ? 'bg-muted/30' : ''"
    data-id="{{ $member['id'] }}"
    data-name="{{ strtolower($member['name']) }}"
    data-email="{{ strtolower($member['email']) }}"
    data-role="{{ $member['role'] }}"
    data-status="{{ $member['status'] }}"
>
    {{-- Checkbox --}}
    <td class="w-12 pl-4 py-3 align-middle">
        <button
            type="button"
            role="checkbox"
            :aria-checked="selected.includes('{{ $member['id'] }}').toString()"
            @click="toggleSelect('{{ $member['id'] }}')"
            :class="selected.includes('{{ $member['id'] }}')
                ? 'bg-primary border-primary text-primary-foreground'
                : 'bg-background border-input'"
            class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors cursor-pointer"
        >
            <x-lucide-check
                x-show="selected.includes('{{ $member['id'] }}')"
                class="size-3" stroke-width="3"
            />
        </button>
    </td>

    {{-- Avatar + nombre + email --}}
    <td class="py-3 px-3 align-middle">
        <div class="flex items-center gap-3">
            <x-ui.avatar :alt="$member['name']" size="sm" class="shrink-0" />
            <div class="min-w-0">
                <p class="text-sm font-medium text-foreground truncate">{{ $member['name'] }}</p>
                <p class="text-xs text-muted-foreground truncate">{{ $member['email'] }}</p>
            </div>
        </div>
    </td>

    {{-- Rol --}}
    <td class="py-3 px-3 align-middle">
        <x-ui.badge
            :state="$roleBadge[$member['role']]"
            :subtle="true"
            variant="{{ $member['role'] === 'member' ? 'secondary' : 'default' }}"
        >
            {{ $member['role_label'] }}
        </x-ui.badge>
    </td>

    {{-- Estado --}}
    <td class="py-3 px-3 align-middle">
        <x-ui.badge :state="$statusBadge[$member['status']]" :subtle="true">
            {{ $member['status_label'] }}
        </x-ui.badge>
    </td>

    {{-- Fecha --}}
    <td class="py-3 px-3 align-middle hidden sm:table-cell">
        <x-ui.typography as="muted" class="text-xs whitespace-nowrap">
            {{ $member['joined'] }}
        </x-ui.typography>
    </td>

    {{-- Acciones --}}
    <td class="py-3 pr-3 align-middle">
        <x-ui.dropdown-menu align="end">
            <x-ui.dropdown-menu.trigger>
                <x-ui.button variant="ghost" size="icon" class="size-8">
                    <x-lucide-more-horizontal class="size-4" />
                </x-ui.button>
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content>
                <x-ui.dropdown-menu.item>
                    <x-lucide-eye class="size-4" /> Ver perfil
                </x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item>
                    <x-lucide-pencil class="size-4" /> Editar
                </x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.sub>
                    <x-ui.dropdown-menu.sub-trigger>
                        <x-lucide-shield class="size-4" /> Cambiar rol
                    </x-ui.dropdown-menu.sub-trigger>
                    <x-ui.dropdown-menu.sub-content>
                        <x-ui.dropdown-menu.item>
                            <x-lucide-shield-check class="size-4" /> Administrador
                        </x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item>
                            <x-lucide-pencil-line class="size-4" /> Editor
                        </x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item>
                            <x-lucide-user class="size-4" /> Miembro
                        </x-ui.dropdown-menu.item>
                    </x-ui.dropdown-menu.sub-content>
                </x-ui.dropdown-menu.sub>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item variant="destructive">
                    <x-lucide-user-x class="size-4" /> Eliminar miembro
                </x-ui.dropdown-menu.item>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </td>
</tr>
