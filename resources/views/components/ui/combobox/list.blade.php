{{--
    Scrollable listbox container.
    Place <x-ui.combobox.item>, <x-ui.combobox.group>, <x-ui.combobox.separator> inside.
--}}
<div
    :id="uid + '-listbox'"
    role="listbox"
    :aria-activedescendant="highlighted !== null && highlighted !== undefined ? uid + ':' + highlighted : false"
    {{ $attributes->twMerge('max-h-56 overflow-y-auto p-1') }}
>
    {{ $slot }}
</div>
