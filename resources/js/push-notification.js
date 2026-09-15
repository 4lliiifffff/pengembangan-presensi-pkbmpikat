/**
 * Client-side Web Push Notification Manager for PKBM Pikat PWA
 */
class PushNotificationManager {
    constructor() {
        this.vapidPublicKey = null;
        this.swRegistration = null;
        this.isSubscribed = false;
    }

    /**
     * Inisialisasi Service Worker & Status Push
     */
    async init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
            console.warn('[Push] Browser ini tidak mendukung Web Push Notification.');
            return false;
        }

        try {
            this.swRegistration = await navigator.serviceWorker.ready;
            const subscription = await this.swRegistration.pushManager.getSubscription();
            this.isSubscribed = !(subscription === null);

            if (this.isSubscribed && subscription) {
                // Auto-sync subscription ke user yang sedang aktif login di backend
                await this.syncSubscriptionToServer(subscription);
            }

            this.updateUiState(this.isSubscribed);
            return true;
        } catch (error) {
            console.error('[Push] Gagal inisialisasi push manager:', error);
            return false;
        }
    }

    /**
     * Kirim subscription ke server untuk disinkronkan dengan user saat ini
     */
    async syncSubscriptionToServer(subscription) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) return false;

            const res = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(subscription)
            });

            const data = await res.json();
            if (data.status === 'success') {
                this.isSubscribed = true;
                this.updateUiState(true);
                return true;
            }
            return false;
        } catch (e) {
            console.warn('[Push] Auto-sync subscription failed:', e);
            return false;
        }
    }

    /**
     * Ambil VAPID Public Key dari server
     */
    async fetchVapidPublicKey() {
        if (this.vapidPublicKey) return this.vapidPublicKey;

        try {
            const res = await fetch('/push/key');
            const data = await res.json();
            if (data.status === 'success' && data.publicKey) {
                this.vapidPublicKey = data.publicKey;
                return this.vapidPublicKey;
            }
        } catch (e) {
            console.error('[Push] Gagal mengambil VAPID key:', e);
        }
        return null;
    }

    /**
     * Minta izin dan daftarkan subscription perangkat
     */
    async subscribe() {
        if (!this.swRegistration) {
            await this.init();
        }

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            throw new Error('Izin notifikasi ditolak oleh pengguna pada browser.');
        }

        const key = await this.fetchVapidPublicKey();
        if (!key) {
            throw new Error('VAPID public key belum terkonfigurasi di server.');
        }

        // Jika sudah ada subscription lama, kita unsubscribe dulu agar bersih
        let existingSub = await this.swRegistration.pushManager.getSubscription();
        if (existingSub) {
            try {
                await existingSub.unsubscribe();
            } catch (e) {
                console.warn('[Push] Unsubscribe previous sub error:', e);
            }
        }

        const convertedKey = this.urlBase64ToUint8Array(key);
        const subscription = await this.swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: convertedKey
        });

        // Kirim subscription ke backend Laravel
        const synced = await this.syncSubscriptionToServer(subscription);
        if (synced) {
            this.isSubscribed = true;
            this.updateUiState(true);
            return true;
        } else {
            throw new Error('Gagal menyimpan subscription di server.');
        }
    }

    /**
     * Hapus pendaftaran notifikasi perangkat
     */
    async unsubscribe() {
        if (!this.swRegistration) return;

        const subscription = await this.swRegistration.pushManager.getSubscription();
        if (subscription) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            await fetch('/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ endpoint: subscription.endpoint })
            });

            await subscription.unsubscribe();
        }

        this.isSubscribed = false;
        this.updateUiState(false);
    }

    /**
     * Kirim notifikasi uji coba (memastikan subscription terdaftar dahulu di DB)
     */
    async sendTestNotification() {
        if (!this.swRegistration) {
            await this.init();
        }

        let subscription = await this.swRegistration?.pushManager?.getSubscription();
        if (!subscription && typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            await this.subscribe();
            subscription = await this.swRegistration?.pushManager?.getSubscription();
        } else if (subscription) {
            await this.syncSubscriptionToServer(subscription);
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/push/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            }
        });
        return await res.json();
    }

    /**
     * Konversi VAPID key URL-safe base64 ke Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Update status UI (tombol toggle & status badge)
     */
    updateUiState(subscribed) {
        const toggleBtn = document.getElementById('pushToggleBtn');
        const statusText = document.getElementById('pushStatusText');
        const testBtn = document.getElementById('pushTestBtn');

        if (statusText) {
            statusText.textContent = subscribed ? 'Aktif (Menerima Notifikasi)' : 'Nonaktif';
            statusText.style.color = subscribed ? '#16a34a' : '#64748b';
        }

        if (toggleBtn) {
            toggleBtn.textContent = subscribed ? 'Matikan Notifikasi' : 'Aktifkan Notifikasi';
            toggleBtn.classList.toggle('active', subscribed);
        }

        if (testBtn) {
            testBtn.style.display = subscribed ? 'inline-flex' : 'none';
        }
    }
}

// Global instance
window.pushManager = new PushNotificationManager();

document.addEventListener('DOMContentLoaded', () => {
    // Daftarkan service worker jika belum
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').then(() => {
            window.pushManager.init();
        }).catch(err => {
            console.warn('[ServiceWorker] Registrasi gagal:', err);
        });
    }
});
