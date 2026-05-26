@props(['state' => null, 'selected' => false])

@php
$stateClass = match($state) {
    'destructive' => 'bg-destructive-subtle sm:bg-destructive-subtle',
    'success'     => 'bg-success-subtle sm:bg-success-subtle',
    'warning'     => 'bg-warning-subtle sm:bg-warning-subtle',
    'info'        => 'bg-info-subtle sm:bg-info-subtle',
    default       => 'bg-border sm:bg-transparent',
};
@endphp

<tr
    @if($selected) data-state="selected" @endif
    {{ $attributes->twMerge(
        'flex flex-col gap-[1px] p-[1px] rounded-lg overflow-hidden transition-colors',
        $stateClass,
        'sm:table-row sm:p-0 sm:gap-0 sm:rounded-none sm:overflow-visible sm:border-b sm:border-border sm:hover:bg-accent/40 sm:data-[state=selected]:bg-muted'
    ) }}
>
    {{ $slot }}
</tr>
