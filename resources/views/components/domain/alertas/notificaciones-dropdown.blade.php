@props(['count' => 0])

<div x-data="notificaciones({ count: {{ $count }}, urls: { novedades: '{{ route('admin.alertas.novedades') }}', leerTodas: '{{ route('admin.alertas.leer-todas') }}' } })"
    @keydown.escape.window="open = false" class="relative">

    {{-- Trigger — mismo patrón que tema y logout en el header --}}
    <x-ui.tooltip content="Alertas" side="bottom">
        <x-ui.button size="icon" variant="ghost" @click="toggle()" aria-label="Alertas" class="relative">
            <x-lucide-bell class="size-4.5" />
            <span x-show="count > 0" x-cloak
                x-text="count > 9 ? '9+' : count"
                class="absolute -top-0.5 -right-0.5 flex min-w-4 h-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-bold text-destructive-foreground leading-none pointer-events-none">
            </span>
        </x-ui.button>
    </x-ui.tooltip>

    {{-- Desktop: panel dropdown absoluto --}}
    <div x-show="open && !esMobile" x-cloak
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-full mt-2 w-80 origin-top-right rounded-lg bg-popover text-popover-foreground shadow-lg ring-1 ring-foreground/10"
        style="z-index: var(--z-popover, 50)">

        <div class="flex items-center justify-between border-b border-border px-4 py-3">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold">Alertas</span>
                <span x-show="count > 0" x-cloak x-text="count + ' sin leer'" class="text-xs text-muted-foreground"></span>
            </div>
            <x-ui.button x-show="count > 0" x-cloak variant="link" @click="marcarTodas()">
                Marcar todas como leídas
            </x-ui.button>
        </div>

        <div class="max-h-80 overflow-y-auto">
            <div x-show="loading" class="divide-y divide-border/50">
                @for ($i = 0; $i < 3; $i++)
                <div class="flex gap-3 px-4 py-3">
                    <x-ui.skeleton class="mt-0.5 size-7 shrink-0 rounded-full" />
                    <div class="flex-1 space-y-1.5">
                        <x-ui.skeleton class="h-3 w-3/4" />
                        <x-ui.skeleton class="h-3 w-full" />
                        <x-ui.skeleton class="h-2.5 w-1/3" />
                    </div>
                </div>
                @endfor
            </div>
            <x-ui.empty-state x-show="!loading && items.length === 0"
                title="Sin alertas pendientes"
                icon="bell"
                class="border-0 bg-transparent shadow-none py-8 px-4" />
            <template x-for="item in items" :key="item.id">
                <div class="flex gap-3 border-b border-border/50 px-4 py-3 last:border-0 hover:bg-accent/50 transition-colors">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium truncate" x-text="item.titulo"></p>
                        <p class="text-xs text-muted-foreground mt-0.5 line-clamp-2" x-text="item.descripcion"></p>
                        <div class="flex items-center justify-between gap-2 mt-1">
                            <p class="text-[11px] text-muted-foreground/70" x-text="item.hace"></p>
                            <a x-show="item.url" x-cloak :href="item.url" @click="open = false"
                               class="text-[11px] font-medium text-primary hover:underline shrink-0">
                                <span x-text="item.url_label"></span> →
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="border-t border-border px-4 py-2.5 text-center">
            <x-ui.button href="{{ route('admin.alertas.index') }}" variant="link" @click="open = false">
                Ver todas las alertas
            </x-ui.button>
        </div>
    </div>

    {{-- Mobile: sheet lateral derecho --}}
    <x-ui.sheet controlledBy="sheetOpen" side="right">
        <x-ui.sheet.header>
            <x-ui.typography as="h4" class="flex items-center gap-2">
                <x-lucide-bell class="size-5" />
                Alertas
                <span x-show="count > 0" x-cloak
                    x-text="'(' + count + ' sin leer)'"
                    class="text-sm font-normal text-muted-foreground">
                </span>
            </x-ui.typography>
        </x-ui.sheet.header>

        <div class="flex-1 min-h-0 overflow-y-auto">
            <div x-show="loading" class="divide-y divide-border/50">
                @for ($i = 0; $i < 4; $i++)
                <div class="flex gap-3 px-5 py-4">
                    <x-ui.skeleton class="mt-0.5 size-8 shrink-0 rounded-full" />
                    <div class="flex-1 space-y-1.5">
                        <x-ui.skeleton class="h-3.5 w-3/4" />
                        <x-ui.skeleton class="h-3 w-full" />
                        <x-ui.skeleton class="h-3 w-1/3" />
                    </div>
                </div>
                @endfor
            </div>
            <x-ui.empty-state x-show="!loading && items.length === 0"
                title="Sin alertas pendientes"
                icon="bell"
                class="border-0 bg-transparent shadow-none py-12 px-5" />
            <template x-for="item in items" :key="item.id">
                <div class="flex gap-3 border-b border-border/50 px-5 py-4 last:border-0">
                    <div class="mt-0.5 shrink-0 flex size-8 items-center justify-center rounded-full bg-warning/15">
                        <x-lucide-triangle-alert class="size-4 text-warning" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium" x-text="item.titulo"></p>
                        <p class="text-xs text-muted-foreground mt-0.5" x-text="item.descripcion"></p>
                        <div class="flex items-center justify-between gap-2 mt-1">
                            <p class="text-xs text-muted-foreground/70" x-text="item.hace"></p>
                            <a x-show="item.url" x-cloak :href="item.url" @click="sheetOpen = false"
                               class="text-xs font-medium text-primary hover:underline shrink-0">
                                <span x-text="item.url_label"></span> →
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <x-ui.sheet.footer class="flex-row">
            <x-ui.button x-show="count > 0" x-cloak variant="secondary" class="flex-1" @click="marcarTodas()">
                <x-lucide-check-check class="size-4" />
                Marcar leídas
            </x-ui.button>
            <x-ui.button href="{{ route('admin.alertas.index') }}" x-bind:class="count > 0 ? 'flex-1' : 'w-full'" @click="sheetOpen = false">
                Ver todas
            </x-ui.button>
        </x-ui.sheet.footer>
    </x-ui.sheet>

</div>
