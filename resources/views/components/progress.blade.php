@props(['value' => 0, 'max' => 100])

@php $pct = min(100, max(0, ($value / $max) * 100)); @endphp

<div
    role="progressbar"
    aria-valuenow="{{ $value }}"
    aria-valuemin="0"
    aria-valuemax="{{ $max }}"
    {{ $attributes->merge(['class' => 'relative h-2 w-full overflow-hidden rounded-full bg-secondary']) }}
>
    <div
        class="h-full bg-primary transition-all"
        style="width: {{ $pct }}%"
    ></div>
</div>
