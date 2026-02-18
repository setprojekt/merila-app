<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE delivery_note_items MODIFY COLUMN returned_status ENUM('USTREZA', 'NE_USTREZA', 'IZLOCENO') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE delivery_note_items MODIFY COLUMN returned_status ENUM('USTREZA', 'NE_USTREZA') NULL");
    }
};
