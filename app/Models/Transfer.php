<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = ['van_account_id', 'naar_account_id', 'bedrag', 'datum', 'omschrijving'];

    protected $casts = [
        'datum'        => 'date',
        'bedrag'       => 'encrypted',
        'omschrijving' => 'encrypted',
    ];

    public function vanAccount()
    {
        return $this->belongsTo(Account::class, 'van_account_id');
    }

    public function naarAccount()
    {
        return $this->belongsTo(Account::class, 'naar_account_id');
    }
}
