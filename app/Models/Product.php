<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'prod_sku',
        'prod_name',
        'prod_custom_name',
        'prod_image',
        'barcode_gs1',
        'barcode_gtin14',
        'description_long',
        'description_short',
        'prod_tags',
        'product_status',
        'product_ranging',
        'prod_category',
        'weight_ind_unit_g',
        'weight_retail_unit_g',
        'weight_carton_g',
        'weight_pallet_g',
        'count_ind_units_per_retail',
        'count_retail_units_per_carton',
        'count_cartons_per_pallet',
        'price_ind_unit',
        'price_retail_unit',
        'price_carton',
        'price_pallet',
        'price_sell_unit_wholesale',
        'price_sell_unit_wholesale_freight',
        'recipe_method',
        'recipe_notes',
        'recipe_oven_temp',
        'recipe_oven_temp_unit',
        'recipe_oven_time',
        'recipe_mould_type',
        'recipe_baking_instructions',
        'batch_initial_weight_g',
        'batch_baking_loss_percent',
        'batch_after_baking_loss_g',
        'batch_waste_percent',
        'batch_after_waste_g',
        'serv_per_package',
        'serv_size_g',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'updated_reason',
        'updated_version',
        'energy_kJ_per_100g',
        'protein_g_per_100g',
        'fat_total_g_per_100g',
        'fat_saturated_g_per_100g',
        'carbohydrate_g_per_100g',
        'sugar_g_per_100g',
        'sodium_mg_per_100g',
        'energy_kJ_per_serve',
        'protein_g_per_serve',
        'fat_total_g_per_serve',
        'fat_saturated_g_per_serve',
        'carbohydrate_g_per_serve',
        'sugar_g_per_serve',
        'sodium_mg_per_serve',
        'labelling_ingredients',
        'labelling_allergens',
        'labelling_may_contain',
        'labelling_ingredients_override',
        'labelling_allergens_override',
        'labelling_may_contain_override',
        'country_of_origin',
        'pack_packaging1',
        'sub_receipe',
        'contingency',
        'client_id',
        'workspace_id',
        'wholesale_price_sell',
        'wholesale_price_kg_price',
        'distributor_price_sell',
        'distributor_price_kg_price',
        'rrp_ex_gst_sell',
        'rrp_ex_gst_price',
        'rrp_inc_gst_sell',
        'rrp_inc_gst_price',
        'favorite',
        'archive',
        'retailer_charges',
        'wholesale_margin',
        'distributor_margin',
        'retailer_margin',
        'company_factory',
        'company_keyperson'
    ];

    /**
     * Get the ingredients for the product.
     */
    public function ingredients()
    {
        return $this->hasMany(ProdIngredient::class, 'product_id')->orderBy('ingredient_order', 'asc');
    }

    /**
     * Get the user who created the product.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the product.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function productClient()
    {
        return $this->belongsTo(ClientProfile::class, 'client_id', 'client_id');
    }

    public function prodLabels()
    {
        return $this->belongsTo(ProdLabel::class, 'id', 'product_id');
    }


    /**
     * Get the ingredients for the product.
     */
    public function productIngredients()
    {
        return $this->hasMany(ProdIngredient::class, 'product_id');
    }



    public function productLabels()
    {
        return $this->hasMany(ProdLabel::class, 'product_id');
    }

    /**
     * Get the tags associated with the product.
     */
    public function getProdTagsAttribute($value)
    {
        return json_decode($value, true) ?? []; // Decode JSON stored in prod_tags field
    }

    /**
     * Set the tags associated with the product.
     */
    public function setProdTagsAttribute($value)
    {
        $this->attributes['prod_tags'] = json_encode($value); // Store tags as JSON
    }

    /**
     * Scope a query to search products based on SKU, name, or custom name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('prod_sku', 'LIKE', "%{$search}%")
            ->orWhere('prod_name', 'LIKE', "%{$search}%")
            ->orWhere('prod_custom_name', 'LIKE', "%{$search}%");
    }

    /**
     * Returns the oven time in HH:MM:SS format.
     */
    public function getFormattedOvenTimeAttribute()
    {
        // Return empty if null or empty
        if (empty($this->recipe_oven_time)) {
            return '';
        }

        // Convert to integer and handle non-numeric values
        $totalSeconds = intval($this->recipe_oven_time);

        // Calculate hours, minutes, and seconds
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }


    /**
     * Get the product image URL.
     * Returns the URL of the product image, or the default image URL if no image is set.
     */
    public function getImageUrlAttribute()
    {
        return !empty($this->prod_image)
            ? asset('assets/img/products/' . $this->prod_image)
            : asset('assets/img/prod_default.png');
    }


    /**
     * Get the product's images from the image library.
     */
    public function imageLibrary()
    {
        return $this->hasMany(image_library::class, 'module_id')->where('module', 'product');
    }

    /**
     * Get the product's associated labours.
     */
    public function prodLabours()
    {
        return $this->hasMany(ProdLabour::class);
    }

    /**
     * Get the product's associated machinery.
     */
    public function prodMachinery()
    {
        return $this->hasMany(ProdMachinery::class);
    }

    /**
     * Get the product's associated packaging.
     */
    public function prodPackaging()
    {
        return $this->hasMany(ProdPackaging::class);
    }

    public function prodFreights()
    {
        return $this->hasMany(ProdFreight::class);
    }


    /**
     * Calculate the cost per kg of the product, based on the price of one retail unit and the weight of that unit in grams.
     */
    public function getCostPerKgAttribute()
    {
        if ($this->weight_retail_unit_g > 0) {
            return ($this->price_retail_unit / ($this->weight_retail_unit_g / 1000));
        }
        return 0;
    }

    /**
     * Calculate the total cost of all labours for this product per kilogram, based on the sum of the cost per kg of each labour.
     */
    public function getTotalLabourCostPerKgAttribute()
    {
        return $this->prodLabours->sum('cost_per_kg');
    }

    public function getTotalFreightCostPerKgAttribute()
    {
        return $this->prodfreights->sum('cost_per_kg');
    }

    /**
     * Calculate the total cost of all machinery for this product per kilogram, based on the sum of the cost per kg of each machinery.
     */
    public function getTotalMachineryCostPerKgAttribute()
    {
        return $this->prodMachinery->sum('cost_per_kg');
    }

    // Get total packaging cost per kg
    public function getTotalPackagingCostPerKgAttribute()
    {
        return $this->prodPackaging->sum('cost_per_kg');
    }

    // Calculate contingency (assuming 5% of total cost)
    public function getContingencyPerKgAttribute()
    {
        $totalCost = $this->cost_per_kg +
            $this->total_labour_cost_per_kg +
            $this->total_machinery_cost_per_kg +
            $this->total_packaging_cost_per_kg;
            // dd($this->cost_per_kg);

        return $totalCost * $this->contingency / 100;
    }

    public function product_category()
    {
        return $this->belongsTo(Product_category::class, 'prod_category');
    }
}
