<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroInvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id','xero_line_item_id',
        'item_code','description',
        'quantity','unit_amount','line_amount',
        'account_code','tax_type','tax_amount','discount_rate',
    ];

    public function invoice()
    {
        return $this->belongsTo(XeroInvoice::class, 'invoice_id');
    }
}