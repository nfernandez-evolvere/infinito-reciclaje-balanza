<x-layouts.app title="Pesaje">

<div
    x-data="balanza()"
    x-init="init()"
    @keydown.window="onKey($event)"
    x-effect="setBeforeUnload(sucio)"
    class="flex flex-col gap-6"
>

    <div>
        <x-ui.typography as="h2">Registro de pesaje</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1">Seguí los tres pasos. Los datos del padrón se completan solos.</x-ui.typography>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-start gap-4 lg:gap-6">

        {{-- Columna izquierda: Pasos --}}
        <div class="flex-1 flex flex-col gap-4 min-w-0">
            <x-domain.balanza.paso-vehiculo />
            <x-domain.balanza.paso-servicio :servicios="$servicios" />
            <x-domain.balanza.paso-peso />
        </div>

        {{-- Columna derecha: Resumen (solo desktop) --}}
        <div class="hidden lg:block lg:w-72 xl:w-80 shrink-0 lg:sticky lg:top-4">
            <x-domain.balanza.resumen-card />
        </div>

    </div>

    <x-domain.balanza.mobile-drawer />

    <form method="POST" action="{{ route('pesajes.store') }}" x-ref="form" class="hidden">
        @csrf
        <input type="hidden" name="vehiculo_id"      x-bind:value="vehiculo?.id">
        <input type="hidden" name="tipo_servicio_id" x-bind:value="servicioId">
        <input type="hidden" name="zona_id"          x-bind:value="zonaId">
        <input type="hidden" name="turno"            x-bind:value="turno">
        <input type="hidden" name="peso_bruto_kg"    x-bind:value="brutoN">
    </form>

    <x-domain.balanza.action-bar />
    <x-domain.balanza.confirm-dialog />

</div>

@if(!auth()->user()->onboarding_visto)
    <x-onboarding.bienvenida-operador :forzar="true" />
@else
    <x-onboarding.bienvenida-operador :forzar="false" />
@endif

</x-layouts.app>
