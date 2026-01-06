<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use App\Services\FSANZMappingService;
use App\Models\{Specification,AIPrompt,SpecificationArchieve,image_library,FsanzFood};
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Excel as ExcelType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BatchabaseAgentController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;
    private $extractionSchema = [
                                    "type" => "object",
                                    "properties" => [
                                        // Meta fields
                                        "document_type" => [
                                            "type" => "string",
                                            "description" => "Type of document: 'specification_sheet' or 'certificate_of_analysis'"
                                        ],
                                        "_confidence" => [
                                            "type" => "object",
                                            "description" => "Confidence scores for major fields: 'high', 'medium', 'low'",
                                            "properties" => [
                                                "name" => ["type" => "string", "enum" => ["high", "medium", "low"]],
                                                "nutritional_info" => ["type" => "string", "enum" => ["high", "medium", "low"]],
                                                "supplier" => ["type" => "string", "enum" => ["high", "medium", "low"]],
                                                "allergens" => ["type" => "string", "enum" => ["high", "medium", "low"]],
                                            ]
                                        ],

                                        "spec" => [
                                            "type" => "object",
                                            "properties" => [
                                                "spec_name" => ["type" => "string", "description" => "Product name - MUST be extracted from document, DO NOT infer"],
                                                "spec_sku" => ["type" => "string", "description" => "Product Spec_SKU/code"],
                                                "aus_regulatory_status" => ["type" => "string", "description" => "Compliance status with Australian regulations"],
                                                "description" => ["type" => "string", "description" => "Product description"],
                                                "lot_number_format" => ["type" => "string", "description" => "Lot/batch number format (e.g., 'YYMMDD-XXX')"],
                                            ]
                                        ],
                                        "parties" => [
                                            "type" => "object",
                                            "properties" => [
                                                // Manufacturing & Supply Chain
                                                "supplier_name" => ["type" => "string", "description" => "Supplier name"],
                                                "manufacturer_name" => ["type" => "string", "description" => "Manufacturer/producer name"],
                                                "manufacturer_address" => ["type" => "string", "description" => "Manufacturer address"],
                                                "manufacturer_contact" => ["type" => "string", "description" => "Manufacturer contact (email/phone)"],
                                                "distributor_name" => ["type" => "string", "description" => "Distributor company name"],
                                                "distributor_contact" => ["type" => "string", "description" => "Distributor contact details"],
                                                "compliance_officer" => ["type" => "string", "description" => "Name of compliance officer"],
                                            ]
                                        ],

                                        // Nutritional Information
                                        "nutritional_info" => [
                                            "type" => "object",
                                            "properties" => [
                                                "nutr_serving_size_g" => ["type" => "number","multipleOf" => 0.01, "description" => "Serving size (e.g., '350.00')"],
                                                "nutr_servings_per_container" => ["number" => "string", "description" => "Number of servings (e.g., '350.00')"],
                                                "nutritional_basis" => [
                                                    "type" => "string",
                                                    "enum" => ["per_100g", "per_100ml"],
                                                    "description" => "Set to 'per_100g' for solid foods OR 'per_100ml' for liquid foods."
                                                ],
                                                "nutr_energy_kj" => ["type" => "number","multipleOf" => 0.01, "description" => "Energy in kilojoules per 100g/100ml (e.g., '350.00')"],
                                                "nutr_protein_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Protein content in grams (e.g., '350.00')"],
                                                "nutr_fat_total_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Total fat content in grams (e.g., '350.00')"],
                                                "nutr_fat_saturated_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Saturated fat content in grams (e.g., '350.00')"],
                                                "nutr_fat_trans_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Trans fat content in grams (e.g., '350.00')"],
                                                "nutr_cholesterol_mg" => ["type" => "number","multipleOf" => 0.01,"description" => "Cholesterol content in milligrams (e.g., '350.00')"],
                                                "nutr_sodium_mg" => ["type" => "number","multipleOf" => 0.01,"description" => "Sodium content in milligrams (e.g., '350.00')"],
                                                "nutr_carbohydrate_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Total carbohydrate content in grams (e.g., '350.00')"],
                                                "nutr_dietary_fiber_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Dietary fiber content in grams (e.g., '350.00')"],
                                                "nutr_sugars_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Total sugar content in grams (e.g., '350.00')"],
                                                "nutr_added_sugars_g" => ["type" => "number","multipleOf" => 0.01,"description" => "Added sugar content in grams (e.g., '350.00')"],
                                                "nutr_vitamin_d_mcg" => ["type" => "number","multipleOf" => 0.01,"description" => "Vitamin D content in micrograms (e.g., '350.00')"],
                                                "nutr_calcium_mg" => ["type" => "number","multipleOf" => 0.01,"description" => "Calcium content in milligrams (e.g., '350.00')"],
                                                "nutr_iron_mg" => ["type" => "number","multipleOf" => 0.01,"description" => "Iron content in milligrams (e.g., '350.00')"],
                                                "nutr_potassium_mg" => ["type" => "number","multipleOf" => 0.01,"description" => "Potassium content in milligrams (e.g., '350.00')"],
                                                "nutr_gluten_content" => ["type" => "string","description" => "Gluten content description (e.g., '350.00')"],
                                                "additional_nutrients" => ["type" => "object","description" => "Additional nutrients not in standard fields"]
                                            ]
                                        ],
                                        // Ingredients & Allergens
                                        "ingredients_allergens" => [
                                            "type" => "object",
                                            "properties" => [
                                                "ing_ingredient_list" => ["type" => "string"],
                                                "allergen_statement" => [
                                                    "type" => "array",
                                                    "items" => ["type" => "string"],
                                                    "description" => "Use FSANZ PEAL allergen names (e.g., 'almond', 'egg', 'milk'). Return [] if none declared."
                                                ],
                                                "may_contain_allergens" => [
                                                    "type" => "array",
                                                    "items" => ["type" => "string"],
                                                    "description" => "Precautionary allergen statements. [] if none."
                                                ],
                                                "allergen_fsanz_declaration" => ["type" => "string", "description" =>"Formal FSANZ-compliant allergen declaration"],
                                                "food_additive_numbers" => ["type" => "array", "items" => ["type" => "string"]],
                                                "ing_percentage_labelling" => ["type" => "string","description" =>"Characterizing ingredient percentages"]
                                            ]
                                        ],
                                        // Country of Origin
                                        "cool" => [
                                            "type" => "object",
                                            "properties" => [
                                                "cool_primary_country" => ["type" => "string", "description"=> "Main country of origin"],
                                                "cool_origin_declaration" => ["type" => "string","description"=> "Complete CoOL statement"],
                                                "cool_percentage_australia" => ["type" => "number","multipleOf" => 0.01,"description"=> "Percentage of Australian ingredients by weight or cost (as decimal 0-1)"],
                                                "cool_calculation_details" => ["type" => "string","description"=> "Calculation Details"],
                                                "cool_label_type" => ["type" => "string","description"=> "CoOL Label Type"],
                                                "cool_fsanz_standard_ref" => ["type" => "string","description"=> "Applicable FSANZ Standard clause"],
                                                "cool_aus_made_claim" => ["type" => "boolean","description"=> "Product carries Australian Made claim"],
                                                "cool_aus_owned_claim" => ["type" => "boolean","description"=> "Product carries Australian Owned claim"],
                                                "cool_aus_grown_claim" => ["type" => "boolean","description"=> "Product carries Australian Grown claim"],
                                                "cool_date_marking_requirement" => ["type" => "string","description"=> "Special date marking requirements for CoOL"]
                                            ]
                                        ],

                                        // Physical Specifications
                                        "physical_specs" => [
                                            "type" => "object",
                                            "properties" => [
                                                "phys_appearance" => ["type" => "string","description" =>"Visual appearance description"],
                                                "phys_color" => ["type" => "string" ,"description" =>"Product color description"],
                                                "phys_odor" => ["type" => "string" ,"description" =>"Product odor/aroma description"],
                                                "phys_texture" => ["type" => "string" ,"description" =>"Product texture description"],
                                                "phys_ph_level" => ["type" => "number" ,"description" =>"pH measurement"],
                                                "phys_moisture_percent" => ["type" => "number" ,"description" =>"Moisture content percentage"],
                                                "phys_water_activity" => ["type" => "number" ,"description" =>"Water activity measurement"],
                                                "phys_density_g_ml" => ["type" => "number" ,"description" =>"Product density"],
                                                "phys_specific_gravity" => ["type" => "number" ,"description" =>"Specific gravity relative to water"],
                                                "phys_viscosity_cps" => ["type" => "string" ,"description" =>"Viscosity measurement or description"],
                                                "additional_properties" => ["type" => "object"]
                                            ]
                                        ],

                                        // Microbiological Specifications
                                        "microbiological_specs" => [
                                            "type" => "object",
                                            "properties" => [
                                                "micro_total_plate_count_cfu_g_max" => ["type" => "string","description" =>"Standard plate count / aerobic plate count"],
                                                "micro_coliforms_cfu_g_max" => ["type" => "string" ,"description" =>"Coliform bacteria test results"],
                                                "micro_e_coli_cfu_g_max" => ["type" => "string" ,"description" =>"E. coli test results"],
                                                "micro_salmonella_absent_in_g" => ["type" => "string" ,"description" =>"Salmonella test results"],
                                                "micro_listeria_absent_in_g" => ["type" => "string" ,"description" =>"Listeria monocytogenes test results"],
                                                "micro_staphylococcus_cfu_g_max" => ["type" => "string" ,"description" =>"Staph aureus test results"],
                                                "micro_yeast_mold_cfu_g_max" => ["type" => "string","description" =>"Yeast and mold count"]
                                            ]
                                        ],

                                        // Packaging
                                        "packaging" => [
                                            "type" => "object",
                                            "properties" => [
                                                "pack_primary_type" => ["type" => "string","description" =>"Type of primary packaging"],
                                                "pack_primary_material" => ["type" => "string","description" =>"Material of primary packaging"],
                                                "pack_primary_dimensions_mm" => ["type" => "string","description" =>"Dimensions of primary package"],
                                                "pack_primary_weight_g" => ["type" => "number","multipleOf" => 0.01,"description" =>"Weight of primary package unit (e.g., '350.00')"],
                                                "pack_secondary_type" => ["type" => "string","description" =>"Type of secondary packaging"],
                                                "pack_secondary_material" => ["type" => "string","description" =>"Material of secondary packaging"],
                                                "pack_secondary_dimensions_mm" => ["type" => "string","description" =>"Dimensions of secondary package"],
                                                "pack_units_per_secondary" => ["type" => "number","multipleOf" => 0.01,"description" =>"Number of primary units per secondary package (e.g., '350.00')"],
                                                "pack_case_dimensions_mm" => ["type" => "string","description" =>"Outer case dimensions (LWH)"],
                                                "pack_case_weight_g" => ["type" => "number","multipleOf" => 0.01,"description" =>"Gross weight of outer case (e.g., '350.00')"],
                                                "pack_units_per_case" => ["type" => "number","multipleOf" => 0.01,"description" =>"Number of primary units per case (e.g., '350.00')"],
                                                "pack_cases_per_layer" => ["type" => "number","multipleOf" => 0.01,"description" =>"Number of layers on a pallet (e.g., '350.00')"],
                                                "pack_layers_per_pallet" => ["type" => "number","multipleOf" => 0.01,"description" =>"Total number of cases per pallet (e.g., '350.00')"],
                                                "pack_pallet_type" => ["type" => "string","description" =>"Type of pallet used"],
                                                "pack_pallet_dimensions_mm" => ["type" => "string","description" =>"Pallet base dimensions"],
                                                "pack_pallet_height_mm" => ["type" => "number","multipleOf" => 0.01,"description" =>"Total height of loaded pallet (e.g., '350.00')"],
                                                "pack_pallet_weight_kg" => ["type" => "number","multipleOf" => 0.01,"description" =>"Gross weight of loaded pallet (e.g., '350.00')"],
                                                "pack_total_cases_per_pallet" => ["type" => "number","multipleOf" => 0.01,"description" =>"Total number of cases per pallet"],
                                            ]
                                        ],

                                        // identifiers
                                        "identifiers" => [
                                            "type" => "object",
                                            "properties" => [
                                               // Traceability & Recall
                                                "id_gtin_13" => ["type" => "string", "description" => "Retail GTIN-13 barcode."],
                                                "id_gtin_14" => ["type" => "string", "description" => "Case-level GTIN-14 barcode."],
                                                "id_sscc" => ["type" => "string", "description" => "Logistics SSCC pallet/carton identifier"],
                                                "id_batch_code_format" => ["type" => "string", "description" => "Format pattern used for batch/lot coding."],
                                                "id_barcode_type" => ["type" => "string", "description" => "Barcode symbology (e.g., EAN-13, ITF-14)."],
                                            ]
                                        ],

                                        // storage
                                        "storage" => [
                                            "type" => "object",
                                            "properties" => [
                                                // Storage & Handling
                                                "storage_temp_min_c" => ["type" => "number","multipleOf" => 0.01, "description" => "Minimum storage temperature in Celsius (e.g., '10.00')"],
                                                "storage_temp_max_c" => ["type" => "number","multipleOf" => 0.01, "description" => "Maximum storage temperature in Celsius (e.g., '10.00')"],
                                                "storage_conditions" => ["type" => "string", "description" => "Detailed storage conditions text"],
                                                "storage_humidity_min_percent" => ["type" => "number","multipleOf" => 0.01, "description" => "Minimum humidity percentage (e.g., '10.00')"],
                                                "storage_humidity_max_percent" => ["type" => "number","multipleOf" => 0.01, "description" => "Maximum humidity percentage (e.g., '10.00')"],
                                            ]
                                        ],

                                         // FSANZ Compliance
                                        "aus_compliance" => [
                                            "type" => "object",
                                            "properties" => [
                                                "health_claims" => ["type" => "array", "items" => ["type" => "string"]],
                                                "nutrition_content_claims" => ["type" => "array", "items" => ["type" => "string"]],
                                                "warning_statements" => ["type" => "array", "items" => ["type" => "string"]],
                                                "advisory_statements" => ["type" => "array", "items" => ["type" => "string"]]
                                            ]
                                        ],
                                        // Certifications
                                        "certifications" => [
                                            "type" => "object",
                                            "properties" => [
                                                "cert_is_organic" => ["type" => "boolean", "description" => "Product is certified organic"],
                                                "cert_is_halal" => ["type" => "boolean", "description" => "Product is halal certified"],
                                                "cert_is_kosher" => ["type" => "boolean", "description" => "Product is kosher certified"],
                                                "cert_is_gluten_free" => ["type" => "boolean", "description" => "Product is certified gluten-free"],
                                                "cert_is_non_gmo" => ["type" => "boolean", "description" => "Product is certified non-GMO"],
                                                "cert_is_fair_trade" => ["type" => "boolean", "description" => "Product is fair trade certified"]
                                            ]
                                        ],

                                        // trace
                                        "trace" => [
                                            "type" => "object",
                                            "properties" => [
                                               // Traceability & Recall
                                                "trace_gln" => ["type" => "string", "description" => "Global Location Number (13-digit)"],
                                                "trace_system" => ["type" => "string", "description" => "Traceability system in use"],
                                                "trace_documentation_required" => ["type" => "boolean", "description" => "Whether trace docs are required"],
                                                "trace_recall_procedure" => ["type" => "string", "description" => "Product recall procedure details"]
                                            ]
                                        ],

                                        // Shelf Life & Dating
                                        "best_before_days" => ["type" => "number","multipleOf" => 0.01, "description" => "Best before period in days from manufacture"],
                                        "use_by_days" => ["type" => "number","multipleOf" => 0.01, "description" => "Use by period in days from manufacture"],
                                        "handling_instructions" => ["type" => "string", "description" => "Product handling instructions"],
                                        "disposal_instructions" => ["type" => "string", "description" => "Disposal/waste instructions"],
                                        "shelf_life_type" => ["type" => "string", "description" => "Shelf life basis (e.g., unopened/ambient)"],
                                        "shelf_life_value" => ["type" => "integer", "description" => "Shelf life numeric value."],
                                        "shelf_life_unit" => ["type" => "string", "description" => "Shelf life unit (days/weeks/months)"],
                                        
                                        //Heavy metals 
                                        "chem_metal_lead" => ["type" => "string", "description" => "Measured concentration of lead in the sample"],
                                        "chem_metal_mercury" => ["type" => "string", "description" => "Measured concentration of mercury in the sample."],
                                        "chem_metal_cadmium" => ["type" => "string", "description" => "Measured concentration of cadmium in the sample."],
                                        "chem_metal_arsenic" => ["type" => "string", "description" => "Measured concentration of arsenic in the sample."],
                                        "chem_metal_tin" => ["type" => "string", "description" => "Tin content test results"],
                                        "chem_pest_chlorpyrifos" => ["type" => "string", "description" =>"Measured concentration of chlorpyrifos residues in the sample."],
                                        "chem_pest_glyphosate" => ["type" => "string", "description" => "Measured concentration of glyphosate residues in the sample."],
                                        "atrazine" => ["type" => "string", "description" => "Measured concentration of atrazine residues in the sample."],
                                        "chem_pest_malathion" => ["type" => "string", "description" => "Malathion residue test results"],
                                        "chem_pest_permethrin" => ["type" => "string", "description" => "Permethrin residue test results"],
                                        "chem_pest_imazalil" => ["type" => "string", "description" => "Imazalil residue test results"],
                                        "chem_mycotoxin_aflatoxin_b1" => ["type" => "string", "description" => "Measured concentration of aflatoxin B1 in the sample."],
                                        "chem_mycotoxin_aflatoxin_total" => ["type" => "string", "description" => "Total aflatoxins (B1+B2+G1+G2) test results"],
                                        "chem_mycotoxin_ochratoxin_a" => ["type" => "string", "description" => "Measured concentration of ochratoxin A in the sample."],
                                        "chem_mycotoxin_deoxynivalenol" => ["type" => "string","description" =>"Measured concentration of deoxynivalenol (DON) in the sample."],
                                        "chem_mycotoxin_zearalenone" => ["type" => "string", "description" => "Measured concentration of zearalenone in the sample."],
                                        "chem_mycotoxin_patulin" => ["type" => "string", "description" => "Patulin test results"],
                                        "chem_pres_sodium_benzoate" => ["type" => "string", "description" => "Measured concentration of sodium benzoate additive."],
                                        "chem_pres_potassium_sorbate" => ["type" => "string", "description" => "Measured concentration of potassium sorbate preservative."],
                                        "chem_pres_sodium_nitrite" =>["type" => "string", "description" => "Sodium nitrite conten"],
                                        "chem_pres_sodium_metabisulfite" => ["type" => "string", "description" => "Sodium metabisulfite content"],
                                        "chem_pres_sulfur_dioxide" => ["type" => "string", "description" => "Sulfur dioxide/sulfites content"],
                                        "chem_pres_calcium_propionate" => ["type" => "string", "description" => "Calcium propionate content"],
                                        "chem_add_tartrazine" => ["type" => "string", "description" => "Tartrazine content"],
                                        "chem_add_sunset_yellow" => ["type" => "string", "description" => "Sunset Yellow FCF content"],
                                        "chem_add_citric_acid" => ["type" => "string", "description" => "Citric acid content content"],
                                        "chem_add_ascorbic_acid" => ["type" => "string", "description" => "Ascorbic acid (Vitamin C) content"],
                                        "chem_add_cochineal" => ["type" => "string", "description" => "Cochineal content"],
                                        "chem_add_monosodium_glutamate" => ["type" => "string", "description" => "Measured concentration of monosodium glutamate (MSG)"],
                                    ],
                                    "required" => ["spec_name", "spec_type"]
                                ];
    private $systemPrompt;
    private $uploadPrompt;
    private $textPrompt;
    private $auditsystemPrompt;
    private $audituserPrompt;
    private $extract_pdf_model;
    private $rolextract_text_modele_id;
    private $audit_summary_model;
    
    protected FSANZMappingService $mappingService;

    // private $extractionSchema;
    private $numericFields = [
        // nutritional
        'nutr_serving_size_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_servings_per_container' => ['type' => 'decimal', 'precision' => 2],
        'nutr_energy_kj' => ['type' => 'decimal', 'precision' => 2],
        'nutr_protein_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_carbohydrate_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_sodium_mg' => ['type' => 'decimal', 'precision' => 2],
        'nutr_fat_total_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_fat_saturated_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_fat_trans_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_sugars_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_added_sugars_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_dietary_fiber_g' => ['type' => 'decimal', 'precision' => 2],
        'nutr_cholesterol_mg' => ['type' => 'decimal', 'precision' => 2],
        'nutr_calcium_mg' => ['type' => 'decimal', 'precision' => 2],
        'nutr_iron_mg' => ['type' => 'decimal', 'precision' => 2],
        'nutr_potassium_mg' => ['type' => 'decimal', 'precision' => 2],
        'nutr_vitamin_d_mcg' => ['type' => 'decimal', 'precision' => 2],

        // physical
        'phys_density_g_ml' => ['type' => 'decimal', 'precision' => 2],
        'phys_specific_gravity' => ['type' => 'decimal', 'precision' => 2],
        'phys_moisture_percent' => ['type' => 'decimal', 'precision' => 2],
        'phys_ph_level' => ['type' => 'decimal', 'precision' => 2],
        'phys_water_activity' => ['type' => 'decimal', 'precision' => 2],

        // packaging weights / counts
        'pack_primary_weight_g' => ['type' => 'decimal', 'precision' => 2],
        'pack_units_per_secondary' => ['type' => 'decimal', 'precision' => 2],
        'pack_case_weight_g' => ['type' => 'decimal', 'precision' => 2],
        'pack_units_per_case' => ['type' => 'decimal', 'precision' => 2],
        'pack_pallet_height_mm' => ['type' => 'decimal', 'precision' => 2],
        'pack_pallet_weight_kg' => ['type' => 'decimal', 'precision' => 2],

        // packaging ints
        'pack_cases_per_layer' => ['type' => 'integer', 'precision' => 0],
        'pack_layers_per_pallet' => ['type' => 'integer', 'precision' => 0],
        'pack_total_cases_per_pallet' => ['type' => 'integer', 'precision' => 0],

        // storage / shelf
        'storage_temp_min_c' => ['type' => 'decimal', 'precision' => 2],
        'storage_temp_max_c' => ['type' => 'decimal', 'precision' => 2],
        'storage_humidity_min_percent' => ['type' => 'decimal', 'precision' => 2],
        'storage_humidity_max_percent' => ['type' => 'decimal', 'precision' => 2],

        // COOL percentage (higher precision)
        'cool_percentage_australia' => ['type' => 'decimal', 'precision' => 4],

        //Shelf life
        'shelf_life_value' => ['type' => 'integer', 'precision' => 0],
        'best_before_days' => ['type' => 'integer', 'precision' => 0],
        'use_by_days' => ['type' => 'integer', 'precision' => 0],
        'cool_percentage_australia' => ['type' => 'integer', 'precision' => 0],

    ];

    public function __construct(FSANZMappingService $mappingService)
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');

        $prompts = AIPrompt::first();
        $this->systemPrompt = $prompts->system_prompt ?? '';
        $this->uploadPrompt = $prompts->upload_user_prompt ?? '';
        $this->textPrompt = $prompts->text_user_prompt ?? '';
        // $this->extractionSchema = $prompts->extraction_schema ??[];
        $this->auditsystemPrompt = $prompts->audit_system_prompt ?? '';
        $this->audituserPrompt = $prompts->audit_user_prompt ?? '';

        $this->extract_pdf_model = $prompts->ai_extract_pdf ?? '';
        $this->rolextract_text_model = $prompts->ai_extract_text ?? '';
        $this->audit_summary_model = $prompts->audit_summary ?? '';

        $this->mappingService = $mappingService;

    }

    public function specifications(Request $request)
    {
        $specifications = Specification::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->latest('updated_at')->get();
        foreach ($specifications as $spec) {
            // get all archives for this spec
            $archives = SpecificationArchieve::where('spec_id', $spec->id)
                        ->select('id as archive_id','spec_name as archive_name','version as archive_version')
                        ->latest('updated_at')
                        ->get()
                        ->map(function($item) use ($spec) {  
                            return [
                                'archive_id' => $item->archive_id,
                                'archive_name'  => $item->archive_name,
                                'archive_version'  => $item->archive_version,
                            ];
                        });
            // add archives to spec attributes
            $spec->version = $archives;
        }
        $clientID = $this->clientID;
        return view('backend.batchbase_agent.specifications', compact('clientID','specifications'));
    } 

    public function add_specifications(Request $request)
    {
        $clientID = $this->clientID;
        return view('backend.batchbase_agent.add_specifications', compact('clientID'));
    }

    public function add_manual(Request $request)
    {
        $clientID = $this->clientID;
        return view('backend.batchbase_agent.add_manual', compact('clientID'));
    }

    public function preview(Request $request)
    {
        try {
            $content = null;
            if (isset($_FILES['image_file']) && !empty($_FILES['image_file']['name'])) {
                $request->validate([
                    'image_file' => 'required|mimes:pdf|max:20480', // up to 20 MB
                ]);

                $filepath = "assets/{$this->clientID}/{$this->ws_id}/specification";
                $image_response = upload_single_files($_FILES['image_file'], $filepath);

                if ($image_response['status'] == false) {
                    return response()->json([
                        'status' => false,
                        'message' => $image_response['message']
                    ]);
                }

                $uploadedFile = "{$filepath}/{$image_response['final_array']['name']}";
                $UploadResponse = $this->process_uploadfile($uploadedFile);
                if ($UploadResponse['status']) {
                    $response = [
                        'status' => true,
                        'content' => $UploadResponse['content'],
                        'type' => 1,
                        'file_name' => $image_response['final_array']['name']
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'content' => $UploadResponse['message']
                    ];
                }
            } elseif ($request->filled('parse_content')) {
                $content = $request->parse_content;
                $AiResponse = $this->AI_field_mapping($content);
                if ($AiResponse['status']) {
                    $response = [
                        'status' => true,
                        'content' => $AiResponse['content'],
                        'type' => 0,
                        'file_name' => ""
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'content' => $AiResponse['message']
                    ];
                }
            }
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function process_uploadfile($filePath)
    {
        try {
            $client = OpenAI::client(env('OPENAI_API_KEY'));
            $filePath = public_path($filePath);

            if (!file_exists($filePath)) {
                throw new \Exception("File not found at: $filePath");
            }

            // 1️⃣ Upload file to OpenAI
            $fileUpload = $client->files()->upload([
                'purpose' => 'assistants',
                'file' => fopen($filePath, 'r'),
            ]);

            $fileId = $fileUpload->id;

            // 2️⃣ Request structured extraction
            $response = $client->chat()->create([
                'model' => $this->extract_pdf_model,
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'food_spec_extraction',
                        'schema' => $this->extractionSchema,
                    ],
                ],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $this->uploadPrompt,
                            ],
                            [
                                'type' => 'file',
                                'file' => ['file_id' => $fileId],
                            ],
                        ],
                    ],
                ],
            ]);

            // 3️⃣ Decode AI JSON output
            $rawContent = $response->choices[0]->message->content ?? '';
            $data = json_decode($rawContent, true);

            // 4️⃣ Cleanup uploaded file from OpenAI
            try {
                $client->files()->delete($fileId);
            } catch (\Exception $cleanupError) {
                \Log::warning("OpenAI file cleanup failed: " . $cleanupError->getMessage());
            }

            // Return as plain array (NOT JsonResponse)
            return [
                'status' => true,
                'content' => $data
            ];
        } catch (\Exception $e) {
            // Attempt cleanup even if main process fails
            if (isset($fileId)) {
                try {
                    $client->files()->delete($fileId);
                } catch (\Exception $cleanupError) {
                    \Log::warning("OpenAI file cleanup failed after exception: " . $cleanupError->getMessage());
                }
            }

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function AI_field_mapping($content){
        try {
            $userMessage = "{$this->textPrompt}:\n\n" . $content;
            $client = OpenAI::client(env('OPENAI_API_KEY'));
            $response = $client->chat()->create([
                'model' => $this->rolextract_text_model,
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'food_spec_extraction',
                        'schema' => $this->extractionSchema
                    ]
                ],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);
            $data = json_decode($response->choices[0]->message->content, true);

            $response = [
                'status' => true,
                'content' => $data
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
        return $response;
    }

    public function store(Request $request){
        try {
            $validated = $request->validate(Specification::rules());
            $aiData = json_decode($request->json_object, true);
            $validated['json_object'] = json_encode($aiData);
            $validated['client_id'] = $this->clientID;
            $validated['workspace_id'] = $this->ws_id;
            $validated['cool_percentage_australia'] = $validated['cool_percentage_australia'] / 100;
            $specification = Specification::create($validated);

            if (isset($_FILES['image_file']) && !empty($_FILES['image_file']['name'])) {
                $filepath = "assets/{$this->clientID}/{$this->ws_id}/specification_images/{$specification->id}";
                $image_response = upload_multiple_files($_FILES['image_file'], $filepath);

                if ($image_response['status'] == false) {
                    return response()->json([
                        'status' => false,
                        'message' => $image_response['message']
                    ]);
                }

                if ($image_response['status'] == true) {
                    $imageArray = $image_response['final_array'];
                    $defaultImage = ($request->input('default_image'))? (int)$request->input('default_image') : 1;
                    $this->insert_product_images($specification->spec_sku, $filepath, $imageArray, $defaultImage, $specification->id);
                    $specification->update(['spec_image' => $defaultImage]);
                }
            }

            $response = [
                'status' => true,
                'message' => "Specification Added.",
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                    'status' => false,
                    'errors' => $e->errors()
                ], 422);
        }
        return response()->json($response);
    }

    private function insert_product_images($sku, $filepath, $imageArray, $defaultImage, $specId)
    {
        foreach ($imageArray as $key => $value) {
            $item = new image_library;
            $item->SKU = $sku;
            $item->module = "specification";
            $item->module_id = $specId;
            $item->image_number = ++$key;
            $item->image_name = $value['name'];
            if(sizeof($imageArray) == 1){
                $item->default_image = true;
            }else{
                $item->default_image = ((int)$key == $defaultImage) ? true : false;
            }
            $item->file_format = $value['type'];
            $item->file_size = $value['size'];
            $item->uploaded_by = $this->user_id;
            $item->last_modified_by = $this->user_id;
            $item->folder_path = $filepath;
            $item->save();
        }
    }

    public function edit(Request $request, Specification $specification)
    {
        $specification->cool_percentage_australia = $specification->cool_percentage_australia? $specification->cool_percentage_australia *100:'';
        $clientID = $this->clientID;
        return view('backend.batchbase_agent.edit', compact('clientID','specification'));
    }

    public function update(Request $request, Specification $specification){
        try {
            $validated = $request->validate(Specification::rules($specification->id));
            $specData = Arr::except($specification->toArray(), [
                        'id','client_id','workspace_id','created_at','updated_at'
                        ]);
            $specData['spec_id'] = $specification->id;            
            $specData['version'] = (SpecificationArchieve::where('spec_id', $specification->id)->max('version') ?? 0) + 1;
            $specData['modified_by'] = $this->user_id;
            SpecificationArchieve::create($specData);

            $validated['cool_percentage_australia'] = $validated['cool_percentage_australia'] / 100;
            $specification->update($validated);

            if (isset($_FILES['image_file']) && !empty($_FILES['image_file']['name'])) {
                $filepath = "assets/{$this->clientID}/{$this->ws_id}/specification_images/{$specification->id}";
                $image_response = upload_multiple_files($_FILES['image_file'], $filepath);

                if ($image_response['status'] == false) {
                    return response()->json([
                        'status' => false,
                        'message' => $image_response['message']
                    ]);
                }

                if ($image_response['status'] == true) {
                    $imageArray = $image_response['final_array'];
                    $defaultImage = ($request->input('default_image'))? (int)$request->input('default_image') : 1;
                    $this->insert_product_images($specification->spec_sku, $filepath, $imageArray, $defaultImage, $specification->id);
                    $specification->update(['spec_image' => $defaultImage]);
                }
            }
            
            $response = [
                'status' => true,
                'message' => "Specification Updated.",
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                    'status' => false,
                    'errors' => $e->errors()
                ], 422);
        }
        return response()->json($response);
    }

    public function delete(Request $request, Specification $specification)
    {
        try {
            if ($specification->archive == 0) {
                $specification->update(['archive' => 1]);
                $message = 'Specification moved to archive status';
            } else {
                $message = 'Specification deleted successfully';

                if ($specification) {
                    if ($specification->spec_upload_type == 1) {
                        $filepath = "assets/{$specification->client_id}/{$specification->workspace_id}/specification";
                        $filename = $specification->file_name;
                        $imgResponse = single_image_remove($filepath, $filename);

                        if ($imgResponse === "success") {
                            $specification->delete();
                        } else {
                            $message = $imgResponse;
                        }
                    } else {
                        $specification->delete();
                    }
                }
            }

            $response = [
                'status' => true,
                'message' => $message
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            $response = [
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $e->errors(),
            ];
        }

        return response()->json($response);
    }

    public function bulk_delete(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $specificationArray = json_decode($request->input('specificationobj'));
            if($archiveVal == "all" || $archiveVal == "0"){
                foreach ($specificationArray as $key => $value) {
                    Specification::where('id',$value)->update(['archive'=> 1]);
                }
                return response()->json([
                    'status' => true,
                    'message' => "Archived all selected items"
                ]);
            }

            foreach ($specificationArray as $key => $value) {
                $specification = Specification::where('id', $value)->first();
                if ($specification) {
                    if($specification->spec_upload_type == 1){
                        $filepath = "assets/{$specification->client_id}/{$specification->workspace_id}/specification";
                        $filename = $specification->file_name;
                        $imgResponse = single_image_remove($filepath,$filename);
                        if($imgResponse == "success"){
                            $specification->delete();
                        }else{
                            continue;
                        }
                    }else{
                        $specification->delete();
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Specification delete successfully"
            ]);

        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function unarchive(Specification $specification)
    {
        try {
            $specification->update(['archive' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Specification unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function show_import()
    {
        return view('backend.batchbase_agent.import-form');
    }

    public function download_template()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'spec_name' => 'Spec Name',
                'spec_sku' => 'Spec Sku',
                'spec_type' => 'Spec Type',
                'spec_status' => 'Status',
                'aus_regulatory_status' => 'Australian Regulatory Status',
                'description' => 'Description',
                'supplier_name' => 'Supplier Name',
                'manufacturer_name' => 'Manufacturer Name',
                'manufacturer_address' => 'Manufacturer Address',
                'manufacturer_contact' => 'Manufacturer Contact',
                'distributor_name' => 'Distributor Name',
                'distributor_contact' => 'Distributor Contact',
                'compliance_officer' => 'Compliance Officer',
                'lot_number_format' => 'Lot Number Format',
                'nutr_serving_size_g' => 'Serving Size (g)',
                'nutr_servings_per_container' => 'Servings per Container',
                'nutritional_basis' => 'Nutrition Basis',
                'nutr_energy_kj' => 'Energy (kJ)',
                'nutr_protein_g' => 'Protein (g)',
                'nutr_carbohydrate_g' => 'Carbohydrate (g)',
                'nutr_sodium_mg' => 'Sodium (mg)',
                'nutr_fat_total_g' => 'Total Fat (g)',
                'nutr_fat_saturated_g' => 'Saturated Fat (g)',
                'nutr_fat_trans_g' => 'Trans Fat (g)',
                'nutr_sugars_g' => 'Sugars (g)',
                'nutr_added_sugars_g' => 'Added Sugars (g)',
                'nutr_dietary_fiber_g' => 'Dietary Fibre (g)',
                'nutr_cholesterol_mg' => 'Cholesterol (mg)',
                'nutr_calcium_mg' => 'Calcium (mg)',
                'nutr_iron_mg' => 'Iron (mg)',
                'nutr_potassium_mg' => 'Potassium (mg)',
                'nutr_vitamin_d_mcg' => 'Vitamin D (mcg)',
                'nutr_gluten_content' => 'Gluten Content',
                'cool_primary_country' => 'Primary Country of Origin',
                'cool_origin_declaration' => 'Origin Declaration',
                'cool_percentage_australia' => 'Australian Content (%)',
                'cool_fsanz_standard_ref' => 'FSANZ Standard Reference',
                'cool_date_marking_requirement' => 'Date Marking Requirement',
                'cool_label_type' => 'Label Type',
                'cool_calculation_method' => 'Calculation Method',
                'cool_aus_made_claim' => 'Australian Made',
                'cool_aus_owned_claim' => 'Australian Owned',
                'cool_aus_grown_claim' => 'Australian Grown',
                'ing_ingredient_list' => 'Ingredient List',
                'allergen_statement' => 'Allergen Statement',
                'allergen_fsanz_declaration' => 'FSANZ Allergen Declaration',
                'ing_percentage_labelling' => 'Percentage Labelling',
                'phys_appearance' => 'Appearance',
                'phys_color' => 'Colour',
                'phys_odor' => 'Odour',
                'phys_texture' => 'Texture',
                'phys_density_g_ml' => 'Density (g/mL)',
                'phys_specific_gravity' => 'Specific Gravity',
                'phys_moisture_percent' => 'Moisture (%)',
                'phys_ph_level' => 'pH Level',
                'phys_water_activity' => 'Water Activity (aw)',
                'phys_viscosity_cps' => 'Viscosity (cP)',
                'micro_total_plate_count_cfu_g_max' => 'Aerobic Plate Count Max',
                'micro_yeast_mold_cfu_g_max' => 'Yeast & Mould Max',
                'micro_coliforms_cfu_g_max' => 'Coliforms Max',
                'micro_e_coli_cfu_g_max' => 'E. coli Max',
                'micro_salmonella_absent_in_g' => 'Salmonella Absent in (g)',
                'micro_listeria_absent_in_g' => 'Listeria Absent in (g)',
                'micro_staphylococcus_cfu_g_max' => 'Staphylococcus Max',
                'pack_primary_type' => 'Primary Pack Type',
                'pack_primary_material' => 'Primary Pack Material',
                'pack_primary_dimensions_mm' => 'Primary Pack Dimensions (mm)',
                'pack_primary_weight_g' => 'Primary Pack Weight (g)',
                'pack_secondary_type' => 'Secondary Pack Type',
                'pack_secondary_material' => 'Secondary Pack Material',
                'pack_secondary_dimensions_mm' => 'Secondary Pack Dimensions (mm)',
                'pack_units_per_secondary' => 'Units per Secondary',
                'pack_case_dimensions_mm' => 'Case Dimensions (mm)',
                'pack_case_weight_g' => 'Case Weight (g)',
                'pack_units_per_case' => 'Units per Case',
                'pack_pallet_type' => 'Pallet Type',
                'pack_pallet_dimensions_mm' => 'Pallet Dimensions (mm)',
                'pack_pallet_height_mm' => 'Pallet Height (mm)',
                'pack_pallet_weight_kg' => 'Pallet Weight (kg)',
                'pack_cases_per_layer' => 'Cases per Layer (Ti)',
                'pack_layers_per_pallet' => 'Layers per Pallet (Hi)',
                'pack_total_cases_per_pallet' => 'Total Cases per Pallet',
                'id_gtin_13' => 'GTIN-13 (Retail)',
                'id_gtin_14' => 'GTIN-14 (Case)',
                'id_sscc' => 'SSCC (Logistics)',
                'id_batch_code_format' => 'Batch Code Format',
                'id_barcode_type' => 'Barcode Type',
                'aus_advisory_statements' => 'Advisory Statements',
                'aus_warning_statements' => 'Warning Statements',
                'aus_health_claims' => 'Health Claims',
                'aus_nutrition_content_claims' => 'Nutrition Content Claims',
                'cert_is_organic' => 'Organic (Certified)',
                'cert_is_halal' => 'Halal (Certified)',
                'cert_is_kosher' => 'Kosher (Certified)',
                'cert_is_gluten_free' => 'Gluten Free (Certified)',
                'cert_is_non_gmo' => 'Non-GMO',
                'cert_is_fair_trade' => 'Fair Trade',
                'cert_certificate_details' => 'Certificate Details',
                'storage_temp_min_c' => 'Storage Temp Min (C)',
                'storage_temp_max_c' => 'Storage Temp Max (C)',
                'storage_humidity_min_percent' => 'Storage RH Min (%)',
                'storage_humidity_max_percent' => 'Storage RH Max (%)',
                'storage_conditions' => 'Storage Conditions',
                'shelf_life_type' => 'Shelf Life Type',
                'shelf_life_value' => 'Shelf Life Value',
                'shelf_life_unit' => 'Shelf Life Unit',
                'best_before_days' => 'Best Before Days',
                'use_by_days' => 'Use By Days',
                'handling_instructions' => 'Handling Instructions',
                'disposal_instructions' => 'Disposal Instructions',
                'trace_gln' => 'GLN',
                'trace_system' => 'Traceability System',
                'trace_recall_procedure' => 'Recall Procedure',
                'trace_document_required' => 'Trace Documents Required',
                'chem_metal_lead' => 'Lead (Pb)',
                'chem_metal_cadmium' => 'Cadmium (Cd)',
                'chem_metal_mercury' => 'Mercury (Hg)',
                'chem_metal_arsenic' => 'Arsenic (As)',
                'chem_metal_tin' => 'Tin (Sn)',
                'chem_pest_glyphosate' => 'Glyphosate',
                'chem_pest_chlorpyrifos' => 'Chlorpyrifos',
                'chem_pest_malathion' => 'Malathion',
                'chem_pest_permethrin' => 'Permethrin',
                'chem_pest_imazalil' => 'Imazalil',
                'chem_pesticide_residues' => 'Residues',
                'chem_mycotoxin_aflatoxin_b1' => 'Aflatoxin B1',
                'chem_mycotoxin_aflatoxin_total' => 'Aflatoxin Total',
                'chem_mycotoxin_ochratoxin_a' => 'Ochratoxin A',
                'chem_mycotoxin_deoxynivalenol' => 'Deoxynivalenol (DON)',
                'chem_mycotoxin_zearalenone' => 'Zearalenone',
                'chem_mycotoxin_patulin' => 'Patulin',
                'chem_add_tartrazine' => 'Tartrazine',
                'chem_add_cochineal' => 'Cochineal',
                'chem_add_sunset_yellow' => 'Sunset Yellow',
                'chem_add_citric_acid' => 'Citric Acid',
                'chem_add_ascorbic_acid' => 'Ascorbic Acid',
                'chem_add_monosodium_glutamate' => 'Monosodium Glutamate (MSG)',
                'chem_pres_sodium_benzoate' => 'Sodium Benzoate',
                'chem_pres_potassium_sorbate' => 'Potassium Sorbate',
                'chem_pres_calcium_propionate' => 'Calcium Propionate',
                'chem_pres_sulfur_dioxide' => 'Sulfur Dioxide',
                'chem_pres_sodium_nitrite' => 'Sodium Nitrite',
                'chem_pres_sodium_metabisulfite' => 'Sodium Metabisulfite'
            ];

            // Write headers
            $col = 1;
            foreach ($headers as $key => $header) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($col) . '1';
                $sheet->setCellValue($cellCoordinate, $header);

                // Style headers
                $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
                $sheet->getStyle($cellCoordinate)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E0E0E0');

                // Set column width
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                $col++;
            }

            $unitColumn = array_search('spec_type', array_keys($headers)) + 1;
            $unitColLetter = Coordinate::stringFromColumnIndex($unitColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($unitColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Raw Material,Finished Product,Packaging Material"');
            }

            $nbColumn = array_search('nutritional_basis', array_keys($headers)) + 1;
            $nbColLetter = Coordinate::stringFromColumnIndex($nbColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($nbColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Per 100g,Per 100ml"');
            }

            $bcColumn = array_search('id_barcode_type', array_keys($headers)) + 1;
            $bcColLetter = Coordinate::stringFromColumnIndex($bcColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($bcColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"1D,2D,QR Type"');
            }


            $ausmadeColumn = array_search('cool_aus_made_claim', array_keys($headers)) + 1;
            $ausmadeColLetter = Coordinate::stringFromColumnIndex($ausmadeColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($ausmadeColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $ausgrownColumn = array_search('cool_aus_grown_claim', array_keys($headers)) + 1;
            $ausgrownColLetter = Coordinate::stringFromColumnIndex($ausgrownColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($ausgrownColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $ausownColumn = array_search('cool_aus_owned_claim', array_keys($headers)) + 1;
            $ausownColLetter = Coordinate::stringFromColumnIndex($ausownColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($ausownColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }


            $isorgColumn = array_search('cert_is_organic', array_keys($headers)) + 1;
            $isorgColLetter = Coordinate::stringFromColumnIndex($isorgColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($isorgColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $ishalalColumn = array_search('cert_is_halal', array_keys($headers)) + 1;
            $ishalalColLetter = Coordinate::stringFromColumnIndex($ishalalColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($ishalalColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $iskosColumn = array_search('cert_is_kosher', array_keys($headers)) + 1;
            $iskosColLetter = Coordinate::stringFromColumnIndex($iskosColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($iskosColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $isgluColumn = array_search('cert_is_gluten_free', array_keys($headers)) + 1;
            $isgluColLetter = Coordinate::stringFromColumnIndex($isgluColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($isgluColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $isgmoColumn = array_search('cert_is_non_gmo', array_keys($headers)) + 1;
            $isgmoColLetter = Coordinate::stringFromColumnIndex($isgmoColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($isgmoColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $isftColumn = array_search('cert_is_fair_trade', array_keys($headers)) + 1;
            $isftColLetter = Coordinate::stringFromColumnIndex($isftColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($isftColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }

            $istraceColumn = array_search('trace_document_required', array_keys($headers)) + 1;
            $istraceColLetter = Coordinate::stringFromColumnIndex($istraceColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($istraceColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }
            
            // Add numeric validation for numeric fields
            foreach ($this->numericFields as $field => $config) {
                $col = array_search($field, array_keys($headers)) + 1;
                if ($col) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    for ($row = 2; $row <= 1000; $row++) {
                        $validation = $sheet->getCell($colLetter . $row)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_DECIMAL)
                            ->setErrorStyle(DataValidation::STYLE_STOP)
                            ->setAllowBlank(true)
                            ->setShowInputMessage(true)
                            ->setShowErrorMessage(true)
                            ->setErrorTitle('Invalid Value')
                            ->setError('Please enter a numeric value')
                            ->setPromptTitle('Numeric Value')
                            ->setPrompt("Enter a number with up to {$config['precision']} decimal places");
                    }
                }
            }

            // Freeze the header row
            $sheet->freezePane('A2');
            // Set auto filter
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->setAutoFilter("A1:{$lastColumn}1");
            // Create the Excel file
            $writer = new Xlsx($spreadsheet);
            // Set more explicit headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Specification_template.xlsx"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            // Save with explicit options
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Template download failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function upload_preview(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        try {
            $file = $request->file('file');
            // Explicitly check file existence and type
            if (!$file->isValid()) {
                throw new \Exception('Invalid file upload');
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls'])) {
                throw new \Exception('Invalid file type. Only XLSX and XLS files are allowed.');
            }
            // Explicitly specify the reader type based on extension
            $readerType = $extension === 'xlsx' ? ExcelType::XLSX : ExcelType::XLS;
            $data = Excel::toArray([], $file->getRealPath(), null, $readerType)[0];
            if (empty($data) || count($data) <= 1) {
                return response()->json([
                    'success' => false,
                    'error' => 'No data found in the uploaded file.'
                ], 400);
            }

            // Remove headers
            $headers = array_shift($data);
            $dbHeaders = $this->convertHeadersToDbColumns($headers);

            // Filter out rows that are completely empty (all `null` values)
            $data = array_filter($data, function ($row) {
                return array_filter($row) !== [];
            });

            // If no rows remain after filtering, return "No data found" error
            if (empty($data)) {
                return response()->json(['success' => false, 'error' => 'No data found in the uploaded file.'], 400);
            }
 
            $mappedData = [];
            foreach ($data as $row) {
                $mappedRow = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $mappedRow[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }
                $mappedData[] = $mappedRow;
            }
            $errors = $this->validateData($mappedData);
            return response()->json(['success' => true, 'data' => $mappedData, 'errors' => $errors]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function convertHeadersToDbColumns($headers)
    {
        $headerMap = [
            'Spec Name' => 'spec_name',
            'Spec Sku' => 'spec_sku',
            'Spec Type' => 'spec_type',
            'Status' => 'spec_status',
            'Australian Regulatory Status' => 'aus_regulatory_status',
            'Description' => 'description',
            'Supplier Name' => 'supplier_name',
            'Manufacturer Name' => 'manufacturer_name',
            'Manufacturer Address' => 'manufacturer_address',
            'Manufacturer Contact' => 'manufacturer_contact',
            'Distributor Name' => 'distributor_name',
            'Distributor Contact' => 'distributor_contact',
            'Compliance Officer' => 'compliance_officer',
            'Lot Number Format' => 'lot_number_format',
            'Serving Size (g)' => 'nutr_serving_size_g',
            'Servings per Container' => 'nutr_servings_per_container',
            'Nutrition Basis' => 'nutritional_basis',
            'Energy (kJ)' => 'nutr_energy_kj',
            'Protein (g)' => 'nutr_protein_g',
            'Carbohydrate (g)' => 'nutr_carbohydrate_g',
            'Sodium (mg)' => 'nutr_sodium_mg',
            'Total Fat (g)' => 'nutr_fat_total_g',
            'Saturated Fat (g)' => 'nutr_fat_saturated_g',
            'Trans Fat (g)' => 'nutr_fat_trans_g',
            'Sugars (g)' => 'nutr_sugars_g',
            'Added Sugars (g)' => 'nutr_added_sugars_g',
            'Dietary Fibre (g)' => 'nutr_dietary_fiber_g',
            'Cholesterol (mg)' => 'nutr_cholesterol_mg',
            'Calcium (mg)' => 'nutr_calcium_mg',
            'Iron (mg)' => 'nutr_iron_mg',
            'Potassium (mg)' => 'nutr_potassium_mg',
            'Vitamin D (mcg)' => 'nutr_vitamin_d_mcg',
            'Gluten Content' => 'nutr_gluten_content',
            'Primary Country of Origin' => 'cool_primary_country',
            'Origin Declaration' => 'cool_origin_declaration',
            'Australian Content (%)' => 'cool_percentage_australia',
            'FSANZ Standard Reference' => 'cool_fsanz_standard_ref',
            'Date Marking Requirement' => 'cool_date_marking_requirement',
            'Label Type' => 'cool_label_type',
            'Calculation Method' => 'cool_calculation_method',
            'Australian Made' => 'cool_aus_made_claim',
            'Australian Owned' => 'cool_aus_owned_claim',
            'Australian Grown' => 'cool_aus_grown_claim',
            'Ingredient List' => 'ing_ingredient_list',
            'Allergen Statement' => 'allergen_statement',
            'FSANZ Allergen Declaration' => 'allergen_fsanz_declaration',
            'Percentage Labelling' => 'ing_percentage_labelling',
            'Appearance' => 'phys_appearance',
            'Colour' => 'phys_color',
            'Odour' => 'phys_odor',
            'Texture' => 'phys_texture',
            'Density (g/mL)' => 'phys_density_g_ml',
            'Specific Gravity' => 'phys_specific_gravity',
            'Moisture (%)' => 'phys_moisture_percent',
            'pH Level' => 'phys_ph_level',
            'Water Activity (aw)' => 'phys_water_activity',
            'Viscosity (cP)' => 'phys_viscosity_cps',
            'Aerobic Plate Count Max' => 'micro_total_plate_count_cfu_g_max',
            'Yeast & Mould Max' => 'micro_yeast_mold_cfu_g_max',
            'Coliforms Max' => 'micro_coliforms_cfu_g_max',
            'E. coli Max' => 'micro_e_coli_cfu_g_max',
            'Salmonella Absent in (g)' => 'micro_salmonella_absent_in_g',
            'Listeria Absent in (g)' => 'micro_listeria_absent_in_g',
            'Staphylococcus Max' => 'micro_staphylococcus_cfu_g_max',
            'Primary Pack Type' => 'pack_primary_type',
            'Primary Pack Material' => 'pack_primary_material',
            'Primary Pack Dimensions (mm)' => 'pack_primary_dimensions_mm',
            'Primary Pack Weight (g)' => 'pack_primary_weight_g',
            'Secondary Pack Type' => 'pack_secondary_type',
            'Secondary Pack Material' => 'pack_secondary_material',
            'Secondary Pack Dimensions (mm)' => 'pack_secondary_dimensions_mm',
            'Units per Secondary' => 'pack_units_per_secondary',
            'Case Dimensions (mm)' => 'pack_case_dimensions_mm',
            'Case Weight (g)' => 'pack_case_weight_g',
            'Units per Case' => 'pack_units_per_case',
            'Pallet Type' => 'pack_pallet_type',
            'Pallet Dimensions (mm)' => 'pack_pallet_dimensions_mm',
            'Pallet Height (mm)' => 'pack_pallet_height_mm',
            'Pallet Weight (kg)' => 'pack_pallet_weight_kg',
            'Cases per Layer (Ti)' => 'pack_cases_per_layer',
            'Layers per Pallet (Hi)' => 'pack_layers_per_pallet',
            'Total Cases per Pallet' => 'pack_total_cases_per_pallet',
            'GTIN-13 (Retail)' => 'id_gtin_13',
            'GTIN-14 (Case)' => 'id_gtin_14',
            'SSCC (Logistics)' => 'id_sscc',
            'Batch Code Format' => 'id_batch_code_format',
            'Barcode Type' => 'id_barcode_type',
            'Advisory Statements' => 'aus_advisory_statements',
            'Warning Statements' => 'aus_warning_statements',
            'Health Claims' => 'aus_health_claims',
            'Nutrition Content Claims' => 'aus_nutrition_content_claims',
            'Organic (Certified)' => 'cert_is_organic',
            'Halal (Certified)' => 'cert_is_halal',
            'Kosher (Certified)' => 'cert_is_kosher',
            'Gluten Free (Certified)' => 'cert_is_gluten_free',
            'Non-GMO' => 'cert_is_non_gmo',
            'Fair Trade' => 'cert_is_fair_trade',
            'Certificate Details' => 'cert_certificate_details',
            'Storage Temp Min (C)' => 'storage_temp_min_c',
            'Storage Temp Max (C)' => 'storage_temp_max_c',
            'Storage RH Min (%)' => 'storage_humidity_min_percent',
            'Storage RH Max (%)' => 'storage_humidity_max_percent',
            'Storage Conditions' => 'storage_conditions',
            'Shelf Life Type' => 'shelf_life_type',
            'Shelf Life Value' => 'shelf_life_value',
            'Shelf Life Unit' => 'shelf_life_unit',
            'Best Before Days' => 'best_before_days',
            'Use By Days' => 'use_by_days', 
            'Handling Instructions' => 'handling_instructions',
            'Disposal Instructions' => 'disposal_instructions',
            'GLN' => 'trace_gln',
            'Traceability System' => 'trace_system',
            'Recall Procedure' => 'trace_recall_procedure',
            'Trace Documents Required' => 'trace_document_required',
            'Lead (Pb)' => 'chem_metal_lead',
            'Cadmium (Cd)' => 'chem_metal_cadmium',
            'Mercury (Hg)' => 'chem_metal_mercury',
            'Arsenic (As)' => 'chem_metal_arsenic',
            'Tin (Sn)' => 'chem_metal_tin',
            'Glyphosate' => 'chem_pest_glyphosate',
            'Chlorpyrifos' => 'chem_pest_chlorpyrifos',
            'Malathion' => 'chem_pest_malathion',
            'Permethrin' => 'chem_pest_permethrin',
            'Imazalil' => 'chem_pest_imazalil',
            'Residues' => 'chem_pesticide_residues',
            'Aflatoxin B1' => 'chem_mycotoxin_aflatoxin_b1',
            'Aflatoxin Total' => 'chem_mycotoxin_aflatoxin_total',
            'Ochratoxin A' => 'chem_mycotoxin_ochratoxin_a',
            'Deoxynivalenol (DON)' => 'chem_mycotoxin_deoxynivalenol',
            'Zearalenone' => 'chem_mycotoxin_zearalenone',
            'Patulin' => 'chem_mycotoxin_patulin',
            'Tartrazine' => 'chem_add_tartrazine',
            'Cochineal' => 'chem_add_cochineal',
            'Sunset Yellow' => 'chem_add_sunset_yellow',
            'Citric Acid' => 'chem_add_citric_acid',
            'Ascorbic Acid' => 'chem_add_ascorbic_acid',
            'Monosodium Glutamate (MSG)' => 'chem_add_monosodium_glutamate',
            'Sodium Benzoate' => 'chem_pres_sodium_benzoate',
            'Potassium Sorbate' => 'chem_pres_potassium_sorbate',
            'Calcium Propionate' => 'chem_pres_calcium_propionate',
            'Sulfur Dioxide' => 'chem_pres_sulfur_dioxide',
            'Sodium Nitrite' => 'chem_pres_sodium_nitrite',
            'Sodium Metabisulfite' => 'chem_pres_sodium_metabisulfite'
        ];

        $dbHeaders = [];
        foreach ($headers as $index => $header) {
            if (isset($headerMap[trim($header)])) {
                $dbHeaders[$index] = $headerMap[trim($header)];
            }
        }
        return $dbHeaders;
    }

    private function formatValue($field, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle numeric fields
        if (isset($this->numericFields[$field])) {
            if (is_numeric($value)) {
                return round((float)$value, $this->numericFields[$field]['precision']);
            }
            return null;
        }

        // Handle text fields - trim and clean
        return is_string($value) ? trim($value) : $value;
    }

    private function validateData($data)
    {
        $errors = [];
        $specNameArray = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            if($row['spec_name']){
                if(in_array($row['spec_name'],$specNameArray)){
                    $errors[] = "Row {$rowNum}: {$row['spec_name']} Specification Name Duplicate";
                }else{
                    $specNameArray[] = $row['spec_name'];
                }
            }
            if($row['spec_name'] == null){
                $errors[] = "Row {$rowNum}: Specification Name field mandatory";
            }

            if($row['spec_sku'] == null){
                $errors[] = "Row {$rowNum}: Specification SKU field mandatory";
            }
            if($row['spec_type'] == null){
                $errors[] = "Row {$rowNum}: Specification Type field mandatory";
            }
            // Validate numeric fields
            foreach ($this->numericFields as $field => $config) {
                if (isset($row[$field]) && $row[$field] !== null && !is_numeric($row[$field])) {
                    $errors[] = "Row {$rowNum}: {$field} must be numeric";
                }
            }
        }
        return $errors;
    }

    public function store_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file, null, \Maatwebsite\Excel\Excel::XLSX)[0];

            // Check if the uploaded file is empty
            if (empty($data)) {
                return redirect()->back()->with('error', 'No data found in the uploaded file.');
            }


            $headers = array_shift($data);
            $dbHeaders = $this->convertHeadersToDbColumns($headers);

            DB::beginTransaction();
            $session = $request->session()->all();
            foreach ($data as $rowIndex => $row) {
                $specData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $specData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }

                // Skip if no SKU
                if (empty($specData['spec_name'])) {
                    continue;
                }

                // Normalize boolean "Yes/No" type fields safely
                $yesNoFields = [
                    'cool_aus_made_claim',
                    'cool_aus_owned_claim',
                    'cool_aus_grown_claim',
                    'trace_document_required',
                    'cert_is_organic',
                    'cert_is_halal',
                    'cert_is_kosher',
                    'cert_is_gluten_free',
                    'cert_is_non_gmo',
                    'cert_is_fair_trade',
                ];

                foreach ($yesNoFields as $field) {
                    $specData[$field] = isset($specData[$field]) && strtolower(trim($specData[$field])) === 'yes' ? 1 : 0;
                }

                if(!empty($specData['cool_percentage_australia'])){
                    $specData['cool_percentage_australia'] = $specData['cool_percentage_australia'] / 100;
                }


                if ( !empty($specData['spec_type'])) {
                    switch ($specData['spec_type']) {
                        case 'Raw Material':
                            $type = "raw_material";
                            break;
                        case 'Finished Product':
                            $type = "product";
                            break;
                        case 'Packaging Material':
                            $type = "package_material";
                            break;
                        default:
                            $type = "raw_material";
                            break;
                    }
                    $specData['spec_type'] = $type;
                }

                if ($specData['nutritional_basis']) {
                    switch ($specData['nutritional_basis']) {
                        case 'Per 100g':
                            $nutrition = "g";
                            break;
                        case 'Per 100ml':
                            $nutrition = "ml";
                            break;
                        default:
                            $nutrition = "g";
                            break;
                    }
                    $specData['nutritional_basis'] = $nutrition;
                }

                if ($specData['id_barcode_type']) {
                    switch ($specData['id_barcode_type']) {
                        case '1D':
                            $bar = "1d";
                            break;
                        case '2D':
                            $bar = "2d";
                            break;
                        case 'QR Type':
                            $bar = "qr";
                            break;
                        default:
                            $bar = "1d";
                            break;
                    }
                    $specData['id_barcode_type'] = $bar;
                }
                
                // Set default values
                $specData['spec_status'] = 'draft';
                $specData['client_id'] = (int)$session['client'];
                $specData['workspace_id'] = (int)$session['workspace'];
                $specData['created_by'] = $this->user_id;
                $specData['updated_by'] = $this->user_id;

                // Try to find existing ingredient by SKU
                $checkname = Specification::where('client_id',$specData['client_id'])->where('workspace_id',$specData['workspace_id'])->where('spec_name', $specData['spec_name'])->first();
                if($checkname){
                    $checkname->update($specData);
                }else{
                    $specData['spec_upload_type'] = 0;
                    Specification::create($specData);
                } 
            }
            DB::commit();
            return redirect()->back()->with('success', 'Specification imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing Specification: ' . $e->getMessage());
        }
    }

    public function audit(Specification $specification)
    {
        try {
            $specData = Arr::except($specification->toArray(), [
                        'id','client_id','workspace_id','created_at','updated_at','spec_upload_type','file_name','spec_url','archive','json_object','audit_response'
                        ]);

            $jsonData = json_encode($specData, JSON_PRETTY_PRINT);
            $client = OpenAI::client(env('OPENAI_API_KEY'));
            $response = $client->chat()->create([
                'model' => $this->audit_summary_model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->auditsystemPrompt],
                    ['role' => 'user', 'content' => $this->audituserPrompt . "\n\n" . $jsonData],
                ],
            ]);
            $auditResult = $response->choices[0]->message->content ?? null;

            $specification->update([
                'audit_response' => json_encode($auditResult, JSON_PRETTY_PRINT)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Audit Summary Generated.',
                'audit' => $auditResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function updateFileName(Request $request)
    {
        
        $specification = Specification::find($request->id);
        if ($specification) {

            $dirPath = "assets/{$specification->client_id}/{$specification->workspace_id}/specification";
            $oldFileName = $specification->file_name;
            $newFileName = $request->file_name; // new filename from AJAX request

            if (substr($dirPath, -1) !== '/') {
                $dirPath .= '/';
            }

            $oldFilePath = $dirPath . $oldFileName;
            $newFilePath = $dirPath . $newFileName;

            // Check if old file exists
            if (file_exists($oldFilePath)) {

                // Try renaming the file
                if (rename($oldFilePath, $newFilePath)) {
                    // Update DB after successful rename
                    $specification->update([
                        'file_name' => $newFileName,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'File renamed successfully.',
                        'new_name' => $newFileName
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to rename the file. Check permissions.'
                    ]);
                }

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Old file not found.'
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Specification not found.'
            ]);
        }
    }

    public function deleteFile(Request $request)
    {
        $specification = Specification::find($request->id);
        if ($specification) {
            $filepath = "assets/{$specification->client_id}/{$specification->workspace_id}/specification";
            $filename = $specification->file_name;
            $imgResponse = single_image_remove($filepath, $filename);
            if ($imgResponse === "success") {
                $specification->update([
                    'file_name' => null,
                    'spec_upload_type' => 0
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Specification deleted.'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => $imgResponse
                ], 500);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Specification not found.'
            ]);
        }
    }

    public function fileUpload(Request $request,Specification $specification)
    {
        try {

             if (isset($_FILES['image_file']) && !empty($_FILES['image_file']['name'])) {
                    $request->validate([
                        'image_file' => 'required|mimes:pdf|max:20480', // up to 20 MB
                    ]);

                    $filepath = "assets/{$specification->client_id}/{$specification->workspace_id}/specification";
                    if($specification->spec_upload_type == 1){
                        $filename = $specification->file_name;
                        $imgResponse = single_image_remove($filepath, $filename);
                        if ($imgResponse != "success") {
                            return response()->json([
                                'success' => false,
                                'message' => $imgResponse
                            ]);
                        }
                    }

                    $image_response = upload_single_files($_FILES['image_file'], $filepath);
                    if ($image_response['status'] == false) {
                        return response()->json([
                            'status' => false,
                            'message' => $image_response['message']
                        ]);
                    }



                    $specification->update([
                        'spec_upload_type' => 1,
                        'file_name' => $image_response['final_array']['name']
                    ]);

                    return response()->json([
                    'status' => true,
                    'message' => 'file added.'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'file not found.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function reRun(Specification $specification){
        try {
            if($specification->spec_upload_type == 0){
                return response()->json([
                    'status' => true,
                    'type' => 0
                ]);
            }

            $filepath = "assets/{$specification->client_id}/{$specification->workspace_id}/specification/{$specification->file_name}";            
            $UploadResponse = $this->process_uploadfile($filepath);
            if ($UploadResponse['status'] == false) {
                return response()->json([
                    'status' => false,
                    'message' => $UploadResponse['message']
                ]);
            }

            $spec = Specification::where('id', $specification->id)->first()->toArray();
            $aiSpec = $UploadResponse['content'];
            $mapping = [
                'spec' => [
                    "spec_name","spec_sku","spec_type","aus_regulatory_status","lot_number_format","description"
                ],
                'parties' => [
                    "supplier_name","manufacturer_name","manufacturer_address","distributor_name","distributor_contact","compliance_officer"
                ],
                'nutritional_info' => [
                    "nutr_serving_size_g","nutr_servings_per_container","nutr_energy_kj","nutr_protein_g","nutr_carbohydrate_g","nutr_sodium_mg","nutr_fat_total_g","nutr_fat_saturated_g","nutr_fat_trans_g","nutr_sugars_g","nutr_added_sugars_g","nutr_dietary_fiber_g","nutr_cholesterol_mg","nutr_calcium_mg","nutr_iron_mg","nutr_potassium_mg","nutr_vitamin_d_mcg","nutr_gluten_content"
                ],
                'ingredients_allergens' => [
                    "ing_ingredient_list","allergen_fsanz_declaration","ing_percentage_labelling"
                ],
                'cool' => [
                    'cool_primary_country','cool_origin_declaration','cool_percentage_australia','cool_fsanz_standard_ref','cool_date_marking_requirement','cool_label_type','cool_calculation_method'
                ],
                'physical_specs' => [
                   "phys_appearance","phys_color","phys_odor","phys_texture","phys_density_g_ml","phys_specific_gravity","phys_moisture_percent","phys_ph_level","phys_water_activity","phys_viscosity_cps"
                ],
                'microbiological_specs' => [
                    "micro_total_plate_count_cfu_g_max","micro_yeast_mold_cfu_g_max","micro_coliforms_cfu_g_max","micro_e_coli_cfu_g_max","micro_salmonella_absent_in_g","micro_listeria_absent_in_g","micro_staphylococcus_cfu_g_max"
                ],
                'packaging' => [
                    "pack_primary_type","pack_primary_material","pack_primary_dimensions_mm","pack_primary_weight_g","pack_secondary_type","pack_secondary_material","pack_secondary_dimensions_mm","pack_units_per_secondary","pack_case_dimensions_mm","pack_case_weight_g","pack_units_per_case","pack_pallet_type","pack_pallet_dimensions_mm","pack_pallet_height_mm","pack_pallet_weight_kg","pack_cases_per_layer","pack_layers_per_pallet","pack_total_cases_per_pallet"
                ],
                'identifiers' => [
                    "id_gtin_13","id_gtin_14","id_sscc","id_batch_code_format","id_barcode_type"
                ],
                'storage' => [
                    "storage_temp_min_c","storage_temp_max_c","storage_humidity_min_percent","storage_humidity_max_percent","storage_conditions"
                ],
                'trace' => [
                    "trace_gln","trace_system","trace_recall_procedure"
                ],
                'root' => [
                    "best_before_days","use_by_days","handling_instructions","disposal_instructions","shelf_life_type","shelf_life_value","shelf_life_unit","chem_metal_lead","chem_metal_mercury","chem_metal_cadmium","chem_metal_arsenic","chem_metal_tin","chem_pest_chlorpyrifos","chem_pest_glyphosate","atrazine","chem_pest_malathion","chem_pest_permethrin","chem_pest_imazalil","chem_mycotoxin_aflatoxin_b1","chem_mycotoxin_aflatoxin_total","chem_mycotoxin_ochratoxin_a","chem_mycotoxin_deoxynivalenol","chem_mycotoxin_zearalenone","chem_mycotoxin_patulin","chem_pres_sodium_benzoate","chem_pres_potassium_sorbate","chem_pres_sodium_nitrite","chem_pres_sodium_metabisulfite","chem_pres_sulfur_dioxide","chem_pres_calcium_propionate","chem_add_tartrazine","chem_add_sunset_yellow","chem_add_citric_acid","chem_add_ascorbic_acid","chem_add_cochineal","chem_add_monosodium_glutamate",
                ]
            ];

            $matching_array = [
                "overview" => [
                    "spec_name","spec_sku","spec_type","aus_regulatory_code","description","lot_number_format","fsanz_code","supplier_name","manufacturer_name","manufacturer_address","manufacturer_contact","distributor_name","distributor_contact","compliance_officer","shelf_life_type","shelf_life_value","shelf_life_unit","storage_temp_min_c","storage_temp_max_c","storage_humidity_min_percent","storage_humidity_max_percent","storage_conditions","handling_instructions","disposal_instructions","trace_gln","trace_system",
                ],
                "nutrition" =>[
                    "nutr_serving_size_g","nutr_servings_per_container","nutr_energy_kj","nutr_protein_g","nutr_carbohydrate_g","nutr_sodium_mg","nutr_fat_total_g","nutr_fat_saturated_g","nutr_fat_trans_g","nutr_sugars_g","nutr_added_sugars_g","nutr_dietary_fiber_g","nutr_cholesterol_mg","nutr_calcium_mg","nutr_iron_mg","nutr_potassium_mg","nutr_vitamin_d_mcg","nutr_gluten_content","ing_ingredient_list","allergen_statement","allergen_fsanz_declaration","ing_percentage_labelling"
                ],
                "cool" =>[
                    'cool_primary_country','cool_origin_declaration','cool_percentage_australia','cool_fsanz_standard_ref','cool_date_marking_requirement','cool_label_type','cool_calculation_method',
                ],
                "package" => [
                    "pack_primary_type","pack_primary_material","pack_primary_dimensions_mm","pack_primary_weight_g","pack_secondary_type","pack_secondary_material","pack_secondary_dimensions_mm","pack_units_per_secondary","pack_case_dimensions_mm","pack_case_weight_g","pack_units_per_case","pack_pallet_type","pack_pallet_dimensions_mm","pack_pallet_height_mm","pack_pallet_weight_kg","pack_cases_per_layer","pack_layers_per_pallet","pack_total_cases_per_pallet","id_gtin_13","id_gtin_14","id_sscc","id_batch_code_format","id_barcode_type"
                ],
                "quality"=>[
                    "phys_appearance","phys_color","phys_odor","phys_texture","phys_density_g_ml","phys_specific_gravity","phys_moisture_percent","phys_ph_level","phys_water_activity","phys_viscosity_cps","micro_total_plate_count_cfu_g_max","micro_yeast_mold_cfu_g_max","micro_coliforms_cfu_g_max","micro_e_coli_cfu_g_max","micro_salmonella_absent_in_g","micro_listeria_absent_in_g","micro_staphylococcus_cfu_g_max","chem_metal_lead","chem_metal_cadmium","chem_metal_mercury","chem_metal_arsenic","chem_metal_tin","chem_pest_glyphosate","chem_pest_chlorpyrifos","chem_pest_malathion","chem_pest_permethrin","chem_pest_imazalil","chem_mycotoxin_aflatoxin_b1","chem_mycotoxin_aflatoxin_total","chem_mycotoxin_ochratoxin_a","chem_mycotoxin_deoxynivalenol","chem_mycotoxin_zearalenone","chem_mycotoxin_patulin","chem_add_tartrazine","chem_add_cochineal","chem_add_sunset_yellow","chem_add_citric_acid","chem_add_ascorbic_acid","chem_add_monosodium_glutamate","chem_pres_sodium_benzoate","chem_pres_potassium_sorbate","chem_pres_calcium_propionate","chem_pres_sulfur_dioxide","chem_pres_sodium_nitrite","chem_pres_sodium_metabisulfite"
                ],
                "compliance" => [
                    'best_before_days','use_by_days','trace_recall_procedure',
                ]
            ];

            $differences = [];
            foreach ($mapping as $group => $fields) {
                foreach ($fields as $field) {
                    // DB value
                    $dbValue = trim((string)($spec[$field] ?? ''));
                    // AI value handling
                    if ($group === 'root') {
                        // root-level fields
                        $aiValue = trim((string)($aiSpec[$field] ?? ''));
                    } else {
                        // grouped fields
                        $aiValue = trim((string)($aiSpec[$group][$field] ?? ''));
                    }
                    // Compare (case-insensitive)
                    if (strcasecmp($dbValue, $aiValue) !== 0) {
                        $differences[$field] = [
                            'db_value' => $dbValue,
                            'ai_value' => $aiValue
                        ];
                    }
                }
            }

            $html = '';
            $total_count = 0;
            $difference_keys = [];
            if(count($differences) > 0){
                $difference_keys = array_keys($differences);
                $overview_html = $nutrition_html = $cool_html = $quality_html = $packaging_html = $compliance_html = "";
                $overview_count = $nutrition_count = $cool_count = $quality_count = $packaging_count = $compliance_count = 0;
            
                foreach ($differences as $key => $value) {
                    $total_count++;
                    // Simple reusable template
                    $rowHtml ='
                        <div class="reextract-overview-wrapper"><div class="row">
                            <div class="col-md-5">
                                <div class="col-md-12 readonly">
                                    <label for="'.$key.'" class="form-label">'. ucwords(str_replace("_"," ",$key)) .'</label>
                                    <input type="text" class="form-control" name="'.$key.'" placeholder="e.g., 100g" value="'. ($value["db_value"] ?: "") .'" readonly>
                                </div>
                            </div>
                            <div class="col-md-1 text-center arrow-box mt-4"><span>&rarr;</span></div>
                            <div class="col-md-5">
                                <div class="col-md-12 readonly">
                                    <label for="re_'.$key.'" class="form-label">New Value</label>
                                    <input type="text" class="form-control" name="re_'.$key.'" placeholder="e.g., 100g" value="'. ($value["ai_value"] ?: "") .'" readonly>
                                </div>
                            </div>
                            <div class="col-md-1">
                            <input class="form-check-input mt-4" type="checkbox" name="'.$key.'" checked>
                            </div>
                        </div></div>
                    ';

                    if(in_array($key,$matching_array['overview'])){
                        $overview_html .= $rowHtml;
                        $overview_count++;
                        continue;
                    }
                    if(in_array($key,$matching_array['nutrition'])){
                        $nutrition_html .= $rowHtml;
                        $nutrition_count++;
                        continue;
                    }
                    if(in_array($key,$matching_array['cool'])){
                        $cool_html .= $rowHtml;
                        $cool_count++;
                        continue;
                    }
                    if(in_array($key,$matching_array['quality'])){
                        $quality_html .= $rowHtml;
                        $quality_count++;
                        continue;
                    }
                    if(in_array($key,$matching_array['package'])){
                        $packaging_html .= $rowHtml;
                        $packaging_count++;
                        continue;
                    }
                    if(in_array($key,$matching_array['compliance'])){
                        $compliance_html .= $rowHtml;
                        $compliance_count++;
                        continue;
                    }
                }

                $html = '
                    <ul class="nav nav-tabs mb-4" id="respecTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#re_overview" type="button" role="tab">Overview <span class="badge bg-primary ms-1">'.$overview_count.'</span></button>
                        </li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#re_nutrition" type="button">Nutrition <span class="badge bg-primary ms-1">'.$nutrition_count.'</span></button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#re_cool" type="button">Cool <span class="badge bg-primary ms-1">'.$cool_count.'</span></button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#re_quality" type="button">Quality <span class="badge bg-primary ms-1">'.$quality_count.'</span></button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#re_packaging" type="button">Packaging <span class="badge bg-primary ms-1">'.$packaging_count.'</span></button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#re_compliance" type="button">Compliance <span class="badge bg-primary ms-1">'.$compliance_count.'</span></button></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="re_overview" role="tabpanel">
                            <div class="overview-card">
                                <h3>Overview & Basic Information</h3>
                                <div class="overview-container">
                                '.$overview_html.'
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="re_nutrition" role="tabpanel">
                            <div class="overview-card">
                                <h3>Nutrition & Allergens</h3>
                                <div class="overview-container">'.$nutrition_html.'
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="re_cool" role="tabpanel">
                            <div class="overview-card">
                                <h3>Country of Origin Labelling</h3>
                                <div class="overview-container">'.$cool_html.'
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="re_quality" role="tabpanel">
                            <div class="overview-card">
                                <h3>Quality Specifications</h3>
                                <div class="overview-container">'.$quality_html.'
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="re_packaging" role="tabpanel">
                            <div class="overview-card">
                                <h3>Packaging & Logistics</h3>
                                <div class="overview-container">'.$packaging_html.'
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="re_compliance" role="tabpanel">
                            <div class="overview-card">
                                <h3>Compliance & Certifications</h3>
                                <div class="overview-container">'.$compliance_html.'
                                </div>
                            </div>
                        </div>
                    </div>
                ';
            }

            $all_overview_html = $this->buildExtractionHtml($matching_array['overview'], $specification, $differences);

            $all_nutrition_html = $this->buildExtractionHtml($matching_array['nutrition'], $specification, $differences);

            $all_cool_html = $this->buildExtractionHtml($matching_array['cool'], $specification, $differences);

            $all_package_html = $this->buildExtractionHtml($matching_array['package'], $specification, $differences);

            $all_quality_html = $this->buildExtractionHtml($matching_array['quality'], $specification, $differences);

            $all_compliance_html = $this->buildExtractionHtml($matching_array['compliance'], $specification, $differences);

            $all_html = '
                <ul class="nav nav-tabs mb-4" id="allspecTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#all_overview" type="button" role="tab">Overview <span class="badge bg-primary ms-1">'.count($matching_array['overview']).'</span></button>
                    </li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#all_nutrition" type="button">Nutrition <span class="badge bg-primary ms-1">'.count($matching_array['nutrition']).'</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#all_cool" type="button">Cool <span class="badge bg-primary ms-1">'.count($matching_array['cool']).'</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#all_quality" type="button">Quality <span class="badge bg-primary ms-1">'.count($matching_array['package']).'</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#all_packaging" type="button">Packaging <span class="badge bg-primary ms-1">'.count($matching_array['quality']).'</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#all_compliance" type="button">Compliance <span class="badge bg-primary ms-1">'.count($matching_array['compliance']).'</span></button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="all_overview" role="tabpanel">
                        <div class="overview-card">
                            <h3>Overview & Basic Information</h3>
                            <div class="overview-container">
                            '.$all_overview_html.'
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="all_nutrition" role="tabpanel">
                        <div class="overview-card">
                            <h3>Nutrition & Allergens</h3>
                            <div class="overview-container">'.$all_nutrition_html.'
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="all_cool" role="tabpanel">
                        <div class="overview-card">
                            <h3>Country of Origin Labelling</h3>
                            <div class="overview-container">'.$all_cool_html.'
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="all_quality" role="tabpanel">
                        <div class="overview-card">
                            <h3>Quality Specifications</h3>
                            <div class="overview-container">'.$all_quality_html.'
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="all_packaging" role="tabpanel">
                        <div class="overview-card">
                            <h3>Packaging & Logistics</h3>
                            <div class="overview-container">'.$all_package_html.'
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="all_compliance" role="tabpanel">
                        <div class="overview-card">
                            <h3>Compliance & Certifications</h3>
                            <div class="overview-container">'.$all_compliance_html.'
                            </div>
                        </div>
                    </div>
                </div>
            ';

            return response()->json([
                'status' => true,
                'type' => 1,
                'message' => $html,
                'count' => $total_count,
                'all_html' => $all_html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function buildExtractionHtml($fields, $specification, $differences)
    {
        $html = "";
        foreach ($fields as $key) {
            $old_val = $specification->$key ?? "";
            $new_val = isset($differences[$key]) ? $differences[$key]["ai_value"] : $old_val;
            $check_val = isset($differences[$key]) ?  "checked": "checked disabled";
            $label = ucwords(str_replace("_", " ", $key));
            $html .= '
                <div class="overview-wrapper">
                    <div class="row">

                        <!-- OLD VALUE -->
                        <div class="col-md-5">
                            <div class="col-md-12 readonly">
                                <label class="form-label">'.$label.'</label>
                                <input type="text" class="form-control" 
                                    name="'.$key.'" 
                                    value="'.$old_val.'" 
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-1 text-center arrow-box mt-4">
                            <span>&rarr;</span>
                        </div>

                        <!-- NEW VALUE -->
                        <div class="col-md-5">
                            <div class="col-md-12 readonly">
                                <label class="form-label">New Value</label>
                                <input type="text" class="form-control" 
                                    name="re_'.$key.'" 
                                    value="'.$new_val.'" 
                                    readonly>
                            </div>
                        </div>

                        <!-- CHECKBOX -->
                        <div class="col-md-1">
                            <input class="form-check-input mt-4" type="checkbox" name="apply_'.$key.'" '.$check_val.'>
                        </div>

                    </div>
                </div>
            ';
        }

        return $html;
    }

    public function reRun_update(Request $request,Specification $specification)
    {
        try {
            
            // 1. Remove empty & null fields
           $filtered = collect($request->all())
            ->filter(function ($value, $key) {
                return $key !== '_token';   // reject only _token
            })
            ->map(function ($value) {
                return ($value === "" || $value === null) ? null : $value; // empty → null
            })
            ->toArray();

            $specData = Arr::except($specification->toArray(), [
                        'id','client_id','workspace_id','created_at','updated_at'
                        ]);
            $specData['spec_id'] = $specification->id;            
            $specData['version'] = (SpecificationArchieve::where('spec_id', $specification->id)->max('version') ?? 0) + 1;
            $specData['modified_by'] = $this->user_id;

            SpecificationArchieve::create($specData);

            // 2. Update only provided fields
            if (!empty($filtered)) {

                // Sanitize numeric fields update null value
                foreach ($filtered as $key => $value) {
                    if ($value === '' || $value === null) {
                        $filtered[$key] = null;
                        continue;
                    }

                    if (array_key_exists($key, $this->numericFields)) {

                        // Remove non-numeric characters
                        $value = preg_replace('/[^0-9.\-]/', '', $value);

                        // Convert value to appropriate decimal format
                        if ($this->numericFields[$key]['type'] === 'decimal') {
                            $precision = $this->numericFields[$key]['precision'] ?? 2;
                            $value = number_format((float)$value, $precision, '.', '');
                        }
                        $filtered[$key] = $value;
                    }
                }
                $specification->update($filtered);
            }

            return response()->json([
                'status' => true,
                'message' => "Specification Reextraction Updated.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function compare(Request $request)
    {
        $spec = Specification::findOrFail($request->spec_id);

        // Source A = always current version
        $sourceA = $spec;

        // Source B = archive record (primary key = id, which you alias as archive_id in JSON)
        $sourceB = SpecificationArchieve::findOrFail($request->sourceB);


        $yesNoFields = [
            'cool_aus_made_claim',
            'cool_aus_owned_claim',
            'cool_aus_grown_claim',
            'trace_document_required',
            'cert_is_organic',
            'cert_is_halal',
            'cert_is_kosher',
            'cert_is_gluten_free',
            'cert_is_non_gmo',
            'cert_is_fair_trade',
        ];

        $fieldLabels = [
            'spec_name' => 'Spec Name',
            'spec_sku' => 'Spec Sku',
            'spec_type' => 'Spec Type',
            'spec_status' => 'Status',
            'aus_regulatory_status' => 'Australian Regulatory Status',
            'description' => 'Description',
            'supplier_name' => 'Supplier Name',
            'manufacturer_name' => 'Manufacturer Name',
            'manufacturer_address' => 'Manufacturer Address',
            'manufacturer_contact' => 'Manufacturer Contact',
            'distributor_name' => 'Distributor Name',
            'distributor_contact' => 'Distributor Contact',
            'compliance_officer' => 'Compliance Officer',
            'lot_number_format' => 'Lot Number Format',
            'nutr_serving_size_g' => 'Serving Size (g)',
            'nutr_servings_per_container' => 'Servings per Container',
            'nutritional_basis' => 'Nutrition Basis',
            'nutr_energy_kj' => 'Energy (kJ)',
            'nutr_protein_g' => 'Protein (g)',
            'nutr_carbohydrate_g' => 'Carbohydrate (g)',
            'nutr_sodium_mg' => 'Sodium (mg)',
            'nutr_fat_total_g' => 'Total Fat (g)',
            'nutr_fat_saturated_g' => 'Saturated Fat (g)',
            'nutr_fat_trans_g' => 'Trans Fat (g)',
            'nutr_sugars_g' => 'Sugars (g)',
            'nutr_added_sugars_g' => 'Added Sugars (g)',
            'nutr_dietary_fiber_g' => 'Dietary Fibre (g)',
            'nutr_cholesterol_mg' => 'Cholesterol (mg)',
            'nutr_calcium_mg' => 'Calcium (mg)',
            'nutr_iron_mg' => 'Iron (mg)',
            'nutr_potassium_mg' => 'Potassium (mg)',
            'nutr_vitamin_d_mcg' => 'Vitamin D (mcg)',
            'nutr_gluten_content' => 'Gluten Content',
            'cool_primary_country' => 'Primary Country of Origin',
            'cool_origin_declaration' => 'Origin Declaration',
            'cool_percentage_australia' => 'Australian Content (%)',
            'cool_fsanz_standard_ref' => 'FSANZ Standard Reference',
            'cool_date_marking_requirement' => 'Date Marking Requirement',
            'cool_label_type' => 'Label Type',
            'cool_calculation_method' => 'Calculation Method',
            'cool_aus_made_claim' => 'Australian Made',
            'cool_aus_owned_claim' => 'Australian Owned',
            'cool_aus_grown_claim' => 'Australian Grown',
            'ing_ingredient_list' => 'Ingredient List',
            'allergen_statement' => 'Allergen Statement',
            'allergen_fsanz_declaration' => 'FSANZ Allergen Declaration',
            'ing_percentage_labelling' => 'Percentage Labelling',
            'phys_appearance' => 'Appearance',
            'phys_color' => 'Colour',
            'phys_odor' => 'Odour',
            'phys_texture' => 'Texture',
            'phys_density_g_ml' => 'Density (g/mL)',
            'phys_specific_gravity' => 'Specific Gravity',
            'phys_moisture_percent' => 'Moisture (%)',
            'phys_ph_level' => 'pH Level',
            'phys_water_activity' => 'Water Activity (aw)',
            'phys_viscosity_cps' => 'Viscosity (cP)',
            'micro_total_plate_count_cfu_g_max' => 'Aerobic Plate Count Max',
            'micro_yeast_mold_cfu_g_max' => 'Yeast & Mould Max',
            'micro_coliforms_cfu_g_max' => 'Coliforms Max',
            'micro_e_coli_cfu_g_max' => 'E. coli Max',
            'micro_salmonella_absent_in_g' => 'Salmonella Absent in (g)',
            'micro_listeria_absent_in_g' => 'Listeria Absent in (g)',
            'micro_staphylococcus_cfu_g_max' => 'Staphylococcus Max',
            'pack_primary_type' => 'Primary Pack Type',
            'pack_primary_material' => 'Primary Pack Material',
            'pack_primary_dimensions_mm' => 'Primary Pack Dimensions (mm)',
            'pack_primary_weight_g' => 'Primary Pack Weight (g)',
            'pack_secondary_type' => 'Secondary Pack Type',
            'pack_secondary_material' => 'Secondary Pack Material',
            'pack_secondary_dimensions_mm' => 'Secondary Pack Dimensions (mm)',
            'pack_units_per_secondary' => 'Units per Secondary',
            'pack_case_dimensions_mm' => 'Case Dimensions (mm)',
            'pack_case_weight_g' => 'Case Weight (g)',
            'pack_units_per_case' => 'Units per Case',
            'pack_pallet_type' => 'Pallet Type',
            'pack_pallet_dimensions_mm' => 'Pallet Dimensions (mm)',
            'pack_pallet_height_mm' => 'Pallet Height (mm)',
            'pack_pallet_weight_kg' => 'Pallet Weight (kg)',
            'pack_cases_per_layer' => 'Cases per Layer (Ti)',
            'pack_layers_per_pallet' => 'Layers per Pallet (Hi)',
            'pack_total_cases_per_pallet' => 'Total Cases per Pallet',
            'id_gtin_13' => 'GTIN-13 (Retail)',
            'id_gtin_14' => 'GTIN-14 (Case)',
            'id_sscc' => 'SSCC (Logistics)',
            'id_batch_code_format' => 'Batch Code Format',
            'id_barcode_type' => 'Barcode Type',
            'aus_advisory_statements' => 'Advisory Statements',
            'aus_warning_statements' => 'Warning Statements',
            'aus_health_claims' => 'Health Claims',
            'aus_nutrition_content_claims' => 'Nutrition Content Claims',
            'cert_is_organic' => 'Organic (Certified)',
            'cert_is_halal' => 'Halal (Certified)',
            'cert_is_kosher' => 'Kosher (Certified)',
            'cert_is_gluten_free' => 'Gluten Free (Certified)',
            'cert_is_non_gmo' => 'Non-GMO',
            'cert_is_fair_trade' => 'Fair Trade',
            'cert_certificate_details' => 'Certificate Details',
            'storage_temp_min_c' => 'Storage Temp Min (C)',
            'storage_temp_max_c' => 'Storage Temp Max (C)',
            'storage_humidity_min_percent' => 'Storage RH Min (%)',
            'storage_humidity_max_percent' => 'Storage RH Max (%)',
            'storage_conditions' => 'Storage Conditions',
            'shelf_life_type' => 'Shelf Life Type',
            'shelf_life_value' => 'Shelf Life Value',
            'shelf_life_unit' => 'Shelf Life Unit',
            'best_before_days' => 'Best Before Days',
            'use_by_days' => 'Use By Days',
            'handling_instructions' => 'Handling Instructions',
            'disposal_instructions' => 'Disposal Instructions',
            'trace_gln' => 'GLN',
            'trace_system' => 'Traceability System',
            'trace_recall_procedure' => 'Recall Procedure',
            'trace_document_required' => 'Trace Documents Required',
            'chem_metal_lead' => 'Lead (Pb)',
            'chem_metal_cadmium' => 'Cadmium (Cd)',
            'chem_metal_mercury' => 'Mercury (Hg)',
            'chem_metal_arsenic' => 'Arsenic (As)',
            'chem_metal_tin' => 'Tin (Sn)',
            'chem_pest_glyphosate' => 'Glyphosate',
            'chem_pest_chlorpyrifos' => 'Chlorpyrifos',
            'chem_pest_malathion' => 'Malathion',
            'chem_pest_permethrin' => 'Permethrin',
            'chem_pest_imazalil' => 'Imazalil',
            'chem_pesticide_residues' => 'Residues',
            'chem_mycotoxin_aflatoxin_b1' => 'Aflatoxin B1',
            'chem_mycotoxin_aflatoxin_total' => 'Aflatoxin Total',
            'chem_mycotoxin_ochratoxin_a' => 'Ochratoxin A',
            'chem_mycotoxin_deoxynivalenol' => 'Deoxynivalenol (DON)',
            'chem_mycotoxin_zearalenone' => 'Zearalenone',
            'chem_mycotoxin_patulin' => 'Patulin',
            'chem_add_tartrazine' => 'Tartrazine',
            'chem_add_cochineal' => 'Cochineal',
            'chem_add_sunset_yellow' => 'Sunset Yellow',
            'chem_add_citric_acid' => 'Citric Acid',
            'chem_add_ascorbic_acid' => 'Ascorbic Acid',
            'chem_add_monosodium_glutamate' => 'Monosodium Glutamate (MSG)',
            'chem_pres_sodium_benzoate' => 'Sodium Benzoate',
            'chem_pres_potassium_sorbate' => 'Potassium Sorbate',
            'chem_pres_calcium_propionate' => 'Calcium Propionate',
            'chem_pres_sulfur_dioxide' => 'Sulfur Dioxide',
            'chem_pres_sodium_nitrite' => 'Sodium Nitrite',
            'chem_pres_sodium_metabisulfite' => 'Sodium Metabisulfite'
        ];

        $fieldGroups = [
            'general' => [
                'label'  => 'General Information',
                'fields' => [
                    'spec_name', 'spec_sku', 'spec_type',
                    'manufacturer_name', 'manufacturer_address', 'manufacturer_contact',
                    'distributor_name', 'distributor_contact','compliance_officer','lot_number_format'
                ],
            ],
            'ingredients_allergens' => [
                'label'  => 'Ingredients & Allergens',
                'fields' => [
                    'ing_ingredient_list',
                    'allergen_statement',
                    'allergen_fsanz_declaration','ing_percentage_labelling','ing_food_additive_numbers'
                ],
            ],
            'nutrition' => [
                'label'  => 'Nutritional Information',
                'fields' => [
                    'nutr_serving_size_g','nutr_servings_per_container','nutritional_basis','nutr_energy_kj','nutr_protein_g','nutr_carbohydrate_g','nutr_sodium_mg','nutr_fat_total_g','nutr_fat_saturated_g','nutr_fat_trans_g','nutr_sugars_g','nutr_added_sugars_g','nutr_dietary_fiber_g','nutr_cholesterol_mg','nutr_calcium_mg','nutr_iron_mg','nutr_potassium_mg','nutr_vitamin_d_mcg','nutr_gluten_content'
                ],
            ],
            'packaging' => [
                'label'  => 'Packaging',
                'fields' => [
                    'pack_primary_type','pack_primary_material','pack_primary_dimensions_mm','pack_primary_weight_g','pack_secondary_type','pack_secondary_material','pack_secondary_dimensions_mm','pack_units_per_secondary','pack_case_dimensions_mm','pack_case_weight_g','pack_units_per_case','pack_pallet_type','pack_pallet_dimensions_mm','pack_pallet_height_mm','pack_pallet_weight_kg','pack_cases_per_layer','pack_layers_per_pallet','pack_total_cases_per_pallet','id_gtin_13','id_gtin_14','id_sscc','id_batch_code_format','id_barcode_type'
                ],
            ],
            'origin_regulatory' => [
                'label'  => 'Origin & Regulatory',
                'fields' => [
                    'cool_primary_country',
                    'cool_origin_declaration',
                    'cool_aus_made_claim',
                    'cool_aus_owned_claim',
                    'cool_aus_grown_claim',
                    'aus_regulatory_status',
                ],
            ],
            'storage_handling' => [
                'label'  => 'Storage & Handling',
                'fields' => [
                    'storage_temp_min_c','storage_temp_max_c','storage_humidity_min_percent','storage_humidity_max_percent','storage_conditions','shelf_life_value','shelf_life_unit','handling_instructions','disposal_instructions'
                ],
            ],
            'physical_specifications' => [
                'label'  => 'Physical Specifications',
                'fields' => [
                    'phys_appearance','phys_color','phys_odor','phys_texture','phys_density_g_ml','phys_specific_gravity','phys_moisture_percent','phys_ph_level','phys_water_activity','phys_viscosity_cps',
                ],
            ],
            'micro' => [
                'label'  => 'Microbiological Specifications',
                'fields' => [
                    'micro_total_plate_count_cfu_g_max','micro_yeast_mold_cfu_g_max','micro_coliforms_cfu_g_max','micro_e_coli_cfu_g_max','micro_salmonella_absent_in_g','micro_listeria_absent_in_g','micro_staphylococcus_cfu_g_max'
                ],
            ],
            'heavy_metals' => [
                'label'  => 'Heavy Metals & Chemicals',
                'fields' => [
                    'chem_metal_lead','chem_metal_cadmium','chem_metal_mercury','chem_metal_arsenic','chem_metal_tin','chem_pest_glyphosate','chem_pest_chlorpyrifos','chem_pest_malathion','chem_pest_permethrin','chem_pest_imazalil','chem_pesticide_residues','chem_mycotoxin_aflatoxin_b1','chem_mycotoxin_aflatoxin_total','chem_mycotoxin_ochratoxin_a','chem_mycotoxin_deoxynivalenol','chem_mycotoxin_zearalenone','chem_mycotoxin_patulin','chem_mycotoxins','chem_add_tartrazine','chem_add_cochineal','chem_add_sunset_yellow','chem_add_citric_acid','chem_add_ascorbic_acid','chem_add_monosodium_glutamate','chem_additives','chem_pres_sodium_benzoate','chem_pres_potassium_sorbate','chem_pres_calcium_propionate','chem_pres_sulfur_dioxide','chem_pres_sodium_nitrite','chem_pres_sodium_metabisulfite'
                ],
            ],
            'traceability' => [
                'label'  => 'Certifications & Traceability',
                'fields' => [
                    'cert_is_organic','cert_is_halal','cert_is_kosher','cert_is_gluten_free','cert_is_non_gmo','cert_is_fair_trade','best_before_days','use_by_days','trace_gln','trace_system','trace_recall_procedure'
                ],
            ],
            
        ];

        // YES/NO conversion
        $convertYesNo = function ($field, $val) use ($yesNoFields) {
            if (in_array($field, $yesNoFields, true)) {
                if ($val === null || $val === '') {
                    return null;
                }
                return ((int) $val === 1) ? 'Yes' : 'No';
            }
            return $val;
        };

        // inline diff
        $inlineDiff = function ($a, $b) {
            $a = $a === null ? '' : (string) $a;
            $b = $b === null ? '' : (string) $b;

            // Both empty → ignore
            if ($a === '' && $b === '') {
                return ['<span class="text-muted">(Empty)</span>', '<span class="text-muted">(Empty)</span>'];
            }

            // Source A empty → show placeholder
            if ($a === '' && $b !== '') {
                return [
                    '<span class="text-empty">(Empty)</span>',
                    '<span class="diff-added"><i class="bi bi-plus-circle"></i> '.e($b).'</span>'
                ];
            }

            if ($b === '' && $a !== '') {
                return [
                    '<span class="diff-removed"><i class="bi bi-x-circle"></i> '.e($a).'</span>',
                    '<span class="text-empty">(Empty)</span>'
                ];
            }


            $tokensA = preg_split('/(\s+)/u', $a, -1, PREG_SPLIT_DELIM_CAPTURE);
            $tokensB = preg_split('/(\s+)/u', $b, -1, PREG_SPLIT_DELIM_CAPTURE);

            $max = max(count($tokensA), count($tokensB));
            $htmlA = '';
            $htmlB = '';

            for ($i = 0; $i < $max; $i++) {
            $tA = $tokensA[$i] ?? '';
            $tB = $tokensB[$i] ?? '';

            if ($tA === $tB) {
                $htmlA .= e($tA);
                $htmlB .= e($tB);
            } else {
                if ($tA !== '') {
                    $htmlA .= '<span class="diff-removed">'.e($tA).'</span>';
                    }
                    if ($tB !== '') {
                        $htmlB .= '<span class="diff-added">'.e($tB).'</span>';
                    }
                }
            }

            return [$htmlA, $htmlB];
        };


        $groupsResult = [];

        foreach ($fieldGroups as $groupKey => $group) {
            $rows = [];

            foreach ($group['fields'] as $field) {
                $rawA = $sourceA->$field ?? null;
                $rawB = $sourceB->$field ?? null;

                $valA = $convertYesNo($field, $rawA);
                $valB = $convertYesNo($field, $rawB);

                // compare as string to avoid 1 vs "1" issues
                if ((string) $valA === (string) $valB) {
                    continue;
                }

                [$htmlA, $htmlB] = $inlineDiff($valA, $valB);

                $rows[] = [
                    'field' => $field,
                    'label' => $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field)),
                    'htmlA' => $htmlA,
                    'htmlB' => $htmlB,
                ];
            }
        
            if (!empty($rows)) {
                $groupsResult[] = [
                    'key'   => $groupKey,
                    'label' => $group['label'],
                    'rows'  => $rows,
                ];
            }
        }

        return response()->json([
            'groups' => $groupsResult,
        ]);

    }


    public function restore(Request $request)
    {
        DB::beginTransaction();

        try {
            $specification = Specification::findOrFail($request->spec_id);
            $archive = SpecificationArchieve::findOrFail($request->archive_id);

            // Get next version number
            $nextVersion = SpecificationArchieve::where('spec_id', $specification->id)->max('version');
            $nextVersion = ($nextVersion ?? 0) + 1;

            // Prepare data for creating a new archive from current spec
            $specData = Arr::except($specification->toArray(), [
                'id', 'client_id', 'workspace_id', 'created_at', 'updated_at'
            ]);

            $specData['spec_id'] = $specification->id;
            $specData['version'] = $nextVersion;
            $specData['modified_by'] = $this->user_id;

            SpecificationArchieve::create($specData);

            // Replace current spec data with selected archive version
            $specArcData = Arr::except($archive->toArray(), [
                'id', 'spec_id', 'version', 'modified_by', 'created_at', 'updated_at'
            ]);

            $specArcData['modified_by'] = $this->user_id; // Update modifier

            $specification->update($specArcData);

            DB::commit();

            return response()->json(['status' => 'restored']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create specification from FSANZ food (API endpoint)
     */
    public function createFromFSANZ(Request $request)
    {
        $request->validate([
            'fsanz_food_id' => 'required|numeric|exists:fsanz_foods,id',
            'workspace_id' => 'nullable|uuid',
            'include_supplementary' => 'nullable|boolean',
            'include_classification' => 'nullable|boolean',
        ]);
        
        try {
            $fsanzFood = FsanzFood::findOrFail($request->fsanz_food_id);

            // Validate FSANZ data
            $validation = $this->mappingService->validateFSANZForSpecification($fsanzFood);

            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                    'errors' => $validation['errors'],
                    'warnings' => $validation['warnings'],
                ], 422);
            }
            
            // Map FSANZ data to specification
            $options = [
                'includeSupplementary' => (bool) $request->include_supplementary,
                'includeClassification' => (bool) $request->include_classification,
            ];
            
            $mappedData = $this->mappingService->mapFSANZToSpecification($fsanzFood, $options);
            
            // Generate SKU and add workspace
            $mappedData['spec_sku'] = 'FSANZ-' . $fsanzFood->fsanz_key;
            $mappedData['client_id'] = $this->clientID;
            $mappedData['workspace_id'] = $this->ws_id;

            $validator = Validator::make($mappedData, [
                // Specification info
                'spec_name' => [
                    'required',
                    'string',
                    Rule::unique('specifications', 'spec_name')
                        ->where(function ($query) use ($mappedData) {
                            return $query->where('client_id', $mappedData['client_id'])
                                        ->where('workspace_id', $mappedData['workspace_id']);
                        }),
                ],
            ]);

            // if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),  
                ], 422);
            }

            // Create specification
            DB::beginTransaction();
            
            $specification = Specification::create($mappedData);
            
            DB::commit();
            
            // Log::info('Specification created from FSANZ', [
            //     'specification_id' => $specification->id,
            //     'fsanz_food_id' => $fsanzFood->id,
            //     'fsanz_key' => $fsanzFood->fsanz_key,
            // ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Specification created successfully',
                'specification' => [
                    'id' => $specification->id,
                    'name' => $specification->name,
                    'spec_sku' => $specification->spec_sku,
                    'status' => $specification->status,
                ],
                'warnings' => $validation['warnings'],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Failed to create specification from FSANZ', [
            //     'error' => $e->getMessage(),
            //     'fsanz_food_id' => $request->fsanz_food_id,
            // ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create specification: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Search specifications for raw material creation
     */
    public function search(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Specification::where([
                    'client_id' => $this->clientID,
                    'workspace_id' => $this->ws_id
                ])
                ->where('archive', 0)
                // ->whereNotNull('fsanz_source_id')
                ->orderBy('spec_name');


        // Apply search filter
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('spec_name', 'like', "%{$search}%")
                  ->orWhere('spec_sku', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        $specifications = $query
            ->limit($request->limit ?? 50)
            ->get([
                'id',
                'spec_name',
                'spec_sku',
                'supplier_name',
                'spec_status',
                'description',
                'nutr_energy_kj',
                'nutr_protein_g',
                'nutr_fat_total_g',
                'nutr_carbohydrate_g',
                'nutr_sodium_mg',
                'phys_specific_gravity',
                'ing_ingredient_list',
                'allergen_statement',
            ]);

        return response()->json([
            'specifications' => $specifications,
            'count' => $specifications->count(),
        ]);
    }

    /**
     * Get full specification details for raw material confirmation
     */
    public function show(string $id)
    {
        $specification = Specification::findOrFail($id);
        
        return response()->json([
            'specification' => $specification,
        ]);
    }




}
