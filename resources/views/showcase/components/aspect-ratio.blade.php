<x-layouts.showcase title="Aspect Ratio — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Aspect Ratio</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Wrapper que mantiene una relación de aspecto fija para imágenes, videos y embeds. Acepta cualquier valor numérico como <x-ui.typography as="code">ratio</x-ui.typography>.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Ratios comunes</x-ui.typography>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['16/9' => '16:9', '4/3' => '4:3', '1/1' => '1:1', '9/16' => '9:16'] as $ratio => $label)
                <div class="space-y-2">
                    <x-ui.aspect-ratio :ratio="$ratio" class="rounded-lg bg-muted overflow-hidden">
                        <div class="flex h-full items-center justify-center text-sm text-muted-foreground font-medium">
                            {{ $label }}
                        </div>
                    </x-ui.aspect-ratio>
                    <x-ui.typography as="muted" class="text-xs text-center">ratio="{{ $ratio }}"</x-ui.typography>
                </div>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con imagen</x-ui.typography>
        <div class="max-w-md">
            <x-ui.aspect-ratio ratio="16/9" class="rounded-lg overflow-hidden bg-muted">
                <img
                    src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80"
                    alt="Montaña"
                    class="h-full w-full object-cover"
                />
            </x-ui.aspect-ratio>
        </div>
    </section>

</div>
</x-layouts.showcase>
