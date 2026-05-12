@props([])
<th {{ $attributes->merge(['class' => 'h-10 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]']) }}>{{ $slot }}</th>
