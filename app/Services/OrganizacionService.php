<?php

namespace App\Services;

use App\Models\Organizacion;
use App\Models\User;
use App\Notifications\AdminInvitacionNotification;
use App\Notifications\AdminNuevaOrganizacionNotification;
use App\Repositories\OrganizacionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Password;
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
        $adminEmail = $data['admin_email'];
        $adminName  = $data['admin_name'] ?? null;

        $data['slug'] = $this->generateSlug($data['nombre']);

        $org = $this->organizacionRepository->create(
            array_diff_key($data, array_flip(['admin_email', 'admin_name']))
        );

        $this->addUserToOrg($org, $adminEmail, $adminName);

        return $org;
    }

    public function addUserToOrg(Organizacion $org, string $email, ?string $name = null): array
    {
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if ($existingUser->isSuperAdmin()) {
                throw new \RuntimeException('Los super admins no pueden pertenecer a una organización.');
            }

            if ($org->users()->where('user_id', $existingUser->id)->exists()) {
                throw new \RuntimeException('El usuario ya pertenece a esta organización.');
            }

            $org->users()->attach($existingUser->id);
            $existingUser->notify(new AdminNuevaOrganizacionNotification($org));

            return $existingUser->only(['id', 'name', 'email', 'role']);
        }

        $newUser = User::create([
            'name'     => $name ?? $email,
            'email'    => $email,
            'password' => Str::random(32),
            'role'     => 'admin',
        ]);

        $org->users()->attach($newUser->id);
        $this->sendPasswordReset($newUser, $org->nombre);

        return $newUser->only(['id', 'name', 'email', 'role']);
    }

    public function sendPasswordReset(User $user, string $orgNombre): void
    {
        $token = Password::createToken($user);
        $user->notify(new AdminInvitacionNotification($token, $orgNombre));
    }

    public function update(Organizacion $organizacion, array $data): Organizacion
    {
        $data['slug'] = $this->generateSlug($data['nombre'], $organizacion->id);
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

    private function generateSlug(string $nombre, ?int $excludeId = null): string
    {
        $base = Str::slug($nombre);

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
