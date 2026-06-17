<?php

namespace App\Models\Scouting;

use App\Traits\HoortBijGebruiker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lid extends Model
{
    use HoortBijGebruiker;

    protected $table = 'scouting_leden';

    protected $fillable = ['user_id', 'naam'];

    public function deelnames(): HasMany
    {
        return $this->hasMany(Kampdeelname::class, 'lid_id');
    }
}
