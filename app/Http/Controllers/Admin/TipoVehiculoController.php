<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoVehiculoRequest;
use App\Http\Requests\UpdateTipoVehiculoRequest;
use App\Models\TipoVehiculo;
use App\Services\TipoVehiculoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipoVehiculoController extends Controller
{
    public function __construct(
        protected TipoVehiculoService $service,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['nombre', 'peso_min', 'peso_max', 'activo']);
        $tipos   = $this->service->listar($filters);

        return view('modules.admin.tipos-vehiculo.index', compact('tipos', 'filters'));
    }

    public function store(StoreTipoVehiculoRequest $request): RedirectResponse
    {
        try {
            $tipo = $this->service->crear($request->validated());

            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', [
                    'message'     => 'Tipo de vehículo creado.',
                    'description' => "\"{$tipo->nombre}\" quedó disponible para asignar a vehículos.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', [
                    'message'     => 'Error inesperado.',
                    'description' => 'Si el problema persiste, revisá los logs del sistema.',
                    'variant'     => 'destructive',
                ]);
        }
    }

    public function update(UpdateTipoVehiculoRequest $request, TipoVehiculo $tiposVehiculo): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->actualizar($tiposVehiculo, $validated);

            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', [
                    'message'     => 'Cambios guardados.',
                    'description' => "\"{$validated['nombre']}\" fue actualizado correctamente.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', [
                    'message'     => 'Error inesperado.',
                    'description' => 'Si el problema persiste, revisá los logs del sistema.',
                    'variant'     => 'destructive',
                ]);
        }
    }

    public function toggle(TipoVehiculo $tiposVehiculo): RedirectResponse
    {
        try {
            if ($tiposVehiculo->activo) {
                $this->service->desactivar($tiposVehiculo);
                $toast = [
                    'message'     => 'Tipo de vehículo desactivado.',
                    'description' => "\"{$tiposVehiculo->nombre}\" no aparecerá en nuevos pesajes.",
                    'variant'     => 'success',
                ];
            } else {
                $this->service->activar($tiposVehiculo);
                $toast = [
                    'message'     => 'Tipo de vehículo activado.',
                    'description' => "\"{$tiposVehiculo->nombre}\" volvió a estar disponible.",
                    'variant'     => 'success',
                ];
            }

            return redirect()->route('admin.tipos-vehiculo.index')->with('toast', $toast);
        } catch (\Throwable) {
            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', [
                    'message'     => 'Error inesperado.',
                    'description' => 'Si el problema persiste, revisá los logs del sistema.',
                    'variant'     => 'destructive',
                ]);
        }
    }

    public function destroy(TipoVehiculo $tiposVehiculo): RedirectResponse
    {
        try {
            $this->service->eliminar($tiposVehiculo);

            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', [
                    'message'     => 'Tipo de vehículo eliminado.',
                    'description' => 'Los vehículos y pesajes asociados no se ven afectados.',
                    'variant'     => 'success',
                ]);
        } catch (QueryException $e) {
            $isConstraint = in_array($e->getCode(), ['23000', '23503']);

            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', $isConstraint ? [
                    'message'     => 'No se puede eliminar.',
                    'description' => "\"{$tiposVehiculo->nombre}\" tiene vehículos asignados. Primero reasignalos o desactivá el tipo.",
                    'variant'     => 'destructive',
                ] : [
                    'message'     => 'Error inesperado.',
                    'description' => 'Si el problema persiste, revisá los logs del sistema.',
                    'variant'     => 'destructive',
                ]);
        } catch (\Throwable) {
            return redirect()->route('admin.tipos-vehiculo.index')
                ->with('toast', [
                    'message'     => 'Error inesperado.',
                    'description' => 'Si el problema persiste, revisá los logs del sistema.',
                    'variant'     => 'destructive',
                ]);
        }
    }
}
