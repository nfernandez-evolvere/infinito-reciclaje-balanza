<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizacionRequest;
use App\Http\Requests\UpdateOrganizacionRequest;
use App\Models\Organizacion;
use App\Services\OrganizacionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizacionController extends Controller
{
    public function __construct(
        protected OrganizacionService $service,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['nombre', 'activo']);
        $organizaciones = $this->service->paginate();

        return view('modules.super_admin.organizaciones.index', compact('organizaciones', 'filters'));
    }

    public function store(StoreOrganizacionRequest $request): RedirectResponse
    {
        try {
            $org = $this->service->create($request->validated());

            return redirect()->route('super.organizaciones.index')
                ->with('toast', [
                    'message'     => 'Organización creada.',
                    'description' => "\"{$org->nombre}\" está lista para operar.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function update(UpdateOrganizacionRequest $request, Organizacion $organizacion): RedirectResponse
    {
        try {
            $this->service->update($organizacion, $request->validated());

            return redirect()->route('super.organizaciones.index')
                ->with('toast', [
                    'message' => 'Cambios guardados.',
                    'variant' => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function toggle(Organizacion $organizacion): RedirectResponse
    {
        try {
            $this->service->toggleActivo($organizacion);

            $msg = $organizacion->activo
                ? ['message' => 'Organización desactivada.', 'description' => "\"{$organizacion->nombre}\" ya no puede operar."]
                : ['message' => 'Organización activada.', 'description' => "\"{$organizacion->nombre}\" volvió a estar disponible."];

            return redirect()->route('super.organizaciones.index')
                ->with('toast', array_merge($msg, ['variant' => 'success']));
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    public function destroy(Organizacion $organizacion): RedirectResponse
    {
        try {
            $nombre = $organizacion->nombre;
            $this->service->delete($organizacion);

            return redirect()->route('super.organizaciones.index')
                ->with('toast', [
                    'message'     => 'Organización eliminada.',
                    'description' => "\"{$nombre}\" fue removida del sistema.",
                    'variant'     => 'success',
                ]);
        } catch (QueryException $e) {
            $isConstraint = in_array($e->getCode(), ['23000', '23503']);

            return redirect()->route('super.organizaciones.index')
                ->with('toast', $isConstraint ? [
                    'message'     => 'No se puede eliminar.',
                    'description' => "\"{$organizacion->nombre}\" tiene datos asociados. Desactivala en su lugar.",
                    'variant'     => 'destructive',
                ] : $this->toastErrorData());
        } catch (\Throwable) {
            return $this->toastError();
        }
    }

    private function toastError(): RedirectResponse
    {
        return redirect()->route('super.organizaciones.index')
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
