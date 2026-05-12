<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/showcase');
});

Route::get('/showcase', function () {
    return view('showcase.index');
});

Route::get('/showcase/docs', function () {
    return view('showcase.docs');
});

Route::get('/showcase/themes', function () {
    return view('showcase.themes');
});

Route::get('/showcase/components/{component}', function (string $component) {
    $view = "showcase.components.{$component}";
    if (view()->exists($view)) {
        return view($view);
    }
    abort(404);
});

// ── Páginas de ejemplo ─────────────────────────────────────────────────
Route::prefix('examples')->group(function () {

    // P10 — Flujo de autenticación
    Route::get('/auth/login',    fn() => view('auth.login'))->name('auth.login');
    Route::get('/auth/register', fn() => view('auth.register'))->name('auth.register');
    Route::get('/auth/verify',   fn() => view('auth.verify'))->name('auth.verify');

    // P01 — Dashboard
    Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');

    // P03 — Miembros
    Route::get('/members', fn() => view('members.index'))->name('members');

    // P02 — Configuración
    Route::get('/settings', fn() => view('settings.index'))->name('settings');

    // P07 — Facturación
    Route::get('/billing', fn() => view('billing.index'))->name('billing');

    // P06 — Perfil de usuario
    Route::get('/users/show', fn() => view('users.show'))->name('users.show');

    // P05 — Notificaciones
    Route::get('/notifications', fn() => view('notifications.index'))->name('notifications');

    // P04 — Onboarding
    Route::get('/onboarding', fn() => view('onboarding.index'))->name('onboarding');

    // P08 — Editor de posts
    Route::get('/posts/create', fn() => view('posts.create'))->name('posts.create');

    // P09 — Analytics
    Route::get('/analytics', fn() => view('analytics.index'))->name('analytics');

    // P11 — Búsqueda global
    Route::get('/search', fn() => view('search.index'))->name('search');

    // P12 — Crear producto
    Route::get('/products/create', fn() => view('products.create'))->name('products.create');

});
