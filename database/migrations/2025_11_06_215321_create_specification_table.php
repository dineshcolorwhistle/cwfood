<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->integer('workspace_id');
            $table->text('name')->nullable();
            $table->text('sku')->nullable();
            $table->text('product_type')->nullable();
            $table->text('status')->nullable();
            $table->text('australian_regulatory_status')->nullable();
            $table->text('description')->nullable();
            $table->text('supplier')->nullable();
            $table->text('manufacturer_name')->nullable();
            $table->text('manufacturer_address')->nullable();
            $table->text('manufacturer_contact')->nullable();
            $table->char('country_of_manufacture', 2)->nullable();   
            $table->text('distributor_name')->nullable();
            $table->text('distributor_contact')->nullable();
            $table->text('compliance_officer')->nullable();
            $table->text('lot_number_format')->nullable();
            $table->decimal('serving_size_g', 10, 2)->nullable();
            $table->decimal('servings_per_container', 10, 2)->nullable();
            $table->text('nutritional_basis')->nullable();
            $table->decimal('energy_kj', 10, 2)->nullable();
            $table->decimal('protein_g', 10, 2)->nullable();
            $table->decimal('carbohydrate_g', 10, 2)->nullable();
            $table->decimal('sodium_mg', 10, 2)->nullable();
            $table->decimal('fat_total_g', 10, 2)->nullable();
            $table->decimal('fat_saturated_g', 10, 2)->nullable();
            $table->decimal('fat_trans_g', 10, 2)->nullable();
            $table->decimal('sugars_g', 10, 2)->nullable();
            $table->decimal('added_sugars_g', 10, 2)->nullable();
            $table->decimal('dietary_fiber_g', 10, 2)->nullable();
            $table->decimal('cholesterol_mg', 10, 2)->nullable();
            $table->decimal('calcium_mg', 10, 2)->nullable();
            $table->decimal('iron_mg', 10, 2)->nullable();
            $table->decimal('potassium_mg', 10, 2)->nullable();
            $table->decimal('vitamin_d_mcg', 10, 2)->nullable();
            $table->text('gluten_content')->nullable();
            $table->text('ingredient_list')->nullable();
            $table->text('allergen_statement')->nullable();
            $table->text('fsanz_allergen_declaration')->nullable();
            $table->text('percentage_labelling')->nullable();
            $table->text('food_additive_numbers')->nullable();
            $table->text('appearance')->nullable();
            $table->text('color')->nullable();
            $table->text('odor')->nullable();
            $table->text('texture')->nullable();
            $table->decimal('density_g_ml', 10, 2)->nullable();
            $table->decimal('specific_gravity', 10, 2)->nullable();
            $table->decimal('moisture_content_percent', 10, 2)->nullable();
            $table->decimal('ph_level', 10, 2)->nullable();
            $table->decimal('water_activity', 10, 2)->nullable();
            $table->text('viscosity_cps')->nullable();
            $table->decimal('total_plate_count_cfu_g_max', 10, 2)->nullable();
            $table->decimal('yeast_mold_cfu_g_max', 10, 2)->nullable();
            $table->decimal('coliforms_cfu_g_max', 10, 2)->nullable();
            $table->decimal('e_coli_cfu_g_max', 10, 2)->nullable();
            $table->decimal('salmonella_absent_in_g', 10, 2)->nullable();
            $table->decimal('listeria_absent_in_g', 10, 2)->nullable();
            $table->decimal('staphylococcus_cfu_g_max', 10, 2)->nullable();
            $table->text('primary_package_type')->nullable();
            $table->text('primary_package_material')->nullable();
            $table->text('primary_package_dimensions_mm')->nullable();
            $table->decimal('primary_package_weight_g', 10, 2)->nullable();
            $table->text('secondary_package_type')->nullable();
            $table->text('secondary_package_material')->nullable();
            $table->text('secondary_package_dimensions_mm')->nullable();
            $table->decimal('units_per_secondary',10, 2)->nullable();
            $table->text('case_dimensions_mm')->nullable();
            $table->decimal('case_weight_g', 10, 2)->nullable();
            $table->decimal('units_per_case',10,2)->nullable();
            $table->text('pallet_type')->nullable();
            $table->text('pallet_dimensions_mm')->nullable();
            $table->decimal('pallet_height_mm', 10, 2)->nullable();
            $table->decimal('pallet_weight_kg', 10, 2)->nullable();
            $table->integer('cases_per_layer')->nullable();
            $table->integer('layers_per_pallet')->nullable();
            $table->integer('total_cases_per_pallet')->nullable();
            $table->text('gtin_13')->nullable();
            $table->text('gtin_14')->nullable();
            $table->text('sscc')->nullable();
            $table->text('batch_code_format')->nullable();
            $table->text('barcode_type')->nullable();
            $table->text('primary_country')->nullable();
            $table->text('origin_declaration')->nullable();
            $table->text('fsanz_standard_ref')->nullable();
            $table->text('date_marking_requirement')->nullable();
            $table->boolean('is_australian_made')->nullable();
            $table->boolean('is_australian_owned')->nullable();
            $table->boolean('is_australian_grown')->nullable();
            $table->text('advisory_statements')->nullable();
            $table->text('warning_statements')->nullable();
            $table->text('health_claims')->nullable();
            $table->text('nutrition_content_claims')->nullable();
            $table->boolean('is_organic')->nullable();
            $table->boolean('is_halal')->nullable();
            $table->boolean('is_kosher')->nullable();
            $table->boolean('is_gluten_free')->nullable();
            $table->boolean('is_non_gmo')->nullable();
            $table->boolean('is_fair_trade')->nullable();
            $table->text('certificate_details')->nullable();
            $table->decimal('storage_temp_min_c', 8, 2)->nullable();
            $table->decimal('storage_temp_max_c', 8, 2)->nullable();
            $table->decimal('storage_humidity_min_percent', 8, 2)->nullable();
            $table->decimal('storage_humidity_max_percent', 8, 2)->nullable();
            $table->text('storage_conditions')->nullable();
            $table->text('shelf_life_type')->nullable();
            $table->integer('shelf_life_value')->nullable();
            $table->text('shelf_life_unit')->nullable();
            $table->text('handling_instructions')->nullable();
            $table->text('disposal_instructions')->nullable();
            $table->text('gln')->nullable();
            $table->text('traceability_system')->nullable();
            $table->text('recall_procedure')->nullable();
            $table->boolean('trace_document')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specifications');
    }
};
