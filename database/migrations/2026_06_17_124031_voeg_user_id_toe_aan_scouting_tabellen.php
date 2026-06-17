<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['scouting_leden', 'scouting_kampen', 'scouting_leiding'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) use ($tabel) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('user_id', "{$tabel}_user_id_idx");
            });
        }

        // Wijs bestaande rijen toe aan gebruiker met id 1, indien die bestaat.
        if (DB::table('users')->where('id', 1)->exists()) {
            foreach (['scouting_leden', 'scouting_kampen', 'scouting_leiding'] as $tabel) {
                DB::table($tabel)->whereNull('user_id')->update(['user_id' => 1]);
            }
        }
    }

    public function down(): void
    {
        foreach (['scouting_leden', 'scouting_kampen', 'scouting_leiding'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) use ($tabel) {
                $table->dropForeign(['user_id']);
                $table->dropIndex("{$tabel}_user_id_idx");
                $table->dropColumn('user_id');
            });
        }
    }
};
