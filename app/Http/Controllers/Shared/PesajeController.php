<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelarPesajeRequest;
use App\Http\Requests\EgresoPesajeRequest;
use App\Http\Requests\UpdatePesajeRequest;
use App\Models\Pesaje;
use App\Models\PesajeLog;
use App\Repositories\PesajeLogRepository;
use App\Repositories\PesajeRepository;
use App\Repositories\TipoServicioRepository;
use App\Repositories\TipoVehiculoRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\ZonaRepository;
use App\Services\PesajeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesajeController extends Controller
{
    private const LOG_LABELS = [
        'tipo_servicio_id' => 'Tipo de servicio',
        'zona_id'          => 'Origen',
        'peso_bruto_kg'    => 'Peso bruto',
        'peso_tara_kg'     => 'Tara',
        'peso_neto_kg'     => 'Peso neto',
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
        protected TipoVehiculoRepository $tipoVehiculoRepository,
    ) {}

    public function index(Request $request): View
    {
        $isAdmin = auth()->user()->isAdmin();
        $filtros = $this->buildFiltros($request, $isAdmin);

        $pesajes = $this->pesajeRepository->filtrado($filtros);
        $kpis = $this->pesajeRepository->kpisFiltrado($filtros);
        $kpisHoy = $this->pesajeRepository->kpisDelTurno();
        $ultimoPesaje = $this->pesajeRepository->ultimoDelTurno();
        $operarios = $this->usuarioRepository->getOperadoresDeLaOrg();

        if (! $isAdmin) {
            return view('modules.shared.historial', [
                'pesajes'        => $pesajes,
                'kpis'           => $kpis,
                'kpisHoy'        => $kpisHoy,
                'ultimoPesaje'   => $ultimoPesaje,
                'filtros'        => $filtros,
                'operarios'      => $operarios,
                'titulo'         => 'Pesajes',
                'routeHistorial' => route('historial'),
            ]);
        }

        // Admin: pantalla unificada con tabs «Pesajes» (con KPIs) y «Modificaciones» (sin KPIs).
        // Cada tab usa su propio namespace de parámetros (Modificaciones con prefijo `m_`)
        // para que filtros, orden y paginación de ambas tablas coexistan sin colisionar.
        $filtrosMod = $this->buildFiltrosModificaciones($request);
        $modificaciones = $this->pesajeRepository->filtrado($filtrosMod, pageName: 'm_page');

        return view('modules.admin.pesajes', [
            'tab'            => $request->input('tab') === 'modificaciones' ? 'modificaciones' : 'pesajes',
            'pesajes'        => $pesajes,
            'modificaciones' => $modificaciones,
            'kpis'           => $kpis,
            'kpisHoy'        => $kpisHoy,
            'ultimoPesaje'   => $ultimoPesaje,
            'filtros'        => $filtros,
            'filtrosMod'     => $filtrosMod,
            'operarios'      => $operarios,
            'titulo'         => 'Pesajes',
            'routeHistorial' => route('admin.pesajes.index'),
            'zonas'          => $this->zonaRepository->activos(),
            'tiposServicio'  => $this->tipoServicioRepository->activos(),
            'tiposVehiculo'  => $this->tipoVehiculoRepository->activos(),
        ]);
    }

    public function show(Pesaje $pesaje): View
    {
        $pesaje->load(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador']);

        $isAdmin = auth()->user()->isAdmin();
        $routeHistorial = $isAdmin ? route('admin.pesajes.index') : route('historial');

        return view('modules.operador.pesaje-detalle', compact('pesaje', 'isAdmin', 'routeHistorial'));
    }

    public function edit(Pesaje $pesaje): View
    {
        $pesaje->load(['vehiculo.tipoVehiculo', 'tipoServicio.tiposVehiculo', 'zona']);

        $servicios = $this->tipoServicioRepository->activosConTiposVehiculo();

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

        $servicio = $pesaje->tipoServicio;
        $zonasDisponibles = $this->zonaRepository->zonasConTurnosPara($servicio)->values();
        $zonaActual = $zonasDisponibles->firstWhere('id', $pesaje->zona_id);

        $initial = [
            'vehiculo'          => $vehiculoJs,
            'servicioId'        => $servicio->id,
            'servicioNombre'    => $servicio->nombre,
            'tiposSugeridos'    => $servicio->tiposVehiculo->pluck('nombre')->values(),
            'zonasDisponibles'  => $zonasDisponibles->toArray(),
            'zonaId'            => $pesaje->zona_id,
            'zonaNombre'        => $pesaje->zona->nombre,
            'turnosDisponibles' => $zonaActual['turnos'] ?? [],
            'turno'             => $pesaje->turno ?? '',
            'pesoBruto'         => $pesaje->peso_bruto_kg,
            'observaciones'     => $pesaje->observaciones ?? '',
        ];

        // Al cancelar la edición se vuelve al mismo listado al que redirige guardar,
        // según rol y pantalla de origen (evita el 403 del admin contra 'historial').
        $rutaRetorno = $this->rutaRetorno();
        $cancelUrl = route($rutaRetorno, $this->tabRetorno($rutaRetorno));

        return view('modules.operador.pesaje-editar', compact('pesaje', 'servicios', 'initial', 'cancelUrl'));
    }

    public function update(UpdatePesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->editar($pesaje, $request->validated(), auth()->user());

        $pesaje->loadMissing('vehiculo');

        $route = $this->rutaRetorno();

        return redirect()->route($route, $this->tabRetorno($route))
            ->with('toast', [
                'message'     => 'Cambios guardados.',
                'description' => 'El historial del pesaje de '.$pesaje->vehiculo->patente.' fue actualizado.',
                'variant'     => 'success',
            ]);
    }

    public function egreso(EgresoPesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->marcarEgreso($pesaje, $request->validated());

        $pesaje->loadMissing('vehiculo');
        $route = $this->rutaRetorno();

        return redirect()->route($route, $this->tabRetorno($route))
            ->with('toast', [
                'message'     => 'Egreso registrado.',
                'description' => 'El egreso de '.$pesaje->vehiculo->patente.' fue registrado.',
                'variant'     => 'success',
            ]);
    }

    public function cancelar(CancelarPesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->cancelar($pesaje, $request->validated(), auth()->user());

        $pesaje->loadMissing('vehiculo');
        $route = $this->rutaRetorno();

        return redirect()->route($route, $this->tabRetorno($route))
            ->with('toast', [
                'message'     => 'Pesaje cancelado.',
                'description' => 'El pesaje de '.$pesaje->vehiculo->patente.' fue removido del turno.',
                'variant'     => 'destructive',
            ]);
    }

    public function log(Pesaje $pesaje): JsonResponse
    {
        $entradas = $this->logRepository->porPesaje($pesaje->id);

        [$servicios, $zonas] = $this->resolveLogLabels($entradas);

        $grupos = $entradas
            ->groupBy(fn ($e) => $e->created_at->timestamp.'|'.$e->usuario_id.'|'.$e->motivo)
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

    private function buildFiltros(Request $request, bool $isAdmin): array
    {
        // El select «Mostrar» (admin) expone un único parámetro `mostrar` que se
        // traduce a los flags que entiende PesajeRepository::buildQuery().
        $mostrar = $isAdmin ? $request->input('mostrar') : null;

        return [
            'desde'            => $request->input('desde') ?: null,
            'hasta'            => $request->input('hasta') ?: null,
            'patente'          => $request->input('patente') ?: null,
            'estado'           => $request->input('estado') ?: null,
            'operario_id'      => $request->input('operario_id') ?: null,
            'zona_id'          => $isAdmin ? ($request->input('zona_id') ?: null) : null,
            'tipo_servicio_id' => $isAdmin ? ($request->input('tipo_servicio_id') ?: null) : null,
            'tipo_vehiculo_id' => $isAdmin ? ($request->input('tipo_vehiculo_id') ?: null) : null,
            'solo_alerta'      => $mostrar === 'alerta' ? true : null,
            'solo_editados'    => $mostrar === 'editados' ? true : null,
            'direction'        => in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'desc',
        ];
    }

    /**
     * Filtros del tab «Modificaciones». Lee los inputs con prefijo `m_` para no
     * colisionar con los del tab «Pesajes» (que comparten la misma pantalla),
     * pero conserva las claves canónicas que entiende PesajeRepository::buildQuery().
     */
    private function buildFiltrosModificaciones(Request $request): array
    {
        return [
            'modificaciones'   => true,
            'tipo'             => in_array($request->input('m_tipo'), ['editado', 'cancelado'], true) ? $request->input('m_tipo') : null,
            'desde'            => $request->input('m_desde') ?: null,
            'hasta'            => $request->input('m_hasta') ?: null,
            'patente'          => $request->input('m_patente') ?: null,
            'operario_id'      => $request->input('m_operario_id') ?: null,
            'zona_id'          => $request->input('m_zona_id') ?: null,
            'tipo_servicio_id' => $request->input('m_tipo_servicio_id') ?: null,
            'tipo_vehiculo_id' => $request->input('m_tipo_vehiculo_id') ?: null,
            'direction'        => in_array($request->input('m_direction'), ['asc', 'desc']) ? $request->input('m_direction') : 'desc',
        ];
    }

    /**
     * Resuelve la ruta de retorno tras editar/cancelar/marcar egreso, según la pantalla de origen.
     * Solo acepta rutas de listado conocidas (whitelist) para evitar redirecciones arbitrarias.
     */
    private function rutaRetorno(): string
    {
        $permitidas = ['historial', 'admin.pesajes.index'];

        if (in_array(request('origen'), $permitidas, true)) {
            return request('origen');
        }

        return auth()->user()->isAdmin() ? 'admin.pesajes.index' : 'historial';
    }

    /**
     * Parámetros extra del redirect de retorno: conserva el tab «Modificaciones»
     * cuando la acción se originó en ese tab de la pantalla de Pesajes del admin.
     *
     * @return array<string, string>
     */
    private function tabRetorno(string $route): array
    {
        return $route === 'admin.pesajes.index' && request('tab') === 'modificaciones'
            ? ['tab' => 'modificaciones']
            : [];
    }

    /**
     * @param  Collection<int, PesajeLog>  $entradas
     * @return array{0: \Illuminate\Support\Collection<int, string>, 1: \Illuminate\Support\Collection<int, string>}
     */
    private function resolveLogLabels(Collection $entradas): array
    {
        $ids = fn (string $campo) => $entradas
            ->where('campo', $campo)
            ->flatMap(fn ($e) => [$e->valor_anterior, $e->valor_nuevo])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->unique()->values();

        $servicios = $this->tipoServicioRepository->nombresPorIds($ids('tipo_servicio_id'));
        $zonas = $this->zonaRepository->nombresPorIds($ids('zona_id'));

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
            'peso_bruto_kg',
            'peso_tara_kg',
            'peso_neto_kg' => number_format((int) $valor, 0, ',', '.').' kg',
            default        => $valor,
        };
    }
}
