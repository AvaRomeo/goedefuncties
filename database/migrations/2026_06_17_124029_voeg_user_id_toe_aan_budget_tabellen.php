<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('user_id', 'accounts_user_id_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('user_id', 'categories_user_id_idx');
        });

        // Wijs bestaande rijen toe aan gebruiker met id 1, indien die bestaat.
        if (DB::table('users')->where('id', 1)->exists()) {
            DB::table('accounts')->whereNull('user_id')->update(['user_id' => 1]);
            DB::table('categories')->whereNull('user_id')->update(['user_id' => 1]);
        }
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('accounts_user_id_idx');
            $table->dropColumn('user_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('categories_user_id_idx');
            $table->dropColumn('user_id');
        });
    }
};
