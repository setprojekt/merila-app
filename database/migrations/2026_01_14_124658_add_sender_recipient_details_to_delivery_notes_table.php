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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('sender_name')->nullable()->after('recipient');
            $table->text('sender_address')->nullable()->after('sender_name');
            $table->text('recipient_address')->nullable()->after('recipient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn(['sender_name', 'sender_address', 'recipient_address']);
        });
    }
};
