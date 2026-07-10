<?php

namespace EduLazaro\WireBug\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BugReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function getTable(): string
    {
        return config('wirebug.table', 'wirebug_reports');
    }

    /**
     * La cuenta que envió el reporte (null si fue un invitado). Usa el modelo
     * de usuario configurado en la app consumidora.
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.users.model', \App\Models\User::class),
            'user_id'
        );
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
