<?php

namespace App\Services;

use App\Models\FsanzFood;

class FSANZMappingService
{
    /**
     * Map FSANZ food data to specification schema
     */
    public function mapFSANZToSpecification(FsanzFood $fsanzFood, array $options = []): array
    {

        // dd($fsanzFood);
        $includeSupplementary = $options['includeSupplementary'] ?? false;
        $includeClassification = $options['includeClassification'] ?? false;

        $dietary = $this->parseDietaryStatus($fsanzFood->estimated_dietary_status);

        // Category 1 & 2 (always included)
        $mapping = [
            // Category 1: FSANZ Official Data
            'spec_name' => $this->safeString($fsanzFood->name) ?? 'Unnamed Food',
            'description' => $this->safeString($fsanzFood->description),
            
            // Nutritional data
            'nutr_energy_kj' => $this->safeNumber($fsanzFood->energy_kj),
            'nutr_protein_g' => $this->safeNumber($fsanzFood->protein_g),
            'nutr_fat_total_g' => $this->safeNumber($fsanzFood->fat_total_g),
            'nutr_fat_saturated_g' => $this->safeNumber($fsanzFood->fat_saturated_g),
            'nutr_carbohydrate_g' => $this->safeNumber($fsanzFood->carbohydrate_g),
            'nutr_sugars_g' => $this->safeNumber($fsanzFood->sugars_g),
            'nutr_sodium_mg' => $this->safeNumber($fsanzFood->sodium_mg),
            'nutritional_basis' => $this->safeString($fsanzFood->measurement_basis) ?? 'per_100g',
            
            // Physical properties
            'phys_specific_gravity' => $this->safeNumber($fsanzFood->specific_gravity),
            
            // Category 2: AI - Compliance Critical
            'ing_ingredient_list' => $this->formatIngredientsList($fsanzFood->estimated_ingredients),
            'allergen_statement' => $this->formatAllergenStatement($fsanzFood->estimated_allergens),
            // 'ingr_contains_allergens' => $fsanzFood->estimated_allergens['contains'] ?? null,
            // 'ingr_may_contain_allergens' => $fsanzFood->estimated_allergens['may_contain'] ?? null,
            
            // Country of Origin
            'cool_primary_country' => $this->safeString($fsanzFood->primary_origin_country),
            'cool_origin_declaration' => $fsanzFood->estimated_origin['reasoning'] ?? null,
            'cool_percentage_australia' => $this->convertAustraliaPercent($fsanzFood->estimated_australia_percent),
            
            // Dietary certifications
            // 'cert_vegan' => $dietary['vegan'],
            'cert_is_organic' => $dietary['vegetarian'],
            'cert_is_halal' => $dietary['halal'],
            'cert_is_kosher' => $dietary['kosher'],
            'cert_is_gluten_free' => $dietary['glutenFree'],
            // 'cert_lactose_free' => $dietary['lactoseFree'],
            
            // Audit trail
            'fsanz_source_id' => $fsanzFood->id,
            
            // Default metadata
            'spec_type' => 'raw_material',
            'spec_status' => 'pending_review',
        ];

        
        if ($includeSupplementary) {

            $estimated = is_array($fsanzFood->estimated_processing_info) 
                ? $fsanzFood->estimated_processing_info 
                : json_decode($fsanzFood->estimated_processing_info, true);

            // ========== Direct string mappings ==========
            $mapping['storage_conditions']    = $estimated['storage_requirements'] ?? null;
            $mapping['handling_instructions'] = $estimated['preservation_method'] ?? null;

            // ========== Shelf Life Value ==========
            if (!empty($estimated['typical_shelf_life'])) {
                preg_match('/(\d+(\.\d+)?)/', $estimated['typical_shelf_life'], $shelfVal);
                $mapping['shelf_life_value'] = $shelfVal[1] ?? null;
            } else {
                $mapping['shelf_life_value'] = null;
            }

            // ========== Shelf Life Unit ==========
            if (!empty($estimated['typical_shelf_life'])) {
                preg_match('/\b(\d+(\.\d+)?)\s*(days?|weeks?|months?|yrs?|years?)\b/i', $estimated['typical_shelf_life'], $unitMatch);

                // Keep original unit (days, weeks, months, years)
                $mapping['shelf_life_unit'] = !empty($unitMatch[3]) 
                    ? strtolower($unitMatch[3])   // output = Days, Weeks, Months, Years
                    : null;

            } else {
                $mapping['shelf_life_unit'] = null;
            }

            // ========== Water Activity ==========
            if (!empty($estimated['water_activity'])) {
                preg_match('/(\d+(\.\d+)?)/', $estimated['water_activity'], $waterVal);
                $mapping['phys_water_activity'] = $waterVal[1] ?? null;
            } else {
                $mapping['phys_water_activity'] = null;
            }

            // ========== pH Value ==========
            if (!empty($estimated['ph_range'])) {
                preg_match('/(\d+(\.\d+)?)/', $estimated['ph_range'], $phVal);
                $mapping['phys_ph_level'] = $phVal[1] ?? null;
            } else {
                $mapping['phys_ph_level'] = null;
            }

            
        }

        // Category 3: AI - Supplementary (optional)
        // if ($includeSupplementary) {
        //     $mapping['ai_hazards_info'] = $fsanzFood->estimated_hazards;
        //     $mapping['ai_processing_info'] = $fsanzFood->estimated_processing_info;
        //     $mapping['ai_regulatory_info'] = $fsanzFood->estimated_regulatory_info;
        //     $mapping['ai_typical_uses'] = $fsanzFood->estimated_typical_uses;
        // }

        // Category 4: Classification (optional)
        // if ($includeClassification) {
        //     $mapping['food_category_code'] = $fsanzFood->food_category_code;
        //     $mapping['food_category_name'] = $fsanzFood->food_category_name;
        //     $mapping['food_group'] = $fsanzFood->food_group;
        //     $mapping['food_subgroup'] = $fsanzFood->food_subgroup;
        //     $mapping['functional_category'] = $fsanzFood->functional_category;
        //     $mapping['is_raw_ingredient'] = $fsanzFood->is_raw_ingredient ?? true;
        //     $mapping['is_additive'] = $fsanzFood->is_additive ?? false;
        //     $mapping['is_processing_aid'] = $fsanzFood->is_processing_aid ?? false;
        // }

        return $mapping;
    }

