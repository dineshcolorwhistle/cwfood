<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fsanz_weight_detail extends Model
{
    use HasFactory;
    // Specify which columns can be mass-assigned
    protected $fillable = [
        'food_group_id',
        'food_group_name',
        'food_description',
        'preparation_method',
        'weight_change_factor'
    ];
}