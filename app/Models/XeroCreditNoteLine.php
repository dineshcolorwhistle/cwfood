<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroCreditNoteLine extends Model
{
    protected $fillable = [
        'credit_note_id','xero_line_item_id',
        'item_code','description',
        'quantity','unit_amount','line_amount',
        'account_code','tax_type','tax_amount','discount_rate',
    ];

    public function creditNote()
    {
        return $this->belongsTo(XeroCreditNote::class, 'credit_note_id');
    }
}