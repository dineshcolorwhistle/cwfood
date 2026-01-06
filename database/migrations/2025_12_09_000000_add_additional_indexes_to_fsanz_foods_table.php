<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add additional indexes to improve server-side DataTables query performance
     */
    public function up(): void
    {
        Schema::table('fsanz_foods', function (Blueprint $table) {
            // Add indexes for columns used in custom filters
            $table->index('measurement_basis');
            $table->index('primary_origin_country');
            
            // Composite index for common search + sort combinations
            $table->index(['name', 'food_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fsanz_foods', function (Blueprint $table) {
            $table->dropIndex(['measurement_basis']);
            $table->dropIndex(['primary_origin_country']);
            $table->dropIndex(['name', 'food_group']);
        });
    }
};

