@props(['checked' => false, 'value' => null])

<input
    type="radio"
    @if($checked) checked @endif
    @if($value !== null) value="{{ $value }}" @endif
    {{ $attributes->merge(['class' => 'h-4 w-4 border border-primary text-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 accent-primary']) }}
>
