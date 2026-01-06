<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdTag extends Model
{
    use HasFactory;
    protected $table = 'prod_tags';
    protected $fillable = [
        'name',
    ];
}
