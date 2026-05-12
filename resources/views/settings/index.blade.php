<x-layouts.app-sidebar title="Configuración">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Configuración</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    <div class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Contenido acotado a max-w-3xl — el form de settings no necesita todo el ancho --}}
        <div class="max-w-3xl space-y-6">

        {{-- Page header --}}
        <div class="space-y-1">
            <x-ui.typography as="h1">Configuración</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                Administrá tu cuenta, notificaciones y preferencias de seguridad.
            </x-ui.typography>
        </div>

        {{-- Tabs --}}
        <x-ui.tabs value="profile">

            <x-ui.tabs.list class="w-full sm:w-auto">
                <x-ui.tabs.trigger value="profile">
                    <x-lucide-user class="size-4" />
                    Perfil
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="notifications">
                    <x-lucide-bell class="size-4" />
                    Notificaciones
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="security">
                    <x-lucide-shield class="size-4" />
                    Seguridad
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="danger">
                    <x-lucide-triangle-alert class="size-4" />
                    Danger Zone
                </x-ui.tabs.trigger>
            </x-ui.tabs.list>

            <x-ui.tabs.content value="profile">
                @include('settings._tab-profile')
            </x-ui.tabs.content>

            <x-ui.tabs.content value="notifications">
                @include('settings._tab-notifications')
            </x-ui.tabs.content>

            <x-ui.tabs.content value="security">
                @include('settings._tab-security')
            </x-ui.tabs.content>

            <x-ui.tabs.content value="danger">
                @include('settings._tab-danger')
            </x-ui.tabs.content>

        </x-ui.tabs>

        </div>{{-- /max-w-3xl --}}

    </div>

</x-layouts.app-sidebar>
