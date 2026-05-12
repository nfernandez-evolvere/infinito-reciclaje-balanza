{{--
    Siguiendo el patrón shadcn: el trigger envuelve automáticamente en <h3> (AccordionHeader).
    Si necesitás el header explícito, usá <x-ui.accordion.header> manualmente.
--}}
<h3 class="flex">
    <button
        type="button"
        data-accordion-trigger
        :data-item-value="itemValue"
        :aria-expanded="isOpen(itemValue).toString()"
        :data-state="isOpen(itemValue) ? 'open' : 'closed'"
        :data-disabled="(itemDisabled || accordionDisabled) ? '' : undefined"
        :data-orientation="orientation"
        :disabled="itemDisabled || accordionDisabled"
        @click="toggle(itemValue)"
        x-init="registerTrigger(itemValue, itemDisabled || accordionDisabled)"
        {{ $attributes->twMerge('flex w-full flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline text-left cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50') }}
    >
        {{ $slot }}
        <x-lucide-chevron-down class="size-4 shrink-0 text-muted-foreground transition-transform duration-200"
            x-bind:class="isOpen(itemValue) ? 'rotate-180' : ''" />
    </button>
</h3>
