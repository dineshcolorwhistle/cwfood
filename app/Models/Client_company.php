<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client_company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'client_id',
        'company_name',
        'contact_id',
        'website',
        'ABN',
        'ACN',
        'billing_address',
        'delivery_address',
        'notes',
        'company_tags',
        'company_category',
        'created_by',  
        'updated_by',
        'archive',
        'source',
        'xero_tenant_id'
    ];
    
    public function companyTags()
    {
        return $this->belongsTo(Client_company_tag::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

   public function primaryContact()
    {
        return $this->hasOne(Client_contact::class, 'company', 'id')->where('primary_contact', 1);
    }

    public function Category()
    {
        return $this->belongsTo(Client_company_category::class, 'company_category');
    }

    
}
