<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\WithToastFlash;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Models\Vehiculo;
use App\Repositories\TipoVehiculoRepository;
use App\Services\VehiculoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehiculoController extends Controller
{
    use WithToastFlash;
    public function __construct(
        protected VehiculoService $service,
        protected TipoVehiculoRepository $tipoVehiculoRepository,
    ) {}

    public function index(Request $request): View
    {
        $filters        = $request->only(['patente', 'numero_interno', 'tipo_vehiculo_id', 'activo']);
        $vehiculos      = $this->service->listar($filters);
        $tiposVehiculo  = $this->tipoVehiculoRepository->todos();

        return view('modules.admin.vehiculos.index', compact('vehiculos', 'filters', 'tiposVehiculo'));
    }

    public function store(StoreVehiculoRequest $request): RedirectResponse
    {
        try {
            $vehiculo = $this->service->crear($request->validated());

            return redirect()->route('admin.vehiculos.index')
                ->with('toast', [
                    'message'     => 'Vehículo creado.',
                    'description' => "\"{$vehiculo->patente}\" quedó disponible para asignar a pesajes.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('admin.vehiculos.index');
        }
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->actualizar($vehiculo, $validated);

            return redirect()->route('admin.vehiculos.index')
                ->with('toast', [
                    'message'     => 'Cambios guardados.',
                    'description' => "\"{$validated['patente']}\" fue actualizado correctamente.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('admin.vehiculos.index');
        }
    }

    public function toggle(Vehiculo $vehiculo): RedirectResponse
    {
        try {
            if ($vehiculo->activo) {
                $this->service->desactivar($vehiculo);
                $toast = [
                    'message'     => 'Vehículo desactivado.',
                    'description' => "\"{$vehiculo->patente}\" no aparecerá en el autocompletado de nuevos pesajes.",
                    'variant'     => 'success',
                ];
            } else {
                $this->service->activar($vehiculo);
                $toast = [
                    'message'     => 'Vehículo activado.',
                    'description' => "\"{$vehiculo->patente}\" volvió a estar disponible.",
                    'variant'     => 'success',
                ];
            }

            return redirect()->route('admin.vehiculos.index')->with('toast', $toast);
        } catch (\Throwable) {
            return $this->toastError('admin.vehiculos.index');
        }
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        try {
            $patente = $vehiculo->patente;
            $this->service->eliminar($vehiculo);

            return redirect()->route('admin.vehiculos.index')
                ->with('toast', [
                    'message'     => 'Vehículo eliminado.',
                    'description' => 'Los pesajes históricos de este vehículo no se ven afectados.',
                    'variant'     => 'success',
                ]);
        } catch (QueryException $e) {
            $isConstraint = in_array($e->getCode(), ['23000', '23503']);

            return redirect()->route('admin.vehiculos.index')
                ->with('toast', $isConstraint ? [
                    'message'     => 'No se puede eliminar.',
                    'description' => "\"{$vehiculo->patente}\" tiene pesajes registrados. Desactivá el vehículo para quitarlo del padrón activo.",
                    'variant'     => 'destructive',
                ] : $this->toastErrorData());
        } catch (\Throwable) {
            return $this->toastError('admin.vehiculos.index');
        }
    }

}
