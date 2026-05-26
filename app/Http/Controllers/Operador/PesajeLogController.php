<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\Zona;
use App\Repositories\PesajeLogRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class PesajeLogController extends Controller
{
    private const LABELS = [
        'tipo_servicio_id' => 'Tipo de servicio',
        'zona_id'          => 'Origen',
        'peso_bruto_kg'    => 'Peso bruto',
        'observaciones'    => 'Observaciones',
        'turno'            => 'Turno',
    ];

    public function __construct(protected PesajeLogRepository $logRepository) {}

    public function __invoke(Pesaje $pesaje): JsonResponse
    {
        $entradas = $this->logRepository->porPesaje($pesaje->id);

        [$servicios, $zonas] = $this->resolvers($entradas);

        $grupos = $entradas
            ->groupBy(fn ($e) => $e->created_at->timestamp . '|' . $e->usuario_id . '|' . $e->motivo)
            ->map(fn ($grupo) => [
                'fecha'   => $grupo->first()->created_at->format('d/m/Y H:i'),
                'motivo'  => $grupo->first()->motivo,
                'usuario' => $grupo->first()->usuario->name,
                'cambios' => $grupo->map(fn ($e) => [
                    'campo'    => self::LABELS[$e->campo] ?? $e->campo,
                    'anterior' => $this->format($e->campo, $e->valor_anterior, $servicios, $zonas),
                    'nuevo'    => $this->format($e->campo, $e->valor_nuevo, $servicios, $zonas),
                ])->values(),
            ])
            ->values();

        return response()->json($grupos);
    }

    private function resolvers(Collection $entradas): array
    {
        $ids = fn (string $campo) => $entradas
            ->where('campo', $campo)
            ->flatMap(fn ($e) => [$e->valor_anterior, $e->valor_nuevo])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->unique()->values();

        $servicios = TipoServicio::whereIn('id', $ids('tipo_servicio_id'))->pluck('nombre', 'id');
        $zonas     = Zona::whereIn('id', $ids('zona_id'))->pluck('nombre', 'id');

        return [$servicios, $zonas];
    }

    private function format(string $campo, ?string $valor, $servicios, $zonas): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return match ($campo) {
            'tipo_servicio_id' => $servicios[$valor] ?? $valor,
            'zona_id'          => $zonas[$valor] ?? $valor,
            'peso_bruto_kg'    => number_format((int) $valor, 0, ',', '.') . ' kg',
            default            => $valor,
        };
    }
}
