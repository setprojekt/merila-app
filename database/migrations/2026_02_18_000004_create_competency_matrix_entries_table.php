<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_matrix_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_item_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['T', 'U', 'O'])->nullable()->comment('T=prenaša znanja, U=usposabljanje, O=usposobljen');
            $table->date('valid_until')->nullable()->comment('Velja do - za zakonsko predpisane');
            $table->timestamps();

            $table->unique(['user_id', 'competency_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_matrix_entries');
    }
};
