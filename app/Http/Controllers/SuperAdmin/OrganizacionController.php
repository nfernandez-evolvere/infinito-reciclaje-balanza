<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Concerns\WithToastFlash;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizacionRequest;
use App\Http\Requests\UpdateOrganizacionRequest;
use App\Models\Organizacion;
use App\Models\User;
use App\Services\OrganizacionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizacionController extends Controller
{
    use WithToastFlash;
    public function __construct(
        protected OrganizacionService $service,
    ) {}

    public function searchUsers(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('role', '!=', 'super_admin')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

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

            $adminEmail = $request->validated()['admin_email'];

            return redirect()->route('super.organizaciones.index')
                ->with('toast', [
                    'message'     => 'Organización creada.',
                    'description' => "\"{$org->nombre}\" fue creada con el admin {$adminEmail}.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('super.organizaciones.index');
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
            return $this->toastError('super.organizaciones.index');
        }
    }

    public function toggle(Organizacion $organizacion): RedirectResponse
    {
        try {
            $eraActivo = $organizacion->activo;
            $nombre    = $organizacion->nombre;
            $this->service->toggleActivo($organizacion);

            $msg = $eraActivo
                ? ['message' => 'Organización desactivada.', 'description' => "\"{$nombre}\" ya no puede operar."]
                : ['message' => 'Organización activada.',   'description' => "\"{$nombre}\" volvió a estar disponible."];

            return redirect()->route('super.organizaciones.index')
                ->with('toast', array_merge($msg, ['variant' => 'success']));
        } catch (\Throwable) {
            return $this->toastError('super.organizaciones.index');
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
                    'description' => "\"{$nombre}\" tiene datos asociados. Desactivala en su lugar.",
                    'variant'     => 'destructive',
                ] : $this->toastErrorData());
        } catch (\Throwable) {
            return $this->toastError('super.organizaciones.index');
        }
    }

    public function addUser(Request $request, Organizacion $organizacion): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name'  => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $user = $this->service->addUserToOrg(
                $organizacion,
                $validated['email'],
                $validated['name'] ?? null,
            );
            return response()->json(['user' => $user]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable) {
            return response()->json(['message' => 'Error inesperado.'], 500);
        }
    }

    public function removeUser(Organizacion $organizacion, User $user): JsonResponse
    {
        if (! $organizacion->users()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'El usuario no pertenece a esta organización.'], 422);
        }

        if ($organizacion->users()->count() <= 1) {
            return response()->json(['message' => 'La organización debe tener al menos un usuario.'], 422);
        }

        $organizacion->users()->detach($user->id);

        return response()->json(['success' => true]);
    }

    public function resetUserPassword(Organizacion $organizacion, User $user): JsonResponse
    {
        if (! $organizacion->users()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'El usuario no pertenece a esta organización.'], 422);
        }

        $this->service->sendPasswordReset($user, $organizacion->nombre);

        return response()->json(['success' => true]);
    }

}
