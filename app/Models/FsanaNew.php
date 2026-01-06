<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsanzNew extends Model
{
    protected $table = 'fsanz_new';

    protected $fillable = [
        'fsanz_key',
        'name',
        'energy_kj',
        'protein_g',
        'fat_total_g',
        'carbohydrate_g',
        'sodium_mg',
        'description',
        'food_group',
        'food_subgroup',
        'food_category_code',
        'food_category_name',
        'data_source',
        'measurement_basis',
        'fat_saturated_g',
        'sugars_g',
        'estimated_dietary_status',
        'dietary_confidence_score',
        'estimated_allergens',
        'allergen_confidence_score',
        'estimated_hazards',
        'hazard_confidence_score',
        'estimated_processing_info',
        'processing_confidence_score',
        'estimated_regulatory_info',
        'regulatory_confidence_score',
        'estimated_ingredients',
        'estimated_typical_uses',
        'estimated_australia_percent',
        'estimated_origin',
        'origin_confidence_score',
        'overall_confidence_score',
        'ai_estimation_status',
        'last_ai_analysis',
        'ai_analysis_notes',
    ];

    protected $casts = [
        'energy_kj' => 'float',
        'protein_g' => 'float',
        'fat_total_g' => 'float',
        'carbohydrate_g' => 'float',
        'sodium_mg' => 'float',

        'fat_saturated_g' => 'float',
        'sugars_g' => 'float',

        'dietary_confidence_score' => 'float',
        'allergen_confidence_score' => 'float',
        'hazard_confidence_score' => 'float',
        'processing_confidence_score' => 'float',
        'regulatory_confidence_score' => 'float',
        'origin_confidence_score' => 'float',
        'overall_confidence_score' => 'float',

        'estimated_australia_percent' => 'float',

        'last_ai_analysis' => 'datetime',

        // Text fields that may contain JSON-like structures (AI output)
        'estimated_dietary_status' => 'string',
        'estimated_allergens' => 'string',
        'estimated_hazards' => 'string',
        'estimated_processing_info' => 'string',
        'estimated_regulatory_info' => 'string',
        'estimated_ingredients' => 'string',
        'estimated_typical_uses' => 'string',

        'ai_analysis_notes' => 'string',
    ];
}
