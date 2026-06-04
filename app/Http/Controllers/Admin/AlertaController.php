<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Repositories\AlertaRepository;
use App\Services\AlertaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function __construct(
        protected AlertaRepository $alertaRepository,
        protected AlertaService $alertaService,
    ) {}

    public function index(Request $request): View
    {
        $userId         = $request->user()->id;
        $organizacionId = app('organizacion')->id;
        $tab = $request->input('tab', 'alertas');

        $filtros = [
            'tipo'  => $request->input('tipo'),
            'leida' => $request->input('leida', ''),
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
        ];

        $alertas  = $this->alertaRepository->listar($userId, $filtros);
        $config   = $this->alertaService->getConfigConDefaults($organizacionId);
        $noLeidas = $this->alertaRepository->countNoLeidas($userId);

        return view('modules.admin.alertas.index', compact('alertas', 'config', 'noLeidas', 'filtros', 'tab'));
    }

    public function marcarLeida(Alerta $alerta): RedirectResponse
    {
        $this->alertaRepository->marcarLeida($alerta);

        return back()->with('toast', [
            'message'     => 'Alerta marcada como leída',
            'description' => $alerta->titulo,
            'variant'     => 'success',
        ]);
    }

    public function marcarTodasLeidas(Request $request): RedirectResponse
    {
        $count = $this->alertaRepository->marcarTodasLeidas($request->user()->id);

        return back()->with('toast', [
            'message'     => "{$count} alertas marcadas como leídas",
            'variant'     => 'success',
        ]);
    }

    public function updateConfig(Request $request): RedirectResponse
    {
        $this->alertaService->guardarConfig(
            app('organizacion')->id,
            $request->input('config', []),
        );

        return back()->with('toast', [
            'message'     => 'Configuración guardada.',
            'description' => 'Los cambios en alertas están activos.',
            'variant'     => 'success',
        ]);
    }

    public function novedades(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'count'  => $this->alertaRepository->countNoLeidas($userId),
            'items'  => $this->alertaRepository->ultimasNoLeidas($userId, 5)->map(fn ($a) => [
                'id'          => $a->id,
                'tipo'        => $a->tipo,
                'tipo_label'  => $a->tipoLabel(),
                'tipo_variant'=> $a->tipoVariant(),
                'titulo'      => $a->titulo,
                'descripcion' => $a->descripcion,
                'hace'        => $a->created_at->diffForHumans(),
                'url_pesaje'  => $a->pesaje_id ? route('admin.pesajes.index', ['search' => $a->pesaje?->vehiculo?->patente]) : null,
            ]),
        ]);
    }
}
