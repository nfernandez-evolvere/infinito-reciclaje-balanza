<?php

use App\Http\Controllers\Operador\BalanzaController;
use App\Http\Controllers\Operador\EgresoPesajeController;
use App\Http\Controllers\Operador\HistorialController;
use App\Http\Controllers\Operador\PesajeController;
use App\Http\Controllers\Operador\PesajeLogController;
use App\Http\Controllers\Operador\ServicioZonasController;
use App\Http\Controllers\Operador\OnboardingController;
use App\Http\Controllers\Operador\VehiculoBuscarController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:operador'])->group(function () {

    // Vistas
    Route::get('/balanza', BalanzaController::class)->name('balanza');
    Route::get('/historial', HistorialController::class)->name('historial');

    // Pesajes — solo operador puede crear
    Route::post('/pesajes', [PesajeController::class, 'store'])->name('pesajes.store');

    // Onboarding
    Route::post('/onboarding/visto', OnboardingController::class)->name('onboarding.visto');
});

// Rutas accesibles a cualquier usuario autenticado
Route::middleware(['auth'])->group(function () {
    Route::get('/vehiculos/buscar', VehiculoBuscarController::class)->name('vehiculos.buscar');
    Route::get('/servicios/{servicio}/zonas', ServicioZonasController::class)->name('servicios.zonas');
    Route::get('/pesajes/{pesaje}', [PesajeController::class, 'show'])->name('pesajes.show');
    Route::get('/pesajes/{pesaje}/edit', [PesajeController::class, 'edit'])->name('pesajes.edit');
    Route::put('/pesajes/{pesaje}', [PesajeController::class, 'update'])->name('pesajes.update');
    Route::post('/pesajes/{pesaje}/egreso', EgresoPesajeController::class)->name('pesajes.egreso');
    Route::get('/pesajes/{pesaje}/log', PesajeLogController::class)->name('pesajes.log');
});
