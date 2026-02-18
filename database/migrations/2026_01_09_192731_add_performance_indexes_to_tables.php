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
        // Instruments table indexes za searchable/sortable stolpce
        Schema::table('instruments', function (Blueprint $table) {
            try {
                // Indexi za individualne stolpce (searchable/sortable)
                $table->index('name', 'instruments_name_index');
            } catch (\Exception $e) {
                // Index že obstaja
            }
            
            try {
                $table->index('type', 'instruments_type_index');
            } catch (\Exception $e) {}
            
            try {
                $table->index('location', 'instruments_location_index');
            } catch (\Exception $e) {}
            
            try {
                $table->index('department', 'instruments_department_index');
            } catch (\Exception $e) {}
            
            try {
                $table->index('archived', 'instruments_archived_index');
            } catch (\Exception $e) {}
            
            // Kombinirani index za pogoste kombinacije filtrov
            try {
                $table->index(['archived', 'status'], 'instruments_archived_status_index');
            } catch (\Exception $e) {}
        });

        // Delivery notes table indexes za searchable/sortable stolpce
        Schema::table('delivery_notes', function (Blueprint $table) {
            try {
                $table->index('recipient', 'delivery_notes_recipient_index');
            } catch (\Exception $e) {}
            
            try {
                $table->index('delivery_date', 'delivery_notes_delivery_date_index');
            } catch (\Exception $e) {}
            
            try {
                $table->index('created_at', 'delivery_notes_created_at_index');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            try {
                $table->dropIndex('instruments_name_index');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('instruments_type_index');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('instruments_location_index');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('instruments_department_index');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('instruments_archived_index');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('instruments_archived_status_index');
            } catch (\Exception $e) {}
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            try {
                $table->dropIndex('delivery_notes_recipient_index');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('delivery_notes_delivery_date_index');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('delivery_notes_created_at_index');
            } catch (\Exception $e) {}
        });
    }
};
