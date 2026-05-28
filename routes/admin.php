<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Shared\PesajeController;
use App\Http\Controllers\Admin\ServicioController;
use App\Http\Controllers\Admin\TipoServicioController;
use App\Http\Controllers\Admin\TipoVehiculoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VehiculoController;
use App\Http\Controllers\Admin\ZonaController;
use App\Http\Controllers\Admin\ZonaServicioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Vistas generales
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/pesajes', [PesajeController::class, 'index'])->name('pesajes.index');
    Route::get('/pesajes/export', [PesajeController::class, 'export'])->name('pesajes.export');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/pdf', [ReporteController::class, 'exportPdf'])->name('reportes.pdf');
    Route::get('/reportes/excel', [ReporteController::class, 'exportExcel'])->name('reportes.excel');

    // Zonas ——————————————————————————————————————————————————————————
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

    // Tipos de servicio ——————————————————————————————————————————————
    Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.index');
    Route::resource('tipos-servicio', TipoServicioController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('tipos-servicio/{tiposServicio}/toggle', [TipoServicioController::class, 'toggle'])
        ->name('tipos-servicio.toggle');

    // Vehículos ——————————————————————————————————————————————————————
    Route::resource('vehiculos', VehiculoController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('vehiculos/{vehiculo}/toggle', [VehiculoController::class, 'toggle'])
        ->name('vehiculos.toggle');
    Route::resource('tipos-vehiculo', TipoVehiculoController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('tipos-vehiculo/{tiposVehiculo}/toggle', [TipoVehiculoController::class, 'toggle'])
        ->name('tipos-vehiculo.toggle');

    // Usuarios ———————————————————————————————————————————————————————
    Route::resource('usuarios', UsuarioController::class)
        ->only(['index', 'store', 'update']);
    Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggle'])
        ->name('usuarios.toggle');
    Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])
        ->name('usuarios.reset-password');
});
