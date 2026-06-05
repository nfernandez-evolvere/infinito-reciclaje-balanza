{{-- Estado vacío de KPIs — sin actividad registrada ni en el día ni en el mes --}}
<x-ui.card variant="elevated">
    <x-ui.card.content>
        <div class="flex flex-col items-center justify-center py-12 text-center gap-3">
            <div class="flex size-11 items-center justify-center rounded-full bg-muted">
                <x-lucide-sprout class="size-5 text-muted-foreground" />
            </div>
            <div class="space-y-1 max-w-sm">
                <p class="text-sm font-medium">Todavía no hay pesajes para mostrar</p>
                <p class="text-xs text-muted-foreground">
                    En cuanto se registre el primer pesaje, las métricas del día y del mes van a aparecer acá automáticamente.
                </p>
            </div>
        </div>
    </x-ui.card.content>
</x-ui.card>
