<?php

namespace App\Models\Scouting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kamp extends Model
{
    protected $table = 'scouting_kampen';

    protected $fillable = [
        'naam', 'start_datum', 'eind_datum', 'locatie', 'beschrijving', 'prijs',
    ];

    protected $casts = [
        'start_datum' => 'date',
        'eind_datum'  => 'date',
        'prijs'       => 'decimal:2',
    ];

    public function deelnames(): HasMany
    {
        return $this->hasMany(Kampdeelname::class, 'kamp_id');
    }

    public function getDuurAttribute(): int
    {
        return $this->start_datum->diffInDays($this->eind_datum) + 1;
    }
}
