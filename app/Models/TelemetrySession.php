<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetrySession extends Model
{
    protected $fillable = [
        'status',
        'status_sinds',
        'rijtijd_seconden',
    ];

    protected $casts = [
        'status_sinds' => 'datetime',
    ];

    /**
     * Er is maar één actieve rij (dit is een lokale, single-player dashboard-app).
     * Haalt hem op of maakt hem aan.
     */
    public static function huidige(): self
    {
        // Geen where-voorwaarde: pakt gewoon de enige/eerste rij als die
        // bestaat. ('id' zit niet in $fillable, dus firstOrCreate(['id' => 1])
        // zou 'id' stilletjes negeren bij het aanmaken en zo bij elke poll
        // een nieuwe rij aanmaken i.p.v. de bestaande te hergebruiken.)
        return static::query()->firstOrCreate([]);
    }
}
