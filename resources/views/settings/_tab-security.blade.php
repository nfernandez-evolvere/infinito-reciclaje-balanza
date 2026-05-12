@php
$sessions = [
    ['device' => 'MacBook Pro', 'browser' => 'Chrome 125', 'location' => 'Buenos Aires, AR', 'last' => 'Ahora',       'current' => true],
    ['device' => 'iPhone 15',   'browser' => 'Safari / iOS', 'location' => 'Buenos Aires, AR', 'last' => 'hace 2 h',    'current' => false],
    ['device' => 'Windows PC',  'browser' => 'Firefox 126',  'location' => 'Córdoba, AR',      'last' => 'hace 3 días', 'current' => false],
];
@endphp

<div class="space-y-6 pt-4">

    {{-- Cambiar contraseña --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Contraseña</x-ui.card.title>
            <x-ui.card.description>Usá una contraseña larga y única para proteger tu cuenta.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="space-y-4">
            <x-ui.form-field for="s-current">
                <x-ui.label for="s-current">Contraseña actual</x-ui.label>
                <x-ui.input id="s-current" type="password" placeholder="••••••••" />
            </x-ui.form-field>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.form-field for="s-new">
                    <x-ui.label for="s-new">Nueva contraseña</x-ui.label>
                    <x-ui.input id="s-new" type="password" placeholder="Mínimo 8 caracteres" />
                </x-ui.form-field>
                <x-ui.form-field for="s-confirm">
                    <x-ui.label for="s-confirm">Confirmar contraseña</x-ui.label>
                    <x-ui.input id="s-confirm" type="password" placeholder="Repetí la nueva" />
                </x-ui.form-field>
            </div>
        </x-ui.card.content>
        <x-ui.card.footer class="justify-end">
            <x-ui.button @click="$store.toast.success('Contraseña actualizada')">
                Actualizar contraseña
            </x-ui.button>
        </x-ui.card.footer>
    </x-ui.card>

    {{-- 2FA --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Autenticación de dos factores</x-ui.card.title>
            <x-ui.card.description>Agregá una capa extra de seguridad a tu cuenta.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <div class="flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <x-ui.typography as="small" element="p">Activar 2FA con app de autenticación</x-ui.typography>
                    <x-ui.typography as="muted" class="text-xs">Google Authenticator, Authy, 1Password, etc.</x-ui.typography>
                </div>

                <x-ui.dialog>
                    <x-ui.dialog.trigger>
                        <x-ui.button variant="outline" size="sm">
                            <x-lucide-shield class="size-4" />
                            Configurar 2FA
                        </x-ui.button>
                    </x-ui.dialog.trigger>
                    <x-ui.dialog.content size="sm">
                        <x-ui.dialog.header>
                            <x-ui.dialog.title>Configurar autenticación de dos factores</x-ui.dialog.title>
                            <x-ui.dialog.description>
                                Escaneá el código QR con tu app de autenticación, luego ingresá el código generado.
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <div class="px-6 pb-2 space-y-6">
                            {{-- QR placeholder --}}
                            <div class="flex justify-center">
                                <div class="size-44 rounded-xl border-2 border-dashed border-border bg-muted flex items-center justify-center">
                                    <x-lucide-qr-code class="size-20 text-muted-foreground" />
                                </div>
                            </div>

                            {{-- OTP --}}
                            <div class="flex flex-col items-center gap-3">
                                <x-ui.typography as="muted" class="text-sm text-center">
                                    Ingresá el código de 6 dígitos de tu app
                                </x-ui.typography>
                                <x-ui.input-otp :length="6" pattern="[0-9]" />
                            </div>
                        </div>

                        <x-ui.dialog.footer>
                            <x-ui.dialog.close>
                                <x-ui.button variant="outline">Cancelar</x-ui.button>
                            </x-ui.dialog.close>
                            <x-ui.dialog.close>
                                <x-ui.button @click="$store.toast.success('2FA activado correctamente')">
                                    Verificar y activar
                                </x-ui.button>
                            </x-ui.dialog.close>
                        </x-ui.dialog.footer>
                    </x-ui.dialog.content>
                </x-ui.dialog>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Sesiones activas --}}
    <x-ui.card>
        <x-ui.card.header>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <x-ui.card.title>Sesiones activas</x-ui.card.title>
                    <x-ui.card.description>Dispositivos que tienen acceso a tu cuenta.</x-ui.card.description>
                </div>
                <x-ui.button
                    variant="outline"
                    size="sm"
                    state="destructive"
                    @click="$store.toast.success('Otras sesiones cerradas')"
                >
                    Cerrar otras sesiones
                </x-ui.button>
            </div>
        </x-ui.card.header>
        <x-ui.card.content class="p-0">
            <x-ui.table>
                <x-ui.table.body>
                    @foreach($sessions as $session)
                    <x-ui.table.row>
                        <x-ui.table.cell class="pl-6">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-md bg-muted flex items-center justify-center shrink-0 text-muted-foreground">
                                    @if(str_contains($session['device'], 'iPhone'))
                                        <x-lucide-smartphone class="size-4" />
                                    @else
                                        <x-lucide-monitor class="size-4" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">{{ $session['device'] }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $session['browser'] }} · {{ $session['location'] }}</p>
                                </div>
                            </div>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            @if($session['current'])
                                <x-ui.badge state="success" :subtle="true">Sesión actual</x-ui.badge>
                            @else
                                <x-ui.typography as="muted" class="text-xs">{{ $session['last'] }}</x-ui.typography>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell class="pr-6 text-right w-24">
                            @if(!$session['current'])
                                <x-ui.button
                                    variant="ghost"
                                    size="sm"
                                    state="destructive"
                                    @click="$store.toast.success('Sesión cerrada')"
                                >
                                    Revocar
                                </x-ui.button>
                            @endif
                        </x-ui.table.cell>
                    </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </x-ui.card.content>
    </x-ui.card>

</div>
