<?php

namespace App\Models\Scouting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lid extends Model
{
    protected $table = 'scouting_leden';

    protected $fillable = [
        'naam', 'geboortedatum', 'speltak',
        'email_ouder', 'telefoon_ouder', 'actief', 'opmerkingen',
    ];

    protected $casts = [
        'geboortedatum' => 'date',
        'actief'        => 'boolean',
    ];

    public function deelnames(): HasMany
    {
        return $this->hasMany(Kampdeelname::class, 'lid_id');
    }

    public function getLeeftijdAttribute(): ?int
    {
        return $this->geboortedatum?->age;
    }
}
