<?php

use App\Models\Instrument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Instrument::whereNotNull('certificate_path')
            ->where('certificate_path', '!=', '')
            ->each(function (Instrument $instrument) {
                DB::table('instrument_certificates')->insert([
                    'instrument_id' => $instrument->id,
                    'certificate_path' => $instrument->certificate_path,
                    'check_date' => $instrument->last_check_date?->format('Y-m-d'),
                    'status' => 'veljavni',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Ne brišemo - tabela se ob rollback izbriše
    }
};
