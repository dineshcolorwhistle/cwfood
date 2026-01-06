<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Services\CognitoUserAdmin;
use Illuminate\Http\Request;

class CognitoUserController extends Controller
{
    public function __construct(private CognitoUserAdmin $admin) {}

    // POST /admin/cognito-users
    public function store($email)
    {
        $data = [
            'email'        => $email,
            // optional attributes you allow ops to set:
            'given_name'   => null,
            'family_name'  => null,
            'name'         => null,
        ];

        try {
            $res = $this->admin->createUser(
                $data['email'],
                array_filter([
                    'given_name'  => $data['given_name']  ?? null,
                    'family_name' => $data['family_name'] ?? null,
                    'name'        => $data['name']        ?? null,
                ])
            );

            return response()->json([
                'ok'     => true,
                'user'   => [
                    'username' => $res['User']['Username'] ?? $data['email'],
                    'status'   => $res['User']['UserStatus'] ?? null,
                    'created'  => $res['User']['UserCreateDate'] ?? null,
                ],
            ], 201);

        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $code = str_contains(strtolower($msg), 'exist') ? 409 : 400;
            return response()->json(['ok' => false, 'error' => $msg], $code);
        }
    }

    // DELETE /admin/cognito-users/{username}
    public function destroy(string $username)
    {
        // If your pool uses email as username, {username} is the email.
        $username = urldecode($username);
        try {
            $this->admin->deleteUser($username);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $code = str_contains(strtolower($msg), 'not found') ? 404 : 400;
            return response()->json(['ok' => false, 'error' => $msg], $code);
        }
    }

    // Check Exist /admin/cognito-users/{username}
    public function Check_user_exist(string $username)
    {
        // If your pool uses email as username, {username} is the email.
        $username = urldecode($username);
        try {
            $result = $this->admin->checkUser($username);
            if (!empty($result['Users'])) {
                return response()->json(['ok' => true]);
            } else {
                return response()->json(['ok' => true,'message' => 'User not exist']);
            }            
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $code = str_contains(strtolower($msg), 'not found') ? 404 : 400;
            return response()->json(['ok' => false, 'error' => $msg], $code);
        }
    }


}