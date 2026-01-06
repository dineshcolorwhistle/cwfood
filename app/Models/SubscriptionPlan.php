<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_code',
        'subscription_name',
        'ideal_for',
        'description',
        'monthly_cost_per_user',
        'annual_cost_per_user',
        'max_users',
        'min_users',
        'max_raw_materials',
        'max_skus',
        'max_work_spaces',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the validation rules for the model.
     */
    public static function validationRules($id = null)
    {
        return [
            'plan_code' => 'required|string|max:255|unique:subscription_plans,plan_code,' . $id,
            'subscription_name' => 'required|string|max:255|unique:subscription_plans,subscription_name,' . $id,
            'ideal_for' => 'nullable|string',
            'monthly_cost_per_user' => 'required|numeric|min:0',
            'annual_cost_per_user' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'min_users' => 'required|integer|min:1',
            'max_raw_materials' => 'required|integer|min:1',
            'max_skus' => 'required|integer|min:1',
            'max_work_spaces' => 'required|integer|min:1',
        ];
    }

    /**
     * Get the user who created the role.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the role.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
