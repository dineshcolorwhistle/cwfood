<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'prod_name',
        'prod_sku',
        'description_short',
        'description_long',
    ];

    public static function rules()
    {
        return [
            'prod_name' => 'required|string|max:255',
            'prod_sku' => 'required|string|unique:products,prod_sku',
            'description_short' => 'required|string',
            'description_long' => 'required|string',
        ];
    }
}
