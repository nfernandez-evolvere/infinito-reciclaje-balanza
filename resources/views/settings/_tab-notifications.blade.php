<div class="space-y-4 pt-4">
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Preferencias de notificaciones</x-ui.card.title>
            <x-ui.card.description>Elegí cómo y cuándo querés recibir notificaciones.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="space-y-1">

            <x-ui.typography as="section-label" element="p" class="pb-1">Email</x-ui.typography>
            <x-settings.notification-item
                label="Actividad de la cuenta"
                description="Pagos procesados, cambios de plan, alertas de seguridad."
                name="notif_account"
                :checked="true"
            />
            <x-ui.separator />
            <x-settings.notification-item
                label="Novedades del producto"
                description="Nuevas funcionalidades y mejoras."
                name="notif_product"
                :checked="true"
            />
            <x-ui.separator />
            <x-settings.notification-item
                label="Menciones en comentarios"
                description="Cuando alguien te menciona en un comentario."
                name="notif_mentions"
                :checked="false"
            />

            <div class="pt-4">
                <x-ui.typography as="section-label" element="p" class="pb-1">Push</x-ui.typography>
            </div>
            <x-settings.notification-item
                label="Alertas urgentes"
                description="Notificaciones que requieren acción inmediata."
                name="notif_push_urgent"
                :checked="true"
            />
            <x-ui.separator />
            <x-settings.notification-item
                label="Recordatorios de tareas"
                description="Alertas cuando una tarea está por vencer."
                name="notif_push_tasks"
                :checked="false"
            />

            <div class="pt-4">
                <x-ui.typography as="section-label" element="p" class="pb-1">Resumen semanal</x-ui.typography>
            </div>
            <x-settings.notification-item
                label="Resumen de actividad"
                description="Un resumen con las métricas y actividad más importante de la semana."
                name="notif_weekly"
                :checked="true"
            />

        </x-ui.card.content>
        <x-ui.card.footer class="justify-end">
            <x-ui.button @click="$store.toast.success('Preferencias guardadas')">
                Guardar preferencias
            </x-ui.button>
        </x-ui.card.footer>
    </x-ui.card>
</div>
