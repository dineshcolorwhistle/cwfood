<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsanzFood extends Model
{
    protected $table = 'fsanz_foods';

    // PRIMARY KEY (Auto Increment BIGINT)
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const UPDATED_AT = 'last_updated';

    protected $fillable = [
        'fsanz_key',
        'name',
        'description',
        'energy_kj',
        'protein_g',
        'fat_total_g',
        'fat_saturated_g',
        'carbohydrate_g',
        'sugars_g',
        'sodium_mg',
        'specific_gravity',
        'measurement_basis',
        'data_source',
        'food_category_code',
        'food_category_name',
        'food_group',
        'food_subgroup',
        'functional_category',
        'is_raw_ingredient',
        'is_additive',
        'is_processing_aid',
        'estimated_ingredients',
        'estimated_allergens',
        'allergen_confidence_score',
        'estimated_hazards',
        'hazard_confidence_score',
        'estimated_dietary_status',
        'dietary_confidence_score',
        'estimated_processing_info',
        'processing_confidence_score',
        'estimated_regulatory_info',
        'regulatory_confidence_score',
        'estimated_typical_uses',
        'estimated_origin',
        'estimated_australia_percent',
        'origin_confidence_score',
        'primary_origin_country',
        'alternative_origin_sources',
        'origin_is_variable',
        'ai_estimation_status',
        'last_ai_analysis',
        'ai_analysis_notes',
        'manual_override',
        'overall_confidence_score',
    ];

    protected $casts = [
        // Nutrition values
        'energy_kj' => 'decimal:2',
        'protein_g' => 'decimal:3',
        'fat_total_g' => 'decimal:3',
        'fat_saturated_g' => 'decimal:3',
        'carbohydrate_g' => 'decimal:3',
        'sugars_g' => 'decimal:3',
        'sodium_mg' => 'decimal:3',
        'specific_gravity' => 'decimal:4',

        // JSON fields
        'functional_category' => 'array',
        'estimated_ingredients' => 'array',
        'estimated_allergens' => 'array',
        'estimated_hazards' => 'array',
        'estimated_dietary_status' => 'array',
        'estimated_processing_info' => 'array',
        'estimated_regulatory_info' => 'array',
        'estimated_typical_uses' => 'array',
        'estimated_origin' => 'array',
        'alternative_origin_sources' => 'array',

        // Boolean flags
        'is_raw_ingredient' => 'boolean',
        'is_additive' => 'boolean',
        'is_processing_aid' => 'boolean',
        'origin_is_variable' => 'boolean',
        'manual_override' => 'boolean',

        // Scores
        'allergen_confidence_score' => 'decimal:4',
        'hazard_confidence_score' => 'decimal:4',
        'dietary_confidence_score' => 'decimal:4',
        'processing_confidence_score' => 'decimal:4',
        'regulatory_confidence_score' => 'decimal:4',
        'overall_confidence_score' => 'decimal:4',
        'estimated_australia_percent' => 'decimal:2',
        'origin_confidence_score' => 'decimal:4',

        // Dates
        'last_ai_analysis' => 'datetime',
        'created_at' => 'datetime',
        'last_updated' => 'datetime',
    ];

    // ============================================
    // Query Scopes
    // ============================================

    public function scopeRawIngredients($query)
    {
        return $query->where('is_raw_ingredient', true);
    }

    public function scopeAdditives($query)
    {
        return $query->where('is_additive', true);
    }

    public function scopeWithAiAnalysis($query)
    {
        return $query->where('ai_estimation_status', 'completed');
    }

    public function scopePendingAiAnalysis($query)
    {
        return $query->where('ai_estimation_status', 'pending');
    }

    public function scopeByFoodGroup($query, string $group)
    {
        return $query->where('food_group', $group);
    }



    // ============================================
    // Accessors
    // ============================================

    public function getHasAiDataAttribute(): bool
    {
        return $this->ai_estimation_status === 'completed';
    }

    public function getContainsAllergensAttribute(): array
    {
        return $this->estimated_allergens['contains'] ?? [];
    }

    public function getMayContainAllergensAttribute(): array
    {
        return $this->estimated_allergens['may_contain'] ?? [];
    }

    public function getDietaryStatus(string $key): ?bool
    {
        $status = $this->estimated_dietary_status;

        if (!is_array($status) || !isset($status[$key])) {
            return null;
        }

        $value = $status[$key];

        // If simple boolean
        if (is_bool($value)) {
            return $value;
        }

        // If nested: prefer "status"
        if (is_array($value) && array_key_exists('status', $value)) {
            return (bool) $value['status'];
        }

        // If nested: fallback to "likely_compliant"
        if (is_array($value) && array_key_exists('likely_compliant', $value)) {
            return (bool) $value['likely_compliant'];
        }

        // If none found → cannot determine
        return null;
    }


}
