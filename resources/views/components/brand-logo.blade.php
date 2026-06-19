@props([])

{{-- Logo de marca. El tamaño lo controla quien lo usa con clases (ej: size-6). --}}
<img
    src="{{ asset('favicon.png') }}"
    alt="{{ config('app.name') }}"
    {{ $attributes->merge(['class' => 'shrink-0 object-contain']) }}
>
