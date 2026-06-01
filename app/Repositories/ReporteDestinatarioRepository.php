<?php

namespace App\Repositories;

use App\Models\ReporteDestinatario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReporteDestinatarioRepository
{
    public function search(?string $q, int $limit = 8): Collection
    {
        return ReporteDestinatario::when(
            $q,
            fn ($query) => $query->where('email', 'like', "%{$q}%")
                                 ->orWhere('nombre', 'like', "%{$q}%")
        )
        ->orderByDesc('uso_count')
        ->limit($limit)
        ->get(['email', 'nombre']);
    }

    public function upsert(int $orgId, array $emails): void
    {
        if (empty($emails)) {
            return;
        }

        $rows = array_map(fn ($email) => [
            'organizacion_id' => $orgId,
            'email'           => strtolower($email),
            'uso_count'       => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $emails);

        ReporteDestinatario::upsert(
            $rows,
            ['organizacion_id', 'email'],
            ['uso_count' => DB::raw('uso_count + 1'), 'updated_at' => now()]
        );
    }
}
