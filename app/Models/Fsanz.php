<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fsanz extends Model
{
    use HasFactory;

    protected $table = 'fsanz_details';  // Link to the renamed database table

    // Specify which columns can be mass-assigned
    protected $fillable = [
        'food_id',
        'food_name',
        'energy_kj',
        'protein_g',
        'fat_total_g',
        'fat_saturated_g',
        'carbohydrate_g',
        'total_sugars_g',
        'sodium_mg',
        'description',
        'specific_gravity'
    ];
}
