<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Packaging extends Model
{
    use HasFactory;

    protected $table = 'packaging';
    protected $fillable = [
        'pack_sku',
        'supplier_id',
        'supplier_sku',
        'environmental',
        'type',
        'sales_channel',
        'name',
        'description',
        'purchase_price',
        'purchase_units',
        'price_per_unit',
        'created_by',
        'updated_by',
        'client_id',
        'workspace_id',
        'archive'
        
    ];

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

    /**
     * The supplier of the packaging.
     */
    public function supplier()
    {
        return $this->belongsTo(Client_company::class, 'supplier_id');
    }

    /**
     * Return the validation rules for the Packaging model.
     */
    public static function validationRules($id = null)
    {
        return [
            'pack_sku' => ['required', 'string', 'max:100', 
                            Rule::unique('packaging', 'pack_sku')
                                ->where(function ($query) {
                                    return $query->where('client_id', session('client'))
                                                ->where('workspace_id', session('workspace'));
                                })
                                ->ignore($id),
                        ],
            'supplier_id' => 'required|exists:client_companies,id',
            'supplier_sku' => 'nullable|string',
            'type' => 'required',
            'sales_channel' => 'nullable|string',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'purchase_units' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'environmental' => 'nullable|string'
        ];
    }
    /**
     * Return the validation messages for the Packaging model.
     */
    public static function validationMessages()
    {
        return [
            'pack_sku.required' => 'Pack SKU is required.',
            'pack_sku.max' => 'Pack SKU may not be greater than 100 characters.',
            'pack_sku.unique' => 'Pack SKU has already been taken.',
            'supplier_id.exists' => 'The selected supplier is invalid.',
            'type.required' => 'Packaging Type is required.',
            'sales_channel.required' => 'Channel is required.',
            'name.required' => 'Name is required.',
            'name.max' => 'Name may not be greater than 255 characters.',
            'description.string' => 'Description must be a string.',
            'purchase_price.required' => 'Price per Order is required.',
            'purchase_price.numeric' => 'Price per Order must be a number.',
            'purchase_price.min' => 'Price per Order must be at least 0.',
            'purchase_units.required' => 'Units per Order is required.',
            'purchase_units.integer' => 'Units per Order must be an integer.',
            'purchase_units.min' => 'Units per Order must be at least 1.'
        ];
    }
}
