<x-layouts.dashboard title="Productos">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Productos</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    @php
        $products = [
            ['id'=>'PRD-001','name'=>'Auriculares Pro Max',   'category'=>'Electrónica','sku'=>'AUR-001','price'=>240.00,'stock'=>45, 'sold'=>342],
            ['id'=>'PRD-002','name'=>'Monitor 4K 27"',         'category'=>'Electrónica','sku'=>'MON-002','price'=>899.00,'stock'=>12, 'sold'=>180],
            ['id'=>'PRD-003','name'=>'Teclado Mecánico RGB',   'category'=>'Electrónica','sku'=>'TEC-003','price'=>180.00,'stock'=>38, 'sold'=>290],
            ['id'=>'PRD-004','name'=>'Webcam HD 1080p',        'category'=>'Electrónica','sku'=>'WEB-004','price'=>120.00,'stock'=>5,  'sold'=>210],
            ['id'=>'PRD-005','name'=>'SSD Externo 1TB',        'category'=>'Electrónica','sku'=>'SSD-005','price'=>110.00,'stock'=>0,  'sold'=>154],
            ['id'=>'PRD-006','name'=>'Mouse Inalámbrico',      'category'=>'Electrónica','sku'=>'MOU-006','price'=>65.00, 'stock'=>82, 'sold'=>420],
            ['id'=>'PRD-007','name'=>'Hub USB-C 7 puertos',    'category'=>'Electrónica','sku'=>'HUB-007','price'=>55.00, 'stock'=>3,  'sold'=>178],
            ['id'=>'PRD-008','name'=>'Zapatillas Running Pro', 'category'=>'Ropa',       'sku'=>'ZAP-008','price'=>89.00, 'stock'=>60, 'sold'=>195],
            ['id'=>'PRD-009','name'=>'Remera Deportiva',       'category'=>'Ropa',       'sku'=>'REM-009','price'=>45.00, 'stock'=>120,'sold'=>380],
            ['id'=>'PRD-010','name'=>'Mochila Urbana 25L',     'category'=>'Ropa',       'sku'=>'MOC-010','price'=>95.00, 'stock'=>7,  'sold'=>220],
            ['id'=>'PRD-011','name'=>'Lámpara LED Escritorio', 'category'=>'Hogar',      'sku'=>'LAM-011','price'=>75.00, 'stock'=>28, 'sold'=>142],
            ['id'=>'PRD-012','name'=>'Cafetera Automática',    'category'=>'Hogar',      'sku'=>'CAF-012','price'=>220.00,'stock'=>0,  'sold'=>89],
        ];

        foreach ($products as &$p) {
            if ($p['stock'] >= 10)    $p['stock_status'] = ['label'=>'En stock',   'variant'=>'success'];
            elseif ($p['stock'] > 0)  $p['stock_status'] = ['label'=>'Stock bajo', 'variant'=>'warning'];
            else                      $p['stock_status'] = ['label'=>'Sin stock',  'variant'=>'destructive'];
        }
        unset($p);

        $categories = ['Todos','Electrónica','Ropa','Hogar'];
        $catCount   = ['Todos'=>count($products),'Electrónica'=>7,'Ropa'=>3,'Hogar'=>2];
        $catColors  = ['Electrónica'=>'bg-blue-500','Ropa'=>'bg-purple-500','Hogar'=>'bg-amber-500'];
    @endphp

    <div class="space-y-6" x-data="{}">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Productos</h1>
                <p class="text-muted-foreground text-sm">Catálogo de productos y gestión de inventario.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button variant="outline" size="sm"
                    @click="$dispatch('toast', { message: 'Importando desde CSV...', variant: 'default' })">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    Importar
                </x-button>
                {{-- Nuevo producto --}}
                <x-dialog>
                    <x-dialog.trigger>
                        <x-button size="sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                            Nuevo producto
                        </x-button>
                    </x-dialog.trigger>
                    <x-dialog.content>
                        <x-dialog.header>
                            <x-dialog.title>Nuevo producto</x-dialog.title>
                            <x-dialog.description>Completá los datos para agregar un producto al catálogo.</x-dialog.description>
                        </x-dialog.header>
                        <div class="space-y-4 py-2">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="col-span-2 space-y-1.5">
                                    <x-label>Nombre del producto</x-label>
                                    <x-input placeholder="Ej: Auriculares Bluetooth Pro" />
                                </div>
                                <div class="space-y-1.5">
                                    <x-label>Categoría</x-label>
                                    <x-select>
                                        <option>Electrónica</option>
                                        <option>Ropa</option>
                                        <option>Hogar</option>
                                        <option>Deportes</option>
                                        <option>Otros</option>
                                    </x-select>
                                </div>
                                <div class="space-y-1.5">
                                    <x-label>SKU</x-label>
                                    <x-input placeholder="Ej: AUR-013" />
                                </div>
                                <div class="space-y-1.5">
                                    <x-label>Precio ($)</x-label>
                                    <x-input type="number" placeholder="0.00" />
                                </div>
                                <div class="space-y-1.5">
                                    <x-label>Stock inicial</x-label>
                                    <x-input type="number" placeholder="0" />
                                </div>
                                <div class="col-span-2 space-y-1.5">
                                    <x-label>Descripción</x-label>
                                    <x-textarea placeholder="Descripción breve del producto..." rows="3" />
                                </div>
                                <div class="col-span-2 space-y-1.5">
                                    <x-label>Estado</x-label>
                                    <x-select>
                                        <option>Activo</option>
                                        <option>Borrador</option>
                                    </x-select>
                                </div>
                            </div>
                        </div>
                        <x-dialog.footer>
                            <x-button variant="outline" @click="open = false">Cancelar</x-button>
                            <x-button @click="open = false; $dispatch('toast', { message: 'Producto creado correctamente', variant: 'success' })">
                                Crear producto
                            </x-button>
                        </x-dialog.footer>
                    </x-dialog.content>
                </x-dialog>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Total en catálogo</p>
                    <p class="text-3xl font-bold mt-1">48</p>
                    <p class="text-xs text-muted-foreground mt-1">12 mostrados en esta página</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">En stock</p>
                    <p class="text-3xl font-bold mt-1">35</p>
                    <p class="text-xs text-muted-foreground mt-1">72.9% del catálogo</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">Stock bajo</p>
                        <x-badge variant="warning">Revisar</x-badge>
                    </div>
                    <p class="text-3xl font-bold mt-1">8</p>
                    <p class="text-xs text-muted-foreground mt-1">menos de 10 unidades</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">Sin stock</p>
                        <x-badge variant="destructive">Urgente</x-badge>
                    </div>
                    <p class="text-3xl font-bold mt-1">5</p>
                    <p class="text-xs text-muted-foreground mt-1">requieren reposición</p>
                </x-card.content>
            </x-card>
        </div>

        {{-- Tabla --}}
        <x-card>
            <x-card.header class="pb-3">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <x-card.title>Inventario</x-card.title>
                        <x-card.description>{{ count($products) }} productos · última actualización hoy</x-card.description>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-input placeholder="Buscar producto o SKU..." class="h-8 w-52 text-xs" />
                        <x-select class="h-8 text-xs w-32">
                            <option>Todos los estados</option>
                            <option>En stock</option>
                            <option>Stock bajo</option>
                            <option>Sin stock</option>
                        </x-select>
                    </div>
                </div>
            </x-card.header>
            <x-card.content class="p-0">
                <x-tabs default="Todos">
                    <div class="px-6 pt-1 pb-2 border-b">
                        <x-tabs.list class="h-9">
                            @foreach($categories as $cat)
                            <x-tabs.trigger value="{{ $cat }}" class="text-xs h-8 gap-1.5">
                                {{ $cat }}
                                <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-muted px-1 text-[10px] font-medium">{{ $catCount[$cat] }}</span>
                            </x-tabs.trigger>
                            @endforeach
                        </x-tabs.list>
                    </div>

                    @foreach($categories as $cat)
                    <x-tabs.content value="{{ $cat }}">
                        <x-table>
                            <x-table.header>
                                <x-table.row>
                                    <x-table.head>Producto</x-table.head>
                                    <x-table.head class="hidden sm:table-cell">SKU</x-table.head>
                                    <x-table.head>Precio</x-table.head>
                                    <x-table.head>Stock</x-table.head>
                                    <x-table.head class="hidden md:table-cell">Vendidos</x-table.head>
                                    <x-table.head class="text-right">Acciones</x-table.head>
                                </x-table.row>
                            </x-table.header>
                            <x-table.body>
                                @foreach($products as $product)
                                @if($cat === 'Todos' || $product['category'] === $cat)
                                <x-table.row>
                                    <x-table.cell>
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-md {{ $catColors[$product['category']] }} flex items-center justify-center text-white text-xs font-bold shrink-0">
                                                {{ strtoupper(substr($product['category'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium">{{ $product['name'] }}</p>
                                                <p class="text-xs text-muted-foreground">{{ $product['category'] }}</p>
                                            </div>
                                        </div>
                                    </x-table.cell>
                                    <x-table.cell class="hidden sm:table-cell font-mono text-xs text-muted-foreground">{{ $product['sku'] }}</x-table.cell>
                                    <x-table.cell class="font-semibold text-sm">${{ number_format($product['price'], 2, ',', '.') }}</x-table.cell>
                                    <x-table.cell>
                                        <div class="flex items-center gap-2">
                                            <x-badge variant="{{ $product['stock_status']['variant'] }}">
                                                {{ $product['stock_status']['label'] }}
                                            </x-badge>
                                            <span class="text-sm text-muted-foreground">{{ $product['stock'] }}</span>
                                        </div>
                                    </x-table.cell>
                                    <x-table.cell class="hidden md:table-cell text-sm text-muted-foreground">{{ $product['sold'] }} uds.</x-table.cell>
                                    <x-table.cell class="text-right">
                                        <x-dropdown-menu>
                                            <x-dropdown-menu.trigger>
                                                <x-button variant="ghost" size="icon" class="h-8 w-8">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                                </x-button>
                                            </x-dropdown-menu.trigger>
                                            <x-dropdown-menu.content align="end">
                                                <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Abriendo editor de producto', variant: 'default' })">Editar</x-dropdown-menu.item>
                                                <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Stock actualizado', variant: 'success' })">Reponer stock</x-dropdown-menu.item>
                                                <x-dropdown-menu.item>Ver pedidos</x-dropdown-menu.item>
                                                <x-dropdown-menu.separator />
                                                <x-dropdown-menu.item :destructive="true" @click="$dispatch('toast', { message: 'Producto eliminado', variant: 'destructive' })">Eliminar</x-dropdown-menu.item>
                                            </x-dropdown-menu.content>
                                        </x-dropdown-menu>
                                    </x-table.cell>
                                </x-table.row>
                                @endif
                                @endforeach
                            </x-table.body>
                        </x-table>
                    </x-tabs.content>
                    @endforeach
                </x-tabs>
            </x-card.content>
            <x-card.footer class="flex items-center justify-between text-sm text-muted-foreground py-3">
                <span>Mostrando {{ count($products) }} de 48 productos</span>
                <x-pagination>
                    <x-pagination.content>
                        <x-pagination.item><x-pagination.link :disabled="true">« Anterior</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link :active="true" href="#">1</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">2</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">3</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">4</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">Siguiente »</x-pagination.link></x-pagination.item>
                    </x-pagination.content>
                </x-pagination>
            </x-card.footer>
        </x-card>

    </div>
</x-layouts.dashboard>
