<?php

namespace App\Models\Scouting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lid extends Model
{
    protected $table = 'scouting_leden';

    protected $fillable = ['naam'];

    public function deelnames(): HasMany
    {
        return $this->hasMany(Kampdeelname::class, 'lid_id');
    }
}
