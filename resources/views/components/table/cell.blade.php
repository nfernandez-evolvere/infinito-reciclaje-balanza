@props([])
<td {{ $attributes->merge(['class' => 'p-4 align-middle [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]']) }}>{{ $slot }}</td>
