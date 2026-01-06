<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client_factory_location extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'location_name', 
        'factory_locations',
        'created_by',
        'updated_by',
    ];

}
