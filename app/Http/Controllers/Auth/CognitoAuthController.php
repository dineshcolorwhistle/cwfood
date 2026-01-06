<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User,CognitoUserToken};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CognitoAuthController extends Controller
{
    /** Session key for OAuth state */
    private const OAUTH_STATE_KEY = 'oauth_state';

    /** Default post-login destination */
    private const DEFAULT_INTENDED = '/product-views';

    /**
     * GET /login
     *
     * - If an existing ID token is still fresh, skip Cognito and continue.
     * - Otherwise, ensure a stable session, create (or reuse) an OAuth `state`,
     *   and redirect to Cognito Hosted UI (optionally deep-linking an IdP).
     */
    public function redirect(Request $request): RedirectResponse
    {
        // If already signed-in with a still-valid Cognito ID token, continue
        if (Auth::check()) {
            $tokenDetails = CognitoUserToken::where('user_id', Auth::id())->first();
            if ($tokenDetails) {
                $issuedAt  = (int) ($tokenDetails->cognito_issued_at ?? 0);
                $expiresIn = (int) ($tokenDetails->cognito_expires_in ?? 0);
            } else {
                $issuedAt  = (int) ($request->session()->get('cognito_issued_at', 0));
                $expiresIn = (int) ($request->session()->get('cognito_expires_in', 0));
            }
            $age = time() - $issuedAt;


            $stillValid = $issuedAt > 0 && $expiresIn > 0 && ($age < ($expiresIn - 60));

            if ($stillValid) {
                return redirect()->intended(self::DEFAULT_INTENDED);
            }

            // Session present but token missing/expired → clean up before re-auth
            $request->session()->forget([
                'cognito_id_token',
                'cognito_access_token',
                'cognito_refresh_token',
                'cognito_expires_in',
                'cognito_issued_at',
            ]);
            CognitoUserToken::where('user_id',Auth::user()->id)->delete(); // Remove Congnito user
            Auth::logout();
        }

        // Reuse in-flight state if /login is hit twice; otherwise create a new one
        $state = (string) $request->session()->get(self::OAUTH_STATE_KEY, '');
        if ($state === '') {
            $state = bin2hex(random_bytes(32));
            $request->session()->put(self::OAUTH_STATE_KEY, $state);
            $request->session()->save();            // commit cookie to browser
            $request->session()->regenerateToken(); // rotate CSRF safely
        }

        // Pass state (and optional IdP) to Cognito Hosted UI
        $params = ['state' => $state];
        if ($idp = $request->query('identity_provider')) {
            $params['identity_provider'] = $idp; // e.g. 'Google' or your Azure provider name
        }

        return Socialite::driver('cognito')
            ->with($params)
            ->redirect();
    }

    /**
     * GET /oauth/cognito/callback
     *
     * - Validate our own OAuth `state` (CSRF protection).
     * - Exchange code for tokens via Socialite (stateless, since we validated state).
     * - Link user by `cognito_sub` or existing `email`, then save tokens (incl. ID token).
     * - Establish Laravel session and continue to the app.
     */
    public function callback(Request $request): RedirectResponse
    {
        // 1) Validate our own OAuth `state` (CSRF protection)
        $sentState   = (string) $request->query('state', '');
        $storedState = (string) $request->session()->pull(self::OAUTH_STATE_KEY, '');

        if ($sentState === '' || $storedState === '' || !hash_equals($storedState, $sentState)) {
            return redirect()->route('login')->with('auth_error', 'Sign-in expired. Please try again.');
        }

        // 2) Exchange code for tokens & profile (stateless because we validated state)
        $socialUser = Socialite::driver('cognito')->stateless()->user();

        // 3) Require an ID token and assert RS256 in the JWT header (defense-in-depth)
        $tokens  = $socialUser->accessTokenResponseBody ?? [];
        $idToken = $tokens['id_token'] ?? null;                    // OIDC ID token (JWT)
        if (!$idToken) {
            return redirect()->route('login')->with('auth_error', 'Sign-in failed. Please try again.');
        }

        // Base64URL-decode JWT header and enforce alg = RS256
        [$hdrB64] = explode('.', $idToken, 3);
        $hdrJson  = base64_decode(strtr($hdrB64, '-_', '+/'));
        $hdr      = json_decode($hdrJson, true);
        if (($hdr['alg'] ?? '') !== 'RS256') {
            return redirect()->route('login')->with('auth_error', 'Invalid token algorithm.');
        }

        // 4) Enforce verified email before creating/updating the local user
        $claims     = $socialUser->user ?? [];                     // OIDC claims as returned by provider
        $email      = $socialUser->getEmail() ?? ($claims['email'] ?? null);
        $isVerified = (bool)($claims['email_verified'] ?? false);

        if (!$email || !$isVerified) {
            // If a tenant with Azure doesn’t release 'email', map an approved claim to 'email' in Cognito.
            return redirect()->route('login')->with('auth_error', 'Email not verified. Contact support.');
        }

        // 5) Resolve or create the local user (prefer existing by cognito_sub, then by email)
        $user = User::where('cognito_sub', $socialUser->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            $user->fill([
                // 'name'        => $socialUser->getName() ?: ($claims['given_name'] ?? $user->name),
                'email'       => $email,
                'cognito_sub' => $socialUser->getId(),
            ])->save();
        } else {
            $user = User::create([
                // 'name'        => $socialUser->getName() ?: ($claims['given_name'] ?? 'User'),
                'email'       => $email,
                'cognito_sub' => $socialUser->getId(),
            ]);
        }

        // 6) Persist tokens for downstream verification / refresh
        $accessToken = $tokens['access_token'] ?? $socialUser->token;
        $refresh     = $tokens['refresh_token'] ?? null;

        $request->session()->put([
            'cognito_id_token'      => $idToken,
            'cognito_access_token'  => $accessToken,
            'cognito_refresh_token' => $refresh,
            'cognito_expires_in'    => $tokens['expires_in'] ?? 3600,
            'cognito_issued_at'     => time(),
        ]);

        CognitoUserToken::updateOrCreate(
            [ 'user_id'  => $user->id],
            [        
            'cognito_id_token'      => $idToken,
            'cognito_access_token'  => $accessToken,
            'cognito_refresh_token' => $refresh,
            'cognito_expires_in'    => $tokens['expires_in'] ?? 3600,
            'cognito_issued_at'     => time(),
        ]);

        // 7) Establish Laravel session & continue
        Auth::login($user);
        return redirect()->intended(self::DEFAULT_INTENDED);
    }

    /**
     * POST /logout
     *
     * - End local session.
     * - Redirect to Cognito federated logout (ends Hosted UI session).
     */
    public function logout(Request $request): RedirectResponse
    {
        CognitoUserToken::where('user_id',Auth::user()->id)->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(Socialite::driver('cognito')->logoutCognitoUser());
    }

    /**
     * Ensure access token expire
     */
    public function ensureFreshToken()
    {
        $tokens = CognitoUserToken::all();
        foreach ($tokens as $token) {
            try {

                $issuedAt  = $token->cognito_issued_at;
                $expiresIn = $token->cognito_expires_in;

                if ($issuedAt <= 0 || $expiresIn <= 0) {
                    continue;
                }
                
                // Calculate token age
                $age = time() - $issuedAt;
                $remaining = $expiresIn - $age;

                // Refresh if token is about to expire (less than 10 minutes left)
                if ($age >= ($expiresIn - 600)) {
                    // Log::info('Cognito token is about to expire. Refreshing token...', [
                    //     'issued_at' => $issuedAt,
                    //     'expires_in' => $expiresIn,
                    //     'age' => $age,
                    // ]);

                    $this->refreshAccessToken($token);
                } else {
                    // Calculate remaining time in minutes and seconds
                    $minutes = floor($remaining / 60);
                    $seconds = $remaining % 60;

                    // Log::info('Cognito token is still valid.', [
                    //     'issued_at' => $issuedAt,
                    //     'expires_in' => $expiresIn,
                    //     'age' => $age,
                    //     'remaining_time' => "{$minutes} mins {$seconds} secs left"
                    // ]);
                }
            } catch (\Exception $e) {
                \Log::error("Token refresh failed for user {$token->user_id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Generate new access token
     */
    public function refreshAccessToken($token): bool
    {
        try {
            $refreshToken = $token->cognito_refresh_token;
            if (!$refreshToken) {
                Log::warning('Cognito: Missing refresh token in session.');
                return false;
            }

            // Build Cognito token endpoint
            $clientId     = config('services.cognito.client_id');
            $clientSecret = config('services.cognito.client_secret');
            $domain       = rtrim(config('services.cognito.host'), '/');
            $tokenUrl     = "{$domain}/oauth2/token";

            // Make request to Cognito
            $response = Http::asForm()->post($tokenUrl, [
                'grant_type'    => 'refresh_token',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            if (!$response->ok()) {
                Log::error('Cognito: Refresh token request failed', ['response' => $response->json()]);
                return false;
            }

            $data = $response->json();
            if (!isset($data['access_token'])) {
                Log::error('Cognito: No access_token returned in refresh response', ['data' => $data]);
                return false;
            }

            $token->update([
                'cognito_access_token' => $data['access_token'],
                'cognito_id_token'     => $data['id_token'] ?? $token->cognito_id_token,
                'cognito_expires_in'   => $data['expires_in'] ?? 3600,
                'cognito_issued_at'    => time(),
            ]);

            Log::info('Cognito: Access token refreshed successfully.');
            return true;
        } catch (\Throwable $e) {
            Log::error('Cognito: Token refresh failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

}