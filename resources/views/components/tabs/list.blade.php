@props([])

<div
    role="tablist"
    @keydown.arrow-right.prevent="
        const tabs = [...$el.querySelectorAll('[role=tab]')];
        const i = tabs.indexOf(document.activeElement);
        if (i !== -1) { const next = tabs[(i + 1) % tabs.length]; next.click(); next.focus(); }
    "
    @keydown.arrow-left.prevent="
        const tabs = [...$el.querySelectorAll('[role=tab]')];
        const i = tabs.indexOf(document.activeElement);
        if (i !== -1) { const prev = tabs[(i - 1 + tabs.length) % tabs.length]; prev.click(); prev.focus(); }
    "
    @keydown.home.prevent="
        const tabs = [...$el.querySelectorAll('[role=tab]')];
        if (tabs.length) { tabs[0].click(); tabs[0].focus(); }
    "
    @keydown.end.prevent="
        const tabs = [...$el.querySelectorAll('[role=tab]')];
        const last = tabs[tabs.length - 1];
        if (last) { last.click(); last.focus(); }
    "
    {{ $attributes->merge(['class' => 'inline-flex h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground']) }}
>
    {{ $slot }}
</div>
