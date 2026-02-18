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
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('internal_id')->unique()->comment('Notranja številka merila, npr. "TP 1647/01"');
            $table->string('name')->comment('Ime merila, npr. "Mikrometer not.20-25 mm"');
            $table->string('type')->nullable()->comment('Vrsta merila');
            $table->string('location')->nullable()->comment('Lokacija, npr. "Planina - kon. obročev"');
            $table->decimal('frequency_years', 4, 2)->default(2.00)->comment('Frekvenca pregleda v letih, npr. 1.5 ali 2.0');
            $table->date('last_check_date')->nullable()->comment('Datum zadnjega pregleda');
            $table->date('next_check_date')->nullable()->comment('Izračunan datum naslednjega pregleda');
            $table->enum('status', ['USTREZA', 'NE_USTREZA', 'IZLOCENO', 'V_KONTROLI'])->default('USTREZA');
            $table->string('certificate_path')->nullable()->comment('Pot do PDF certifikata');
            $table->boolean('archived')->default(false)->comment('Arhivirano (status IZLOCENO)');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->comment('Odgovoren uporabnik');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('next_check_date');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instruments');
    }
};
