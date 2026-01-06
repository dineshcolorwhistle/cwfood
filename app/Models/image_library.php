<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class image_library extends Model
{
    use HasFactory;

    protected $fillable = [
        'SKU',
        'module',
        'module_id',
        'image_number',
        'image_name',
        'default_image',
        'file_format',
        'file_size',
        'folder_path',
        'uploaded_by',
        'last_modified_by'
    ];

}
