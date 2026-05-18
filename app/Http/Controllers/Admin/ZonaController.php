<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreZonaRequest;
use App\Http\Requests\UpdateZonaRequest;
use App\Models\TipoServicio;
use App\Models\Zona;
use App\Services\ZonaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZonaController extends Controller
{
    public function __construct(
        protected ZonaService $service,
    ) {}

    public function index(Request $request): View
    {
        $filters      = $request->only(['nombre', 'activo']);
        $zonas        = $this->service->listar($filters);
        $tiposServicio = TipoServicio::activos()->orderBy('nombre')->get();

        return view('modules.admin.zonas.index', compact('zonas', 'filters', 'tiposServicio'));
    }

    public function store(StoreZonaRequest $request): RedirectResponse
    {
        try {
            $zona = $this->service->crear($request->validated());

            return redirect()->route('admin.zonas.index')
                ->with('toast', [
                    'message'     => 'Zona creada.',
                    'description' => "\"{$zona->nombre}\" quedó disponible para asignar servicios.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function update(UpdateZonaRequest $request, Zona $zona): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->actualizar($zona, $validated);

            return redirect()->route('admin.zonas.index')
                ->with('toast', [
                    'message'     => 'Cambios guardados.',
                    'description' => "\"{$validated['nombre']}\" fue actualizada correctamente.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
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

            return redirect()->route('admin.zonas.index')->with('toast', $toast);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function destroy(Zona $zona): RedirectResponse
    {
        try {
            $nombre = $zona->nombre;
            $this->service->eliminar($zona);

            return redirect()->route('admin.zonas.index')
                ->with('toast', [
                    'message'     => 'Zona eliminada.',
                    'description' => 'Los pesajes históricos no se ven afectados.',
                    'variant'     => 'success',
                ]);
        } catch (QueryException $e) {
            $isConstraint = in_array($e->getCode(), ['23000', '23503']);

            return redirect()->route('admin.zonas.index')
                ->with('toast', $isConstraint ? [
                    'message'     => 'No se puede eliminar.',
                    'description' => "\"{$nombre}\" tiene pesajes registrados. Desactivala en su lugar.",
                    'variant'     => 'destructive',
                ] : $this->toastErrorData());
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    private function toastError(): RedirectResponse
    {
        return redirect()->route('admin.zonas.index')
            ->with('toast', $this->toastErrorData());
    }

    private function toastErrorData(): array
    {
        return [
            'message'     => 'Error inesperado.',
            'description' => 'Si el problema persiste, revisá los logs del sistema.',
            'variant'     => 'destructive',
        ];
    }
}
