<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\WithToastFlash;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordUsuarioRequest;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    use WithToastFlash;
    public function __construct(
        protected UsuarioService $service,
    ) {}

    public function index(Request $request): View
    {
        $filters  = $request->only(['buscar', 'role', 'activo']);
        $usuarios = $this->service->listar($filters);

        return view('modules.admin.usuarios.index', compact('usuarios', 'filters'));
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        try {
            $data           = $request->validated();
            $plainPassword  = $data['password'];
            $data['activo'] = true;
            $usuario        = $this->service->crear($data);

            Mail::to($usuario)->send(new WelcomeMail($usuario, $plainPassword, app('organizacion')));

            return redirect()->route('admin.usuarios.index')
                ->with('toast', [
                    'message'     => 'Usuario creado.',
                    'description' => "\"{$usuario->name}\" recibió un correo con sus datos de acceso.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('admin.usuarios.index');
        }
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $validated = $request->validated();

        if ($usuario->id === $request->user()->id && $validated['role'] !== 'admin') {
            return redirect()->route('admin.usuarios.index')
                ->with('toast', [
                    'message'     => 'Acción no permitida.',
                    'description' => 'No podés cambiar tu propio rol.',
                    'variant'     => 'destructive',
                ]);
        }

        try {
            $this->service->actualizar($usuario, $validated);

            return redirect()->route('admin.usuarios.index')
                ->with('toast', [
                    'message'     => 'Cambios guardados.',
                    'description' => "\"{$validated['name']}\" fue actualizado.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('admin.usuarios.index');
        }
    }

    public function toggle(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id) {
            return redirect()->route('admin.usuarios.index')
                ->with('toast', [
                    'message'     => 'Acción no permitida.',
                    'description' => 'No podés desactivar tu propia cuenta.',
                    'variant'     => 'destructive',
                ]);
        }

        try {
            if ($usuario->activo) {
                $this->service->desactivar($usuario);
                $toast = [
                    'message'     => 'Usuario desactivado.',
                    'description' => "\"{$usuario->name}\" no podrá iniciar sesión hasta que sea reactivado.",
                    'variant'     => 'success',
                ];
            } else {
                $this->service->activar($usuario);
                $toast = [
                    'message'     => 'Usuario activado.',
                    'description' => "\"{$usuario->name}\" ya puede volver a iniciar sesión.",
                    'variant'     => 'success',
                ];
            }

            return redirect()->route('admin.usuarios.index')->with('toast', $toast);
        } catch (\Throwable) {
            return $this->toastError('admin.usuarios.index');
        }
    }

    public function resetPassword(ResetPasswordUsuarioRequest $request, User $usuario): RedirectResponse
    {
        try {
            $this->service->resetearPassword($usuario, $request->validated()['password']);

            return redirect()->route('admin.usuarios.index')
                ->with('toast', [
                    'message'     => 'Contraseña restablecida.',
                    'description' => "La nueva contraseña de \"{$usuario->name}\" quedó activa.",
                    'variant'     => 'success',
                ]);
        } catch (\Throwable) {
            return $this->toastError('admin.usuarios.index');
        }
    }

}
