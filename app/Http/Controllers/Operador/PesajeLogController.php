<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\Pesaje;
use App\Repositories\PesajeLogRepository;
use Illuminate\Http\JsonResponse;

class PesajeLogController extends Controller
{
    public function __construct(protected PesajeLogRepository $logRepository) {}

    public function __invoke(Pesaje $pesaje): JsonResponse
    {
        $entradas = $this->logRepository->porPesaje($pesaje->id);

        return response()->json(
            $entradas->map(fn ($e) => [
                'id'             => $e->id,
                'campo'          => $e->campo,
                'valor_anterior' => $e->valor_anterior,
                'valor_nuevo'    => $e->valor_nuevo,
                'motivo'         => $e->motivo,
                'usuario'        => $e->usuario->name,
                'fecha'          => $e->created_at->format('d/m/Y H:i'),
            ])
        );
    }
}
