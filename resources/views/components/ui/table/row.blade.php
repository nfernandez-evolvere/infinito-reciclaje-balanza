@props(['state' => null, 'selected' => false])

@php
$stateClass = match($state) {
    'destructive' => 'bg-destructive-subtle',
    'success'     => 'bg-success-subtle',
    'warning'     => 'bg-warning-subtle',
    'info'        => 'bg-info-subtle',
    default       => '',
};
@endphp

<tr
    @if($selected) data-state="selected" @endif
    {{ $attributes->twMerge(
        'block rounded-lg border border-border transition-colors',
        $stateClass,
        'sm:table-row sm:rounded-none sm:border-0 sm:border-b sm:border-border sm:hover:bg-accent/40 sm:data-[state=selected]:bg-muted'
    ) }}
>
    {{ $slot }}
</tr>
