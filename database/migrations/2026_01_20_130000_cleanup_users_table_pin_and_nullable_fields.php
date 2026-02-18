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
     * Cleanup migracija za users tabelo:
     * 1. Standardiziraj stolpec PIN na 'pin_code'
     * 2. Preimenuj 'pin' v 'pin_code', če obstaja
     * 3. Reši konflikte, če obstajata oba stolpca
     * 4. Nastavi email in password kot NULLABLE
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $hasPinColumn = Schema::hasColumn('users', 'pin');
            $hasPinCodeColumn = Schema::hasColumn('users', 'pin_code');
            
            // SCENARIJ 1: Obstaja samo 'pin' - preimenuj v 'pin_code'
            if ($hasPinColumn && !$hasPinCodeColumn) {
                // Najprej odstranimo unique constraint, če obstaja
                try {
                    DB::statement('ALTER TABLE users DROP INDEX users_pin_unique');
                } catch (\Exception $e) {
                    // Index ne obstaja, ni problema
                }
                
                // Preimenuj stolpec in ga naredi primeren za hashiran PIN (string dovolj velik za bcrypt)
                $table->renameColumn('pin', 'pin_code');
            }
            
            // SCENARIJ 2: Obstajata oba stolpca - merge podatkov v pin_code
            if ($hasPinColumn && $hasPinCodeColumn) {
                // Prenesi podatke iz 'pin' v 'pin_code', kjer pin_code je NULL
                DB::statement('UPDATE users SET pin_code = pin WHERE pin_code IS NULL AND pin IS NOT NULL');
                
                // Odstrani unique constraint na 'pin', če obstaja
                try {
                    DB::statement('ALTER TABLE users DROP INDEX users_pin_unique');
                } catch (\Exception $e) {
                    // Index ne obstaja, ni problema
                }
                
                // Odstrani stari 'pin' stolpec
                $table->dropColumn('pin');
            }
            
            // SCENARIJ 3: Ne obstaja niti 'pin' niti 'pin_code' - ustvari 'pin_code'
            if (!$hasPinColumn && !$hasPinCodeColumn) {
                $table->string('pin_code')->nullable()->after('password');
            }
        });
        
        // Po čiščenju PIN stolpcev - zagotovi, da je pin_code pravi tip (string za bcrypt hash)
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pin_code')) {
                // Če je bil pin_code dodan iz starega 'pin' (ki je bil char(4)), 
                // moramo zagotoviti, da je dovolj velik za bcrypt hash
                $table->string('pin_code', 255)->nullable()->change();
            }
        });
        
        // Končno - spremeni email in password v NULLABLE
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            
            // Odstrani unique constraint na email, če obstaja, saj lahko imamo več uporabnikov brez emaila (NULL)
            // Laravel bo avtomatsko dovolil več NULL vrednosti
        });
        
        // Odstrani can_login_with_pin in must_change_pin stolpca, če obstajata
        // (ker model User uporablja force_renew_pin in can_login_with_pin)
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_pin')) {
                $table->dropColumn('must_change_pin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Vrni email in password na NOT NULL
            // OPOZORILO: To bo failalo, če obstajajo uporabniki brez email/password
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            
            // Vrni pin_code nazaj na 'pin' (char 4)
            if (Schema::hasColumn('users', 'pin_code')) {
                $table->renameColumn('pin_code', 'pin');
                $table->string('pin', 4)->nullable()->unique()->change();
            }
        });
    }
};
