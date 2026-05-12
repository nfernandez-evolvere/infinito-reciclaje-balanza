@props([
    'as'      => 'p',   // h1|h2|h3|h4|p|lead|large|small|muted|code|blockquote|list
    'element' => null,  // override del tag HTML
])

@php
$config = match($as) {
    'h1'         => ['tag' => 'h1',         'class' => 'scroll-m-20 text-4xl font-bold tracking-tight leading-[1.1]'],
    'h2'         => ['tag' => 'h2',         'class' => 'scroll-m-20 text-3xl font-semibold tracking-tight leading-[1.2] first:mt-0'],
    'h3'         => ['tag' => 'h3',         'class' => 'scroll-m-20 text-2xl font-semibold tracking-tight leading-[1.25]'],
    'h4'         => ['tag' => 'h4',         'class' => 'scroll-m-20 text-xl font-semibold tracking-tight leading-[1.3]'],
    'lead'       => ['tag' => 'p',          'class' => 'text-xl text-muted-foreground leading-relaxed'],
    'large'      => ['tag' => 'div',        'class' => 'text-lg font-semibold'],
    'small'      => ['tag' => 'small',      'class' => 'text-sm font-medium leading-none'],
    'muted'      => ['tag' => 'p',          'class' => 'text-sm text-muted-foreground'],
    'section-label' => ['tag' => 'h2',    'class' => 'text-xs font-semibold uppercase tracking-widest text-muted-foreground'],
    'code'       => ['tag' => 'code',       'class' => 'relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm font-semibold'],
    'blockquote' => ['tag' => 'blockquote', 'class' => 'mt-6 border-l-2 border-border pl-6 italic text-muted-foreground'],
    'list'       => ['tag' => 'ul',         'class' => 'my-4 ml-6 list-disc [&>li]:mt-2 text-sm'],
    default      => ['tag' => 'p',          'class' => 'leading-relaxed text-sm [&:not(:first-child)]:mt-4'],
};

$tag = $element ?? $config['tag'];
@endphp

<{{ $tag }} {{ $attributes->twMerge($config['class']) }}>
    {{ $slot }}
</{{ $tag }}>
