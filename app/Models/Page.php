<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'url',
        'title',
        'description',
        'content',
        'scope',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the user who created the page.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the page.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Return the validation rules for the Page model.
     */
    public static function validationRules($id = null)
    {
        return [
            'title' => ['required', 'max:255'],
            'slug' => ['required', 'max:255', Rule::unique('pages')->ignore($id)],
            'url' => ['nullable', 'max:255'],
            'description' => 'nullable',
            'content' => 'nullable',
            'scope' => 'nullable'
        ];
    }
}
