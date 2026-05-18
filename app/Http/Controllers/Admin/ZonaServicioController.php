<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreZonaServicioRequest;
use App\Models\TipoServicio;
use App\Models\Zona;
use App\Services\ZonaService;
use Illuminate\Http\RedirectResponse;

class ZonaServicioController extends Controller
{
    public function __construct(
        protected ZonaService $service,
    ) {}

    public function store(StoreZonaServicioRequest $request, Zona $zona): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $turnos    = $validated['turnos'] ?? [];
            $horarios  = $validated['horarios'] ?? [];

            $this->service->asignarServicio(
                $zona,
                (int) $validated['tipo_servicio_id'],
                $turnos,
                $horarios,
            );

            $servicio = TipoServicio::find($validated['tipo_servicio_id']);

            return redirect()->route('admin.zonas.index')
                ->with('toast', [
                    'message'     => 'Servicio asignado.',
                    'description' => "\"{$servicio->nombre}\" fue asignado a {$zona->nombre}.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function update(StoreZonaServicioRequest $request, Zona $zona, TipoServicio $tipoServicio): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $turnos    = $validated['turnos'] ?? [];
            $horarios  = $validated['horarios'] ?? [];

            $this->service->actualizarServicio($zona, $tipoServicio->id, $turnos, $horarios);

            return redirect()->route('admin.zonas.index')
                ->with('toast', [
                    'message'     => 'Asignación actualizada.',
                    'description' => "\"{$tipoServicio->nombre}\" en {$zona->nombre} fue actualizado.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function destroy(Zona $zona, TipoServicio $tipoServicio): RedirectResponse
    {
        try {
            $this->service->quitarServicio($zona, $tipoServicio->id);

            return redirect()->route('admin.zonas.index')
                ->with('toast', [
                    'message'     => 'Servicio quitado.',
                    'description' => "\"{$tipoServicio->nombre}\" fue removido de {$zona->nombre}.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    private function toastError(): RedirectResponse
    {
        return redirect()->route('admin.zonas.index')
            ->with('toast', [
                'message'     => 'Error inesperado.',
                'description' => 'Si el problema persiste, revisá los logs del sistema.',
                'variant'     => 'destructive',
            ]);
    }
}
