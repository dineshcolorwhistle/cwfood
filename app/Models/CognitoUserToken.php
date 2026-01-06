<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CognitoUserToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cognito_access_token',
        'cognito_refresh_token',
        'cognito_id_token',
        'cognito_issued_at',
        'cognito_expires_in',
    ];

    protected $casts = [
        'cognito_issued_at' => 'integer',
        'cognito_expires_in' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

