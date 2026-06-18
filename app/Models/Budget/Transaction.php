<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['account_id', 'category_id', 'type', 'bedrag', 'datum', 'omschrijving'];

    protected $casts = [
        'datum'        => 'date',
        'bedrag'       => 'encrypted',
        'omschrijving' => 'encrypted',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
