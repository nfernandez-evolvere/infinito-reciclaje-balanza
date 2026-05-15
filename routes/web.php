<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

// --- Operador ---
Route::middleware(['auth', 'role:operador'])->group(function () {
    Route::get('/balanza', fn () => view('modules.operador.balanza'))->name('balanza');
    Route::get('/historial', fn () => view('modules.operador.historial'))->name('historial');
});

// --- Admin ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('modules.admin.dashboard'))->name('dashboard');
    Route::get('/pesajes', fn () => view('modules.admin.pesajes.index'))->name('pesajes.index');
    Route::get('/reportes', fn () => view('modules.admin.reportes.index'))->name('reportes.index');

    // Padrón
    Route::get('/zonas', fn () => view('modules.admin.zonas.index'))->name('zonas.index');
    Route::get('/servicios', fn () => view('modules.admin.servicios.index'))->name('servicios.index');
    Route::get('/vehiculos', fn () => view('modules.admin.vehiculos.index'))->name('vehiculos.index');
    Route::get('/tipos-vehiculo', fn () => view('modules.admin.tipos-vehiculo.index'))->name('tipos-vehiculo.index');
    Route::get('/usuarios', fn () => view('modules.admin.usuarios.index'))->name('usuarios.index');
});

Route::fallback(function () {
    $user = auth()->user();
    $home = $user
        ? ($user->isAdmin() ? route('admin.dashboard') : route('balanza'))
        : route('login');
    return response()->view('errors.404', [
        'home'          => $home,
        'showAppLayout' => $user !== null,
    ], 404);
});
