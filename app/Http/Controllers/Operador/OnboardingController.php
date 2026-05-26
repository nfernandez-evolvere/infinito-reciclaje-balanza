<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        auth()->user()->update(['onboarding_visto' => true]);

        return response()->json(['ok' => true]);
    }
}
