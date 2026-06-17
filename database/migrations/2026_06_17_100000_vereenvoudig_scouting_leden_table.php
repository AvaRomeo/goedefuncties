<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scouting_leden', function (Blueprint $table) {
            $table->dropColumn([
                'geboortedatum',
                'speltak',
                'email_ouder',
                'telefoon_ouder',
                'actief',
                'opmerkingen',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('scouting_leden', function (Blueprint $table) {
            $table->date('geboortedatum')->nullable()->after('naam');
            $table->string('speltak')->default('Esta-Welpen')->after('geboortedatum');
            $table->string('email_ouder')->nullable()->after('speltak');
            $table->string('telefoon_ouder')->nullable()->after('email_ouder');
            $table->boolean('actief')->default(true)->after('telefoon_ouder');
            $table->text('opmerkingen')->nullable()->after('actief');
        });
    }
};
