<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client_key_personnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'keyperson_name', 
        'key_personnel',
        'created_by',
        'updated_by',
    ];

}
