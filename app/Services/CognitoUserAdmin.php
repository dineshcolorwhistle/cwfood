<?php
namespace App\Services;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;
use App\Services\AwsRoleCredentials;   

class CognitoUserAdmin
{
    public function __construct(private AwsRoleCredentials $creds) {}

    private function client(): CognitoIdentityProviderClient
    {
        $c = $this->creds->get();

        return new CognitoIdentityProviderClient([
            'version'     => '2016-04-18',
            'region'      => env('AWS_REGION'),
            'credentials' => [
                'key'    => $c['key'],
                'secret' => $c['secret'],
                'token'  => $c['token'],
            ],
        ]);
    }

    /**
     * Create a user with verified email (no welcome email).
     * Returns AWS response array.
     */
    public function createUser(string $email, array $extraAttributes = []): array
    {
        $attrs = [
            ['Name' => 'email',          'Value' => $email],
            ['Name' => 'email_verified', 'Value' => 'true'],
        ];

        // Merge extra attributes (e.g., given_name, family_name)
        foreach ($extraAttributes as $name => $value) {
            if ($value === null || $value === '') continue;
            $attrs[] = ['Name' => $name, 'Value' => (string)$value];
        }

        $params = [
            'UserPoolId'     => env('COGNITO_USER_POOL_ID'),
            'Username'       => $email,             // pool default uses email as username; adjust if needed
            'UserAttributes' => $attrs,
            'MessageAction'  => 'SUPPRESS',         // don't send invite email
        ];

        try {
            return $this->client()->adminCreateUser($params)->toArray();
        } catch (AwsException $e) {
            // Surface a clean message to controller
            throw new \RuntimeException($e->getAwsErrorMessage() ?: $e->getMessage(), previous: $e);
        }
    }

    /**
     * Delete a user by Cognito Username (email if you use email as username).
     */
    public function deleteUser(string $username): void
    {
        $params = [
            'UserPoolId' => env('COGNITO_USER_POOL_ID'),
            'Username'   => $username,
        ];
        try {
            $this->client()->adminDeleteUser($params);
        } catch (AwsException $e) {
            throw new \RuntimeException($e->getAwsErrorMessage() ?: $e->getMessage(), previous: $e);
        }
    }

    /**
     * Check a user by Cognito Username (email if you use email as username).
    */
    public function checkUser(string $username): array
    {      
        $params = [
            'UserPoolId' => env('COGNITO_USER_POOL_ID'),
            'Filter'     => 'email = "' . $username . '" and status = "CONFIRMED"',
            'Limit'      => 1,
        ];
        try {  
            $result = $this->client()->listUsers($params)->toArray();
            return $result['Users'] ?? [];   // directly return users
        } catch (AwsException $e) {
            throw new \RuntimeException($e->getAwsErrorMessage() ?: $e->getMessage(), previous: $e);
        }
    }
    
}