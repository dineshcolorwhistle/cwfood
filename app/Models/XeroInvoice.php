<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroInvoice extends Model
{
    protected $fillable = [
        'client_id','xero_tenant_id','xero_invoice_id',
        'client_contact_id','xero_contact_id',
        'type','status','number','reference',
        'date','due_date','currency_code',
        'sub_total','total_tax','total','amount_due','amount_paid','amount_credited',
        'xero_updated_at_utc',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'xero_updated_at_utc' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(XeroInvoiceLine::class, 'invoice_id');
    }

    public function contact()
    {
        return $this->belongsTo(Client_contact::class, 'client_contact_id');
    }
}