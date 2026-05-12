{{--
    Groups a set of <x-ui.combobox.item> under a shared label.
    Hides itself when all child items are filtered out.
--}}
<div
    role="presentation"
    x-show="$el.querySelectorAll('[role=option]').length === 0 || [...$el.querySelectorAll('[role=option]')].some(el => el.style.display !== 'none')"
    {{ $attributes }}
>
    {{ $slot }}
</div>
