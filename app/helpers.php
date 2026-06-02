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
        // Single array in memory for the lifetime of the process.
        // On first call: populated from the persistent cache (one read).
        // New entries are appended; the whole array is flushed on shutdown (one write).
        static $mem     = null;
        static $dirty   = false;

        if ($mem === null) {
            $mem = cache()->get('tw_cache', []);

            register_shutdown_function(function () use (&$mem, &$dirty): void {
                if ($dirty && app()->bound('cache')) {
                    cache()->forever('tw_cache', $mem);
                }
            });
        }

        $key = implode("\x00", $classes);

        if (!array_key_exists($key, $mem)) {
            $mem[$key] = TailwindMerge::merge(...$classes);
            $dirty = true;
        }

        return $mem[$key];
    }
}
