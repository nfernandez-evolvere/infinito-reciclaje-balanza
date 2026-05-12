@props([
    'value' => '',
    'id'    => null,
])

<div class="flex items-center gap-2">
    <button
        type="button"
        role="radio"
        :aria-checked="(value === '{{ $value }}').toString()"
        :data-state="value === '{{ $value }}' ? 'checked' : 'unchecked'"
        @click="value = '{{ $value }}'"
        :class="{
            'border-primary':          value === '{{ $value }}' && (!state || state === 'default'),
            'border-destructive':      value === '{{ $value }}' && state === 'destructive',
            'border-success':          value === '{{ $value }}' && state === 'success',
            'border-warning':          value === '{{ $value }}' && state === 'warning',
            'border-info':             value === '{{ $value }}' && state === 'info',
            'border-input':            value !== '{{ $value }}' && (!state || state === 'default'),
            'border-destructive-border': value !== '{{ $value }}' && state === 'destructive',
            'border-success-border':   value !== '{{ $value }}' && state === 'success',
            'border-warning-border':   value !== '{{ $value }}' && state === 'warning',
            'border-info-border':      value !== '{{ $value }}' && state === 'info',
        }"
        class="size-4 shrink-0 rounded-full border-2 flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
        @if($id) id="{{ $id }}" @endif
    >
        <div
            x-show="value === '{{ $value }}'"
            x-transition:enter="transition ease-out duration-75"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            :class="{
                'bg-primary':     !state || state === 'default',
                'bg-destructive': state === 'destructive',
                'bg-success':     state === 'success',
                'bg-warning':     state === 'warning',
                'bg-info':        state === 'info',
            }"
            class="size-2 rounded-full"
        ></div>
        <input
            type="radio"
            :name="name"
            value="{{ $value }}"
            :checked="value === '{{ $value }}'"
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
        />
    </button>
    @if($slot->isNotEmpty())
        <label
            @if($id) for="{{ $id }}" @endif
            class="text-sm leading-none cursor-pointer select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
        >
            {{ $slot }}
        </label>
    @endif
</div>
