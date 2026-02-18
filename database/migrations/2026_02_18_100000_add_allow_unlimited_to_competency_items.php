<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competency_items', function (Blueprint $table) {
            $table->boolean('allow_unlimited')->default(false)->after('validity_years');
        });

        DB::table('competency_items')
            ->where('name', 'Izpit za vožnjo viličarja')
            ->update(['allow_unlimited' => true]);
    }

    public function down(): void
    {
        Schema::table('competency_items', function (Blueprint $table) {
            $table->dropColumn('allow_unlimited');
        });
    }
};
