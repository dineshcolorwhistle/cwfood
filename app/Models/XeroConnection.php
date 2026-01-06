<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroConnection extends Model
{
    protected $fillable = [
        'user_id','client_id','tenant_id','tenant_name',
        'xero_user_id','access_token','refresh_token','expires_at',
        'last_synced_contacts_at','last_synced_invoices_at','last_synced_credit_notes_at','last_synced_status'
    ];

    protected $casts = [
        'expires_at'                   => 'datetime',
        'last_synced_contacts_at'      => 'datetime',   // <- rename
        'last_synced_invoices_at'      => 'datetime',
        'last_synced_credit_notes_at'  => 'datetime',
        'created_at'                   => 'datetime',
        'updated_at'                   => 'datetime',
    ];
}