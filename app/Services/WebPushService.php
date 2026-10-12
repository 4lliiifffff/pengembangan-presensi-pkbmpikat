<?php

namespace App\Services;

use App\Models\Magang;
use App\Models\PushSubscription;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * Instance WebPush.
     */
    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $this->initWebPush();
    }

    /**
     * Inisialisasi WebPush client dengan VAPID authentication.
     */
    protected function initWebPush(): void
    {
        $publicKey = config('services.webpush.vapid.public_key');
        $privateKey = config('services.webpush.vapid.private_key');
        $subject = config('services.webpush.vapid.subject', 'mailto:admin@pkbmpikat.sch.id');

        if (! $publicKey || ! $privateKey) {
            return;
        }

        $auth = [
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        try {
            $client = new Client([
                'timeout' => 30,
                'connect_timeout' => 10,
                'verify' => config('services.webpush.verify_ssl', false),
            ]);

            $this->webPush = new WebPush($auth, [], $client);
            $this->webPush->setReuseVAPIDHeaders(true);
        } catch (\Throwable $e) {
            Log::error('WebPush initialization error: '.$e->getMessage());
        }
    }

    /**
     * Kirim notifikasi Web Push ke User tertentu.
     *
     * @param  User|Tutor|Siswa|Magang|int  $user
     * @param  array{title: string, body: string, icon?: string, badge?: string, url?: string, data?: array}  $payload
     * @return int Jumlah perangkat yang berhasil menerima notifikasi
     */
    public function sendToUser(mixed $user, array $payload): int
    {
        $userId = null;

        if ($user instanceof User) {
            $userId = $user->id;
        } elseif ($user instanceof Tutor) {
            $userId = $user->user_id;
        } elseif ($user instanceof Siswa) {
            $userId = $user->user_id;
        } elseif ($user instanceof Magang) {
            $userId = $user->user_id;
        } elseif (is_int($user)) {
            // Cek apakah ID adalah user_id langsung
            $hasSub = PushSubscription::where('user_id', $user)->exists();
            if ($hasSub) {
                $userId = $user;
            } else {
                $tutor = Tutor::find($user);
                $userId = $tutor?->user_id;
                if (! $userId) {
                    $siswa = Siswa::find($user);
                    $userId = $siswa?->user_id ?? $user;
                }
            }
        }

        if (! $userId) {
            return 0;
        }

        $subscriptions = PushSubscription::where('user_id', $userId)->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke Siswa tertentu.
     */
    public function sendToSiswa(Siswa|int $siswa, array $payload): int
    {
        return $this->sendToUser($siswa, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke semua Siswa yang aktif.
     */
    public function sendToAllStudents(array $payload): int
    {
        $subscriptions = PushSubscription::whereHas('user', function ($q) {
            $q->where('role', 'siswa')->where('is_active', 1);
        })->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke Magang tertentu.
     */
    public function sendToMagang(Magang|int $magang, array $payload): int
    {
        return $this->sendToUser($magang, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke semua Magang yang aktif.
     */
    public function sendToAllMagang(array $payload): int
    {
        $subscriptions = PushSubscription::whereHas('user', function ($q) {
            $q->where('role', 'magang')->where('is_active', 1);
        })->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke Tutor tertentu.
     */
    public function sendToTutor(Tutor|int $tutor, array $payload): int
    {
        return $this->sendToUser($tutor, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke semua Tutor yang aktif.
     */
    public function sendToAllTutors(array $payload): int
    {
        $subscriptions = PushSubscription::whereHas('user', function ($q) {
            $q->where('role', 'tutor')->where('is_active', 1);
        })->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke semua Admin yang aktif.
     */
    public function sendToAdmins(array $payload): int
    {
        $subscriptions = PushSubscription::whereHas('user', function ($q) {
            $q->where('role', 'admin')->where('is_active', 1);
        })->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke Kepala Sekolah yang aktif.
     */
    public function sendToKepsek(array $payload): int
    {
        $subscriptions = PushSubscription::whereHas('user', function ($q) {
            $q->where('role', 'kepala_sekolah')->where('is_active', 1);
        })->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Kirim notifikasi Web Push ke Manajemen (Admin + Kepala Sekolah).
     */
    public function sendToManagement(array $payload): int
    {
        $subscriptions = PushSubscription::whereHas('user', function ($q) {
            $q->whereIn('role', ['admin', 'kepala_sekolah'])->where('is_active', 1);
        })->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Mengirim notifikasi ke kumpulan PushSubscription model (WebPush PWA & FCM Native APK).
     *
     * @param  Collection<PushSubscription>  $subscriptions
     */
    public function sendToSubscriptions($subscriptions, array $payload): int
    {
        $fcmSubscriptions = $subscriptions->filter(function ($sub) {
            return str_starts_with($sub->endpoint, 'fcm:') || $sub->content_encoding === 'fcm_native';
        });

        $webPushSubscriptions = $subscriptions->reject(function ($sub) {
            return str_starts_with($sub->endpoint, 'fcm:') || $sub->content_encoding === 'fcm_native';
        });

        $successCount = 0;

        if ($fcmSubscriptions->isNotEmpty()) {
            $successCount += $this->sendToFcmSubscriptions($fcmSubscriptions, $payload);
        }

        if ($webPushSubscriptions->isNotEmpty()) {
            $successCount += $this->sendToWebPushSubscriptions($webPushSubscriptions, $payload);
        }

        return $successCount;
    }

    /**
     * Mengirim notifikasi via WebPush standar (Service Worker PWA / Browser).
     *
     * @param  Collection<PushSubscription>  $subscriptions
     */
    protected function sendToWebPushSubscriptions($subscriptions, array $payload): int
    {
        if (! $this->webPush) {
            Log::warning('WebPush is not configured with VAPID keys.');

            return 0;
        }

        $data = [
            'title' => $payload['title'] ?? 'Presensi PKBM Pikat',
            'body' => $payload['body'] ?? 'Anda memiliki notifikasi baru.',
            'icon' => $payload['icon'] ?? '/assets/img/Logo.jpeg',
            'badge' => $payload['badge'] ?? '/assets/img/Logo.jpeg',
            'url' => $payload['url'] ?? route('tutor.dashboard'),
            'data' => $payload['data'] ?? [],
        ];

        $jsonPayload = json_encode($data);
        $subscriptionMap = [];

        foreach ($subscriptions as $sub) {
            try {
                $webPushSubscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aes128gcm',
                ]);

                $this->webPush->queueNotification($webPushSubscription, $jsonPayload);
                $subscriptionMap[$sub->endpoint] = $sub;
            } catch (\Throwable $e) {
                Log::warning('Failed queueing push notification for endpoint: '.$e->getMessage());
            }
        }

        $successCount = 0;

        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $subModel = $subscriptionMap[$endpoint] ?? null;

            if ($report->isSuccess()) {
                $successCount++;
            } else {
                Log::info("WebPush dispatch failure for endpoint [{$endpoint}]: ".$report->getReason());

                if ($report->isSubscriptionExpired() && $subModel) {
                    $subModel->delete();
                    Log::info("Deleted expired push subscription id: {$subModel->id}");
                }
            }
        }

        return $successCount;
    }

    /**
     * Mengirim notifikasi via Firebase Cloud Messaging (FCM HTTP v1 API) ke Android Mobile APK.
     *
     * @param  Collection<PushSubscription>  $subscriptions
     */
    protected function sendToFcmSubscriptions(Collection $subscriptions, array $payload): int
    {
        $accessToken = $this->getFcmAccessToken();
        if (! $accessToken) {
            Log::warning('FCM access token could not be acquired. Skipping FCM dispatch.');

            return 0;
        }

        $projectId = config('services.fcm.project_id', 'kehadiran-pkbmpikat');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $client = new Client([
            'timeout' => 15,
            'connect_timeout' => 5,
        ]);

        $title = $payload['title'] ?? 'Presensi PKBM Pikat';
        $body = $payload['body'] ?? 'Anda memiliki notifikasi baru.';
        $targetUrl = $payload['url'] ?? route('tutor.dashboard');

        $successCount = 0;

        foreach ($subscriptions as $sub) {
            $token = str_starts_with($sub->endpoint, 'fcm:')
                ? substr($sub->endpoint, 4)
                : $sub->endpoint;

            if (empty($token)) {
                continue;
            }

            $messagePayload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => [
                        'url' => (string) $targetUrl,
                        'title' => (string) $title,
                        'body' => (string) $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'icon' => 'ic_launcher',
                            'color' => '#0B5ED7',
                            'default_sound' => true,
                            'default_vibrate_timings' => true,
                        ],
                    ],
                ],
            ];

            try {
                $response = $client->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $messagePayload,
                ]);

                if ($response->getStatusCode() === 200) {
                    $successCount++;
                }
            } catch (ClientException $e) {
                $statusCode = $e->getResponse()?->getStatusCode();
                $responseBody = (string) $e->getResponse()?->getBody();
                Log::warning("FCM dispatch error ({$statusCode}): {$responseBody}");

                if ($statusCode === 404 || str_contains($responseBody, 'UNREGISTERED')) {
                    $sub->delete();
                    Log::info("Deleted expired/uninstalled FCM subscription id: {$sub->id}");
                }
            } catch (\Throwable $e) {
                Log::error('FCM dispatch unexpected error: '.$e->getMessage());
            }
        }

        return $successCount;
    }

    /**
     * Dapatkan OAuth2 Access Token untuk Google Firebase Cloud Messaging v1 menggunakan Service Account.
     */
    public function getFcmAccessToken(): ?string
    {
        return Cache::remember('fcm_v1_access_token', 3300, function () {
            $credentialsPath = config('services.fcm.credentials_path');
            if (! $credentialsPath || ! file_exists($credentialsPath)) {
                $files = glob(storage_path('app/*firebase*.json'));
                if (! empty($files)) {
                    $credentialsPath = $files[0];
                } else {
                    Log::warning('FCM credentials file not found.');

                    return null;
                }
            }

            $jsonContent = file_get_contents($credentialsPath);
            $json = json_decode($jsonContent, true);
            if (! $json || empty($json['private_key']) || empty($json['client_email'])) {
                Log::warning('Invalid FCM credentials JSON structure.');

                return null;
            }

            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claimSet = json_encode([
                'iss' => $json['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlClaimSet = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claimSet));
            $signatureInput = $base64UrlHeader.'.'.$base64UrlClaimSet;

            $signature = '';
            $privateKey = openssl_pkey_get_private($json['private_key']);
            if (! $privateKey) {
                Log::error('Failed to parse FCM private key.');

                return null;
            }

            if (! openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                Log::error('Failed to sign FCM JWT with private key.');

                return null;
            }

            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $jwt = $signatureInput.'.'.$base64UrlSignature;

            $client = new Client(['timeout' => 10]);

            try {
                $response = $client->post('https://oauth2.googleapis.com/token', [
                    'form_params' => [
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                        'assertion' => $jwt,
                    ],
                ]);

                $data = json_decode((string) $response->getBody(), true);

                return $data['access_token'] ?? null;
            } catch (\Throwable $e) {
                Log::error('FCM OAuth2 token request failed: '.$e->getMessage());

                return null;
            }
        });
    }
}
