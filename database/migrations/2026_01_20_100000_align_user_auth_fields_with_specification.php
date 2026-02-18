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
        Schema::table('users', function (Blueprint $table) {
            // Preimenuj 'pin' v 'pin_code' (skladno s specifikacijo)
            $table->renameColumn('pin', 'pin_code');
            
            // Preimenuj 'must_change_pin' v 'force_renew_pin' (skladno s specifikacijo)
            $table->renameColumn('must_change_pin', 'force_renew_pin');
            
            // Dodaj manjkajoči stolpec za vsiljeno menjavo gesla
            $table->boolean('force_renew_password')->default(false)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Vrni originalna imena
            $table->renameColumn('pin_code', 'pin');
            $table->renameColumn('force_renew_pin', 'must_change_pin');
            
            // Odstrani dodani stolpec
            $table->dropColumn('force_renew_password');
        });
    }
};
