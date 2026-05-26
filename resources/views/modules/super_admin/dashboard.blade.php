<x-layouts.app title="Dashboard">
    <div class="space-y-8">

        {{-- Encabezado --}}
        <div>
            <h1 class="text-h2">Panel general</h1>
            <p class="text-lead mt-1">Estado global del sistema.</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <x-ui.card variant="elevated">
                <x-ui.card.content class="pt-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-caption text-muted-foreground">Organizaciones</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="rounded-md bg-muted p-2">
                            <x-lucide-building-2 class="size-4 text-muted-foreground" />
                        </div>
                    </div>
                </x-ui.card.content>
            </x-ui.card>

            <x-ui.card variant="elevated">
                <x-ui.card.content class="pt-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-caption text-muted-foreground">Activas</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['activas'] }}</p>
                        </div>
                        <div class="rounded-md bg-success/10 p-2">
                            <x-lucide-check-circle class="size-4 text-success" />
                        </div>
                    </div>
                </x-ui.card.content>
            </x-ui.card>

            <x-ui.card variant="elevated">
                <x-ui.card.content class="pt-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-caption text-muted-foreground">Inactivas</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['inactivas'] }}</p>
                        </div>
                        <div class="rounded-md bg-muted p-2">
                            <x-lucide-circle-slash class="size-4 text-muted-foreground" />
                        </div>
                    </div>
                </x-ui.card.content>
            </x-ui.card>

            <x-ui.card variant="elevated">
                <x-ui.card.content class="pt-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-caption text-muted-foreground">Usuarios</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['usuarios'] }}</p>
                        </div>
                        <div class="rounded-md bg-muted p-2">
                            <x-lucide-users class="size-4 text-muted-foreground" />
                        </div>
                    </div>
                </x-ui.card.content>
            </x-ui.card>

        </div>

        {{-- Organizaciones recientes --}}
        <x-ui.card variant="elevated">
            <x-ui.card.header>
                <div class="flex items-center justify-between">
                    <div>
                        <x-ui.card.title>Últimas organizaciones</x-ui.card.title>
                        <x-ui.card.description>Las 5 más recientes del sistema.</x-ui.card.description>
                    </div>
                    <x-ui.button variant="outline" size="sm" :href="route('super.organizaciones.index')">
                        Ver todas
                    </x-ui.button>
                </div>
            </x-ui.card.header>
            <x-ui.card.content class="pt-0">
                @if($recientes->isEmpty())
                    <p class="text-body-sm text-muted-foreground py-4">Sin organizaciones registradas todavía.</p>
                @else
                    <x-ui.table>
                        <x-ui.table.header>
                            <x-ui.table.row>
                                <x-ui.table.head>Nombre</x-ui.table.head>
                                <x-ui.table.head>Slug</x-ui.table.head>
                                <x-ui.table.head class="text-center">Usuarios</x-ui.table.head>
                                <x-ui.table.head class="text-right">Estado</x-ui.table.head>
                            </x-ui.table.row>
                        </x-ui.table.header>
                        <x-ui.table.body>
                            @foreach($recientes as $org)
                                <x-ui.table.row>
                                    <x-ui.table.cell class="font-medium">{{ $org->nombre }}</x-ui.table.cell>
                                    <x-ui.table.cell class="text-muted-foreground font-mono text-xs">{{ $org->slug }}</x-ui.table.cell>
                                    <x-ui.table.cell class="text-center">{{ $org->users_count }}</x-ui.table.cell>
                                    <x-ui.table.cell class="text-right">
                                        @if($org->activo)
                                            <x-ui.badge variant="success">ACTIVA</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="secondary">INACTIVA</x-ui.badge>
                                        @endif
                                    </x-ui.table.cell>
                                </x-ui.table.row>
                            @endforeach
                        </x-ui.table.body>
                    </x-ui.table>
                @endif
            </x-ui.card.content>
        </x-ui.card>

    </div>
</x-layouts.app>
