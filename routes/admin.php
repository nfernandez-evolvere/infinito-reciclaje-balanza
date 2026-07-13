<?php

use App\Http\Controllers\Admin\AlertaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OnboardingController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\TipoServicioController;
use App\Http\Controllers\Admin\TipoVehiculoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VehiculoController;
use App\Http\Controllers\Admin\ZonaController;
use App\Http\Controllers\Shared\PesajeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Onboarding
    Route::post('/onboarding/visto', [OnboardingController::class, 'store'])->name('onboarding.visto');

    // Vistas generales
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/pesajes', [PesajeController::class, 'index'])->name('pesajes.index');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/historial/parcial', [ReporteController::class, 'historialParcial'])
        ->name('reportes.historial.parcial');
    Route::get('/reportes/pdf-v2', [ReporteController::class, 'exportPdfV2'])->name('reportes.pdf-v2');
    Route::get('/reportes/excel-v2', [ReporteController::class, 'exportExcelV2'])->name('reportes.excel-v2');
    Route::get('/reportes/historial/{generado}/descargar', [ReporteController::class, 'downloadHistorial'])
        ->name('reportes.historial.download');
    Route::post('/reportes/historial/{generado}/aprobar', [ReporteController::class, 'aprobarHistorial'])
        ->name('reportes.historial.aprobar');
    Route::post('/reportes/historial/{generado}/descartar', [ReporteController::class, 'descartarHistorial'])
        ->name('reportes.historial.descartar');
    Route::post('/reportes/historial/{generado}/reintentar', [ReporteController::class, 'reintentarHistorial'])
        ->name('reportes.historial.reintentar');
    Route::put('/reportes/historial/{generado}/conclusiones', [ReporteController::class, 'updateConclusionesHistorial'])
        ->name('reportes.historial.conclusiones.update');
    Route::get('/reportes/destinatarios', [ReporteController::class, 'indexDestinatarios'])->name('reportes.destinatarios.index');
    Route::put('/reportes/configuracion', [ReporteController::class, 'updateConfiguracion'])->name('reportes.configuracion.update');
    Route::post('/reportes/programados', [ReporteController::class, 'storeProgramado'])->name('reportes.programados.store');
    Route::put('/reportes/programados/{programado}', [ReporteController::class, 'updateProgramado'])->name('reportes.programados.update');
    Route::delete('/reportes/programados/{programado}', [ReporteController::class, 'destroyProgramado'])->name('reportes.programados.destroy');
    Route::patch('/reportes/programados/{programado}/toggle', [ReporteController::class, 'toggleProgramado'])
        ->name('reportes.programados.toggle');
    Route::post('/reportes/programados/{programado}/enviar-ahora', [ReporteController::class, 'enviarAhoraProgramado'])
        ->name('reportes.programados.enviar-ahora');
    Route::get('/reportes/programados/{programado}/pdf', [ReporteController::class, 'downloadPdfProgramado'])
        ->name('reportes.programados.pdf');
    Route::get('/reportes/programados/{programado}/excel', [ReporteController::class, 'downloadExcelProgramado'])
        ->name('reportes.programados.excel');

    // Alertas ————————————————————————————————————————————————————————
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::get('/alertas/novedades', [AlertaController::class, 'novedades'])->name('alertas.novedades');
    Route::patch('/alertas/{alerta}/leer', [AlertaController::class, 'marcarLeida'])->name('alertas.leer');
    Route::post('/alertas/leer-todas', [AlertaController::class, 'marcarTodasLeidas'])->name('alertas.leer-todas');
    Route::put('/alertas/configuracion', [AlertaController::class, 'updateConfig'])->name('alertas.configuracion.update');

    // Zonas (se gestionan dentro de cada servicio, en la pantalla de tipos de servicio) ——
    Route::resource('zonas', ZonaController::class)
        ->only(['store', 'update', 'destroy']);
    Route::patch('zonas/{zona}/toggle', [ZonaController::class, 'toggle'])
        ->name('zonas.toggle');

    // Tipos de servicio ——————————————————————————————————————————————
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
