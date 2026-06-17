<?php

namespace App\Models;

use App\Traits\HoortBijGebruiker;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HoortBijGebruiker;

    protected $fillable = ['user_id', 'naam', 'type', 'kleur', 'icoon', 'trefwoorden'];

    protected $casts = [
        'trefwoorden' => 'array',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
