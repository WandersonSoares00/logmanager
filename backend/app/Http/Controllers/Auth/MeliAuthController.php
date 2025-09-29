<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MeliAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MeliAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $codeVerifier = Str::random(128);
        $hashed = hash('sha256', $codeVerifier, true);
        $codeChallenge = rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');

        $state = Str::random(40);

        $request->session()->put('meli_code_verifier', $codeVerifier);
        $request->session()->put('meli_state', $state);

        $request->session()->save();

        $queryParams = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.meli.app_id'),
            'redirect_uri' => config('services.meli.redirect_uri'),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $url = 'https://auth.mercadolivre.com.br/authorization?' . $queryParams;

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        // Validação do STATE
        $sessionState = $request->session()->pull('meli_state');
        if (!$sessionState || $sessionState !== $request->input('state')) {
            Log::error('Invalid state value na autenticação MELI.');
            return redirect(config('services.meli.frontend_url') . '/auth/error?message=InvalidState');
        }

        if (!$request->has('code')) {
            return redirect(config('services.meli.frontend_url') . '/auth/error?message=AuthorizationFailed');
        }

        // Validação do PKCE
        $codeVerifier = $request->session()->pull('meli_code_verifier');
        if (!$codeVerifier) {
            Log::error('Code verifier não encontrado na sessão.');
            return redirect(config('services.meli.frontend_url') . '/auth/error?message=InvalidState');
        }

        Log::write('info', 'Callback recebido do MELI com sucesso.', $request->all());

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.meli.app_id'),
                'client_secret' => config('services.meli.secret'),
                'code' => $request->code,
                'redirect_uri' => config('services.meli.redirect_uri'),
                'code_verifier' => $codeVerifier,
            ]);

            $response->throw();
            $data = $response->json();

            $accessToken = $data['access_token'];
            $userResponse = Http::withToken($accessToken)->get('https://api.mercadolibre.com/users/me');
            $userResponse->throw();
            $meliUser = $userResponse->json();

            $localUser = User::firstOrCreate(
                ['email' => $meliUser['email']],
                ['name' => $meliUser['first_name'] . ' ' . $meliUser['last_name'], 'password' => bcrypt(Str::random(16))]
            );

            MeliAccount::updateOrCreate(
                ['meli_user_id' => $meliUser['id']],
                [
                    'user_id' => $localUser->id,
                    'nickname' => $meliUser['nickname'],
                    'access_token' => $accessToken,
                    'refresh_token' => $data['refresh_token'],
                    'expires_at' => now()->addSeconds($data['expires_in']),
                ]
            );

            $apiToken = $localUser->createToken('meli-token')->plainTextToken;

            return redirect(config('services.meli.frontend_url') . '/auth/success?token=' . $apiToken);

        } catch (Throwable $e) {
            Log::error('Falha na autenticação com o Mercado Livre: ' . $e->getMessage());
            return redirect(config('services.meli.frontend_url') . '/auth/error?message=ApiError');
        }
    }
}
