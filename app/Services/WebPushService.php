<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
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
     * @param  array{title: string, body: string, icon?: string, badge?: string, url?: string, data?: array}  $payload
     * @return int Jumlah perangkat yang berhasil menerima notifikasi
     */
    public function sendToUser(User|Tutor|int $user, array $payload): int
    {
        $userId = null;

        if ($user instanceof User) {
            $userId = $user->id;
        } elseif ($user instanceof \App\Models\Tutor) {
            $userId = $user->user_id;
        } elseif (is_int($user)) {
            // Cek apakah ID adalah user_id langsung atau tutor_id
            $hasSub = PushSubscription::where('user_id', $user)->exists();
            if ($hasSub) {
                $userId = $user;
            } else {
                $tutor = \App\Models\Tutor::find($user);
                $userId = $tutor?->user_id ?? $user;
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
     * Kirim notifikasi Web Push ke Tutor tertentu.
     */
    public function sendToTutor(\App\Models\Tutor|int $tutor, array $payload): int
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
     * Mengirim notifikasi ke kumpulan PushSubscription model.
     *
     * @param  Collection<PushSubscription>  $subscriptions
     */
    public function sendToSubscriptions($subscriptions, array $payload): int
    {
        if (! $this->webPush) {
            Log::warning('WebPush is not configured with VAPID keys.');

            return 0;
        }

        // Format payload default
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

                // Hapus subscription yang kadaluarsa / uninstalled
                if ($report->isSubscriptionExpired() && $subModel) {
                    $subModel->delete();
                    Log::info("Deleted expired push subscription id: {$subModel->id}");
                }
            }
        }

        return $successCount;
    }
}
