<?php

use TailwindMerge\Laravel\Facades\TailwindMerge;

/**
 * Merge Tailwind classes resolving conflicts intelligently.
 * Equivalent to cn() / twMerge() in shadcn/ui React.
 *
 * Usage:
 *   tw('p-4 bg-red-500', 'p-6')          → 'bg-red-500 p-6'
 *   tw('rounded-md', 'rounded-full')      → 'rounded-full'
 *   tw($base, $sizeClass, $colorClass)    → merged without conflicts
 */
if (!function_exists('tw')) {
    function tw(string ...$classes): string
    {
        return TailwindMerge::merge(...$classes);
    }
}
