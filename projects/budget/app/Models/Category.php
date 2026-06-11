<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['naam', 'type', 'kleur', 'icoon', 'trefwoorden'];

    protected $casts = [
        'trefwoorden' => 'array',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
