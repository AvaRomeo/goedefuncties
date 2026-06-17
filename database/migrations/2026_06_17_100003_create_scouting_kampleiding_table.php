<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scouting_kampleiding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamp_id')->constrained('scouting_kampen')->cascadeOnDelete();
            $table->foreignId('leiding_id')->constrained('scouting_leiding')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['kamp_id', 'leiding_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scouting_kampleiding');
    }
};
