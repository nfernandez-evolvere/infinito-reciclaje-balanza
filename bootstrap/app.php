<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(prepend: [
            \App\Http\Middleware\ResolveOrganizacion::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            \Illuminate\Http\Request $request
        ) {
            try {
                $store     = app('session.store');
                $encrypted = $request->cookies->get(config('session.cookie'));
                if ($encrypted) {
                    $store->setId(\Illuminate\Support\Facades\Crypt::decrypt($encrypted, false));
                    $store->start();
                    $request->setLaravelSession($store);
                }
            } catch (\Throwable) {}

            $user = auth()->user();
            $home = $user
                ? ($user->isAdmin() ? route('admin.dashboard') : route('balanza'))
                : route('login');

            return response()->view('errors.404', [
                'home'          => $home,
                'showAppLayout' => $user !== null,
            ], 404);
        });
    })->create();
