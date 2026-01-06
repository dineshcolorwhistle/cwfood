<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the client associated with the workspace.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created the workspace.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the workspace.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Return the validation rules for the Workspace model.
     */
    public static function validationRules($id = null, $clientId = null)
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('workspaces')
                    ->where('client_id', $clientId)
                    ->ignore($id)
            ],
            'description' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}
