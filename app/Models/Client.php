<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'type',
        'discount',
        'subscription_status',
        'current_subscription_id',
        'created_by',
        'updated_by'
    ];

    const STATUS = [1 => 'Active', 0 => 'Inactive'];
    const SUBSCRIPTION_STATUS = ['active', 'inactive', 'expired'];

    /**
     * Get the user who created the client.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the client.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The current active subscription associated with the client.
     */
    public function currentSubscription()
    {
        return $this->belongsTo(ClientSubscription::class, 'current_subscription_id');
    }

    public function CompanyCategory()
    {
        return $this->hasMany(Client_company_category::class, 'client_id');
    }

    public function CompanyTags()
    {
        return $this->hasMany(Client_company_tag::class, 'client_id');
    }

    public function ContactCategory()
    {
        return $this->hasMany(Client_contact_category::class, 'client_id');
    }

    public function ContactTags()
    {
        return $this->hasMany(Client_contact_tag::class, 'client_id');
    }

    public function Contacts()
    {
        return $this->hasMany(Client_contact::class, 'client_id');
    }

    public function Companies()
    {
        return $this->hasMany(Client_company::class, 'client_id');
    }

    /**
     * Get the subscriptions associated with the client.
     */
    public function subscriptions()
    {
        return $this->hasMany(ClientSubscription::class);
    }

    public static function validationRules($id = null)
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('clients')->ignore($id)
            ],
            'description' => 'nullable|max:500',
            'status' => ['nullable', 'in:' . implode(',', array_keys(self::STATUS))],
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'users_allocated' => 'nullable|integer|min:0',
            'raw_materials_allocated' => 'nullable|integer|min:0',
            'skus_allocated' => 'nullable|integer|min:0',
            'work_spaces_allocated' => 'nullable|integer|min:0',
            'type' => 'nullable|string',
            'discount' => 'nullable|integer|min:0',
        ];
    }
}
