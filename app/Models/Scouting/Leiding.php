<?php

namespace App\Models\Scouting;

use App\Traits\HoortBijGebruiker;
use Illuminate\Database\Eloquent\Model;

class Leiding extends Model
{
    use HoortBijGebruiker;

    protected $table = 'scouting_leiding';

    protected $fillable = ['user_id', 'naam'];
}
