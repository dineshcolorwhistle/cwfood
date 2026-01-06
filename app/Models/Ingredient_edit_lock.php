<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Ingredient_edit_lock extends Model
{
    use HasFactory;
    protected $fillable = [
        'sku_id',
        'user_id',
        'locked_at',
        'expires_at'
    ];
}
