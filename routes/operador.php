<?php

use App\Http\Controllers\Operador\OnboardingController;
use App\Http\Controllers\Operador\PesajeController as OperadorPesajeController;
use App\Http\Controllers\Shared\ManualController;
use App\Http\Controllers\Shared\PesajeController;
use App\Http\Controllers\Shared\TipoServicioController;
use App\Http\Controllers\Shared\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:operador'])->group(function () {

    // Vistas
    Route::get('/balanza', [OperadorPesajeController::class, 'create'])->name('balanza');
    Route::get('/historial', [PesajeController::class, 'index'])->name('historial');

    // Pesajes — solo operador puede crear
    Route::post('/pesajes', [OperadorPesajeController::class, 'store'])->name('pesajes.store');

    // Onboarding
    Route::post('/onboarding/visto', [OnboardingController::class, 'store'])->name('onboarding.visto');
});

// Rutas accesibles a cualquier usuario autenticado
Route::middleware(['auth'])->group(function () {
    Route::get('/manual/{slug?}', [ManualController::class, 'show'])->name('manual.show');

    Route::get('/vehiculos/buscar', [VehiculoController::class, 'buscar'])->name('vehiculos.buscar');
    Route::get('/servicios/{servicio}/zonas', [TipoServicioController::class, 'zonas'])->name('servicios.zonas');
    Route::get('/pesajes/{pesaje}', [PesajeController::class, 'show'])->name('pesajes.show');
    Route::get('/pesajes/{pesaje}/edit', [PesajeController::class, 'edit'])->name('pesajes.edit');
    Route::put('/pesajes/{pesaje}', [PesajeController::class, 'update'])->name('pesajes.update');
    Route::post('/pesajes/{pesaje}/egreso', [PesajeController::class, 'egreso'])->name('pesajes.egreso');
    Route::patch('/pesajes/{pesaje}/cancelar', [PesajeController::class, 'cancelar'])->name('pesajes.cancelar');
    Route::get('/pesajes/{pesaje}/log', [PesajeController::class, 'log'])->name('pesajes.log');
});
