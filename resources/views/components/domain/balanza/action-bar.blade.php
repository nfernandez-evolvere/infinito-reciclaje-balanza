<template x-teleport="#layout-action-bar">
    <div class="bg-card border-t border-border px-4 sm:px-6 py-2 flex items-center gap-3">
        <x-ui.button variant="outline" @click="limpiar()">
            <x-lucide-rotate-ccw x-show="!editMode" class="size-4" />
            <x-lucide-arrow-left x-show="editMode" x-cloak class="size-4" />
            <span class="hidden sm:inline" x-text="editMode ? 'Cancelar' : 'Limpiar'">Limpiar</span>
        </x-ui.button>

        {{-- Resumen mobile --}}
        <x-ui.button variant="ghost" @click="mobileResumenAbierto = true" class="lg:hidden">
            <x-lucide-clipboard-list class="size-4" />
            <span class="hidden sm:inline">Resumen</span>
        </x-ui.button>

        <div class="flex-1"></div>

        <span class="hidden sm:inline text-sm font-medium text-muted-foreground" x-text="hintContextual"></span>

        <x-ui.button class="uppercase tracking-widest font-bold" @click="guardar()" x-bind:disabled="!canSave">
            <x-lucide-save class="size-4" />
            <span x-text="editMode ? 'Guardar cambios' : 'Guardar pesaje'">Guardar pesaje</span>
        </x-ui.button>
    </div>
</template>
