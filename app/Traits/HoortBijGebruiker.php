<?php

namespace App\Traits;

trait HoortBijGebruiker
{
    protected static function bootHoortBijGebruiker(): void
    {
        // Filter alle queries automatisch op de ingelogde gebruiker.
        static::addGlobalScope('gebruiker', function ($query) {
            if (auth()->check()) {
                $query->where((new static)->getTable() . '.user_id', auth()->id());
            }
        });

        // Zet user_id automatisch bij aanmaken.
        static::creating(function ($model) {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }
}
