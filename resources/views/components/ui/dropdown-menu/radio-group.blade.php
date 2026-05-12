@props([
    'value' => null,
    'name'  => null,
])

<div
    role="group"
    x-data="{ radioValue: @js($value) }"
    {{ $attributes }}
>
    @if($name)
        <input type="hidden" :name="@js($name)" :value="radioValue" />
    @endif
    {{ $slot }}
</div>
