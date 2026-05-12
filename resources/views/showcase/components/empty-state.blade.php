<x-layouts.showcase title="Empty State — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Empty State</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Componente para secciones sin datos. Acepta título, descripción, ícono opcional y un slot para una acción CTA.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Solo título</x-ui.typography>
        <x-ui.empty-state title="Sin resultados" />
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con descripción</x-ui.typography>
        <x-ui.empty-state
            title="No hay proyectos"
            description="Todavía no creaste ningún proyecto. Empezá creando uno nuevo."
        />
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con ícono</x-ui.typography>
        <x-ui.empty-state
            title="Bandeja de entrada vacía"
            description="No tenés mensajes sin leer. Volvé más tarde."
            icon="circle-info"
        />
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con acción</x-ui.typography>
        <x-ui.empty-state
            title="No hay integrantes"
            description="Todavía no agregaste integrantes al equipo."
            icon="plus"
        >
            <x-ui.button size="sm">
                <x-lucide-plus />
                Agregar integrante
            </x-ui.button>
        </x-ui.empty-state>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Uso en tabla</x-ui.typography>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.row>
                    <x-ui.table.head>Nombre</x-ui.table.head>
                    <x-ui.table.head>Estado</x-ui.table.head>
                    <x-ui.table.head>Fecha</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                <tr>
                    <td colspan="3" class="p-0">
                        <x-ui.empty-state
                            title="Sin registros"
                            description="Aplicá otro filtro o creá un nuevo registro."
                            class="border-0 rounded-none"
                        />
                    </td>
                </tr>
            </x-ui.table.body>
        </x-ui.table>
    </section>

</div>
</x-layouts.showcase>
