@props(['orientation' => 'horizontal'])  {{-- horizontal | vertical --}}

{{--
    Fusiona los botones hijos colapsando sus border-radius intermedios.
    Los hijos deben ser <x-ui.button> — el CSS actúa sobre :first-child / :last-child.
--}}
<div {{ $attributes->twMerge(
    'inline-flex',
    $orientation === 'vertical' ? 'flex-col' : '',
    $orientation === 'vertical'
        ? '[&>*]:rounded-none [&>*:first-child]:rounded-t-md [&>*:last-child]:rounded-b-md [&>*:not(:first-child)]:-mt-px'
        : '[&>*]:rounded-none [&>*:first-child]:rounded-l-md [&>*:last-child]:rounded-r-md [&>*:not(:first-child)]:-ml-px',
    '[&>*]:relative [&>*:focus]:z-10 [&>*:hover]:z-10'
) }}>
    {{ $slot }}
</div>
