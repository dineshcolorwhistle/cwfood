<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Freight extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'freight_id',
        'freight_supplier',
        'freight_price',
        'freight_unit',
        'parcel_weight',
        'created_by',
        'updated_by',
        'client_id',
        'workspace_id',
        'archive'
    ];

    /**
     * Get the user who created the labour.
     */
    public function creator()
    {
        return $this->belongsTo(Freight::class, 'created_by');
    }

    /**
     * Get the user who updated the labour.
     */
    public function updater()
    {
        return $this->belongsTo(Freight::class, 'updated_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Client_company::class, 'freight_supplier');
    }

    /**
     * Return the validation rules for the Labour model.
     */
    public static function validationRules($id = null, )
    {
        return [
            'name'=>['required','string',
                        Rule::unique('freights', 'name')
                        ->where(function ($query) {
                            return $query->where('client_id', session('client'))
                                        ->where('workspace_id', session('workspace'));
                        })
                        ->ignore($id),
                    ],
            'description'=>'nullable|string',
            'freight_id'=>'nullable|string',
            'freight_supplier'=>'nullable|string',
            'freight_price'=>'required|numeric',
            'freight_unit'=>'required|string',
            'parcel_weight'=>'nullable|numeric'
        ];
    }

    /**
     * Return the validation messages for the Labour model.
     */
    public static function validationMessages()
    {
        return [
            'name.required' => 'name is required.',            
            'freight_price.required' => 'Freight Price is required.',
            'freight_unit.required' => 'Freight Unit is required.'
        ];
    }
}
