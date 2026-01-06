<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Product_category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'client_id',
        'workspace_id',
        'contact_id',
        'created_by',
        'updated_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
  
    public function products()
    {
        return $this->hasMany(Product::class, 'prod_category')
                    ->select(['id', 'prod_name', 'prod_category']); // include foreign key!
    }

    
}
