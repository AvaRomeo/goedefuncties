<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scouting_kampen', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->date('start_datum');
            $table->date('eind_datum');
            $table->string('locatie')->nullable();
            $table->text('beschrijving')->nullable();
            $table->decimal('prijs', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scouting_kampen');
    }
};
