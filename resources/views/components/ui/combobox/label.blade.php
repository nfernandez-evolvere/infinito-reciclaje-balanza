{{--
    Group header label inside <x-ui.combobox.group>.
--}}
<div
    role="presentation"
    {{ $attributes->twMerge('px-2 py-1.5 text-xs font-semibold text-muted-foreground select-none') }}
>
    {{ $slot }}
</div>
