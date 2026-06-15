<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scouting_leden', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->date('geboortedatum')->nullable();
            $table->string('speltak')->default('Esta-Welpen');
            $table->string('email_ouder')->nullable();
            $table->string('telefoon_ouder')->nullable();
            $table->boolean('actief')->default(true);
            $table->text('opmerkingen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scouting_leden');
    }
};
