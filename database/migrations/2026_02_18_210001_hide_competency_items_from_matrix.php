<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Skrij Funkcionalni preizkusi in celotno kategorijo ZAKONSKO PREDPISANA USPOSOBLJENOST iz matrike */
    public function up(): void
    {
        $hiddenNames = [
            'Funkcionalni preizkusi',
            'Varstvo pri delu in požarna varnost (na 2 leti)',
            'Evakuacija in prvo posredovanje (na 3 let)',
            'Izpit prve pomoči',
            'Izpit za vožnjo viličarja',
            'Varno delo z mostnim dvigalom (na 2 leti)',
            'Zdravniški pregled (na 3 leta)',
        ];

        DB::table('competency_items')
            ->whereIn('name', $hiddenNames)
            ->update(['is_hidden' => true]);
    }

    public function down(): void
    {
        DB::table('competency_items')->update(['is_hidden' => false]);
    }
};
