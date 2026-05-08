<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\DispositivoPush;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    private ?string $projectId;
    private ?string $credentialsPath;

    public function __construct()
    {
        $this->projectId       = config('services.firebase.project_id');
        $this->credentialsPath = config('services.firebase.credentials');
    }

    // ─── Notificar cita creada ────────────────────────────────────────────────

    public function citaCreada(Cita $cita): void
    {
        $cita->load('cliente.user');

        $clienteUser = $cita->cliente->user ?? null;
        if (!$clienteUser) return;

        $this->enviar(
            userId: $clienteUser->id,
            titulo: 'Nueva cita registrada',
            cuerpo: "Tu cita para el {$cita->fecha_hora_formateada} ha sido registrada. Motivo: {$cita->motivo}",
            data: ['tipo' => 'cita_creada', 'cita_id' => (string) $cita->id]
        );
    }

    // ─── Recordatorio de cita ─────────────────────────────────────────────────

    public function recordatorioCita(Cita $cita): void
    {
        $clienteUser = $cita->cliente->user ?? null;
        if (!$clienteUser) return;

        $this->enviar(
            userId: $clienteUser->id,
            titulo: 'Recordatorio de cita',
            cuerpo: "Recuerda que mañana tienes cita a las {$cita->fecha_hora->format('H:i')}. Motivo: {$cita->motivo}",
            data: ['tipo' => 'recordatorio', 'cita_id' => (string) $cita->id]
        );

        $cita->update(['recordatorio_enviado' => true]);
    }

    // ─── Envío genérico ───────────────────────────────────────────────────────

    private function enviar(int $userId, string $titulo, string $cuerpo, array $data = []): void
    {
        if (!$this->projectId || !$this->credentialsPath) return;

        $tokens = DispositivoPush::where('user_id', $userId)->pluck('token_fcm')->toArray();
        if (empty($tokens)) return;

        $accessToken = $this->getAccessToken();
        if (!$accessToken) return;

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        foreach ($tokens as $token) {
            try {
                $response = Http::withToken($accessToken)->post($url, [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $titulo, 'body' => $cuerpo],
                        'data'         => $data,
                        'android'      => ['notification' => ['sound' => 'default']],
                    ],
                ]);

                if ($response->failed()) {
                    Log::warning('FCM: envío fallido', ['token' => substr($token, 0, 20), 'status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                Log::error("FCM: excepción al enviar notificación: {$e->getMessage()}");
            }
        }
    }

    // ─── OAuth2: obtener access token con caché ───────────────────────────────

    private function getAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', 3500, function () {
            try {
                $credentials = json_decode(file_get_contents($this->credentialsPath), true);

                $jwt = $this->buildJwt(
                    clientEmail: $credentials['client_email'],
                    privateKey:  $credentials['private_key'],
                    tokenUri:    $credentials['token_uri'],
                );

                $response = Http::asForm()->post($credentials['token_uri'], [
                    'grant_type' => 'urn:ietf:params:oauth2:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);

                return $response->json('access_token');
            } catch (\Throwable $e) {
                Log::error("FCM: no se pudo obtener access token: {$e->getMessage()}");
                return null;
            }
        });
    }

    // ─── Construir JWT RS256 para service account ─────────────────────────────

    private function buildJwt(string $clientEmail, string $privateKey, string $tokenUri): string
    {
        $now = time();

        $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'iss'   => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $tokenUri,
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $data = "{$header}.{$payload}";
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return "{$data}." . $this->base64url($signature);
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
