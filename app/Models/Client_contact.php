<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client_contact extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'company',
        'first_name',
        'last_name',
        'email',
        'phone',
        'primary_contact',
        'notes',
        'contact_tags',
        'contact_category',
        'created_by',
        'updated_by',
        'archive',
        // NEW fields from Xero integration
        'source',
        'xero_tenant_id',
        'xero_contact_id',
        'xero_updated_at_utc',
        'xero_is_archived',
    ];
    
    public function company()
    {
        return $this->belongsTo(Client_company::class, 'company', 'id');
    }

    public function ClientCompany()
    {
        return $this->belongsTo(Client_company::class, 'company', 'id');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function Category()
    {
        return $this->belongsTo(Client_contact_category::class, 'contact_category');
    }

    public function xeroInvoices()
    {
        return $this->hasMany(\App\Models\XeroInvoice::class, 'client_contact_id');
    }

    public function xeroCreditNotes()
    {
        return $this->hasMany(\App\Models\XeroCreditNote::class, 'client_contact_id');
    }

}
