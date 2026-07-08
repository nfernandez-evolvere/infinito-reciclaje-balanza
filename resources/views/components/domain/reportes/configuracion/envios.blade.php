@props(['config'])

{{--
    Panel "Envíos": qué tipos de reporte están disponibles y si los programados
    requieren revisión manual antes de salir por email.
--}}
<div class="flex flex-col gap-6">
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Tipos de reporte activos</x-ui.card.title>
            <x-ui.card.description>Controlá qué tipos de reportes están disponibles para generar y programar.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-label">Reporte mensual (PDF)</p>
                        <p class="text-caption">Presentación con gráficos, tablas y conclusiones para el municipio.</p>
                    </div>
                    <x-ui.switch name="tipo_informe_mensual_activo" :checked="$config->tipo_informe_mensual_activo ?? true" />
                </div>
                <x-ui.separator />
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-label">Reporte de alertas</p>
                        <p class="text-caption">Lista de pesajes con alertas de peso y gaps operativos.</p>
                    </div>
                    <x-ui.switch name="tipo_alertas_activo" :checked="$config->tipo_alertas_activo ?? false" />
                </div>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Revisión de envíos</x-ui.card.title>
            <x-ui.card.description>Control manual de los reportes programados antes de que salgan por email.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <div class="flex items-center justify-between gap-2">
                <div>
                    <p class="text-label">Requerir revisión antes de enviar</p>
                    <p class="text-caption">Cada reporte generado queda pendiente en el historial hasta que lo apruebes. Cada programado puede sobreescribir esta opción.</p>
                </div>
                <x-ui.switch name="revision_requerida" :checked="$config->revision_requerida ?? true" />
            </div>
        </x-ui.card.content>
    </x-ui.card>
</div>
