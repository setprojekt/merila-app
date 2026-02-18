<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_path');
            $table->date('check_date')->nullable()->comment('Datum pregleda/kontrole');
            $table->string('status', 20)->default('veljavni')->comment('veljavni = aktualen, arhiviran = pretekli');
            $table->timestamps();
        });

        Schema::table('instrument_certificates', function (Blueprint $table) {
            $table->index(['instrument_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_certificates');
    }
};
