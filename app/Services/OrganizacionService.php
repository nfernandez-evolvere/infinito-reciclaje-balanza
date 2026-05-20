<?php

namespace App\Services;

use App\Models\Organizacion;
use App\Repositories\OrganizacionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class OrganizacionService
{
    public function __construct(
        protected OrganizacionRepository $organizacionRepository,
    ) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->organizacionRepository->paginate($perPage);
    }

    public function create(array $data): Organizacion
    {
        $data['slug'] = $this->generateSlug($data['nombre'], $data['slug'] ?? null);
        return $this->organizacionRepository->create($data);
    }

    public function update(Organizacion $organizacion, array $data): Organizacion
    {
        if (isset($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['nombre'], $data['slug'], $organizacion->id);
        }
        return $this->organizacionRepository->update($organizacion, $data);
    }

    public function delete(Organizacion $organizacion): void
    {
        $this->organizacionRepository->delete($organizacion);
    }

    public function toggleActivo(Organizacion $organizacion): Organizacion
    {
        return $this->organizacionRepository->toggleActivo($organizacion);
    }

    private function generateSlug(string $nombre, ?string $slugInput, ?int $excludeId = null): string
    {
        $base = $slugInput ? Str::slug($slugInput) : Str::slug($nombre);

        $slug = $base;
        $i = 1;
        while (
            Organizacion::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
