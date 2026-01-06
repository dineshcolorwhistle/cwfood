<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Machinery extends Model
{
    use HasFactory;

    protected $table = 'machinery';

    protected $fillable = [
        'name',
        'model_number',
        'year_of_manufacture',
        'manufacturer',
        'serial_number',
        'energy_efficiency',
        'power_consumption_kw',
        'cost_per_hour_aud',
        'maintenance_frequency',
        'last_maintenance_date',
        'condition',
        'location',
        'production_rate_units_hr',
        'setup_time_minutes',
        'downtime_impact_aud_hr',
        'wear_and_tear_factor',
        'depreciation_rate_percent_yr',
        'notes',
        'created_by',
        'updated_by',
        'client_id',
        'workspace_id',
        'machinery_id',
        'archive'
    ];

    protected $dates = ['last_maintenance_date'];

    /**
     * Get the user who created the machinery.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the machinery.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Client_company::class, 'manufacturer');
    }

    /**
     * Return the validation rules for the Machinery model.
     */
    public static function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:100',
            'cost_per_hour_aud' => 'required|numeric|min:0',
            'model_number' => 'nullable|string|max:50',
            'year_of_manufacture' => 'nullable|integer',
            'manufacturer' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:50',
            'energy_efficiency' => 'nullable',
            'power_consumption_kw' => 'nullable|numeric|min:0.1',
            'maintenance_frequency' => 'nullable',
            'last_maintenance_date' => 'nullable|date',
            'condition' => 'nullable',
            'location' => 'nullable|string|max:100',
            'production_rate_units_hr' => 'nullable|numeric|min:0',
            'setup_time_minutes' => 'nullable|integer|min:1',
            'downtime_impact_aud_hr' => 'nullable|numeric|min:0',
            'wear_and_tear_factor' => 'nullable|numeric|between:0,1',
            'depreciation_rate_percent_yr' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'machinery_id' => [
                'required',
                'string',
                Rule::unique('machinery', 'machinery_id')
                    ->where(function ($query) {
                        return $query->where('client_id', session('client'))
                                    ->where('workspace_id', session('workspace'));
                    })
                    ->ignore($id),
               ]
        ];
    }


    /**
     * Return the validation messages for the Machinery model.
     */
    public static function validationMessages()
    {
        return [
            'name.required' => 'Machine Name is required.',
            'name.max' => 'Machine Name may not be greater than 100 characters.',
            'cost_per_hour_aud.required' => 'Cost per Hour is required.',
            'cost_per_hour_aud.numeric' => 'Cost per Hour must be a number.',
            'cost_per_hour_aud.min' => 'Cost per Hour must be at least 0.'
        ];
    }
}
