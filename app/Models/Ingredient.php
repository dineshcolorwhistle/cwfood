<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $table = 'ingredients';

    protected $fillable = [
        'ing_sku',
        'name_by_kitchen',
        'name_by_supplier',
        'ing_image',
        'raw_material_status',
        'raw_material_ranging',
        'ing_tags',
        'gtin',
        'supplier_code',
        'supplier_name',
        'category',
        'ingredients_list_supplier',
        'allergens',
        'price_per_item',
        'units_per_item',
        'ingredient_units',
        'purchase_units',
        'price_per_kg_l',
        'country_of_origin',
        'australian_percent',
        'specific_gravity',
        'energy_kj',
        'protein_g',
        'fat_total_g',
        'fat_saturated_g',
        'carbohydrate_g',
        'sugars_g',
        'sodium_mg',
        'shelf_life',
        'raw_material_description',
        'supplier_spec_url',
        'ai_predicted_allergence',
        'ingredients_peal',
        'error_allergies_missing',
        'error_peal_missing',
        'error_county_missing',
        'error_nip_missing',
        'error_missing_shelf_life',
        'client_id',
        'workspace_id',
        'archive',

    ];

    /**
     * Scope a query to search ingredients by SKU or name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('ing_sku', 'LIKE', "%{$search}%")
            ->orWhere('name_by_kitchen', 'LIKE', "%{$search}%");
    }


    public function scopesearchCategory($query, $search)
    {
        return $query->where('category', $search);
    }

    public function scopesearchSubCategory($query, $search)
    {
        return $query->where('sub_category', $search);
    }

    public function scopesearchStatus($query, $search)
    {
        return $query->where('status', $search);
    }
    public function scopesearchActive($query, $search)
    {
        return $query->where('is_active', $search);
    }

    /**
     * Get the user who created the ingredient.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the ingredient.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Client_company::class, 'supplier_name');
    }

    public function raw_category()
    {
        return $this->belongsTo(Rawmaterial_category::class, 'category');
    }

    public function Country()
    {
        return $this->belongsTo(Ing_country::class, 'country_of_origin','COID');
    }



}
