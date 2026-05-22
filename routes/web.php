<?php

use App\Http\Controllers\Admin\TipoServicioController;
use App\Http\Controllers\Admin\TipoVehiculoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VehiculoController;
use App\Http\Controllers\Admin\ZonaController;
use App\Http\Controllers\Admin\ZonaServicioController;
use App\Http\Controllers\SuperAdmin\OrganizacionController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

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
    Route::resource('usuarios', UsuarioController::class)
        ->only(['index', 'store', 'update']);
    Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggle'])
        ->name('usuarios.toggle');
    Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])
        ->name('usuarios.reset-password');
});

// --- Super Admin ---
Route::middleware(['auth', 'role:super_admin'])->name('super.')->group(function () {
    Route::get('/dashboard', fn () => view('modules.super_admin.dashboard'))->name('dashboard');
    Route::resource('organizaciones', OrganizacionController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('organizaciones/{organizacion}/toggle', [OrganizacionController::class, 'toggle'])
        ->name('organizaciones.toggle');
    Route::get('usuarios/search', [OrganizacionController::class, 'searchUsers'])
        ->name('usuarios.search');
    Route::post('organizaciones/{organizacion}/usuarios', [OrganizacionController::class, 'addUser'])
        ->name('organizaciones.addUser');
    Route::delete('organizaciones/{organizacion}/usuarios/{user}', [OrganizacionController::class, 'removeUser'])
        ->name('organizaciones.removeUser');
    Route::post('organizaciones/{organizacion}/usuarios/{user}/reset-password', [OrganizacionController::class, 'resetUserPassword'])
        ->name('organizaciones.resetUserPassword');
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
