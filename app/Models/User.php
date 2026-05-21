<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'onboarding_visto', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_visto' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function organizaciones(): BelongsToMany
    {
        return $this->belongsToMany(Organizacion::class, 'organizacion_user');
    }

    public function resolveRouteBinding($value, $field = null): ?static
    {
        $query = $this->where($field ?? $this->getRouteKeyName(), $value);

        $org = app('organizacion');
        if ($org) {
            $query->whereHas('organizaciones', fn ($q) => $q->where('organizaciones.id', $org->id));
        }

        return $query->first();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperador(): bool
    {
        return $this->role === 'operador';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}
