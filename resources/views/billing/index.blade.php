<x-layouts.app-sidebar title="Facturación">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Facturación</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    @php
    $plans = [
        [
            'name'     => 'Gratis',
            'price'    => '$0',
            'popular'  => false,
            'current'  => false,
            'features' => ['3 proyectos', '1 GB de almacenamiento', 'Soporte por email'],
        ],
        [
            'name'     => 'Pro',
            'price'    => '$12',
            'popular'  => true,
            'current'  => true,
            'features' => ['Proyectos ilimitados', '20 GB de almacenamiento', 'Soporte prioritario', 'Miembros ilimitados', 'Integraciones avanzadas'],
        ],
        [
            'name'     => 'Enterprise',
            'price'    => '$49',
            'popular'  => false,
            'current'  => false,
            'features' => ['Todo lo de Pro', 'SLA garantizado', 'SSO / SAML', 'Auditoría avanzada', 'Soporte dedicado'],
        ],
    ];

    $invoices = [
        ['date' => '01 jun 2024', 'desc' => 'Plan Pro — Junio 2024',  'amount' => '$12.00', 'status' => 'paid'],
        ['date' => '01 may 2024', 'desc' => 'Plan Pro — Mayo 2024',   'amount' => '$12.00', 'status' => 'paid'],
        ['date' => '01 abr 2024', 'desc' => 'Plan Pro — Abril 2024',  'amount' => '$12.00', 'status' => 'paid'],
        ['date' => '01 mar 2024', 'desc' => 'Plan Pro — Marzo 2024',  'amount' => '$12.00', 'status' => 'paid'],
        ['date' => '01 feb 2024', 'desc' => 'Plan Gratis — Feb 2024', 'amount' => '$0.00',  'status' => 'paid'],
    ];

    $quotas = [
        ['label' => 'Almacenamiento',     'used' => 8.4,  'total' => 20,   'unit' => 'GB',  'pct' => 42],
        ['label' => 'Miembros del equipo','used' => 7,    'total' => null,  'unit' => '',    'pct' => 70],
        ['label' => 'Solicitudes API',    'used' => 48200,'total' => 100000,'unit' => '/mes','pct' => 48],
    ];

    $statusBadge = ['paid' => 'success', 'pending' => 'warning', 'failed' => 'destructive'];
    $statusLabel = ['paid' => 'Pagado',  'pending' => 'Pendiente','failed' => 'Fallido'];
    @endphp

    <div class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Page header --}}
        <div class="space-y-1">
            <x-ui.typography as="h1">Facturación</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                Administrá tu plan, método de pago e historial de facturas.
            </x-ui.typography>
        </div>

        {{-- Alert trial --}}
        <x-ui.alert state="warning">
            <x-lucide-clock class="size-4" />
            <x-ui.alert.title>Tu período de prueba vence en 7 días</x-ui.alert.title>
            <x-ui.alert.description>
                Después del 15 de julio volverás al plan Gratis. Actualizá tu plan para no perder el acceso.
            </x-ui.alert.description>
        </x-ui.alert>

        {{-- Plan actual + quota --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <x-ui.card class="lg:col-span-2">
                <x-ui.card.header>
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <x-ui.card.title>Plan actual</x-ui.card.title>
                            <x-ui.card.description>Próxima facturación: 1 de julio 2024</x-ui.card.description>
                        </div>
                        <x-ui.badge>Plan Pro</x-ui.badge>
                    </div>
                </x-ui.card.header>
                <x-ui.card.content class="space-y-4">
                    @foreach($quotas as $quota)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-foreground font-medium">{{ $quota['label'] }}</span>
                            <span class="text-muted-foreground">
                                {{ $quota['used'] }}
                                @if($quota['total'])
                                    / {{ $quota['total'] }} {{ $quota['unit'] }}
                                @else
                                    miembros activos
                                @endif
                            </span>
                        </div>
                        <x-ui.progress
                            :value="$quota['pct']"
                            size="sm"
                            :state="$quota['pct'] > 80 ? 'warning' : null"
                        />
                    </div>
                    @endforeach
                </x-ui.card.content>
                <x-ui.card.footer class="justify-between">
                    <x-ui.button variant="ghost" state="destructive" size="sm">
                        Cancelar suscripción
                    </x-ui.button>
                    <x-ui.button variant="outline" size="sm">
                        <x-lucide-zap class="size-4" />
                        Cambiar plan
                    </x-ui.button>
                </x-ui.card.footer>
            </x-ui.card>

            {{-- Método de pago --}}
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>Método de pago</x-ui.card.title>
                </x-ui.card.header>
                <x-ui.card.content class="space-y-4">
                    <div class="flex items-center gap-3 rounded-lg border border-border bg-muted/30 px-4 py-3">
                        <x-lucide-credit-card class="size-5 text-muted-foreground shrink-0" />
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Visa •••• 4242</p>
                            <p class="text-xs text-muted-foreground">Vence 12/2026</p>
                        </div>
                        <x-ui.badge state="success" :subtle="true" class="ml-auto shrink-0">Activa</x-ui.badge>
                    </div>
                </x-ui.card.content>
                <x-ui.card.footer class="flex-col gap-2">
                    <x-ui.button variant="outline" class="w-full" size="sm">
                        <x-lucide-pencil class="size-4" />
                        Editar tarjeta
                    </x-ui.button>
                    <x-ui.button variant="ghost" class="w-full" size="sm">
                        <x-lucide-plus class="size-4" />
                        Agregar método
                    </x-ui.button>
                </x-ui.card.footer>
            </x-ui.card>

        </div>

        {{-- Planes disponibles --}}
        <div class="space-y-4">
            <x-ui.typography as="h2">Planes disponibles</x-ui.typography>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                    <x-billing.plan-card
                        :name="$plan['name']"
                        :price="$plan['price']"
                        :features="$plan['features']"
                        :popular="$plan['popular']"
                        :current="$plan['current']"
                    />
                @endforeach
            </div>
        </div>

        {{-- Facturas --}}
        <x-ui.card>
            <x-ui.card.header>
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <x-ui.card.title>Historial de facturas</x-ui.card.title>
                        <x-ui.card.description>Descargá tus facturas anteriores.</x-ui.card.description>
                    </div>
                    <x-ui.button variant="outline" size="sm">
                        <x-lucide-download class="size-4" />
                        Exportar todas
                    </x-ui.button>
                </div>
            </x-ui.card.header>
            <x-ui.card.content class="p-0">
                <x-ui.table>
                    <x-ui.table.header>
                        <x-ui.table.row class="hover:bg-transparent border-t">
                            <x-ui.table.head class="pl-6">Fecha</x-ui.table.head>
                            <x-ui.table.head>Descripción</x-ui.table.head>
                            <x-ui.table.head>Monto</x-ui.table.head>
                            <x-ui.table.head>Estado</x-ui.table.head>
                            <x-ui.table.head class="w-12 pr-6"></x-ui.table.head>
                        </x-ui.table.row>
                    </x-ui.table.header>
                    <x-ui.table.body>
                        @foreach($invoices as $invoice)
                        <x-ui.table.row>
                            <x-ui.table.cell class="pl-6">
                                <x-ui.typography as="muted" class="text-sm">{{ $invoice['date'] }}</x-ui.typography>
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                <span class="text-sm text-foreground">{{ $invoice['desc'] }}</span>
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                <span class="text-sm font-medium text-foreground">{{ $invoice['amount'] }}</span>
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                <x-ui.badge :state="$statusBadge[$invoice['status']]" :subtle="true">
                                    {{ $statusLabel[$invoice['status']] }}
                                </x-ui.badge>
                            </x-ui.table.cell>
                            <x-ui.table.cell class="pr-6 text-right">
                                <x-ui.button variant="ghost" size="icon" class="size-8">
                                    <x-lucide-download class="size-4" />
                                </x-ui.button>
                            </x-ui.table.cell>
                        </x-ui.table.row>
                        @endforeach
                    </x-ui.table.body>
                </x-ui.table>
            </x-ui.card.content>
        </x-ui.card>

    </div>

</x-layouts.app-sidebar>
