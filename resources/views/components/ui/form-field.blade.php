@props([
    'state'   => null,   // null | destructive | success | warning | info
    'message' => null,   // texto del helper-text
    'label'   => null,   // label del campo (opcional, alternativa al slot label)
    'for'     => null,   // id del input asociado
])

{{--
    Wrapper que propaga el estado semántico a label + input + helper-text.
    Uso:
        <x-ui.form-field state="destructive" message="Este campo es requerido" for="email">
            <x-ui.label>Email</x-ui.label>
            <x-ui.input id="email" type="email" :state="$state ?? null" />
        </x-ui.form-field>
--}}

<div {{ $attributes->twMerge('grid gap-1.5') }}>
    {{ $slot }}
    @if($message)
        <x-ui.helper-text :state="$state" :message="$message" />
    @endif
</div>
