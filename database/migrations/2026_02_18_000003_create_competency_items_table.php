<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competency_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('requires_validity')->default(false)->comment('Za zakonsko predpisane - ima stolpec velja do');
            $table->unsignedInteger('validity_years')->nullable()->comment('npr. 2 ali 3 leta');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_items');
    }
};
