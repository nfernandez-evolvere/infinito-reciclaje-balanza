<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\WithToastFlash;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoServicioRequest;
use App\Http\Requests\UpdateTipoServicioRequest;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Services\TipoServicioService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipoServicioController extends Controller
{
    use WithToastFlash;
    public function __construct(
        protected TipoServicioService $service,
    ) {}

    public function index(Request $request): View
    {
        $filters       = $request->only(['nombre', 'activo', 'tipo_vehiculo_id']);
        $tipos         = $this->service->listar($filters);
        $tiposVehiculo = TipoVehiculo::orderBy('nombre')->get();

        return view('modules.admin.tipos-servicio.index', compact('tipos', 'filters', 'tiposVehiculo'));
    }

    public function store(StoreTipoServicioRequest $request): RedirectResponse
    {
        try {
            $tipo = $this->service->crear($request->validated());

            return redirect()->route('admin.tipos-servicio.index')
                ->with('toast', [
                    'message'     => 'Tipo de servicio creado.',
                    'description' => "\"{$tipo->nombre}\" quedó disponible para asignar a zonas.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('admin.tipos-servicio.index');
        }
    }

    public function update(UpdateTipoServicioRequest $request, TipoServicio $tiposServicio): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->actualizar($tiposServicio, $validated);

            return redirect()->route('admin.tipos-servicio.index')
                ->with('toast', [
                    'message'     => 'Cambios guardados.',
                    'description' => "\"{$validated['nombre']}\" fue actualizado correctamente.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('admin.tipos-servicio.index');
        }
    }

    public function toggle(TipoServicio $tiposServicio): RedirectResponse
    {
        try {
            if ($tiposServicio->activo) {
                $this->service->desactivar($tiposServicio);
                $toast = [
                    'message'     => 'Tipo de servicio desactivado.',
                    'description' => "\"{$tiposServicio->nombre}\" no estará disponible para nuevos pesajes.",
                    'variant'     => 'success',
                ];
            } else {
                $this->service->activar($tiposServicio);
                $toast = [
                    'message'     => 'Tipo de servicio activado.',
                    'description' => "\"{$tiposServicio->nombre}\" volvió a estar disponible.",
                    'variant'     => 'success',
                ];
            }

            return redirect()->route('admin.tipos-servicio.index')->with('toast', $toast);
        } catch (\Throwable) {
            return $this->toastError('admin.tipos-servicio.index');
        }
    }

    public function destroy(TipoServicio $tiposServicio): RedirectResponse
    {
        try {
            $nombre = $tiposServicio->nombre;
            $this->service->eliminar($tiposServicio);

            return redirect()->route('admin.tipos-servicio.index')
                ->with('toast', [
                    'message'     => 'Tipo de servicio eliminado.',
                    'description' => 'Los pesajes asociados no se ven afectados.',
                    'variant'     => 'success',
                ]);
        } catch (QueryException $e) {
            $isConstraint = in_array($e->getCode(), ['23000', '23503']);

            return redirect()->route('admin.tipos-servicio.index')
                ->with('toast', $isConstraint ? [
                    'message'     => 'No se puede eliminar.',
                    'description' => "\"{$nombre}\" tiene pesajes registrados. Desactivalo en su lugar.",
                    'variant'     => 'destructive',
                ] : $this->toastErrorData());
        } catch (\Throwable) {
            return $this->toastError('admin.tipos-servicio.index');
        }
    }

}
