<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'client_id',
        'start_date',
        'end_date',
        // 'users_allocated',
        // 'raw_materials_allocated',
        // 'skus_allocated',
        // 'work_spaces_allocated',
        'active_status',
        'created_by',
        'updated_by'
    ];

    

    const ACTIVE_STATUS = ['active', 'inactive', 'expired'];

    /**
     * Get the client associated with this subscription.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the plan associated with this subscription.
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get the user who created this record.
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
