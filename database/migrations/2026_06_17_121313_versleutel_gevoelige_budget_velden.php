<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Versleutel bestaande plaintext-waarden vóórdat de kolommen TEXT worden.

        DB::table('accounts')->orderBy('id')->each(function ($rij) {
            DB::table('accounts')->where('id', $rij->id)->update([
                'beginsaldo' => Crypt::encryptString((string) $rij->beginsaldo),
            ]);
        });

        DB::table('transactions')->orderBy('id')->each(function ($rij) {
            DB::table('transactions')->where('id', $rij->id)->update([
                'bedrag'       => Crypt::encryptString((string) $rij->bedrag),
                'omschrijving' => $rij->omschrijving !== null
                    ? Crypt::encryptString($rij->omschrijving)
                    : null,
            ]);
        });

        DB::table('transfers')->orderBy('id')->each(function ($rij) {
            DB::table('transfers')->where('id', $rij->id)->update([
                'bedrag'       => Crypt::encryptString((string) $rij->bedrag),
                'omschrijving' => $rij->omschrijving !== null
                    ? Crypt::encryptString($rij->omschrijving)
                    : null,
            ]);
        });

        // Wijzig kolomtypes naar TEXT zodat de versleutelde strings passen.
        Schema::table('accounts', function (Blueprint $table) {
            $table->text('beginsaldo')->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('bedrag')->change();
            $table->text('omschrijving')->nullable()->change();
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->text('bedrag')->change();
            $table->text('omschrijving')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Ontsleutel terug naar plaintext en herstel kolomtypes.

        DB::table('accounts')->orderBy('id')->each(function ($rij) {
            try {
                $waarde = Crypt::decryptString($rij->beginsaldo);
            } catch (\Exception) {
                $waarde = $rij->beginsaldo;
            }
            DB::table('accounts')->where('id', $rij->id)->update(['beginsaldo' => $waarde]);
        });

        DB::table('transactions')->orderBy('id')->each(function ($rij) {
            try { $bedrag = Crypt::decryptString($rij->bedrag); } catch (\Exception) { $bedrag = $rij->bedrag; }
            $omschrijving = null;
            if ($rij->omschrijving !== null) {
                try { $omschrijving = Crypt::decryptString($rij->omschrijving); } catch (\Exception) { $omschrijving = $rij->omschrijving; }
            }
            DB::table('transactions')->where('id', $rij->id)->update(compact('bedrag', 'omschrijving'));
        });

        DB::table('transfers')->orderBy('id')->each(function ($rij) {
            try { $bedrag = Crypt::decryptString($rij->bedrag); } catch (\Exception) { $bedrag = $rij->bedrag; }
            $omschrijving = null;
            if ($rij->omschrijving !== null) {
                try { $omschrijving = Crypt::decryptString($rij->omschrijving); } catch (\Exception) { $omschrijving = $rij->omschrijving; }
            }
            DB::table('transfers')->where('id', $rij->id)->update(compact('bedrag', 'omschrijving'));
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('beginsaldo', 10, 2)->default(0)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('bedrag', 10, 2)->change();
            $table->string('omschrijving', 255)->nullable()->change();
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->decimal('bedrag', 10, 2)->change();
            $table->string('omschrijving', 255)->nullable()->change();
        });
    }
};
