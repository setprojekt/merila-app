<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('group', 'global')
            ->where('name', 'mail_password')
            ->delete();
    }

    public function down(): void
    {
        // Ob obnovitvi ne dodajamo nazaj – geslo je odstranjeno iz aplikacije
    }
};
