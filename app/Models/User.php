<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class User extends Model implements Authenticatable
{
    use HasFactory, AuthenticatableTrait, Notifiable;

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'role_id',
        'picture',
        'created_by',
        'updated_by',
        'cognito_sub'
    ];

    
    /**
     * Get the client associated with the user.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

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
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater of the user.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($id)
            ],
            'client_id' => 'nullable|exists:clients,id',
            'role_id' => 'required|exists:roles,id',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

    // In User model
    public function getProfileImageUrlAttribute()
    {
        return !empty($this->picture)
            ? asset('assets/img/profile/' . $this->picture)
            : asset('assets/img/user-profile.png');
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

    public function isClientAdmin()
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
