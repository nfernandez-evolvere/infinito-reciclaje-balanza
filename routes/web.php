<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';
require __DIR__.'/operador.php';
require __DIR__.'/admin.php';
require __DIR__.'/super.php';

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    $user = auth()->user();
    return redirect(match(true) {
        $user->isSuperAdmin() => route('super.dashboard'),
        $user->isAdmin()      => route('admin.dashboard'),
        default               => route('balanza'),
    });
});

Route::fallback(function () {
    $user = auth()->user();
    if ($user) {
        $home = match(true) {
            $user->isSuperAdmin() => route('super.dashboard'),
            $user->isAdmin()      => route('admin.dashboard'),
            default               => route('balanza'),
        };
    } else {
        $home = route('login');
    }
    return response()->view('errors.404', [
        'home'          => $home,
        'showAppLayout' => $user !== null,
    ], 404);
});
