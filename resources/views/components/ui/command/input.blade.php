@props(['placeholder' => 'Buscar...'])

<div class="border-b border-border px-3">
    <x-ui.input
        type="text"
        x-model="search"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        autocorrect="off"
        spellcheck="false"
        class="border-0 shadow-none focus-visible:ring-0 focus-visible:ring-offset-0 bg-transparent"
        {{ $attributes }}
    >
        <x-slot:leading>
            <x-lucide-search />
        </x-slot:leading>
    </x-ui.input>
</div>
