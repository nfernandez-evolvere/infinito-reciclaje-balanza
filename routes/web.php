<?php

use App\Http\Controllers\Admin\TipoServicioController;
use App\Http\Controllers\Admin\TipoVehiculoController;
use App\Http\Controllers\Admin\VehiculoController;
use App\Http\Controllers\Admin\ZonaController;
use App\Http\Controllers\Admin\ZonaServicioController;
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
    Route::resource('zonas', ZonaController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('zonas/{zona}/toggle', [ZonaController::class, 'toggle'])
        ->name('zonas.toggle');
    Route::post('zonas/{zona}/servicios', [ZonaServicioController::class, 'store'])
        ->name('zonas.servicios.store');
    Route::put('zonas/{zona}/servicios/{tipoServicio}', [ZonaServicioController::class, 'update'])
        ->name('zonas.servicios.update');
    Route::delete('zonas/{zona}/servicios/{tipoServicio}', [ZonaServicioController::class, 'destroy'])
        ->name('zonas.servicios.destroy');
    Route::get('/servicios', fn () => view('modules.admin.servicios.index'))->name('servicios.index');
    Route::resource('tipos-servicio', TipoServicioController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('tipos-servicio/{tiposServicio}/toggle', [TipoServicioController::class, 'toggle'])
        ->name('tipos-servicio.toggle');
    Route::resource('vehiculos', VehiculoController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('vehiculos/{vehiculo}/toggle', [VehiculoController::class, 'toggle'])
        ->name('vehiculos.toggle');
    Route::resource('tipos-vehiculo', TipoVehiculoController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('tipos-vehiculo/{tiposVehiculo}/toggle', [TipoVehiculoController::class, 'toggle'])
        ->name('tipos-vehiculo.toggle');
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
