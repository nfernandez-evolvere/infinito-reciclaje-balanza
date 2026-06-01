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
    Route::get('/reportes/preview-pdf', function () {
        abort_unless(app()->isLocal(), 404);

        $reporte = [
            'kpis'        => ['total' => 390, 'toneladas' => 744.9, 'dias_op' => 22, 'dias_rango' => 31, 'promedio_ton_dia' => 33.86, 'promedio_kg_viaje' => 1909],
            'evolucion'   => [
                'datos'   => array_map(fn ($d) => ['fecha' => now()->startOfMonth()->addDays($d)->format('d/m'), 'viajes' => 10 + ($d % 7) * 3, 'toneladas' => round(15 + ($d % 5) * 6 + 5, 1)], range(0, 21)),
                'promedio' => 33.9, 'maximo' => 48.2, 'minimo' => 12.5,
            ],
            'zonas'       => collect([
                ['nombre' => 'Zona Norte',  'turno' => 'Mañana', 'viajes' => 142, 'toneladas' => 284.5, 'kg_viaje' => 2003, 'porcentaje' => 38, 'kg_ha' => 1240.5, 'kg_hab' => 1.2],
                ['nombre' => 'Zona Sur',    'turno' => 'Tarde',  'viajes' => 98,  'toneladas' => 178.2, 'kg_viaje' => 1818, 'porcentaje' => 24, 'kg_ha' => 980.0,  'kg_hab' => 0.9],
                ['nombre' => 'Zona Centro', 'turno' => null,     'viajes' => 76,  'toneladas' => 145.8, 'kg_viaje' => 1918, 'porcentaje' => 20, 'kg_ha' => null,   'kg_hab' => null],
                ['nombre' => 'Zona Oeste',  'turno' => 'Mañana', 'viajes' => 54,  'toneladas' => 98.3,  'kg_viaje' => 1820, 'porcentaje' => 13, 'kg_ha' => 620.0,  'kg_hab' => 0.6],
                ['nombre' => 'Zona Este',   'turno' => 'Tarde',  'viajes' => 20,  'toneladas' => 38.1,  'kg_viaje' => 1905, 'porcentaje' => 5,  'kg_ha' => 310.0,  'kg_hab' => 0.4],
            ]),
            'vehiculos'   => collect([
                ['nombre' => 'Camión compactador', 'viajes' => 210, 'toneladas' => 420.5, 'kg_viaje' => 2002, 'porcentaje' => 56],
                ['nombre' => 'Camión volcador',    'viajes' => 120, 'toneladas' => 210.3, 'kg_viaje' => 1752, 'porcentaje' => 28],
                ['nombre' => 'Utilitario',         'viajes' => 60,  'toneladas' => 114.1, 'kg_viaje' => 1901, 'porcentaje' => 16],
            ]),
            'desde'       => now()->startOfMonth(),
            'hasta'       => now()->endOfMonth(),
            'config'      => null,
            'conclusiones' => [],
        ];

        $pdf = app(\App\Services\PdfService::class)->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte'));

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
        ]);
    })->name('reportes.preview-pdf');

    Route::get('/reportes/preview', function () {
        $zonas = collect([
            ['nombre' => 'Zona Norte',   'turno' => 'Mañana', 'viajes' => 142, 'toneladas' => 284.5, 'kg_viaje' => 2003, 'porcentaje' => 38, 'kg_ha' => 1240.5],
            ['nombre' => 'Zona Sur',     'turno' => 'Tarde',  'viajes' => 98,  'toneladas' => 178.2, 'kg_viaje' => 1818, 'porcentaje' => 24, 'kg_ha' => 980.0],
            ['nombre' => 'Zona Centro',  'turno' => null,     'viajes' => 76,  'toneladas' => 145.8, 'kg_viaje' => 1918, 'porcentaje' => 20, 'kg_ha' => null],
            ['nombre' => 'Zona Oeste',   'turno' => 'Mañana', 'viajes' => 54,  'toneladas' => 98.3,  'kg_viaje' => 1820, 'porcentaje' => 13, 'kg_ha' => 620.0],
            ['nombre' => 'Zona Este',    'turno' => 'Tarde',  'viajes' => 20,  'toneladas' => 38.1,  'kg_viaje' => 1905, 'porcentaje' => 5,  'kg_ha' => 310.0],
        ]);
        $vehiculos = collect([
            ['nombre' => 'Camión compactador',  'viajes' => 210, 'toneladas' => 420.5, 'kg_viaje' => 2002, 'porcentaje' => 56],
            ['nombre' => 'Camión volcador',     'viajes' => 120, 'toneladas' => 210.3, 'kg_viaje' => 1752, 'porcentaje' => 28],
            ['nombre' => 'Utilitario',          'viajes' => 60,  'toneladas' => 114.1, 'kg_viaje' => 1901, 'porcentaje' => 16],
        ]);
        $kpis = ['total' => 390, 'toneladas' => 744.9, 'dias_op' => 22, 'dias_rango' => 31, 'promedio_ton_dia' => 33.86, 'promedio_kg_viaje' => 1909];
        $evolucion = [
            'promedio' => 33.9, 'maximo' => 48.2, 'minimo' => 12.5,
            'datos' => array_map(fn($d) => ['fecha' => now()->startOfMonth()->addDays($d)->format('Y-m-d'), 'toneladas' => round(20 + rand(0, 300) / 10, 1)], range(0, 21)),
        ];
        $reporte = ['desde' => now()->startOfMonth(), 'hasta' => now()->endOfMonth()];
        return view('modules.admin.reportes.preview', compact('kpis', 'evolucion', 'zonas', 'vehiculos', 'reporte'));
    })->name('reportes.preview');
Route::get('/reportes/pdf-presentacion', [ReporteController::class, 'exportPdfPresentacion'])->name('reportes.pdf-presentacion');
    Route::get('/reportes/excel', [ReporteController::class, 'exportExcel'])->name('reportes.excel');
    Route::get('/reportes/destinatarios', [ReporteController::class, 'indexDestinatarios'])->name('reportes.destinatarios.index');
    Route::put('/reportes/configuracion', [ReporteController::class, 'updateConfiguracion'])->name('reportes.configuracion.update');
    Route::post('/reportes/programados', [ReporteController::class, 'storeProgramado'])->name('reportes.programados.store');
    Route::put('/reportes/programados/{programado}', [ReporteController::class, 'updateProgramado'])->name('reportes.programados.update');
    Route::delete('/reportes/programados/{programado}', [ReporteController::class, 'destroyProgramado'])->name('reportes.programados.destroy');
    Route::post('/reportes/programados/{programado}/enviar-ahora', [ReporteController::class, 'enviarAhoraProgramado'])
        ->name('reportes.programados.enviar-ahora');

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
