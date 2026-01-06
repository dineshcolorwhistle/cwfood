<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdPackaging extends Model
{
    protected $table = 'prod_packaging';

    protected $fillable = [
        'product_id',
        'packaging_id',
        'packaging_name',
        'packaging_type',
        'cost_per_sell_unit',
        'weight_per_sell_unit',
        'cost_per_kg',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the product associated with the record.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the packaging associated with the record.
     */
    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    /**
     * Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
