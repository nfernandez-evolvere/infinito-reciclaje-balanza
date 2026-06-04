<?php

namespace App\Console\Commands;

use App\Models\Organizacion;
use App\Services\AlertaService;
use Illuminate\Console\Command;

class DetectarAlertasCommand extends Command
{
    protected $signature   = 'alertas:detectar {--org= : ID de organización específica}';
    protected $description = 'Detecta alertas automáticas (volumen atípico, gaps, frecuencia por zona)';

    public function handle(AlertaService $alertaService): int
    {
        $orgId = $this->option('org');

        $orgs = $orgId
            ? Organizacion::where('id', $orgId)->get()
            : Organizacion::all();

        foreach ($orgs as $org) {
            $this->line("Procesando organización: {$org->nombre} (#{$org->id})");

            try {
                $alertaService->detectarParaOrganizacion($org->id);
                $this->info("  ✓ Detección completada");
            } catch (\Throwable $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
