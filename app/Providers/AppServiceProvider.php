<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\ComponentAttributeBag;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         * $attributes->twMerge('base-classes', $computed, ...)
         *
         * Merges the component's computed classes with any class the caller
         * passed in, resolving Tailwind conflicts (e.g. p-4 wins over p-6).
         * Equivalent to cn() / twMerge() in shadcn/ui React.
         *
         * Usage in a Blade component:
         *   <div {{ $attributes->twMerge('p-6 rounded-xl', $variantClass) }}>
         */
        ComponentAttributeBag::macro('twMerge', function (string ...$classes): ComponentAttributeBag {
            /** @var ComponentAttributeBag $this */
            $userClass  = $this->get('class', '');
            $merged     = tw(...[...$classes, $userClass]);

            return $this->except('class')->merge(['class' => $merged]);
        });
    }
}
