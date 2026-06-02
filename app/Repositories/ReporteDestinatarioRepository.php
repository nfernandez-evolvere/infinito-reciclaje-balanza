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

        $emails = array_values(array_map('strtolower', $emails));
        $table  = (new ReporteDestinatario)->getTable();
        $now    = now()->format('Y-m-d\TH:i:s');

        // UPDATE en tabla única — uso_count no es ambiguo (sin MERGE)
        DB::table($table)
            ->where('organizacion_id', $orgId)
            ->whereIn('email', $emails)
            ->update(['uso_count' => DB::raw('uso_count + 1'), 'updated_at' => $now]);

        $existing = DB::table($table)
            ->where('organizacion_id', $orgId)
            ->whereIn('email', $emails)
            ->pluck('email')
            ->map(fn ($e) => strtolower($e))
            ->toArray();

        $toInsert = array_values(array_diff($emails, $existing));

        foreach (array_chunk($toInsert, 100) as $chunk) {
            DB::table($table)->insert(array_map(fn ($email) => [
                'organizacion_id' => $orgId,
                'email'           => $email,
                'uso_count'       => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], $chunk));
        }
    }
}
