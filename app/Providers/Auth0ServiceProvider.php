<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Auth0\Laravel\Users\RepositoryContract;
use App\Auth\Auth0UserRepository;

class Auth0ServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(RepositoryContract::class, Auth0UserRepository::class);
    }
}
