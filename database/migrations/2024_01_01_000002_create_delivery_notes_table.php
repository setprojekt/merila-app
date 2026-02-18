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
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique()->comment('Zaporedna številka dobavnice');
            $table->foreignId('sender_id')->constrained('users')->comment('Ustvaril uporabnik');
            $table->string('recipient')->comment('Prejemnik (zunanja kontrola)');
            $table->enum('status', ['ODPRTA', 'POSLANA', 'ZAKLJUCENA'])->default('ODPRTA');
            $table->date('delivery_date')->nullable()->comment('Datum odpreme');
            $table->date('expected_return_date')->nullable()->comment('Pričakovan datum vrnitve');
            $table->date('actual_return_date')->nullable()->comment('Dejanski datum vrnitve');
            $table->text('notes')->nullable()->comment('Opombe');
            $table->integer('total_instruments')->default(0)->comment('Število meril na dobavnici (cached)');
            $table->timestamps();
            
            $table->index('status');
            $table->index('sender_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
