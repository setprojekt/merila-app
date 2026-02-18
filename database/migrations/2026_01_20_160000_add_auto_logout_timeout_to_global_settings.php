<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dodaj nastavitev za auto-logout timeout v settings tabelo
        DB::table('settings')->insert([
            'group' => 'global',
            'name' => 'auto_logout_timeout',
            'locked' => false,
            'payload' => json_encode(1800), // Default: 30 minut (1800 sekund)
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'global')
            ->where('name', 'auto_logout_timeout')
            ->delete();
    }
};
