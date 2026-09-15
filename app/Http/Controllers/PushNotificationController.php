<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushNotificationController extends Controller
{
    public function __construct(protected WebPushService $webPushService) {}

    /**
     * Mengembalikan VAPID Public Key untuk pendaftaran PushManager di browser.
     */
    public function getPublicKey(): JsonResponse
    {
        $publicKey = config('services.webpush.vapid.public_key');

        return response()->json([
            'status' => 'success',
            'publicKey' => $publicKey,
        ]);
    }

    /**
     * Menyimpan atau memperbarui subscription perangkat pengguna yang sedang login.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'nullable|string',
            'keys.auth' => 'nullable|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $endpoint = $request->input('endpoint');
        $publicKey = $request->input('keys.p256dh');
        $authToken = $request->input('keys.auth');
        $contentEncoding = $request->input('contentEncoding', 'aes128gcm');
        $deviceInfo = $request->header('User-Agent');

        PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $endpoint,
            ],
            [
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $contentEncoding,
                'device_info' => $deviceInfo,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi perangkat berhasil diaktifkan.',
        ]);
    }

    /**
     * Menghapus subscription perangkat pengguna saat notifikasi dimatikan.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $user = Auth::user();
        if ($user) {
            PushSubscription::where('user_id', $user->id)
                ->where('endpoint', $request->input('endpoint'))
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi perangkat telah dinonaktifkan.',
        ]);
    }

    /**
     * Mengirim notifikasi uji coba ke perangkat pengguna yang sedang aktif.
     */
    public function sendTest(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $subscriptionCount = PushSubscription::where('user_id', $user->id)->count();
        if ($subscriptionCount === 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Perangkat belum terdaftar di database. Silakan matikan lalu aktifkan notifikasi kembali.',
            ]);
        }

        $sentCount = $this->webPushService->sendToUser($user, [
            'title' => '🔔 Notifikasi Presensi Aktif!',
            'body' => 'Selamat, '.($user->nama_lengkap ?? $user->name ?? 'Tutor').'! Perangkat Anda siap menerima notifikasi jadwal & presensi.',
            'url' => route('tutor.dashboard'),
        ]);

        if ($sentCount > 0) {
            return response()->json([
                'status' => 'success',
                'message' => "Notifikasi uji coba berhasil dikirim ke {$sentCount} perangkat Anda.",
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengirim sinyal push ke perangkat. Periksa koneksi internet atau registrasi ulang notifikasi.',
        ]);
    }
}
