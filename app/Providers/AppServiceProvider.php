<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
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
        Password::defaults(fn () =>
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
        );

        Gate::define('record-weighing', fn ($user) => $user->isOperador());
        Gate::define('view-own-historial', fn ($user) => $user->isOperador());
        Gate::define('edit-pesaje', fn ($user) => $user->isOperador() || $user->isAdmin());
        Gate::define('manage-masters', fn ($user) => $user->isAdmin());
        Gate::define('view-dashboard', fn ($user) => $user->isAdmin());
        Gate::define('manage-usuarios', fn ($user) => $user->isAdmin());

        ComponentAttributeBag::macro('twMerge', function (string ...$classes): ComponentAttributeBag {
            /** @var ComponentAttributeBag $this */
            $userClass  = $this->get('class', '');
            $merged     = tw(...[...$classes, $userClass]);

            return $this->except('class')->merge(['class' => $merged]);
        });
    }
}
