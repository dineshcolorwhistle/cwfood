<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Specification extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'workspace_id','spec_name','spec_sku','spec_type','spec_status','aus_regulatory_code','description','supplier_name','manufacturer_name','manufacturer_address','manufacturer_contact','distributor_name','distributor_contact','compliance_officer','lot_number_format','nutr_serving_size_g','nutr_servings_per_container','nutritional_basis','nutr_energy_kj','nutr_protein_g','nutr_carbohydrate_g','nutr_sodium_mg','nutr_fat_total_g','nutr_fat_saturated_g','nutr_fat_trans_g','nutr_sugars_g','nutr_added_sugars_g','nutr_dietary_fiber_g','nutr_cholesterol_mg','nutr_calcium_mg','nutr_iron_mg','nutr_potassium_mg','nutr_vitamin_d_mcg','nutr_gluten_content','ing_ingredient_list','allergen_statement','allergen_fsanz_declaration','ing_percentage_labelling','ing_food_additive_numbers','phys_appearance','phys_color','phys_odor','phys_texture','phys_density_g_ml','phys_specific_gravity','phys_moisture_percent','phys_ph_level','phys_water_activity','phys_viscosity_cps','micro_total_plate_count_cfu_g_max','micro_yeast_mold_cfu_g_max','micro_coliforms_cfu_g_max','micro_e_coli_cfu_g_max','micro_salmonella_absent_in_g','micro_listeria_absent_in_g','micro_staphylococcus_cfu_g_max','pack_primary_type','pack_primary_material','pack_primary_dimensions_mm','pack_primary_weight_g','pack_secondary_type','pack_secondary_material','pack_secondary_dimensions_mm','pack_units_per_secondary','pack_case_dimensions_mm','pack_case_weight_g','pack_units_per_case','pack_pallet_type','pack_pallet_dimensions_mm','pack_pallet_height_mm','pack_pallet_weight_kg','pack_cases_per_layer','pack_layers_per_pallet','pack_total_cases_per_pallet','id_gtin_13','id_gtin_14','id_sscc','id_batch_code_format','id_barcode_type','cool_primary_country','cool_origin_declaration','cool_percentage_australia','aus_regulatory_status','cool_fsanz_standard_ref','cool_date_marking_requirement','cool_aus_made_claim','cool_aus_owned_claim','cool_aus_grown_claim','cool_label_type','cool_calculation_method','aus_advisory_statements','aus_warning_statements','aus_health_claims','aus_nutrition_content_claims','cert_is_organic','cert_is_halal','cert_is_kosher','cert_is_gluten_free','cert_is_non_gmo','cert_is_fair_trade','cert_certificate_details','storage_temp_min_c','storage_temp_max_c','storage_humidity_min_percent','storage_humidity_max_percent','storage_conditions','best_before_days','use_by_days','shelf_life_type','shelf_life_value','shelf_life_unit','handling_instructions','disposal_instructions','trace_gln','trace_system','trace_recall_procedure','trace_document_required','chem_metal_lead','chem_metal_cadmium','chem_metal_mercury','chem_metal_arsenic','chem_metal_tin','chem_pest_glyphosate','chem_pest_chlorpyrifos','chem_pest_malathion','chem_pest_permethrin','chem_pest_imazalil','chem_pesticide_residues','chem_mycotoxin_aflatoxin_b1','chem_mycotoxin_aflatoxin_total','chem_mycotoxin_ochratoxin_a','chem_mycotoxin_deoxynivalenol','chem_mycotoxin_zearalenone','chem_mycotoxin_patulin','chem_mycotoxins','chem_add_tartrazine','chem_add_cochineal','chem_add_sunset_yellow','chem_add_citric_acid','chem_add_ascorbic_acid','chem_add_monosodium_glutamate','chem_additives','chem_pres_sodium_benzoate','chem_pres_potassium_sorbate','chem_pres_calcium_propionate','chem_pres_sulfur_dioxide','chem_pres_sodium_nitrite','chem_pres_sodium_metabisulfite','chem_preservatives','spec_upload_type','file_name','spec_url','archive','json_object','audit_response','spec_image','fsanz_source_id','created_at','updated_at'];
    protected $casts = [
        'audit_response' => 'array',
    ];
    
    /**
     * Validation rules for Specification
     */
    public static function rules($id = null)
    {

        return [
            // Base meta fields
            'client_id' => 'nullable|integer',
            'workspace_id' => 'nullable|integer',

            // Specification info
            'spec_name' => [
                                'required',
                                'string',
                                Rule::unique('specifications', 'spec_name')
                                    ->where(function ($query) {
                                        return $query->where('client_id', session('client'))
                                                    ->where('workspace_id', session('workspace'));
                                    })
                                    ->ignore($id)
                                ],
            'spec_sku' => 'required|string',
            'spec_type' => 'required|string',
            'spec_status' => 'nullable|string',
            'aus_regulatory_code' => 'nullable|string',
            'description' => 'nullable|string',

            // Supplier / Manufacturer
            'supplier_name' => 'nullable|string',
            'manufacturer_name' => 'nullable|string',
            'manufacturer_address' => 'nullable|string',
            'manufacturer_contact' => 'nullable|string',
            'distributor_name' => 'nullable|string',
            'distributor_contact' => 'nullable|string',
            'compliance_officer' => 'nullable|string',
            'lot_number_format' => 'nullable|string',

            // Nutritional information
            'nutr_serving_size_g' => 'nullable|numeric',
            'nutr_servings_per_container' => 'nullable|numeric',
            'nutritional_basis' => 'nullable|string',
            'nutr_energy_kj' => 'nullable|numeric',
            'nutr_protein_g' => 'nullable|numeric',
            'nutr_carbohydrate_g' => 'nullable|numeric',
            'nutr_sodium_mg' => 'nullable|numeric',
            'nutr_fat_total_g' => 'nullable|numeric',
            'nutr_fat_saturated_g' => 'nullable|numeric',
            'nutr_fat_trans_g' => 'nullable|numeric',
            'nutr_sugars_g' => 'nullable|numeric',
            'nutr_added_sugars_g' => 'nullable|numeric',
            'nutr_dietary_fiber_g' => 'nullable|numeric',
            'nutr_cholesterol_mg' => 'nullable|numeric',
            'nutr_calcium_mg' => 'nullable|numeric',
            'nutr_iron_mg' => 'nullable|numeric',
            'nutr_potassium_mg' => 'nullable|numeric',
            'nutr_vitamin_d_mcg' => 'nullable|numeric',
            'nutr_gluten_content' => 'nullable|string',

            // Ingredient & allergen
            'ing_ingredient_list' => 'nullable|string',
            'allergen_statement' => 'nullable|string',
            'allergen_fsanz_declaration' => 'nullable|string',
            'ing_percentage_labelling' => 'nullable|string',
            'ing_food_additive_numbers' => 'nullable|string',

            // Physical characteristics
            'phys_appearance' => 'nullable|string',
            'phys_color' => 'nullable|string',
            'phys_odor' => 'nullable|string',
            'phys_texture' => 'nullable|string',
            'phys_density_g_ml' => 'nullable|numeric',
            'phys_specific_gravity' => 'nullable|numeric',
            'phys_moisture_percent' => 'nullable|numeric',
            'phys_ph_level' => 'nullable|numeric',
            'phys_water_activity' => 'nullable|string',
            'phys_viscosity_cps' => 'nullable|string',

            // Microbiology
            'micro_total_plate_count_cfu_g_max' => 'nullable|string',
            'micro_yeast_mold_cfu_g_max' => 'nullable|string',
            'micro_coliforms_cfu_g_max' => 'nullable|string',
            'micro_e_coli_cfu_g_max' => 'nullable|string',
            'micro_salmonella_absent_in_g' => 'nullable|string',
            'micro_listeria_absent_in_g' => 'nullable|string',
            'micro_staphylococcus_cfu_g_max' => 'nullable|string',

            // Packaging
            'pack_primary_type' => 'nullable|string',
            'pack_primary_material' => 'nullable|string',
            'pack_primary_dimensions_mm' => 'nullable|string',
            'pack_primary_weight_g' => 'nullable|numeric',
            'pack_secondary_type' => 'nullable|string',
            'pack_secondary_material' => 'nullable|string',
            'pack_secondary_dimensions_mm' => 'nullable|string',
            'pack_units_per_secondary' => 'nullable|integer',
            'pack_case_dimensions_mm' => 'nullable|string',
            'pack_case_weight_g' => 'nullable|numeric',
            'pack_units_per_case' => 'nullable|integer',
            'pack_pallet_type' => 'nullable|string',
            'pack_pallet_dimensions_mm' => 'nullable|string',
            'pack_pallet_height_mm' => 'nullable|numeric',
            'pack_pallet_weight_kg' => 'nullable|numeric',
            'pack_cases_per_layer' => 'nullable|integer',
            'pack_layers_per_pallet' => 'nullable|integer',
            'pack_total_cases_per_pallet' => 'nullable|integer',

            // Identifiers
            'id_gtin_13' => 'nullable|string',
            'id_gtin_14' => 'nullable|string',
            'id_sscc' => 'nullable|string',
            'id_batch_code_format' => 'nullable|string',
            'id_barcode_type' => 'nullable|string',

            // Country of origin & COOL
            'cool_primary_country' => 'nullable|string',
            'cool_origin_declaration' => 'nullable|string',
            'cool_percentage_australia' => 'nullable|numeric',
            'cool_fsanz_standard_ref' => 'nullable|string',
            'cool_date_marking_requirement' => 'nullable|string',
            'cool_aus_made_claim' => 'nullable|boolean',
            'cool_aus_owned_claim' => 'nullable|boolean',
            'cool_aus_grown_claim' => 'nullable|boolean',
            'cool_label_type' => 'nullable|string',
            'cool_calculation_method' => 'nullable|string',

            // Australian regulatory info
            'aus_regulatory_status' => 'nullable|string',
            'aus_advisory_statements' => 'nullable|string',
            'aus_warning_statements' => 'nullable|string',
            'aus_health_claims' => 'nullable|string',
            'aus_nutrition_content_claims' => 'nullable|string',

            // Certifications
            'cert_is_organic' => 'nullable|boolean',
            'cert_is_halal' => 'nullable|boolean',
            'cert_is_kosher' => 'nullable|boolean',
            'cert_is_gluten_free' => 'nullable|boolean',
            'cert_is_non_gmo' => 'nullable|boolean',
            'cert_is_fair_trade' => 'nullable|boolean',
            'cert_certificate_details' => 'nullable|string',

            // Storage & shelf life
            'storage_temp_min_c' => 'nullable|numeric',
            'storage_temp_max_c' => 'nullable|numeric',
            'storage_humidity_min_percent' => 'nullable|numeric',
            'storage_humidity_max_percent' => 'nullable|numeric',
            'storage_conditions' => 'nullable|string',
            'shelf_life_type' => 'nullable|string',
            'shelf_life_value' => 'nullable|integer',
            'shelf_life_unit' => 'nullable|string',
            'best_before_days' => 'nullable|integer',
            'use_by_days' => 'nullable|integer',
            'handling_instructions' => 'nullable|string',
            'disposal_instructions' => 'nullable|string',

            // Traceability
            'trace_gln' => 'nullable|string',
            'trace_system' => 'nullable|string',
            'trace_recall_procedure' => 'nullable|string',
            'trace_document_required' => 'nullable|boolean',

            // Metals & contaminants
            'chem_metal_lead' => 'nullable|string',
            'chem_metal_cadmium' => 'nullable|string',
            'chem_metal_mercury' => 'nullable|string',
            'chem_metal_arsenic' => 'nullable|string',
            'chem_metal_tin' => 'nullable|string',
            'chem_pest_glyphosate' => 'nullable|string',
            'chem_pest_chlorpyrifos' => 'nullable|string',
            'chem_pest_malathion' => 'nullable|string',
            'chem_pest_permethrin' => 'nullable|string',
            'chem_pest_imazalil' => 'nullable|string',
            'chem_pesticide_residues' => 'nullable|string',
            'chem_mycotoxin_aflatoxin_b1' => 'nullable|string',
            'chem_mycotoxin_aflatoxin_total' => 'nullable|string',
            'chem_mycotoxin_ochratoxin_a' => 'nullable|string',
            'chem_mycotoxin_deoxynivalenol' => 'nullable|string',
            'chem_mycotoxin_zearalenone' => 'nullable|string',
            'chem_mycotoxin_patulin' => 'nullable|string',
            'chem_mycotoxins' => 'nullable|string',
            'chem_add_tartrazine' => 'nullable|string',
            'chem_add_cochineal' => 'nullable|string',
            'chem_add_sunset_yellow' => 'nullable|string',
            'chem_add_citric_acid' => 'nullable|string',
            'chem_add_ascorbic_acid' => 'nullable|string',
            'chem_add_monosodium_glutamate' => 'nullable|string',
            'chem_additives' => 'nullable|string',
            'chem_pres_sodium_benzoate' => 'nullable|string',
            'chem_pres_potassium_sorbate' => 'nullable|string',
            'chem_pres_calcium_propionate' => 'nullable|string',
            'chem_pres_sulfur_dioxide' => 'nullable|string',
            'chem_pres_sodium_nitrite' => 'nullable|string',
            'chem_pres_sodium_metabisulfite' => 'nullable|string',
            'chem_preservatives' => 'nullable|string',
            
            // Upload info
            'spec_upload_type' => 'nullable|integer',
            'file_name' => 'nullable|string',
            'spec_url' => 'nullable|string',
            'image_file.*' => 'nullable|mimes:jpg,jpeg,png|max:5120',
            'spec_image' => 'nullable|string',  
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


}
