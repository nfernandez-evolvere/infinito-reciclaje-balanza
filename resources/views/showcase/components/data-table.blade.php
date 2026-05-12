<x-layouts.showcase title="Data Table — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Data Table</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Tabla interactiva construida sobre <x-ui.typography as="code">&lt;x-ui.data-table&gt;</x-ui.typography>. Incluye sorting, filtering, paginación client-side, selección de filas y visibilidad de columnas — sin JavaScript adicional.</x-ui.typography>
    </div>

    {{-- Ejemplo completo — pagos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Pagos recientes — todas las features</x-ui.typography>

        @php
        $columns = [
            ['key' => 'id',     'label' => 'ID',      'sortable' => true,  'class' => 'font-mono text-xs w-[110px]'],
            ['key' => 'status', 'label' => 'Estado',  'sortable' => true],
            ['key' => 'email',  'label' => 'Email',   'sortable' => true],
            ['key' => 'method', 'label' => 'Método',  'sortable' => true,  'hideable' => true],
            ['key' => 'amount', 'label' => 'Monto',   'sortable' => true,  'class' => 'text-right'],
        ];

        $payments = [
            ['id' => '728ed52f', 'status' => 'Pendiente',   'email' => 'm@example.com',       'method' => 'Tarjeta',       'amount' => '$100.00'],
            ['id' => '489e1d42', 'status' => 'Procesando',  'email' => 'example@gmail.com',   'method' => 'PayPal',        'amount' => '$125.00'],
            ['id' => 'a3f291bc', 'status' => 'Exitoso',     'email' => 'carol@company.com',   'method' => 'Transferencia', 'amount' => '$242.00'],
            ['id' => '7d2e5f91', 'status' => 'Fallido',     'email' => 'bob@email.io',        'method' => 'Tarjeta',       'amount' => '$89.00'],
            ['id' => 'c9b14d3a', 'status' => 'Exitoso',     'email' => 'alice@startup.co',   'method' => 'Tarjeta',       'amount' => '$560.00'],
            ['id' => '1e8f7c22', 'status' => 'Pendiente',   'email' => 'john@business.net',   'method' => 'PayPal',        'amount' => '$310.00'],
            ['id' => 'f4a09e71', 'status' => 'Exitoso',     'email' => 'sarah@domain.com',    'method' => 'Transferencia', 'amount' => '$78.00'],
            ['id' => '3b5c8d60', 'status' => 'Procesando',  'email' => 'mike@corp.org',       'method' => 'Tarjeta',       'amount' => '$195.00'],
            ['id' => 'e2d17a44', 'status' => 'Exitoso',     'email' => 'lisa@agency.com',     'method' => 'PayPal',        'amount' => '$430.00'],
            ['id' => '9f3b2c18', 'status' => 'Fallido',     'email' => 'james@freelance.io',  'method' => 'Tarjeta',       'amount' => '$55.00'],
            ['id' => '6a1e9d85', 'status' => 'Exitoso',     'email' => 'emma@shop.com',       'method' => 'Transferencia', 'amount' => '$890.00'],
            ['id' => 'b7c30f52', 'status' => 'Pendiente',   'email' => 'oliver@studio.net',   'method' => 'PayPal',        'amount' => '$140.00'],
            ['id' => '4d8a1e96', 'status' => 'Exitoso',     'email' => 'ava@tech.co',         'method' => 'Tarjeta',       'amount' => '$275.00'],
            ['id' => '2c7f4b30', 'status' => 'Procesando',  'email' => 'noah@labs.io',        'method' => 'Transferencia', 'amount' => '$615.00'],
            ['id' => '5e9d3c74', 'status' => 'Exitoso',     'email' => 'sophia@design.com',   'method' => 'Tarjeta',       'amount' => '$380.00'],
            ['id' => '8b2f6a19', 'status' => 'Fallido',     'email' => 'liam@builds.dev',     'method' => 'PayPal',        'amount' => '$47.00'],
            ['id' => 'd1e85c63', 'status' => 'Exitoso',     'email' => 'mia@brand.com',       'method' => 'Tarjeta',       'amount' => '$720.00'],
            ['id' => '0f4a7b21', 'status' => 'Pendiente',   'email' => 'ethan@services.net',  'method' => 'Transferencia', 'amount' => '$205.00'],
            ['id' => '7c9e2d58', 'status' => 'Exitoso',     'email' => 'isabella@cloud.io',   'method' => 'PayPal',        'amount' => '$495.00'],
            ['id' => '3a6f1e84', 'status' => 'Procesando',  'email' => 'lucas@platform.com',  'method' => 'Tarjeta',       'amount' => '$163.00'],
        ];
        @endphp

        <x-ui.data-table
            :columns="$columns"
            :data="$payments"
            :selectable="true"
            :per-page="10"
            :per-page-options="[5, 10, 20]"
            search-placeholder="Buscar por email, estado..."
        />
    </section>

    <x-ui.separator />

    {{-- Sin paginación --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Sin paginación — solo sorting y filtering</x-ui.typography>

        @php
        $simpleColumns = [
            ['key' => 'name',  'label' => 'Nombre',      'sortable' => true],
            ['key' => 'role',  'label' => 'Rol',         'sortable' => true],
            ['key' => 'email', 'label' => 'Email',       'sortable' => true],
            ['key' => 'status','label' => 'Estado',      'sortable' => true],
        ];
        $team = [
            ['name' => 'Ana García',      'role' => 'Admin',        'email' => 'ana@example.com',     'status' => 'Activo'],
            ['name' => 'Carlos López',    'role' => 'Editor',       'email' => 'carlos@example.com',  'status' => 'Activo'],
            ['name' => 'María Martínez',  'role' => 'Viewer',       'email' => 'maria@example.com',   'status' => 'Inactivo'],
            ['name' => 'Juan Rodríguez',  'role' => 'Editor',       'email' => 'juan@example.com',    'status' => 'Activo'],
            ['name' => 'Lucía Fernández', 'role' => 'Admin',        'email' => 'lucia@example.com',   'status' => 'Activo'],
            ['name' => 'Pedro Díaz',      'role' => 'Viewer',       'email' => 'pedro@example.com',   'status' => 'Inactivo'],
        ];
        @endphp

        <x-ui.data-table
            :columns="$simpleColumns"
            :data="$team"
            :paginate="false"
        />
    </section>

</div>
</x-layouts.showcase>
