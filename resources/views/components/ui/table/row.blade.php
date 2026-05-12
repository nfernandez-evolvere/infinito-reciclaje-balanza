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
        'border-b border-border transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted',
        $stateClass
    ) }}
>
    {{ $slot }}
</tr>
