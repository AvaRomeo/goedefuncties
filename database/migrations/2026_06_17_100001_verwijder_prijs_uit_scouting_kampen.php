<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scouting_kampen', function (Blueprint $table) {
            $table->dropColumn('prijs');
        });
    }

    public function down(): void
    {
        Schema::table('scouting_kampen', function (Blueprint $table) {
            $table->decimal('prijs', 8, 2)->nullable()->after('beschrijving');
        });
    }
};
