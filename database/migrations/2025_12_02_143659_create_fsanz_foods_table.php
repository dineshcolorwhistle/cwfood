<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FSANZ Foods Table - Australian Food Standards Database
     * Contains both official FSANZ nutritional data and AI-estimated supplementary data
     */
    public function up(): void
    {
        Schema::create('fsanz_foods', function (Blueprint $table) {
            // ============================================
            // CATEGORY 1: Primary Identifiers
            // ============================================
            $table->id();
            $table->string('fsanz_key')->unique()->comment('Unique FSANZ food identifier');
            $table->text('name')->comment('Food item name');
            
            // ============================================
            // CATEGORY 2: Official FSANZ Nutritional Data (per 100g)
            // ============================================
            $table->decimal('energy_kj', 10, 2)->nullable()->comment('Energy in kilojoules');
            $table->decimal('protein_g', 10, 3)->nullable()->comment('Protein in grams');
            $table->decimal('fat_total_g', 10, 3)->nullable()->comment('Total fat in grams');
            $table->decimal('fat_saturated_g', 10, 3)->nullable()->comment('Saturated fat in grams');
            $table->decimal('carbohydrate_g', 10, 3)->nullable()->comment('Carbohydrates in grams');
            $table->decimal('sugars_g', 10, 3)->nullable()->comment('Sugars in grams');
            $table->decimal('sodium_mg', 10, 3)->nullable()->comment('Sodium in milligrams');
            $table->text('description')->nullable()->comment('Food description');
            $table->decimal('specific_gravity', 8, 4)->nullable()->comment('Specific gravity of liquid foods');
            
            // ============================================
            // CATEGORY 3: Food Description & Metadata
            // ============================================
            
            $table->string('measurement_basis')->default('per_100g')->comment('Nutritional data basis');
            $table->string('data_source')->default('FSANZ Food File')->comment('Source of nutritional data');

            $table->json('estimated_ingredients')->nullable()->comment('AI-estimated ingredients list');
            $table->json('estimated_allergens')->nullable()->comment('AI-estimated allergen data');
            
            // ============================================
            // CATEGORY 7: AI-Estimated Hazards & Processing
            // ============================================
            $table->decimal('allergen_confidence_score', 5, 4)->nullable()->comment('Allergen estimation confidence 0-1');
            $table->json('estimated_hazards')->nullable()->comment('AI-estimated HACCP hazards');
            $table->decimal('hazard_confidence_score', 5, 4)->nullable()->comment('Hazard estimation confidence 0-1');
            $table->json('estimated_processing_info')->nullable()->comment('AI-estimated processing information');
            $table->decimal('processing_confidence_score', 5, 4)->nullable()->comment('Processing estimation confidence 0-1');

            // ============================================
            // CATEGORY 4: Food Classification (FSANZ Categories)
            // ============================================
            $table->string('food_category_code')->nullable()->comment('FSANZ category code');
            $table->string('food_category_name')->nullable()->comment('FSANZ category name');
            $table->string('food_group')->nullable()->comment('Primary food group');
            $table->string('food_subgroup')->nullable()->comment('Food subgroup');
            $table->json('functional_category')->default('[]')->comment('Functional categories array');
            
            // ============================================
            // CATEGORY 5: Product Type Flags
            // ============================================
            $table->boolean('is_raw_ingredient')->default(true)->comment('Is a raw/unprocessed ingredient');
            $table->boolean('is_additive')->default(false)->comment('Is a food additive (E-numbers)');
            $table->boolean('is_processing_aid')->default(false)->comment('Is a processing aid');

            
            // ============================================
            // CATEGORY 8: AI-Estimated Dietary & Regulatory
            // ============================================
            $table->json('estimated_dietary_status')->nullable()->comment('AI-estimated dietary status (vegan, halal, etc.)');
            $table->decimal('dietary_confidence_score', 5, 4)->nullable()->comment('Dietary estimation confidence 0-1');
            $table->json('estimated_regulatory_info')->nullable()->comment('AI-estimated regulatory requirements');
            $table->decimal('regulatory_confidence_score', 5, 4)->nullable()->comment('Regulatory estimation confidence 0-1');
            $table->json('estimated_typical_uses')->nullable()->comment('AI-estimated typical uses and applications');
            
            // ============================================
            // CATEGORY 9: Country of Origin Data
            // ============================================
            $table->json('estimated_origin')->nullable()->comment('AI-estimated origin details');
            $table->decimal('estimated_australia_percent', 5, 2)->nullable()->comment('Estimated Australian content %');
            $table->decimal('origin_confidence_score', 5, 4)->nullable()->comment('Origin estimation confidence 0-1');
            $table->string('primary_origin_country')->nullable()->comment('Primary country of origin');
            $table->json('alternative_origin_sources')->nullable()->comment('Alternative sourcing countries');
            $table->boolean('origin_is_variable')->default(false)->comment('Origin varies by supplier/season');
            
            // ============================================
            // CATEGORY 10: AI Analysis Metadata
            // ============================================
            $table->string('ai_estimation_status')->default('pending')->comment('AI analysis status: pending, completed, failed');
            $table->timestamp('last_ai_analysis')->nullable()->comment('Last AI analysis timestamp');
            $table->text('ai_analysis_notes')->nullable()->comment('AI analysis notes and warnings');
            $table->boolean('manual_override')->default(false)->comment('Manual data override flag');
            $table->decimal('overall_confidence_score', 5, 4)->nullable()->comment('Overall AI confidence 0-1');
            
            // ============================================
            // CATEGORY 11: Timestamps
            // ============================================
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();
            
            // ============================================
            // Indexes for Performance
            // ============================================
            $table->index('name');
            $table->index('food_group');
            $table->index('food_subgroup');
            $table->index('ai_estimation_status');
            $table->index('is_raw_ingredient');
            $table->index('is_additive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fsanz_foods');
    }
};
