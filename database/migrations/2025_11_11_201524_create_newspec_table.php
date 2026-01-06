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
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->integer('workspace_id');
            $table->text('spec_name')->nullable();
            $table->text('spec_sku')->nullable();
            $table->text('spec_type')->nullable();
            $table->text('spec_status')->nullable();
            $table->text('aus_regulatory_code')->nullable();
            $table->text('description')->nullable();
            $table->text('supplier_name')->nullable();
            $table->text('mfr_name')->nullable();
            $table->text('mfr_address')->nullable();
            $table->text('mfr_contact')->nullable();
            $table->text('distributor_name')->nullable();
            $table->text('distributor_contact')->nullable();
            $table->text('compliance_officer')->nullable();
            $table->text('lot_number_format')->nullable();
            $table->decimal('nutr_serving_size_g', 10, 2)->nullable();
            $table->decimal('nutr_servings_per_container', 10, 2)->nullable();
            $table->text('nutritional_basis')->nullable();
            $table->decimal('nutr_energy_kj', 10, 2)->nullable();
            $table->decimal('nutr_protein_g', 10, 2)->nullable();
            $table->decimal('nutr_carbohydrate_g', 10, 2)->nullable();
            $table->decimal('nutr_sodium_mg', 10, 2)->nullable();
            $table->decimal('nutr_fat_total_g', 10, 2)->nullable();
            $table->decimal('nutr_fat_saturated_g', 10, 2)->nullable();
            $table->decimal('nutr_fat_trans_g', 10, 2)->nullable();
            $table->decimal('nutr_sugars_g', 10, 2)->nullable();
            $table->decimal('nutr_added_sugars_g', 10, 2)->nullable();
            $table->decimal('nutr_dietary_fiber_g', 10, 2)->nullable();
            $table->decimal('nutr_cholesterol_mg', 10, 2)->nullable();
            $table->decimal('nutr_calcium_mg', 10, 2)->nullable();
            $table->decimal('nutr_iron_mg', 10, 2)->nullable();
            $table->decimal('nutr_potassium_mg', 10, 2)->nullable();
            $table->decimal('nutr_vitamin_d_mcg', 10, 2)->nullable();
            $table->text('nutr_gluten_content')->nullable();
            $table->text('ing_ingredient_list')->nullable();
            $table->text('allergen_statement')->nullable();
            $table->text('allergen_fsanz_declaration')->nullable();
            $table->text('ing_percentage_labelling')->nullable();
            $table->text('ing_food_additive_numbers')->nullable();
            $table->text('phys_appearance')->nullable();
            $table->text('phys_color')->nullable();
            $table->text('phys_odor')->nullable();
            $table->text('phys_texture')->nullable();
            $table->decimal('phys_density_g_ml', 10, 2)->nullable();
            $table->decimal('phys_specific_gravity', 10, 2)->nullable();
            $table->decimal('phys_moisture_percent', 10, 2)->nullable();
            $table->decimal('phys_ph_level', 10, 2)->nullable();
            $table->decimal('phys_water_activity', 10, 2)->nullable();
            $table->text('phys_viscosity_cps')->nullable();
            $table->decimal('micro_total_plate_count_cfu_g_max', 10, 2)->nullable();
            $table->decimal('micro_yeast_mold_cfu_g_max', 10, 2)->nullable();
            $table->decimal('micro_coliforms_cfu_g_max', 10, 2)->nullable();
            $table->decimal('micro_e_coli_cfu_g_max', 10, 2)->nullable();
            $table->decimal('micro_salmonella_absent_in_g', 10, 2)->nullable();
            $table->decimal('micro_listeria_absent_in_g', 10, 2)->nullable();
            $table->decimal('micro_staphylococcus_cfu_g_max', 10, 2)->nullable();
            $table->text('pack_primary_type')->nullable();
            $table->text('pack_primary_material')->nullable();
            $table->text('pack_primary_dimensions_mm')->nullable();
            $table->decimal('pack_primary_weight_g', 10, 2)->nullable();
            $table->text('pack_secondary_type')->nullable();
            $table->text('pack_secondary_material')->nullable();
            $table->text('pack_secondary_dimensions_mm')->nullable();
            $table->decimal('pack_units_per_secondary',10, 2)->nullable();
            $table->text('pack_case_dimensions_mm')->nullable();
            $table->decimal('pack_case_weight_g', 10, 2)->nullable();
            $table->decimal('pack_units_per_case',10,2)->nullable();
            $table->text('pack_pallet_type')->nullable();
            $table->text('pack_pallet_dimensions_mm')->nullable();
            $table->decimal('pack_pallet_height_mm', 10, 2)->nullable();
            $table->decimal('pack_pallet_weight_kg', 10, 2)->nullable();
            $table->integer('pack_cases_per_layer')->nullable();
            $table->integer('pack_layers_per_pallet')->nullable();
            $table->integer('pack_total_cases_per_pallet')->nullable();
            $table->text('id_gtin_13')->nullable();
            $table->text('id_gtin_14')->nullable();
            $table->text('id_sscc')->nullable();
            $table->text('id_batch_code_format')->nullable();
            $table->text('id_barcode_type')->nullable();
            $table->text('cool_primary_country')->nullable();
            $table->text('cool_origin_declaration')->nullable();
            $table->decimal('cool_percentage_australia', 5, 4)->nullable();
            $table->text('aus_regulatory_status')->nullable();
            $table->text('aus_fsanz_standard_ref')->nullable();
            $table->text('aus_date_marking_requirement')->nullable();
            $table->boolean('aus_made_claim')->nullable();
            $table->boolean('aus_owned_claim')->nullable();
            $table->boolean('aus_grown_claim')->nullable();
            $table->text('aus_advisory_statements')->nullable();
            $table->text('aus_warning_statements')->nullable();
            $table->text('aus_health_claims')->nullable();
            $table->text('aus_nutrition_content_claims')->nullable();
            $table->boolean('cert_is_organic')->nullable();
            $table->boolean('cert_is_halal')->nullable();
            $table->boolean('cert_is_kosher')->nullable();
            $table->boolean('cert_is_gluten_free')->nullable();
            $table->boolean('cert_is_non_gmo')->nullable();
            $table->boolean('cert_is_fair_trade')->nullable();
            $table->text('cert_certificate_details')->nullable();
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
            $table->text('trace_gln')->nullable();
            $table->text('trace_system')->nullable();
            $table->text('trace_recall_procedure')->nullable();
            $table->boolean('trace_document_required')->nullable();
            $table->decimal('metal_lead_pb_mgkg', 10, 4)->nullable();
            $table->decimal('metal_mercury_hg_mgkg', 10, 4)->nullable();
            $table->decimal('metal_cadmium_cd_mgkg', 10, 4)->nullable();
            $table->decimal('metal_arsenic_as_mgkg', 10, 4)->nullable();
            $table->decimal('pest_ddt_mgkg', 10, 4)->nullable();
            $table->decimal('pest_chlorpyrifos_mgkg', 10, 4)->nullable();
            $table->decimal('pest_glyphosate_mgkg', 10, 4)->nullable();
            $table->decimal('pest_atrazine_mgkg', 10, 4)->nullable();
            $table->decimal('myco_aflatoxin_b1_ugkg', 10, 4)->nullable();
            $table->decimal('myco_ochratoxin_a_ugkg', 10, 4)->nullable();
            $table->decimal('myco_deoxynivalenol_don_ugkg', 10, 4)->nullable();
            $table->decimal('myco_zearalenone_ugkg', 10, 4)->nullable();
            $table->decimal('add_sodium_benzoate_mgkg', 10, 4)->nullable();
            $table->decimal('add_potassium_sorbate_mgkg', 10, 4)->nullable();
            $table->decimal('add_monosodium_glutamate_msg_mgkg', 10, 4)->nullable();
            $table->decimal('add_sulphites_mgkg', 10, 4)->nullable();      
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specifications');
    }
};
