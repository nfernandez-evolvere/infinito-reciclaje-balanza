<x-layouts.showcase title="Table — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Table</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Tabla semántica con API compositiva: Table → Header / Body / Footer → Row → Head / Cell. Las filas soportan estados semánticos y selección.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.table>
            <x-ui.table.caption>Lista de facturas recientes.</x-ui.table.caption>
            <x-ui.table.header>
                <x-ui.table.row>
                    <x-ui.table.head class="w-[120px]">Factura</x-ui.table.head>
                    <x-ui.table.head>Estado</x-ui.table.head>
                    <x-ui.table.head>Método</x-ui.table.head>
                    <x-ui.table.head class="text-right">Monto</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                @foreach([
                    ['INV001', 'Pagada',    'Tarjeta',       '$250.00'],
                    ['INV002', 'Pendiente', 'PayPal',        '$150.00'],
                    ['INV003', 'Sin pagar', 'Transferencia', '$350.00'],
                    ['INV004', 'Pagada',    'Tarjeta',       '$450.00'],
                    ['INV005', 'Pagada',    'PayPal',        '$550.00'],
                ] as [$id, $status, $method, $amount])
                    <x-ui.table.row>
                        <x-ui.table.cell class="font-medium">{{ $id }}</x-ui.table.cell>
                        <x-ui.table.cell>{{ $status }}</x-ui.table.cell>
                        <x-ui.table.cell>{{ $method }}</x-ui.table.cell>
                        <x-ui.table.cell class="text-right">{{ $amount }}</x-ui.table.cell>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
        </x-ui.table>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos en filas --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos en filas</x-ui.typography>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.row>
                    <x-ui.table.head>Tarea</x-ui.table.head>
                    <x-ui.table.head>Estado</x-ui.table.head>
                    <x-ui.table.head>Responsable</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                <x-ui.table.row state="success">
                    <x-ui.table.cell class="font-medium">Deploy a producción</x-ui.table.cell>
                    <x-ui.table.cell><x-ui.badge variant="outline" class="border-success-border text-success">Completado</x-ui.badge></x-ui.table.cell>
                    <x-ui.table.cell>Ana García</x-ui.table.cell>
                </x-ui.table.row>
                <x-ui.table.row state="warning">
                    <x-ui.table.cell class="font-medium">Revisión de seguridad</x-ui.table.cell>
                    <x-ui.table.cell><x-ui.badge variant="outline" class="border-warning-border text-warning">En revisión</x-ui.badge></x-ui.table.cell>
                    <x-ui.table.cell>Carlos López</x-ui.table.cell>
                </x-ui.table.row>
                <x-ui.table.row state="destructive">
                    <x-ui.table.cell class="font-medium">Migración de base de datos</x-ui.table.cell>
                    <x-ui.table.cell><x-ui.badge variant="outline" class="border-destructive-border text-destructive">Fallido</x-ui.badge></x-ui.table.cell>
                    <x-ui.table.cell>María Martínez</x-ui.table.cell>
                </x-ui.table.row>
                <x-ui.table.row state="info">
                    <x-ui.table.cell class="font-medium">Actualización de dependencias</x-ui.table.cell>
                    <x-ui.table.cell><x-ui.badge variant="outline" class="border-info-border text-info">Programado</x-ui.badge></x-ui.table.cell>
                    <x-ui.table.cell>Juan Rodríguez</x-ui.table.cell>
                </x-ui.table.row>
                <x-ui.table.row>
                    <x-ui.table.cell class="font-medium">Tests unitarios</x-ui.table.cell>
                    <x-ui.table.cell><x-ui.badge variant="secondary">Pendiente</x-ui.badge></x-ui.table.cell>
                    <x-ui.table.cell>Lucía Fernández</x-ui.table.cell>
                </x-ui.table.row>
            </x-ui.table.body>
        </x-ui.table>
    </section>

    <x-ui.separator />

    {{-- Con footer --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con footer</x-ui.typography>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.row>
                    <x-ui.table.head>Producto</x-ui.table.head>
                    <x-ui.table.head class="text-right">Cantidad</x-ui.table.head>
                    <x-ui.table.head class="text-right">Precio</x-ui.table.head>
                    <x-ui.table.head class="text-right">Total</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                @foreach([
                    ['Plan Pro',         1,  '$99.00',  '$99.00'],
                    ['Soporte premium',  1,  '$29.00',  '$29.00'],
                    ['Almacenamiento',   2,  '$9.00',   '$18.00'],
                ] as [$name, $qty, $price, $total])
                    <x-ui.table.row>
                        <x-ui.table.cell class="font-medium">{{ $name }}</x-ui.table.cell>
                        <x-ui.table.cell class="text-right">{{ $qty }}</x-ui.table.cell>
                        <x-ui.table.cell class="text-right">{{ $price }}</x-ui.table.cell>
                        <x-ui.table.cell class="text-right">{{ $total }}</x-ui.table.cell>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
            <x-ui.table.footer>
                <x-ui.table.row>
                    <x-ui.table.cell colspan="3">Total</x-ui.table.cell>
                    <x-ui.table.cell class="text-right">$146.00</x-ui.table.cell>
                </x-ui.table.row>
            </x-ui.table.footer>
        </x-ui.table>
    </section>

</div>
</x-layouts.showcase>
