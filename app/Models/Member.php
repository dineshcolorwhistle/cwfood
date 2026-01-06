<?php

namespace App\Models;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Member extends Model implements Authenticatable
{
    use HasFactory, AuthenticatableTrait, Notifiable;

    protected $fillable = [
        'client_id',
        'workspace_id',
        'user_id',
        'name',
        'email',
        'password',
        'role_id',
        'assign_workspace',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the creator of the user.
     */
    public function creator()
    {
        return $this->belongsTo(Member::class, 'created_by');
    }

    /**
     * Get the updater of the user.
     */
    public function updater()
    {
        return $this->belongsTo(Member::class, 'updated_by');
    }

    public static function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required','email'],
            'role_id' => 'required|exists:roles,id',
            // 'email' => ['required','email',Rule::unique('members')->ignore($id)],
            // 'password' => $id
            //     ? 'nullable|min:6'
            //     : 'nullable|min:6', // Default to 'Secret' if not provided
        ];
    }

    // Mutator to hash password
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            // Only hash if not already hashed
            $this->attributes['password'] = is_null($value) ? $value : Hash::make($value);
        }
    }

   


    // Role-based access methods
    public function isNutriflowAdmin()
    {
        return $this->role && $this->role->scope === 'platform';
    }

    public function isClientSuperAdmin()
    {
        return $this->role && $this->role->scope === 'client';
    }

    public function isWorkspaceAdmin()
    {
        return $this->role && $this->role->scope === 'workspace';
    }

    // Access Control Methods
    public function canAccessPlatform()
    {
        return $this->isNutriflowAdmin();
    }

    public function canAccessClient($clientId)
    {
        return $this->isNutriflowAdmin() ||($this->client_id == $clientId && ($this->isClientSuperAdmin()));
    }

    public function canAccessWorkspace($workspaceId, $clientId = null)
    {
        // If no client is specified, try to get from current context
        $clientId = $clientId ?? $this->client_id;

        return $this->isNutriflowAdmin() ||
            ($this->client_id == $clientId &&
                ($this->isClientSuperAdmin() || $this->isWorkspaceAdmin()));
    }
}
