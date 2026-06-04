@php
    $currentDoc = $docs[$slug];
@endphp

<x-layouts.app title="Manual de uso">

{{-- Mobile: selector de sección --}}
<div class="mb-4 lg:hidden">
    <div class="relative">
        <x-lucide-book-open class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
        <select
            onchange="window.location.href = this.value"
            class="h-9 w-full appearance-none rounded-md border border-input bg-background pl-9 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
        >
            @foreach($docs as $docSlug => $doc)
                <option value="{{ route('manual.show', $docSlug) }}" @selected($docSlug === $slug)>
                    {{ $doc['label'] }}
                </option>
            @endforeach
        </select>
        <x-lucide-chevron-down class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
    </div>
</div>

<div class="flex flex-col gap-4 lg:flex-row lg:gap-6 lg:items-start">

    {{-- Sidebar (solo desktop) --}}
    <nav class="hidden lg:block shrink-0 lg:sticky lg:top-4 lg:w-52">
        <x-ui.card class="overflow-hidden p-0">

            <div class="flex items-center gap-2 border-b border-border px-4 py-3">
                <x-lucide-book-open class="size-3.5 shrink-0 text-muted-foreground" />
                <p class="text-overline">Manual</p>
            </div>

            <ul class="p-1.5 space-y-0.5">
                @foreach($docs as $docSlug => $doc)
                    @php $isActive = $docSlug === $slug; @endphp
                    <li>
                        <a
                            href="{{ route('manual.show', $docSlug) }}"
                            @class([
                                'flex items-center gap-2.5 rounded-md px-3 py-2 text-sm transition-colors',
                                'bg-primary/10 text-primary font-medium'              => $isActive,
                                'text-muted-foreground hover:bg-accent hover:text-foreground' => ! $isActive,
                            ])
                        >
                            <x-dynamic-component
                                :component="'lucide-' . $doc['icon']"
                                @class(['size-3.5 shrink-0', 'text-primary' => $isActive, 'text-muted-foreground' => ! $isActive])
                            />
                            {{ $doc['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

        </x-ui.card>
    </nav>

    {{-- Contenido --}}
    <div class="min-w-0 flex-1 space-y-0">
        <x-ui.card class="overflow-hidden">

            {{-- Header del documento activo --}}
            <x-ui.card.header class="border-b border-border bg-muted/30">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                        <x-dynamic-component
                            :component="'lucide-' . $currentDoc['icon']"
                            class="size-4 text-primary"
                        />
                    </div>
                    <div>
                        <x-ui.card.title>{{ $currentDoc['label'] }}</x-ui.card.title>
                        <p class="text-caption mt-0.5">Manual de uso</p>
                    </div>
                </div>
            </x-ui.card.header>

            {{-- Cuerpo --}}
            <x-ui.card.content class="pt-6">
                <div class="prose">
                    {!! $content !!}
                </div>
            </x-ui.card.content>

        </x-ui.card>
    </div>

</div>

</x-layouts.app>
