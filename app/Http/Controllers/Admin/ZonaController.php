<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\WithToastFlash;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreZonaRequest;
use App\Http\Requests\UpdateZonaRequest;
use App\Models\Zona;
use App\Services\ZonaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class ZonaController extends Controller
{
    use WithToastFlash;

    private const INDEX = 'admin.tipos-servicio.index';

    public function __construct(
        protected ZonaService $service,
    ) {}

    public function store(StoreZonaRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $turnos = $validated['turnos'] ?? [];
            $horarios = $validated['horarios'] ?? [];
            unset($validated['turnos'], $validated['horarios']);

            $zona = $this->service->crear($validated, $turnos, $horarios);

            return redirect()->route(self::INDEX)
                ->with('toast', [
                    'message'     => 'Zona creada.',
                    'description' => "\"{$zona->nombre}\" quedó disponible en el formulario de pesaje.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError(self::INDEX);
        }
    }

    public function update(UpdateZonaRequest $request, Zona $zona): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $turnos = $validated['turnos'] ?? [];
            $horarios = $validated['horarios'] ?? [];
            unset($validated['turnos'], $validated['horarios']);

            $this->service->actualizar($zona, $validated, $turnos, $horarios);

            return redirect()->route(self::INDEX)
                ->with('toast', [
                    'message'     => 'Cambios guardados.',
                    'description' => "\"{$validated['nombre']}\" fue actualizada.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError(self::INDEX);
        }
    }

    public function toggle(Zona $zona): RedirectResponse
    {
        try {
            if ($zona->activo) {
                $this->service->desactivar($zona);
                $toast = [
                    'message'     => 'Zona desactivada.',
                    'description' => "\"{$zona->nombre}\" no aparecerá en el formulario de pesaje.",
                    'variant'     => 'success',
                ];
            } else {
                $this->service->activar($zona);
                $toast = [
                    'message'     => 'Zona activada.',
                    'description' => "\"{$zona->nombre}\" volvió a estar disponible.",
                    'variant'     => 'success',
                ];
            }

            return redirect()->route(self::INDEX)->with('toast', $toast);
        } catch (\Throwable) {
            return $this->toastError(self::INDEX);
        }
    }

    public function destroy(Zona $zona): RedirectResponse
    {
        $nombre = $zona->nombre;

        try {
            $this->service->eliminar($zona);

            return redirect()->route(self::INDEX)
                ->with('toast', [
                    'message'     => 'Zona eliminada.',
                    'description' => 'Los pesajes históricos no se ven afectados.',
                    'variant'     => 'destructive',
                ]);
        } catch (QueryException $e) {
            $isConstraint = in_array($e->getCode(), ['23000', '23503']);

            return redirect()->route(self::INDEX)
                ->with('toast', $isConstraint ? [
                    'message'     => 'No se puede eliminar.',
                    'description' => "\"{$nombre}\" tiene pesajes registrados. Desactivala en su lugar.",
                    'variant'     => 'destructive',
                ] : $this->toastErrorData());
        } catch (\Throwable) {
            return $this->toastError(self::INDEX);
        }
    }
}
