<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client_billing extends Model
{
    use HasFactory;

    protected $table = 'Client_billings';

    protected $fillable = [
        'client_id',
        'stripe_customer_id',
        'payment_method_id',
        'subscription_id',
        'price_id',
        'subscription_plan'
    ];

    /**
     * Get the user who created the role.
    */
    public function clientSubscription()
    {
        return $this->belongsTo(ClientSubscription::class, 'client_id','client_id');
    }


}
