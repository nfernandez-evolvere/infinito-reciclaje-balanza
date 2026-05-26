<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeRequest;
use App\Models\TipoServicio;
use App\Models\User;
use App\Models\Zona;
use App\Repositories\PesajeRepository;
use App\Services\PesajeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PesajeController extends Controller
{
    public function __construct(
        protected PesajeService $pesajeService,
        protected PesajeRepository $pesajeRepository,
    ) {}

    public function index(Request $request): View
    {
        $filtros = [
            'desde'            => $request->input('desde', today()->subDays(29)->toDateString()),
            'hasta'            => $request->input('hasta', today()->toDateString()),
            'patente'          => $request->input('patente') ?: null,
            'estado'           => $request->input('estado') ?: null,
            'operario_id'      => $request->input('operario_id') ?: null,
            'zona_id'          => $request->input('zona_id') ?: null,
            'tipo_servicio_id' => $request->input('tipo_servicio_id') ?: null,
            'solo_alerta'      => $request->boolean('solo_alerta') ?: null,
            'solo_editados'    => $request->boolean('solo_editados') ?: null,
        ];

        $pesajes      = $this->pesajeRepository->filtrado($filtros);
        $kpis         = $this->pesajeRepository->kpisDe($pesajes);
        $kpisHoy      = $this->pesajeRepository->kpisDelTurno();
        $ultimoPesaje = $this->pesajeRepository->ultimoDelTurno();
        $operarios    = User::whereHas('organizaciones', fn ($q) => $q->where('organizaciones.id', app('organizacion')->id))
            ->where('role', 'operador')
            ->orderBy('name')
            ->get();
        $zonas         = Zona::activos()->orderBy('nombre')->get();
        $tiposServicio = TipoServicio::activos()->orderBy('nombre')->get();

        return view('modules.shared.historial', [
            'pesajes'        => $pesajes,
            'kpis'           => $kpis,
            'kpisHoy'        => $kpisHoy,
            'ultimoPesaje'   => $ultimoPesaje,
            'filtros'        => $filtros,
            'operarios'      => $operarios,
            'zonas'          => $zonas,
            'tiposServicio'  => $tiposServicio,
            'titulo'         => 'Pesajes',
            'routeHistorial' => route('admin.pesajes.index'),
            'exportUrl'      => route('admin.pesajes.export'),
        ]);
    }

    public function create(): View
    {
        $servicios = TipoServicio::activos()->with('tipoVehiculoSugerido')->get();

        return view('modules.shared.balanza', [
            'servicios'  => $servicios,
            'formAction' => route('admin.pesajes.store'),
            'cancelUrl'  => route('admin.pesajes.index'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filtros = [
            'desde'            => $request->input('desde', today()->subDays(29)->toDateString()),
            'hasta'            => $request->input('hasta', today()->toDateString()),
            'patente'          => $request->input('patente') ?: null,
            'estado'           => $request->input('estado') ?: null,
            'operario_id'      => $request->input('operario_id') ?: null,
            'zona_id'          => $request->input('zona_id') ?: null,
            'tipo_servicio_id' => $request->input('tipo_servicio_id') ?: null,
            'solo_alerta'      => $request->boolean('solo_alerta') ?: null,
            'solo_editados'    => $request->boolean('solo_editados') ?: null,
        ];

        $pesajes  = $this->pesajeRepository->filtrado($filtros);
        $filename = 'pesajes-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($pesajes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM para Excel

            fputcsv($handle, [
                'ID', 'Entrada', 'Salida', 'Estado',
                'Patente', 'Tipo vehículo', 'Servicio', 'Origen',
                'Operario', 'Bruto (kg)', 'Tara (kg)', 'Neto (kg)',
                'Alerta peso', 'Editado',
            ]);

            foreach ($pesajes as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->created_at->format('d/m/Y H:i'),
                    $p->hora_salida?->format('d/m/Y H:i') ?? '',
                    $p->estado,
                    $p->vehiculo->patente,
                    $p->vehiculo->tipoVehiculo?->nombre ?? '',
                    $p->tipoServicio->nombre,
                    $p->zona->nombre,
                    $p->operador->name,
                    $p->peso_bruto_kg,
                    $p->peso_tara_kg,
                    $p->peso_neto_kg,
                    $p->alerta_peso ? 'Sí' : 'No',
                    $p->editado ? 'Sí' : 'No',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(StorePesajeRequest $request): RedirectResponse
    {
        $this->pesajeService->crear($request->validated(), auth()->user());

        return redirect()->route('admin.pesajes.index')
            ->with('toast', [
                'message'     => 'Pesaje registrado',
                'description' => 'El pesaje fue guardado con éxito.',
                'variant'     => 'success',
            ]);
    }
}
