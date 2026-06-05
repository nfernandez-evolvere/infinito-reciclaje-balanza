<?php

namespace App\Http\Controllers\Shared;

use App\Http\Concerns\WithToastFlash;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use WithToastFlash;

    public function __construct(
        protected ProfileService $profileService,
    ) {}

    public function show(): View
    {
        return view('modules.shared.perfil');
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $this->profileService->actualizarNombre(
                $request->user(),
                $request->validated()['name'],
            );

            return redirect()->route('perfil.show')->with('toast', [
                'message'     => 'Datos actualizados.',
                'description' => 'Tu nombre fue guardado correctamente.',
                'variant'     => 'success',
            ]);
        } catch (\Throwable) {
            return $this->toastError('perfil.show');
        }
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            $this->profileService->cambiarPassword(
                $request->user(),
                $request->validated()['password'],
            );

            return redirect()->route('perfil.show')->with('toast', [
                'message'     => 'Contraseña actualizada.',
                'description' => 'Tu contraseña fue cambiada correctamente.',
                'variant'     => 'success',
            ]);
        } catch (\Throwable) {
            return $this->toastError('perfil.show');
        }
    }
}
