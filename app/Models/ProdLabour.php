<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdLabour extends Model
{
    protected $table = 'prod_labours';

    protected $fillable = [
        'product_id',
        'labour_id',
        'labour_type',
        'people_count',
        'hours_per_person',
        'hourly_rate',
        'product_units',
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
     * Get the labour associated with the record.
     */
    public function labour()
    {
        return $this->belongsTo(Labour::class);
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
