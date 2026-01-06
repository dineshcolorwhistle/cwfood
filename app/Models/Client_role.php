<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Client_role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
        'client_id',
        'workspace_id'
    ];

    // Scopes
    const SCOPES = [
        'platform' => 'Platform',
        'client' => 'Client',
        'workspace' => 'Workspace'
    ];

    /**
     * Get the user who created the role.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the role.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Custom validation rules
    public static function validationRules($id = null)
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('roles')->ignore($id),
            ],
            'description' => 'nullable|max:500'
        ];
    }

    /**
     * Get the users that belong to the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

}
