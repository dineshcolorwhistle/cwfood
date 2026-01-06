<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroCreditNote extends Model
{
    protected $fillable = [
        'client_id','xero_tenant_id','xero_credit_note_id',
        'client_contact_id','xero_contact_id',
        'type','status','number','reference',
        'date','currency_code',
        'sub_total','total_tax','total','amount_applied','remaining_credit',
        'xero_updated_at_utc',
    ];

    protected $casts = [
        'date' => 'date',
        'xero_updated_at_utc' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(XeroCreditNoteLine::class, 'credit_note_id');
    }

    public function contact()
    {
        return $this->belongsTo(Client_contact::class, 'client_contact_id');
    }
}