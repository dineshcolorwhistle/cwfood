<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache,Http,Log,Auth};
use App\Models\{CognitoUserToken};

class VerifyCognitoIdToken
{
    public function handle(Request $req, Closure $next)
    {

        $tokenDetails = CognitoUserToken::where('user_id', Auth::id())->first();
        if ($tokenDetails) {
            $idToken = $tokenDetails->cognito_id_token;
        }else{
            $idToken = $req->session()->get('cognito_id_token');
        }
        
        if (!$idToken) {
            return redirect()->route('login');
            Log::info('Coginto triggered logout: congnito id missing');
        }

        try {
            // 1) Fetch & cache JWKS
            $jwksJson = Cache::remember('cognito_jwks', now()->addHours(5), function () {
                $url = config('services.cognito.jwks_url');
                if (!$url) {
                    throw new \RuntimeException('Missing JWKS URL config');
                }
                $resp = Http::timeout(5)->get($url);
                if (!$resp->ok()) {
                    throw new \RuntimeException('JWKS fetch failed');
                }    
                return $resp->json();
            });

            if (!is_array($jwksJson) || empty($jwksJson['keys'])) {
                throw new \RuntimeException('Invalid JWKS payload');
            }

            /** 🔒 Reject unsupported key types (defense-in-depth) */
            foreach ($jwksJson['keys'] as $k) {
                if (($k['kty'] ?? '') !== 'RSA') {
                    throw new \RuntimeException('Unsupported key type');
                }
            }

            // 2) Build key set and decode (php-jwt v6)
            $keySet = JWK::parseKeySet($jwksJson);
            $decoded = JWT::decode($idToken, $keySet);

            // 3) Validate standard OIDC claims
            $issExpected = env('COGNITO_ISSUER');
            $audExpected = config('services.cognito.client_id');

            if (($decoded->iss ?? null) !== $issExpected) {
                throw new \RuntimeException('bad iss');
            }

            $audClaim = $decoded->aud ?? null;
            $audOk = is_array($audClaim)
                ? in_array($audExpected, $audClaim, true)
                : ($audClaim === $audExpected);
            if (!$audOk) {
                throw new \RuntimeException('bad aud');
            }

            if (($decoded->token_use ?? null) !== 'id') {
                throw new \RuntimeException('bad use');
            }

            if (isset($decoded->exp) && time() >= (int)$decoded->exp) {
                throw new \RuntimeException('expired');
            }

            // (Optional) expose claims if you need them later
            // $req->attributes->set('cognito_id_claims', (array) $decoded);

            return $next($req);
        } catch (\Throwable $e) {
            // Clear any stale tokens & force re-auth
            $req->session()->forget([
                'cognito_id_token',
                'cognito_access_token',
                'cognito_refresh_token',
                'cognito_expires_in',
                'cognito_issued_at',
            ]);
            Log::info('Coginto triggered logout',['message' => $e->getMessage()]);
            return redirect()->route('login')
                ->with('auth_error', 'Session invalid. Please sign in again.');
        }
    }
}