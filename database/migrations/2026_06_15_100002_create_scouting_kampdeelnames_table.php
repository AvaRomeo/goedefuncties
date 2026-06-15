<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scouting_kampdeelnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lid_id')->constrained('scouting_leden')->cascadeOnDelete();
            $table->foreignId('kamp_id')->constrained('scouting_kampen')->cascadeOnDelete();
            $table->boolean('bevestigd')->default(false);
            $table->decimal('bedrag', 8, 2)->nullable();
            $table->boolean('betaald')->default(false);
            $table->text('bijzonderheden')->nullable();
            $table->timestamps();
            $table->unique(['lid_id', 'kamp_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scouting_kampdeelnames');
    }
};
