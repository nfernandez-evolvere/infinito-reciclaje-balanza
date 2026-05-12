<x-layouts.dashboard title="Configuración">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Configuración</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Configuración</h1>
            <p class="text-muted-foreground text-sm">Gestioná las preferencias y ajustes de tu cuenta y tienda.</p>
        </div>

        <x-tabs default="general" class="space-y-6">
            <x-tabs.list>
                <x-tabs.trigger value="general">General</x-tabs.trigger>
                <x-tabs.trigger value="notificaciones">Notificaciones</x-tabs.trigger>
                <x-tabs.trigger value="seguridad">Seguridad</x-tabs.trigger>
                <x-tabs.trigger value="apariencia">Apariencia</x-tabs.trigger>
            </x-tabs.list>

            {{-- ── Tab: General ──────────────────────────────────────────────────── --}}
            <x-tabs.content value="general" class="space-y-6">

                <x-card>
                    <x-card.header>
                        <x-card.title>Información de la tienda</x-card.title>
                        <x-card.description>Datos públicos que aparecen en facturas y emails al cliente.</x-card.description>
                    </x-card.header>
                    <x-card.content class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <x-label for="store-name">Nombre de la tienda</x-label>
                                <x-input id="store-name" value="TechStore Argentina" />
                            </div>
                            <div class="space-y-1.5">
                                <x-label for="store-email">Email de soporte</x-label>
                                <x-input id="store-email" type="email" value="soporte@techstore.com.ar" />
                            </div>
                            <div class="space-y-1.5">
                                <x-label for="store-phone">Teléfono</x-label>
                                <x-input id="store-phone" value="+54 11 4000-0000" />
                            </div>
                            <div class="space-y-1.5">
                                <x-label for="store-web">Sitio web</x-label>
                                <x-input id="store-web" value="techstore.com.ar" />
                            </div>
                            <div class="md:col-span-2 space-y-1.5">
                                <x-label for="store-address">Dirección fiscal</x-label>
                                <x-input id="store-address" value="Av. Corrientes 1234, CABA, Buenos Aires" />
                            </div>
                        </div>
                    </x-card.content>
                    <x-card.footer class="gap-2">
                        <x-button variant="outline">Cancelar</x-button>
                        <x-button @click="$dispatch('toast', { message: 'Información guardada correctamente', variant: 'success' })">
                            Guardar cambios
                        </x-button>
                    </x-card.footer>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Preferencias regionales</x-card.title>
                        <x-card.description>Afectan el formato de precios, fechas y el idioma de la plataforma.</x-card.description>
                    </x-card.header>
                    <x-card.content class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <x-label>País</x-label>
                                <x-select>
                                    <option selected>Argentina</option>
                                    <option>México</option>
                                    <option>Colombia</option>
                                    <option>Chile</option>
                                    <option>España</option>
                                </x-select>
                            </div>
                            <div class="space-y-1.5">
                                <x-label>Zona horaria</x-label>
                                <x-select>
                                    <option selected>America/Buenos_Aires (UTC-3)</option>
                                    <option>America/Mexico_City (UTC-6)</option>
                                    <option>America/Bogota (UTC-5)</option>
                                </x-select>
                            </div>
                            <div class="space-y-1.5">
                                <x-label>Moneda</x-label>
                                <x-select>
                                    <option selected>ARS — Peso Argentino</option>
                                    <option>USD — Dólar Estadounidense</option>
                                    <option>MXN — Peso Mexicano</option>
                                </x-select>
                            </div>
                            <div class="space-y-1.5">
                                <x-label>Formato de fecha</x-label>
                                <x-select>
                                    <option selected>DD/MM/YYYY</option>
                                    <option>MM/DD/YYYY</option>
                                    <option>YYYY-MM-DD</option>
                                </x-select>
                            </div>
                        </div>
                    </x-card.content>
                    <x-card.footer class="gap-2">
                        <x-button variant="outline">Cancelar</x-button>
                        <x-button @click="$dispatch('toast', { message: 'Preferencias guardadas', variant: 'success' })">
                            Guardar cambios
                        </x-button>
                    </x-card.footer>
                </x-card>

            </x-tabs.content>

            {{-- ── Tab: Notificaciones ───────────────────────────────────────────── --}}
            <x-tabs.content value="notificaciones" class="space-y-4">

                @foreach([
                    ['title'=>'Pedidos', 'items'=>[
                        ['label'=>'Nuevo pedido recibido',    'desc'=>'Notificación al recibir cada nuevo pedido',           'on'=>true],
                        ['label'=>'Pago confirmado',          'desc'=>'Alerta cuando un pago es procesado con éxito',         'on'=>true],
                        ['label'=>'Pedido enviado',           'desc'=>'Notificación al actualizar el estado a "Enviado"',     'on'=>true],
                        ['label'=>'Pedido cancelado',         'desc'=>'Alerta cuando un cliente cancela su pedido',           'on'=>false],
                    ]],
                    ['title'=>'Inventario', 'items'=>[
                        ['label'=>'Stock bajo (< 10 uds.)',   'desc'=>'Alerta cuando un producto baja de 10 unidades',        'on'=>true],
                        ['label'=>'Sin stock',                'desc'=>'Alerta inmediata cuando un producto llega a 0',        'on'=>true],
                    ]],
                    ['title'=>'Clientes', 'items'=>[
                        ['label'=>'Nuevo registro',           'desc'=>'Notificación por cada nuevo cliente registrado',       'on'=>false],
                        ['label'=>'Cancelación de cuenta',    'desc'=>'Alerta cuando un cliente elimina su cuenta',           'on'=>true],
                    ]],
                    ['title'=>'Reportes', 'items'=>[
                        ['label'=>'Resumen semanal por email','desc'=>'Recibí un resumen de métricas cada lunes a las 9 AM',  'on'=>true],
                        ['label'=>'Reporte mensual',          'desc'=>'Informe completo al inicio de cada mes',               'on'=>true],
                    ]],
                ] as $group)
                <x-card>
                    <x-card.header>
                        <x-card.title>{{ $group['title'] }}</x-card.title>
                    </x-card.header>
                    <x-card.content class="space-y-4">
                        @foreach($group['items'] as $item)
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ $item['label'] }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ $item['desc'] }}</p>
                            </div>
                            <x-switch :checked="$item['on']" />
                        </div>
                        @if(!$loop->last)<x-separator />@endif
                        @endforeach
                    </x-card.content>
                </x-card>
                @endforeach

                <div class="flex justify-end">
                    <x-button @click="$dispatch('toast', { message: 'Preferencias de notificaciones guardadas', variant: 'success' })">
                        Guardar preferencias
                    </x-button>
                </div>

            </x-tabs.content>

            {{-- ── Tab: Seguridad ───────────────────────────────────────────────── --}}
            <x-tabs.content value="seguridad" class="space-y-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Cambiar contraseña</x-card.title>
                        <x-card.description>Usá una contraseña de al menos 8 caracteres con letras y números.</x-card.description>
                    </x-card.header>
                    <x-card.content class="space-y-4 max-w-md">
                        <div class="space-y-1.5">
                            <x-label for="pass-current">Contraseña actual</x-label>
                            <x-input id="pass-current" type="password" placeholder="••••••••" />
                        </div>
                        <div class="space-y-1.5">
                            <x-label for="pass-new">Nueva contraseña</x-label>
                            <x-input id="pass-new" type="password" placeholder="••••••••" />
                        </div>
                        <div class="space-y-1.5">
                            <x-label for="pass-confirm">Confirmar nueva contraseña</x-label>
                            <x-input id="pass-confirm" type="password" placeholder="••••••••" />
                        </div>
                    </x-card.content>
                    <x-card.footer>
                        <x-button @click="$dispatch('toast', { message: 'Contraseña actualizada correctamente', variant: 'success' })">
                            Actualizar contraseña
                        </x-button>
                    </x-card.footer>
                </x-card>

                <x-card>
                    <x-card.header>
                        <div class="flex items-center justify-between">
                            <div>
                                <x-card.title>Autenticación de dos factores</x-card.title>
                                <x-card.description>Agregá una capa extra de seguridad a tu cuenta.</x-card.description>
                            </div>
                            <x-badge variant="warning">Desactivada</x-badge>
                        </div>
                    </x-card.header>
                    <x-card.content>
                        <p class="text-sm text-muted-foreground mb-4">
                            Con 2FA activada, necesitarás tu contraseña más un código de autenticador al iniciar sesión.
                        </p>
                        <x-button variant="outline"
                            @click="$dispatch('toast', { message: 'Configuración de 2FA — próximamente', variant: 'default' })">
                            Activar 2FA
                        </x-button>
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Sesiones activas</x-card.title>
                        <x-card.description>Dispositivos con sesión abierta en tu cuenta.</x-card.description>
                    </x-card.header>
                    <x-card.content class="p-0">
                        <x-table>
                            <x-table.header>
                                <x-table.row>
                                    <x-table.head>Dispositivo</x-table.head>
                                    <x-table.head>Ubicación</x-table.head>
                                    <x-table.head>IP</x-table.head>
                                    <x-table.head>Última actividad</x-table.head>
                                    <x-table.head class="text-right">Acción</x-table.head>
                                </x-table.row>
                            </x-table.header>
                            <x-table.body>
                                @foreach([
                                    ['device'=>'Chrome · Windows 10','location'=>'Buenos Aires, AR','ip'=>'186.54.xx.xx','time'=>'Ahora','current'=>true],
                                    ['device'=>'Safari · iPhone 15',  'location'=>'Buenos Aires, AR','ip'=>'190.21.xx.xx','time'=>'Hace 2 h', 'current'=>false],
                                    ['device'=>'Firefox · macOS',     'location'=>'Córdoba, AR',     'ip'=>'200.12.xx.xx','time'=>'Ayer',     'current'=>false],
                                ] as $session)
                                <x-table.row>
                                    <x-table.cell>
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-2 rounded-full {{ $session['current'] ? 'bg-success' : 'bg-muted-foreground' }}"></div>
                                            <span class="text-sm">{{ $session['device'] }}</span>
                                            @if($session['current'])<x-badge variant="outline" class="text-xs ml-1">Esta sesión</x-badge>@endif
                                        </div>
                                    </x-table.cell>
                                    <x-table.cell class="text-sm text-muted-foreground">{{ $session['location'] }}</x-table.cell>
                                    <x-table.cell class="font-mono text-xs text-muted-foreground">{{ $session['ip'] }}</x-table.cell>
                                    <x-table.cell class="text-sm text-muted-foreground">{{ $session['time'] }}</x-table.cell>
                                    <x-table.cell class="text-right">
                                        @if(!$session['current'])
                                        <x-button variant="ghost" size="sm" class="text-xs text-destructive hover:text-destructive"
                                            @click="$dispatch('toast', { message: 'Sesión cerrada', variant: 'default' })">
                                            Cerrar
                                        </x-button>
                                        @endif
                                    </x-table.cell>
                                </x-table.row>
                                @endforeach
                            </x-table.body>
                        </x-table>
                    </x-card.content>
                    <x-card.footer>
                        <x-button variant="outline" class="text-destructive hover:text-destructive"
                            @click="$dispatch('toast', { message: 'Todas las otras sesiones fueron cerradas', variant: 'default' })">
                            Cerrar todas las otras sesiones
                        </x-button>
                    </x-card.footer>
                </x-card>

            </x-tabs.content>

            {{-- ── Tab: Apariencia ──────────────────────────────────────────────── --}}
            <x-tabs.content value="apariencia" class="space-y-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Tema de la interfaz</x-card.title>
                        <x-card.description>Elegí cómo se muestra la aplicación.</x-card.description>
                    </x-card.header>
                    <x-card.content>
                        <div class="grid grid-cols-3 gap-3 max-w-sm">
                            @foreach([['value'=>'system','label'=>'Sistema'],['value'=>'light','label'=>'Claro'],['value'=>'dark','label'=>'Oscuro']] as $theme)
                            <label class="flex flex-col items-center gap-2 cursor-pointer">
                                <div class="h-16 w-full rounded-lg border-2 {{ $theme['value'] === 'dark' ? 'border-primary bg-zinc-900' : 'border-border bg-background' }} flex items-center justify-center">
                                    @if($theme['value'] === 'system')
                                    <div class="h-8 w-8 rounded-md border bg-muted"></div>
                                    @elseif($theme['value'] === 'light')
                                    <div class="h-8 w-8 rounded-md bg-white border shadow-sm"></div>
                                    @else
                                    <div class="h-8 w-8 rounded-md bg-zinc-800 border border-zinc-700"></div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-radio name="theme" value="{{ $theme['value'] }}" :checked="$theme['value'] === 'dark'" />
                                    <span class="text-sm">{{ $theme['label'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Idioma y región</x-card.title>
                    </x-card.header>
                    <x-card.content class="space-y-4 max-w-sm">
                        <div class="space-y-1.5">
                            <x-label>Idioma de la interfaz</x-label>
                            <x-select>
                                <option selected>Español (Argentina)</option>
                                <option>Español (España)</option>
                                <option>English (US)</option>
                                <option>Português (BR)</option>
                            </x-select>
                        </div>
                        <div class="space-y-1.5">
                            <x-label>Formato numérico</x-label>
                            <x-select>
                                <option selected>1.234,56 (punto miles, coma decimal)</option>
                                <option>1,234.56 (coma miles, punto decimal)</option>
                            </x-select>
                        </div>
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Densidad de la interfaz</x-card.title>
                        <x-card.description>Controla el espaciado de tablas y listas.</x-card.description>
                    </x-card.header>
                    <x-card.content class="space-y-3">
                        @foreach([['value'=>'compact','label'=>'Compacta','desc'=>'Más filas visibles, menos espacio entre elementos'],['value'=>'comfortable','label'=>'Cómoda','desc'=>'Balance entre densidad y legibilidad (recomendado)'],['value'=>'spacious','label'=>'Espaciosa','desc'=>'Mayor separación, ideal para pantallas grandes']] as $density)
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border hover:bg-accent transition-colors {{ $density['value'] === 'comfortable' ? 'border-primary bg-accent' : '' }}">
                            <x-radio name="density" value="{{ $density['value'] }}" :checked="$density['value'] === 'comfortable'" class="mt-0.5" />
                            <div>
                                <p class="text-sm font-medium">{{ $density['label'] }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ $density['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </x-card.content>
                    <x-card.footer>
                        <x-button @click="$dispatch('toast', { message: 'Preferencias de apariencia guardadas', variant: 'success' })">
                            Guardar preferencias
                        </x-button>
                    </x-card.footer>
                </x-card>

            </x-tabs.content>

        </x-tabs>
    </div>
</x-layouts.dashboard>
