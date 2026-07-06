<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eén enkele rij houdt de rijtijd-status bij, zodat een refresh van de
        // browser (of meerdere tabbladen) de opgebouwde rijtijd niet resetten.
        Schema::create('telemetry_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('stilstaand'); // 'rijdend' of 'stilstaand'
            $table->timestamp('status_sinds')->nullable();   // wanneer de huidige status begon
            $table->unsignedInteger('rijtijd_seconden')->default(0); // opgebouwde rijtijd sinds laatste pauze
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetry_sessions');
    }
};
