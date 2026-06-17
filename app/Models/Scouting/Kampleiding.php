<?php

namespace App\Models\Scouting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kampleiding extends Model
{
    protected $table = 'scouting_kampleiding';

    protected $fillable = ['kamp_id', 'leiding_id'];

    public function leiding(): BelongsTo
    {
        return $this->belongsTo(Leiding::class, 'leiding_id');
    }
}
