<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hoofdpagina transacties: ORDER BY datum DESC, id DESC
            $table->index('datum', 'tx_datum');

            // Rekening-detailpagina: WHERE account_id = ? ORDER BY datum DESC, id DESC
            $table->index(['account_id', 'datum'], 'tx_account_datum');

            // Saldoberekening: WHERE account_id = ? AND type = ?
            $table->index(['account_id', 'type'], 'tx_account_type');

            // Import-duplicaatdetectie: WHERE account_id = ? AND datum = ? AND type = ?
            $table->index(['account_id', 'datum', 'type'], 'tx_account_datum_type');
        });

        Schema::table('transfers', function (Blueprint $table) {
            // Saldoberekening transfers: van_account_id en naar_account_id zijn al FK-geïndexeerd,
            // maar datum helpt bij gesorteerde weergave per rekening.
            $table->index('datum', 'tr_datum');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('tx_datum');
            $table->dropIndex('tx_account_datum');
            $table->dropIndex('tx_account_type');
            $table->dropIndex('tx_account_datum_type');
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->dropIndex('tr_datum');
        });
    }
};
