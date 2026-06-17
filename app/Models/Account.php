<?php

namespace App\Models;

use App\Traits\HoortBijGebruiker;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HoortBijGebruiker;

    protected $fillable = ['user_id', 'naam', 'type', 'bank', 'kleur', 'icoon', 'beginsaldo'];

    protected $casts = [
        'beginsaldo' => 'encrypted',
    ];

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
        $inkomsten = $this->transactions()->where('type', 'inkomst')->get()->sum(fn($t) => (float) $t->bedrag);
        $uitgaven  = $this->transactions()->where('type', 'uitgave')->get()->sum(fn($t) => (float) $t->bedrag);
        $inkomend  = $this->transfersNaar()->get()->sum(fn($t) => (float) $t->bedrag);
        $uitgaand  = $this->transfersVan()->get()->sum(fn($t) => (float) $t->bedrag);

        return (float) $this->beginsaldo + $inkomsten - $uitgaven + $inkomend - $uitgaand;
    }
}
