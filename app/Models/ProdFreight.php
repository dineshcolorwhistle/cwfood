<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProdFreight extends Model
{
    protected $table = 'prod_freights';
    protected $fillable = [
        'product_id',
        'freight_id',
        'freight_supplier',
        'freight_cost',
        'freight_units',
        'freight_weight',
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
     * Get the labour associated with the record.
     */
    public function freight()
    {
        return $this->belongsTo(Freight::class);
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
