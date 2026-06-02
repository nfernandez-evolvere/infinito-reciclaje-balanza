<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\ResolveOrganizacion;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            ResolveOrganizacion::class,
        ]);
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            try {
                $store = app('session.store');
                $encrypted = $request->cookies->get(config('session.cookie'));
                if ($encrypted) {
                    $store->setId(Crypt::decrypt($encrypted, false));
                    $store->start();
                    $request->setLaravelSession($store);
                }
            } catch (Throwable) {
            }

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
