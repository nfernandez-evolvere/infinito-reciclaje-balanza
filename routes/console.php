<?php

use App\Jobs\GenerarEnviarReporteJob;
use App\Models\ReporteProgramado;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    ReporteProgramado::withoutGlobalScopes()
        ->activos()
        ->where('proximo_envio_at', '<=', now())
        ->each(fn ($r) => GenerarEnviarReporteJob::dispatch($r->id));
})->everyFifteenMinutes()->name('reportes-programados');
