<?php

use App\Http\Controllers\Shared\ProfileController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';
require __DIR__.'/operador.php';
require __DIR__.'/admin.php';
require __DIR__.'/super.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [ProfileController::class, 'show'])->name('perfil.show');
    Route::put('/perfil', [ProfileController::class, 'updateProfile'])->name('perfil.update');
    Route::put('/perfil/contrasena', [ProfileController::class, 'updatePassword'])->name('perfil.password');
});

Route::get('/', function () {
    if (! auth()->check()) {
        return view('modules.landing');
    }
    $user = auth()->user();

    return redirect(match (true) {
        $user->isSuperAdmin() => route('super.dashboard'),
        $user->isAdmin()      => route('admin.dashboard'),
        default               => route('balanza'),
    });
})->name('landing');

Route::fallback(function () {
    $user = auth()->user();
    if ($user) {
        $home = match (true) {
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
