<?php

namespace App\Models\Scouting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kampdeelname extends Model
{
    protected $table = 'scouting_kampdeelnames';

    protected $fillable = [
        'lid_id', 'kamp_id', 'bevestigd', 'bedrag', 'betaald', 'bijzonderheden',
    ];

    protected $casts = [
        'bevestigd' => 'boolean',
        'betaald'   => 'boolean',
        'bedrag'    => 'decimal:2',
    ];

    public function lid(): BelongsTo
    {
        return $this->belongsTo(Lid::class, 'lid_id');
    }

    public function kamp(): BelongsTo
    {
        return $this->belongsTo(Kamp::class, 'kamp_id');
    }
}
