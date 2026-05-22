<?php

namespace App\Http\Concerns;

use Illuminate\Http\RedirectResponse;

trait WithToastFlash
{
    protected function toastErrorData(): array
    {
        return [
            'message'     => 'Error inesperado.',
            'description' => 'Si el problema persiste, revisá los logs del sistema.',
            'variant'     => 'destructive',
        ];
    }

    protected function toastError(string $route): RedirectResponse
    {
        return redirect()->route($route)->with('toast', $this->toastErrorData());
    }
}