    /**
     * Validate FSANZ food has minimum required data for specification creation
     */
    public function validateFSANZForSpecification(FSANZFood $fsanzFood): array
    {
        $errors = [];
        $warnings = [];

        // Critical validations (block creation)
        if (empty($fsanzFood->name) || trim($fsanzFood->name) === '') {
            $errors[] = 'Food name is required';
        }

        if (empty($fsanzFood->id)) {
            $errors[] = 'FSANZ food ID is missing';
        }

        // At least one nutritional value should exist
        $nutritionalValues = [
            $fsanzFood->energy_kj,
            $fsanzFood->protein_g,
            $fsanzFood->fat_total_g,
            $fsanzFood->fat_saturated_g,
            $fsanzFood->carbohydrate_g,
            $fsanzFood->sugars_g,
            $fsanzFood->sodium_mg,
        ];
        $hasNutritionalData = collect($nutritionalValues)->filter(fn($val) => $val !== null)->isNotEmpty();

        if (!$hasNutritionalData) {
            $errors[] = 'At least one nutritional value (energy, protein, fat, or carbohydrate) is required';
        }

        // Quality warnings (allow creation but inform user)
        if (empty($fsanzFood->estimated_allergens)) {
            $warnings[] = 'No allergen data available - allergen fields will be empty';
        }

        if (empty($fsanzFood->estimated_dietary_status)) {
            $warnings[] = 'No dietary status data available - certification fields will be false';
        }

        if (empty($fsanzFood->primary_origin_country) && empty($fsanzFood->estimated_australia_percent)) {
            $warnings[] = 'No country of origin data available';
        }

        if (empty($fsanzFood->estimated_ingredients)) {
            $warnings[] = 'No ingredient list data available';
        }

        // Check for incomplete nutritional panel
        $missingNutrients = [];
        if ($fsanzFood->energy_kj === null) $missingNutrients[] = 'energy';
        if ($fsanzFood->protein_g === null) $missingNutrients[] = 'protein';
        if ($fsanzFood->fat_total_g === null) $missingNutrients[] = 'fat';
        if ($fsanzFood->fat_saturated_g === null) $missingNutrients[] = 'fat saturated';
        if ($fsanzFood->carbohydrate_g === null) $missingNutrients[] = 'carbohydrate';
        if ($fsanzFood->sugars_g === null) $missingNutrients[] = 'sugar';
        if ($fsanzFood->sodium_mg === null) $missingNutrients[] = 'sodium';

        if (count($missingNutrients) > 0 && count($missingNutrients) < 4) {
            $warnings[] = 'Incomplete nutritional data: missing ' . implode(', ', $missingNutrients);
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Parse dietary status from FSANZ estimated_dietary_status JSON
     */
    protected function parseDietaryStatus($dietaryStatus): array
    {
        // Default structure
        $defaults = [
            'vegan'        => false,
            'vegetarian'   => false,
            'halal'        => false,
            'kosher'       => false,
            'glutenFree'   => false,
            'lactoseFree'  => false,
        ];

        if (empty($dietaryStatus) || !is_array($dietaryStatus)) {
            return $defaults;
        }

        // Unified boolean resolver — matches model logic
        $resolveValue = function ($key) use ($dietaryStatus) {

            if (!isset($dietaryStatus[$key])) {
                return false;
            }

            $value = $dietaryStatus[$key];

            // Simple boolean
            if (is_bool($value)) {
                return $value;
            }

            // Nested array formats
            if (is_array($value)) {

                // Prefer 'status' field (same as model getDietaryStatus)
                if (array_key_exists('status', $value)) {
                    return (bool) $value['status'];
                }

                // FSANZ alternative field name 'likely_compliant' 
                if (array_key_exists('likely_compliant', $value)) {
                    return (bool) $value['likely_compliant'];
                }

                // Old format: claim
                if (array_key_exists('claim', $value)) {
                    return (bool) $value['claim'];
                }
            }

            return false;
        };

        return [
            'vegan'       => $resolveValue('vegan'),
            'vegetarian'  => $resolveValue('vegetarian'),
            'halal'       => $resolveValue('halal'),
            'kosher'      => $resolveValue('kosher'),

            // Gluten free supports multiple FSANZ field names
            'glutenFree'  => $resolveValue('gluten_free') || $resolveValue('glutenFree'),

            // Lactose free supports multiple FSANZ field names
            'lactoseFree' => $resolveValue('lactose_free') || $resolveValue('lactoseFree'),
        ];
    }


    /**
     * Format ingredients list from estimated_ingredients JSON
     */
    protected function formatIngredientsList($estimatedIngredients): ?string
    {
        if (empty($estimatedIngredients)) return null;

        try {
            $ingredients = [];

            if (is_array($estimatedIngredients)) {
                // Check if it's a simple array
                if (isset($estimatedIngredients[0])) {
                    $ingredients = $estimatedIngredients;
                }
                // Handle object with primary_ingredients array
                elseif (!empty($estimatedIngredients['primary_ingredients'])) {
                    $ingredients = $estimatedIngredients['primary_ingredients'];
                }
                // Handle object with ingredients array
                elseif (!empty($estimatedIngredients['ingredients'])) {
                    $ingredients = $estimatedIngredients['ingredients'];
                }
            }

            $names = collect($ingredients)
                ->map(function ($ing) {
                    if (is_string($ing)) return $ing;
                    return $ing['name'] ?? $ing['ingredient'] ?? $ing['description'] ?? null;
                })
                ->filter()
                ->map(fn($name) => trim($name))
                ->filter(fn($name) => strlen($name) > 0)
                ->values()
                ->toArray();

            return count($names) > 0 ? implode(', ', $names) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format allergen statement from estimated_allergens JSON
     */
    protected function formatAllergenStatement($allergens): ?string
    {
        if (empty($allergens) || !is_array($allergens)) {
            return null;
        }

        try {
            $list = [];

            // Extract "contains"
            if (!empty($allergens['contains']) && is_array($allergens['contains'])) {
                foreach ($allergens['contains'] as $item) {
                    if (!empty($item['allergen'])) {
                        $list[] = $item['allergen'];
                    }
                }
            }

            // Extract "may_contain"
            if (!empty($allergens['may_contain']) && is_array($allergens['may_contain'])) {
                foreach ($allergens['may_contain'] as $item) {
                    if (!empty($item['allergen'])) {
                        $list[] = $item['allergen'];
                    }
                }
            }

            return !empty($list) ? implode(', ', $list) : null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert FSANZ australia_percent (0-100) to specification format (0-1 decimal)
     */
    protected function convertAustraliaPercent($fsanzPercent): ?float
    {
        if ($fsanzPercent === null) return null;

        $num = (float) $fsanzPercent;
        if (is_nan($num)) return null;

        // Clamp to valid range 0-100
        $clamped = max(0, min(100, $num));

        // Convert to decimal with 4 decimal places
        return round($clamped / 100, 4);
    }

    protected function safeNumber($value): ?float
    {
        if ($value === null || $value === '') return null;
        $num = (float) $value;
        return is_nan($num) ? null : $num;
    }

    protected function safeString($value): ?string
    {
        if ($value === null || $value === '') return null;
        return trim((string) $value) ?: null;
    }
}
