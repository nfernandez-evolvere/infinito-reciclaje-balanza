<x-layouts.app title="Alertas">
<x-ui.tabs
    :value="$tab"
    class="flex flex-col gap-6"
    x-init="$watch('active', val => {
        const url = new URL(window.location);
        url.searchParams.set('tab', val);
        history.pushState({}, '', url);
    })"
>

    <div class="flex items-start justify-between gap-4">
        <div>
            <x-ui.typography as="h2">Alertas</x-ui.typography>
            <x-ui.typography as="muted">
                Detectadas automáticamente por el sistema.
                @if($noLeidas > 0)
                    <span class="font-medium text-warning-foreground">{{ $noLeidas }} sin leer.</span>
                @endif
            </x-ui.typography>
        </div>

        {{-- Marcar todas como leídas: solo en el tab de alertas y si hay alertas --}}
        @if($alertas->total() > 0)
            <form method="POST" action="{{ route('admin.alertas.leer-todas') }}" x-show="active === 'alertas'" x-cloak class="shrink-0">
                @csrf
                <x-ui.button type="submit" variant="ghost">
                    <x-lucide-check-check class="size-4" />
                    <span class="hidden sm:inline">Marcar todas como leídas</span>
                    <span class="sm:hidden">Marcar leídas</span>
                </x-ui.button>
            </form>
        @endif
    </div>

    <x-ui.tabs.list class="flex w-full sm:w-fit">
        <x-ui.tabs.trigger value="alertas" class="flex-1 sm:flex-none">
            <x-lucide-triangle-alert class="size-4" />
            <span>Alertas</span>
            @if($noLeidas > 0)
                <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-destructive px-1 text-[11px] font-bold text-destructive-foreground">
                    {{ $noLeidas > 99 ? '99+' : $noLeidas }}
                </span>
            @endif
        </x-ui.tabs.trigger>
        <x-ui.tabs.trigger value="configuracion" class="flex-1 sm:flex-none">
            <x-lucide-settings-2 class="size-4" />
            <span>Configuración</span>
        </x-ui.tabs.trigger>
    </x-ui.tabs.list>

    {{-- ── Tab: Alertas ── --}}
    <x-ui.tabs.content value="alertas" class="mt-0">
        <x-domain.alertas.tabla :alertas="$alertas" :filtros="$filtros" />
    </x-ui.tabs.content>

    {{-- ── Tab: Configuración ── --}}
    <x-ui.tabs.content value="configuracion" class="mt-0">
        <x-domain.alertas.form-config :config="$config" />
    </x-ui.tabs.content>

</x-ui.tabs>
</x-layouts.app>
