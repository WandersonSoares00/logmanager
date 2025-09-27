<?php

namespace App\Console\Commands;

use App\Models\MeliAccount;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MeliRefreshTokenCommand extends Command
{
    protected $signature = 'meli:refresh-tokens';
    protected $description = 'Atualiza os access tokens do Mercado Livre que estão para expirar';

    public function handle()
    {
        $this->info('Iniciando a verificação de tokens do Mercado Livre...');

        // Contas em que o token expira nos próximos 15 minutos
        $expiringAccounts = MeliAccount::where('expires_at', '<', now()->addMinutes(15))->get();

        if ($expiringAccounts->isEmpty()) {
            $this->info('Nenhum token para atualizar.');
            return 0;
        }

        $this->info($expiringAccounts->count() . ' conta(s) precisam de atualização de token.');

        foreach ($expiringAccounts as $account) {
            try {
                $this->info('Refresh token: ' . $account->refresh_token);

                $response = Http::post('https://api.mercadolibre.com/oauth/token', [
                    'grant_type' => 'refresh_token',
                    'client_id' => config('services.meli.app_id'),
                    'client_secret' => config('services.meli.secret'),
                    'refresh_token' => $account->refresh_token,
                ]);

                $response->throw();
                $data = $response->json();

                $account->update([
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'], // Opcional, MELI pode ou não retornar um novo
                    'expires_at' => now()->addSeconds($data['expires_in']),
                ]);

                Log::info('Token para a conta MELI ID ' . $account->meli_user_id . ' foi atualizado com sucesso.');
                $this->info('Token para ' . $account->nickname . ' atualizado.');

            } catch (RequestException $e) {
                $this->error('Falha ao atualizar ' . $account->nickname);

                // Exibe o status code e a resposta exata da API do MELI
                if ($e->response) {
                    $statusCode = $e->response->status();
                    $errorBody = $e->response->body();
                    $this->warn("   -> Status Code: {$statusCode}");
                    $this->warn("   -> Erro da API: {$errorBody}");
                    Log::error("Falha ao atualizar token para MELI ID {$account->meli_user_id}. Status: {$statusCode}. Body: {$errorBody}");
                } else {
                    $this->warn("   -> Erro de conexão: " . $e->getMessage());
                    Log::error("Falha de conexão ao atualizar token para MELI ID {$account->meli_user_id}: " . $e->getMessage());
                }

            } catch (\Throwable $e) {
                Log::error('Falha ao atualizar token para a conta MELI ID ' . $account->meli_user_id . ': ' . $e->getMessage());
                $this->error('Falha ao atualizar ' . $account->nickname);
            }
        }

        $this->info('Verificação de tokens finalizada.');
        return 0;
    }
}
