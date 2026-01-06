<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdIngredient extends Model
{
    protected $table = 'prod_ingredients';

    protected $fillable = [
        'product_id',
        'ing_id',
        'product_sku',
        'ing_sku',
        'ing_name',
        'quantity_weight',
        'units_g_ml',
        'component',
        'kitchen_comments',
        'spec_grav',
        'quantity_g',
        'quantity_loss_g',
        'quantity_waste_g',
        'cost_per_kg',
        'cost_per_batch',
        'allergens',
        'peel_name',
        'ingredient_order',
        'ingredient_order_weight'
    ];

    // Set default values
    protected $attributes = [
        'units_g_ml' => 'g',
        'spec_grav' => 1.000,
        'quantity_g' => 0.00,
        'quantity_loss_g' => 0.00,
        'quantity_waste_g' => 0.00,
        'cost_per_kg' => 0.00,
        'cost_per_batch' => 0.00
    ];

    /**
     * The product that this ingredient belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the ingredient associated with this product ingredient.
     */
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ing_id');
    }
}
