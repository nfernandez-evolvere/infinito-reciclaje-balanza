<?php

namespace App\Models\Concerns;

use App\Models\Organizacion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganizacion
{
    protected static function bootBelongsToOrganizacion(): void
    {
        static::addGlobalScope('organizacion', function ($query) {
            $org = app()->bound('organizacion') ? app('organizacion') : null;
            if ($org) {
                $query->where(
                    $query->getModel()->getTable().'.organizacion_id',
                    $org->id
                );
            }
        });

        static::creating(function ($model) {
            if (empty($model->organizacion_id)) {
                $org = app()->bound('organizacion') ? app('organizacion') : null;
                if ($org) {
                    $model->organizacion_id = $org->id;
                }
            }
        });
    }

    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }
}
