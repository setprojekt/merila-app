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
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained()->cascadeOnDelete();
            $table->enum('returned_status', ['USTREZA', 'NE_USTREZA'])->nullable()->comment('Status po vrnitvi');
            $table->date('returned_date')->nullable()->comment('Datum vrnitve');
            $table->date('check_date')->nullable()->comment('Datum pregleda');
            $table->text('notes')->nullable()->comment('Opombe');
            $table->boolean('returned')->default(false)->comment('Označeno kot vrnjeno');
            $table->timestamps();
            
            $table->index('delivery_note_id');
            $table->index('instrument_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
