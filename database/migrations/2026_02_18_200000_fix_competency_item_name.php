<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('competency_items')
            ->where('name', 'Priprava smole, vlaken in ...')
            ->update(['name' => 'Priprava smole, vlaken in komponent']);
    }

    public function down(): void
    {
        DB::table('competency_items')
            ->where('name', 'Priprava smole, vlaken in komponent')
            ->update(['name' => 'Priprava smole, vlaken in ...']);
    }
};
