<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelarPesajeRequest;
use App\Http\Requests\EgresoPesajeRequest;
use App\Http\Requests\UpdatePesajeRequest;
use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\Zona;
use App\Repositories\PesajeLogRepository;
use App\Repositories\PesajeRepository;
use App\Repositories\TipoServicioRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\ZonaRepository;
use App\Services\PesajeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PesajeController extends Controller
{
    private const LOG_LABELS = [
        'tipo_servicio_id' => 'Tipo de servicio',
        'zona_id'          => 'Origen',
        'peso_bruto_kg'    => 'Peso bruto',
        'observaciones'    => 'Observaciones',
        'turno'            => 'Turno',
        'estado'           => 'Estado',
    ];

    public function __construct(
        protected PesajeService $pesajeService,
        protected PesajeRepository $pesajeRepository,
        protected PesajeLogRepository $logRepository,
        protected UsuarioRepository $usuarioRepository,
        protected ZonaRepository $zonaRepository,
        protected TipoServicioRepository $tipoServicioRepository,
    ) {}

    public function index(Request $request): View
    {
        $isAdmin = auth()->user()->isAdmin();
        $filtros = $this->buildFiltros($request, $isAdmin);

        $pesajes      = $this->pesajeRepository->filtrado($filtros);
        $kpis         = $this->pesajeRepository->kpisFiltrado($filtros);
        $kpisHoy      = $this->pesajeRepository->kpisDelTurno();
        $ultimoPesaje = $this->pesajeRepository->ultimoDelTurno();
        $operarios    = $this->usuarioRepository->getOperadoresDeLaOrg();

        $viewData = [
            'pesajes'      => $pesajes,
            'kpis'         => $kpis,
            'kpisHoy'      => $kpisHoy,
            'ultimoPesaje' => $ultimoPesaje,
            'filtros'      => $filtros,
            'operarios'    => $operarios,
        ];

        if ($isAdmin) {
            $viewData['titulo']         = 'Pesajes';
            $viewData['routeHistorial'] = route('admin.pesajes.index');
            $viewData['exportUrl']      = route('admin.pesajes.export');
            $viewData['zonas']          = $this->zonaRepository->activos();
            $viewData['tiposServicio']  = $this->tipoServicioRepository->activos();
        } else {
            $viewData['titulo']         = 'Pesajes';
            $viewData['routeHistorial'] = route('historial');
        }

        return view('modules.shared.historial', $viewData);
    }

    public function show(Pesaje $pesaje): View
    {
        $pesaje->load(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador']);
        return view('modules.operador.pesaje-detalle', compact('pesaje'));
    }

    public function edit(Pesaje $pesaje): View
    {
        $pesaje->load(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona']);

        $servicios = $this->tipoServicioRepository->activosConVehiculoSugerido();

        $v = $pesaje->vehiculo;
        $vehiculoJs = [
            'id'       => $v->id,
            'patente'  => $v->patente,
            'interno'  => $v->numero_interno,
            'tara'     => $v->tara_kg,
            'tipo'     => $v->tipoVehiculo?->nombre,
            'titular'  => $v->titular,
            'peso_min' => $v->tipoVehiculo?->peso_min_kg,
            'peso_max' => $v->tipoVehiculo?->peso_max_kg,
        ];

        $servicio         = $pesaje->tipoServicio;
        $zonasDisponibles = $this->zonaRepository->zonasConTurnosPara($servicio)->values();
        $zonaActual       = $zonasDisponibles->firstWhere('id', $pesaje->zona_id);

        $initial = [
            'vehiculo'          => $vehiculoJs,
            'servicioId'        => $servicio->id,
            'servicioNombre'    => $servicio->nombre,
            'tipoSugerido'      => $servicio->tipoVehiculoSugerido?->nombre,
            'zonasDisponibles'  => $zonasDisponibles->toArray(),
            'zonaId'            => $pesaje->zona_id,
            'zonaNombre'        => $pesaje->zona->nombre,
            'turnosDisponibles' => $zonaActual['turnos'] ?? [],
            'turno'             => $pesaje->turno ?? '',
            'pesoBruto'         => $pesaje->peso_bruto_kg,
            'observaciones'     => $pesaje->observaciones ?? '',
        ];

        return view('modules.operador.pesaje-editar', compact('pesaje', 'servicios', 'initial'));
    }

    public function update(UpdatePesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->editar($pesaje, $request->validated(), auth()->user());

        $pesaje->loadMissing('vehiculo');

        $route = auth()->user()->isAdmin() ? 'admin.pesajes.index' : 'historial';

        return redirect()->route($route)
            ->with('toast', [
                'message'     => 'Cambios guardados',
                'description' => 'Se actualizó el pesaje de ' . $pesaje->vehiculo->patente . '.',
                'variant'     => 'success',
            ]);
    }

    public function egreso(EgresoPesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->marcarEgreso($pesaje, $request->validated());

        $route = auth()->user()->isAdmin() ? 'admin.pesajes.index' : 'historial';

        return redirect()->route($route)
            ->with('toast', ['message' => 'Egreso registrado.', 'description' => '', 'variant' => 'success']);
    }

    public function cancelar(CancelarPesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->cancelar($pesaje, $request->validated(), auth()->user());

        $pesaje->loadMissing('vehiculo');
        $route = auth()->user()->isAdmin() ? 'admin.pesajes.index' : 'historial';

        return redirect()->route($route)
            ->with('toast', [
                'message'     => 'Pesaje cancelado',
                'description' => 'Se canceló el pesaje de ' . $pesaje->vehiculo->patente . '.',
                'variant'     => 'default',
            ]);
    }

    public function log(Pesaje $pesaje): JsonResponse
    {
        $entradas = $this->logRepository->porPesaje($pesaje->id);

        [$servicios, $zonas] = $this->resolveLogLabels($entradas);

        $grupos = $entradas
            ->groupBy(fn ($e) => $e->created_at->timestamp . '|' . $e->usuario_id . '|' . $e->motivo)
            ->map(fn ($grupo) => [
                'fecha'   => $grupo->first()->created_at->format('d/m/Y H:i'),
                'motivo'  => $grupo->first()->motivo,
                'usuario' => $grupo->first()->usuario->name,
                'cambios' => $grupo->map(fn ($e) => [
                    'campo'    => self::LOG_LABELS[$e->campo] ?? $e->campo,
                    'anterior' => $this->formatLogValue($e->campo, $e->valor_anterior, $servicios, $zonas),
                    'nuevo'    => $this->formatLogValue($e->campo, $e->valor_nuevo, $servicios, $zonas),
                ])->values(),
            ])
            ->values();

        return response()->json($grupos);
    }

    public function export(Request $request): StreamedResponse
    {
        $filtros  = $this->buildFiltros($request, true);
        $pesajes  = $this->pesajeRepository->filtradoTodos($filtros);
        $filename = 'pesajes-' . now()->format('Y-m-d') . '.csv';

        return $this->pesajeService->exportarCsv($pesajes, $filename);
    }

    private function buildFiltros(Request $request, bool $isAdmin): array
    {
        return [
            'desde'            => $request->input('desde') ?: null,
            'hasta'            => $request->input('hasta') ?: null,
            'patente'          => $request->input('patente') ?: null,
            'estado'           => $request->input('estado') ?: null,
            'operario_id'      => $request->input('operario_id') ?: null,
            'zona_id'          => $isAdmin ? ($request->input('zona_id') ?: null) : null,
            'tipo_servicio_id' => $isAdmin ? ($request->input('tipo_servicio_id') ?: null) : null,
            'solo_alerta'      => $isAdmin ? ($request->boolean('solo_alerta') ?: null) : null,
            'solo_editados'    => $isAdmin ? ($request->boolean('solo_editados') ?: null) : null,
            'sort_direction'   => in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'desc',
        ];
    }

    private function resolveLogLabels(Collection $entradas): array
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

    private function formatLogValue(string $campo, ?string $valor, $servicios, $zonas): ?string
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
