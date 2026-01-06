<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Labour extends Model
{
    use HasFactory;

    protected $fillable = [
        'labour_id',
        'labour_type',
        'hourly_rate',
        'labour_category',
        'notes',
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
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the labour.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Return the validation rules for the Labour model.
     */
    public static function validationRules($id = null, )
    {
        return [
            'labour_id' => 'required',
            'labour_type' => ['required',
                                Rule::unique('labours', 'labour_type')
                                        ->where(function ($query) {
                                            return $query->where('client_id', session('client'))
                                                        ->where('workspace_id', session('workspace'));
                                        })
                                        ->ignore($id),
                            ],
            'hourly_rate' => 'required|numeric|min:0',
            'labour_category' => 'required',
            'notes' => 'nullable|string'
        ];
    }

    /**
     * Return the validation messages for the Labour model.
     */
    public static function validationMessages()
    {
        return [
            'labour_id.required' => 'Labour ID is required.',
            'labour_id.unique' => 'Labour ID has already been taken.',
            'labour_type.required' => 'Labour Type is required.',
            'hourly_rate.required' => 'Hourly Rate is required.',
            'hourly_rate.numeric' => 'Hourly Rate must be a number.',
            'hourly_rate.min' => 'Hourly Rate must be at least 0.',
            'labour_category.required' => 'Labour Category is required.',
            'notes.string' => 'Notes must be a string.'
        ];
    }
}
