<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoVehiculoRequest;
use App\Http\Requests\UpdateTipoVehiculoRequest;
use App\Models\TipoVehiculo;
use App\Services\TipoVehiculoService;
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
        $this->service->crear($request->validated());

        return redirect()->route('admin.tipos-vehiculo.index')
            ->with('toast', ['message' => 'Tipo de vehículo creado.', 'variant' => 'success']);
    }

    public function update(UpdateTipoVehiculoRequest $request, TipoVehiculo $tiposVehiculo): RedirectResponse
    {
        $this->service->actualizar($tiposVehiculo, $request->validated());

        return redirect()->route('admin.tipos-vehiculo.index')
            ->with('toast', ['message' => 'Tipo de vehículo actualizado.', 'variant' => 'success']);
    }

    public function toggle(TipoVehiculo $tiposVehiculo): RedirectResponse
    {
        if ($tiposVehiculo->activo) {
            $this->service->desactivar($tiposVehiculo);
            $message = 'Tipo de vehículo desactivado.';
        } else {
            $this->service->activar($tiposVehiculo);
            $message = 'Tipo de vehículo activado.';
        }

        return redirect()->route('admin.tipos-vehiculo.index')
            ->with('toast', ['message' => $message, 'variant' => 'success']);
    }

    public function destroy(TipoVehiculo $tiposVehiculo): RedirectResponse
    {
        $this->service->eliminar($tiposVehiculo);

        return redirect()->route('admin.tipos-vehiculo.index')
            ->with('toast', ['message' => 'Tipo de vehículo eliminado.', 'variant' => 'success']);
    }
}
