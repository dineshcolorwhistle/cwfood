<?php
namespace App\Auth;

use Auth0\Laravel\Users\Contract;
use Auth0\Laravel\Users\StatefulUser;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;

class Auth0UserRepository implements Contract
{
    public function fromSession($user): ?Authenticatable
    {
        return $this->resolveUser($user);
    }

    public function fromAccessToken($user): ?Authenticatable
    {
        return $this->resolveUser($user);
    }

    public function fromIdToken($user): ?Authenticatable
    {
        return $this->resolveUser($user);
    }

    protected function resolveUser($auth0User): ?Authenticatable
    {
        $email = $auth0User?->getAttribute('email');
        
        if (!$email) {
            return null;
        }

        // Return your local DB user
        return User::where('email', $email)->first();
    }
}
