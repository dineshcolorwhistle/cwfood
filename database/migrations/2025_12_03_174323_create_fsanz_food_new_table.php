<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fsanz_new', function (Blueprint $table) {
            $table->id();

            $table->string('fsanz_key')->index();
            $table->string('name')->nullable();

            $table->decimal('energy_kj', 10, 2)->nullable();
            $table->decimal('protein_g', 10, 2)->nullable();
            $table->decimal('fat_total_g', 10, 2)->nullable();
            $table->decimal('carbohydrate_g', 10, 2)->nullable();
            $table->decimal('sodium_mg', 10, 2)->nullable();

            $table->longText('description')->nullable();
            $table->string('food_group')->nullable();
            $table->string('food_subgroup')->nullable();

            $table->string('food_category_code')->nullable();
            $table->string('food_category_name')->nullable();

            $table->string('data_source')->nullable();
            $table->string('measurement_basis')->nullable();

            $table->decimal('fat_saturated_g', 10, 2)->nullable();
            $table->decimal('sugars_g', 10, 2)->nullable();

            $table->longText('estimated_dietary_status')->nullable();
            $table->decimal('dietary_confidence_score', 5, 2)->nullable();

            $table->longText('estimated_allergens')->nullable();
            $table->decimal('allergen_confidence_score', 5, 2)->nullable();

            $table->longText('estimated_hazards')->nullable();
            $table->decimal('hazard_confidence_score', 5, 2)->nullable();

            $table->longText('estimated_processing_info')->nullable();
            $table->decimal('processing_confidence_score', 5, 2)->nullable();

            $table->longText('estimated_regulatory_info')->nullable();
            $table->decimal('regulatory_confidence_score', 5, 2)->nullable();

            $table->longText('estimated_ingredients')->nullable();
            $table->longText('estimated_typical_uses')->nullable();

            $table->decimal('estimated_australia_percent', 6, 2)->nullable();

            $table->string('estimated_origin')->nullable();
            $table->decimal('origin_confidence_score', 5, 2)->nullable();

            $table->decimal('overall_confidence_score', 5, 2)->nullable();

            $table->string('ai_estimation_status')->nullable();
            $table->dateTime('last_ai_analysis')->nullable();
            $table->longText('ai_analysis_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fsanz_new');
    }
};
