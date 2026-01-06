<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'company_name', 
        'industry', 
        'company_email', 
        'phone_number', 
        'website_url',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'established_date',
        'legal_structure',
        'number_of_employees',
        'annual_revenue',
        'social_media_links',
        'company_logo_url',
        'company_description',
        'business_hours',
        'created_by',
        'updated_by',
        'trading_name',
        'gstStatus',
        'abn',
        'acn',
        'nzbn',
        'intCompanyReg',
        'annualCountry',
        'factory_locations',
        'key_personnel',
        'primaryColor',
        'secondaryColor',
        'accentColor',
        'timeZone',
        'dateFormat',
        'timeFormat',
        'numberFormat',
        'emailNotifications',
        'inAppNotifications',
        'notificationFrequency',
        'anzsicCode',
        'processingLevelPrimarySelect',
        'processingLevelSecondarySelect',
        'processingLevelTertiarySelect',
        'productCategorySectorSelect',
        'productionTypeSelect',
        'social_links',
        'certifications',
        'commonAllergens',
        'lastAuditDate',
        'nextAuditDate'
    ];

    // Define any dates (e.g., for casting timestamps)
    protected $dates = ['established_date', 'created_at', 'updated_at'];

    // Relationship with client
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function FactoryDetails(){
        return $this->hasMany(client_factory_location::class, 'client_id', 'client_id');
    }

    public function KeypersonDetails(){
        return $this->hasMany(client_key_personnel::class, 'client_id', 'client_id');
    }
}
