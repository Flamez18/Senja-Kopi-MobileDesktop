<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;

class FcmService
{
    private string $projectId = '';

    public function __construct()
    {
        // Ambil project ID dari file credentials
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS', 'firebase-service-account.json'));
        if (file_exists($credentialsPath)) {
            $credentials = json_decode(file_get_contents($credentialsPath), true);
            $this->projectId = $credentials['project_id'] ?? '';
        }
    }

    /**
     * Kirim notifikasi ke satu device token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        $this->sendToMultipleTokens([$token], $title, $body, $data);
    }

    /**
     * Kirim notifikasi ke banyak device token.
     */
    public function sendToMultipleTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_filter($tokens); // Hapus token null/kosong
        if (empty($tokens)) {
            return;
        }

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::warning('FCM: Tidak bisa mendapatkan access token');
                return;
            }

            // Konversi semua data value ke string (required by FCM)
            $stringData = array_map('strval', $data);

            foreach ($tokens as $token) {
                $this->sendSingleMessage($accessToken, $token, $title, $body, $stringData);
            }
        } catch (\Exception $e) {
            Log::error('FCM sendToMultipleTokens error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim satu pesan ke satu token via FCM HTTP v1.
     */
    private function sendSingleMessage(string $accessToken, string $token, string $title, string $body, array $data): void
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $data,
                'android' => [
                    'notification' => [
                        'channel_id' => 'kopi_senja_orders',
                        'sound'      => 'default',
                    ],
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withoutVerifying()
            ->post($url, $payload);

        if ($response->successful()) {
            Log::info("FCM sent to token ending in ..." . substr($token, -10));
        } else {
            Log::warning("FCM send failed: " . $response->body());
        }
    }

    /**
     * Dapatkan OAuth2 access token dari Google Service Account.
     */
    private function getAccessToken(): ?string
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS', 'firebase-service-account.json'));

        if (!file_exists($credentialsPath)) {
            Log::error('FCM: firebase-service-account.json tidak ditemukan di ' . $credentialsPath);
            return null;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials(false);

            $token = $client->fetchAccessTokenWithAssertion();
            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('FCM getAccessToken error: ' . $e->getMessage());
            return null;
        }
    }
}
