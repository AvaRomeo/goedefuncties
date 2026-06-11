<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['naam', 'type', 'kleur', 'beginsaldo'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transfersVan()
    {
        return $this->hasMany(Transfer::class, 'van_account_id');
    }

    public function transfersNaar()
    {
        return $this->hasMany(Transfer::class, 'naar_account_id');
    }

    public function getSaldoAttribute(): float
    {
        $inkomsten = $this->transactions()->where('type', 'inkomst')->sum('bedrag');
        $uitgaven = $this->transactions()->where('type', 'uitgave')->sum('bedrag');
        $inkomend = $this->transfersNaar()->sum('bedrag');
        $uitgaand = $this->transfersVan()->sum('bedrag');

        return $this->beginsaldo + $inkomsten - $uitgaven + $inkomend - $uitgaand;
    }
}
