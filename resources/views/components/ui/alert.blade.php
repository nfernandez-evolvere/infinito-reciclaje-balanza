@props([
    'state' => null,   // null | destructive | success | warning | info
])

@php
$wrapperClass = match($state) {
    'destructive' => 'border-destructive-border bg-destructive-subtle text-destructive-subtle-foreground [&>svg]:text-destructive',
    'success'     => 'border-success-border bg-success-subtle text-success-subtle-foreground [&>svg]:text-success',
    'warning'     => 'border-warning-border bg-warning-subtle text-warning-subtle-foreground [&>svg]:text-warning',
    'info'        => 'border-info-border bg-info-subtle text-info-subtle-foreground [&>svg]:text-info',
    default       => 'border-border bg-background text-foreground',
};
@endphp

{{--
  Icon must be the first child (SVG element) for the sibling selectors to work:
    [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4  — pins icon
    [&>svg~*]:pl-7                                  — indents content after icon
--}}
<div role="alert" {{ $attributes->twMerge('relative w-full rounded-lg border p-4 [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg~*]:pl-7', $wrapperClass) }}>
    {{ $slot }}
</div>
