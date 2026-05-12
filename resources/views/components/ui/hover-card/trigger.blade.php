{{-- inline-flex en lugar de contents — getBoundingClientRect() necesita una caja real --}}
<span x-ref="trigger" class="inline-flex" {{ $attributes }}>
    {{ $slot }}
</span>
