<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Http\Controllers\Auth\CognitoAuthController;

class CognitoToken extends Command
{
    protected $signature = 'app:cognito-token-refresh';
    protected $description = 'Cognito access token refresh before expired.';

    public function handle()
    {
        app(CognitoAuthController::class)->ensureFreshToken();
        return;
    }
}