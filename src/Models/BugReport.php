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
     * Quién envió el reporte (null si fue un invitado). Polimórfico: cada
     * app decide el modelo (User, Client...). Respeta el morph map.
     */
    public function reporter()
    {
        return $this->morphTo('reporter');
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
