<?php

namespace App\Providers;

use App\Database\SqlServerSchemaDDLGrammar;
use App\Database\SqlServerSchemaGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\ComponentAttributeBag;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binding default para que app('organizacion') sea siempre seguro de llamar.
        // ResolveOrganizacion middleware lo sobreescribe con instance() cuando hay org real.
        $this->app->bind('organizacion', fn () => null);
    }

    public function boot(): void
    {
        if (config('database.default') === 'sqlsrv') {
            $conn   = DB::connection('sqlsrv');
            $schema = config('database.connections.sqlsrv.schema', 'dbo');

            $conn->setQueryGrammar(
                (new SqlServerSchemaGrammar($conn))->setSchema($schema)
            );
            $conn->setSchemaGrammar(
                (new SqlServerSchemaDDLGrammar($conn))->setSchema($schema)
            );
        }


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

        // super_admin bypassa todas las gates
        Gate::before(fn ($user) => $user->isSuperAdmin() ? true : null);

        Gate::define('record-weighing', fn ($user) => $user->isOperador());
        Gate::define('view-own-historial', fn ($user) => $user->isOperador());
        Gate::define('edit-pesaje', fn ($user) => $user->isOperador() || $user->isAdmin());
        Gate::define('manage-masters', fn ($user) => $user->isAdmin());
        Gate::define('view-dashboard', fn ($user) => $user->isAdmin());
        Gate::define('manage-usuarios', fn ($user) => $user->isAdmin());
        Gate::define('manage-organizaciones', fn ($user) => $user->isSuperAdmin());

        ComponentAttributeBag::macro('twMerge', function (string ...$classes): ComponentAttributeBag {
            /** @var ComponentAttributeBag $this */
            $userClass = $this->get('class', '');

            // Skip expensive merge when the caller adds no class override.
            $merged = ($userClass !== '')
                ? tw(...[...$classes, $userClass])
                : implode(' ', array_filter($classes));

            return $this->except('class')->merge(['class' => $merged]);
        });
    }
}
