<?php
namespace App\Services;

use Aws\Sts\StsClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AwsRoleCredentials
{
    public function get(): array
    {
        // Cache temporary creds to avoid calling STS on every request.
        return Cache::remember('aws:assumed:cognito-admin', now()->addMinutes(55), function () {
            $sts = new StsClient([
                'version' => '2011-06-15',
                'region'  => env('AWS_REGION'),
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            $resp = $sts->assumeRole([
                'RoleArn'         => env('COGNITO_ADMIN_ROLE_ARN'),
                'RoleSessionName' => 'batchbase-web-' . Str::random(8),
                'DurationSeconds' => 3600,
            ]);

            $c = $resp->get('Credentials');

            return [
                'key'    => $c['AccessKeyId'],
                'secret' => $c['SecretAccessKey'],
                'token'  => $c['SessionToken'],
                'expires'=> $c['Expiration'],
            ];
        });
    }
}