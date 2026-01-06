<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdMachinery extends Model
{
    protected $table = 'prod_machinery';

    protected $fillable = [
        'product_id',
        'machinery_id',
        'machine_type',
        'hours',
        'cost_per_hour',
        'product_units',
        'weight',
        'cost_per_kg',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the product associated with the record.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the machinery associated with the record.
     */
    public function machinery()
    {
        return $this->belongsTo(Machinery::class);
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
