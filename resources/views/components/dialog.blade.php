@props(['open' => false])

<div x-data="{
    open: {{ $open ? 'true' : 'false' }},
    previousFocus: null,
    dialogId: 'dialog-' + Math.random().toString(36).slice(2, 9)
}" {{ $attributes }}>
    {{ $slot }}
</div>
