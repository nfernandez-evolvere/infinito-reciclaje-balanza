<div class="relative">
    <select {{ $attributes->twMerge('w-full text-[15px] py-3 pl-3.5 pr-10 rounded-md border border-border bg-background text-foreground outline-none appearance-none transition-[border-color,box-shadow] duration-150 focus:border-primary focus:ring-2 focus:ring-primary/20') }}>
        {{ $slot }}
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
        <x-lucide-chevron-down class="size-4 text-muted-foreground" />
    </div>
</div>
