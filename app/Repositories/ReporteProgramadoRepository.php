<?php

namespace App\Repositories;

use App\Models\ReporteProgramado;
use Illuminate\Database\Eloquent\Collection;

class ReporteProgramadoRepository
{
    public function allOrdered(): Collection
    {
        return ReporteProgramado::orderBy('nombre')->get();
    }

    public function create(array $data): ReporteProgramado
    {
        return ReporteProgramado::create($data);
    }

    public function update(ReporteProgramado $programado, array $data): ReporteProgramado
    {
        $programado->update($data);
        return $programado;
    }

    public function delete(ReporteProgramado $programado): void
    {
        $programado->delete();
    }
}
