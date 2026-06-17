<?php

namespace App\Models\Scouting;

use Illuminate\Database\Eloquent\Model;

class Leiding extends Model
{
    protected $table = 'scouting_leiding';

    protected $fillable = ['naam'];
}
