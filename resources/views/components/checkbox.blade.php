@props(['checked' => false, 'value' => null])

<input
    type="checkbox"
    @if($checked) checked @endif
    @if($value !== null) value="{{ $value }}" @endif
    {{ $attributes->merge(['class' => 'peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 checked:bg-primary checked:text-primary-foreground accent-primary']) }}
>
