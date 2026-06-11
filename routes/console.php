<?php

use App\Console\Commands\DetectarAlertasCommand;
use App\Models\ReporteProgramado;
use App\Repositories\ReporteGeneradoRepository;
use App\Services\ReporteGeneradoService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Registros varados en estados de procesamiento (worker muerto sin pasar
    // por failed()) → fallido, para que el admin pueda reintentar desde la UI.
    app(ReporteGeneradoRepository::class)->marcarVaradosComoFallidos();

    // iniciarGeneracion crea el registro (período/destinatarios congelados) y
    // avanza proximo_envio_at ANTES de que corra el job: un reporte esperando
    // revisión o una cola lenta no provocan re-despachos cada 15 minutos.
    ReporteProgramado::withoutGlobalScopes()
        ->activos()
        ->where('proximo_envio_at', '<=', now())
        ->each(fn ($r) => app(ReporteGeneradoService::class)->iniciarGeneracion($r));
})->everyFifteenMinutes()->name('reportes-programados');

// Detección de alertas automáticas — corre diariamente a las 00:30
Schedule::command(DetectarAlertasCommand::class)->dailyAt('00:30')->name('detectar-alertas');
