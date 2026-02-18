<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Zagotovi, da obstaja pin_code stolpec v users tabeli
     */
    public function up(): void
    {
        // Preveri, ali obstaja stolpec pin_code
        if (!Schema::hasColumn('users', 'pin_code')) {
            Schema::table('users', function (Blueprint $table) {
                // Če obstaja 'pin', ga preimenuj v 'pin_code'
                if (Schema::hasColumn('users', 'pin')) {
                    // Odstrani unique constraint, če obstaja
                    try {
                        DB::statement('ALTER TABLE users DROP INDEX users_pin_unique');
                    } catch (\Exception $e) {
                        // Index ne obstaja
                    }
                    
                    // Preimenuj in spremeni tip
                    $table->renameColumn('pin', 'pin_code');
                } else {
                    // Če ne obstaja niti pin niti pin_code, ustvari nov stolpec
                    $table->string('pin_code', 255)->nullable()->after('password');
                }
            });
            
            // Zagotovi, da je pin_code dovolj velik za bcrypt hash
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'pin_code')) {
                    $table->string('pin_code', 255)->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'pin_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pin_code');
            });
        }
    }
};
