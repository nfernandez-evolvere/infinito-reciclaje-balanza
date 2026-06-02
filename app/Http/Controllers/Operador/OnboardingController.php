<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Repositories\UsuarioRepository;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    public function __construct(protected UsuarioRepository $usuarioRepository) {}

    public function store(): JsonResponse
    {
        $this->usuarioRepository->marcarOnboardingVisto(auth()->user());

        return response()->json(['ok' => true]);
    }
}
