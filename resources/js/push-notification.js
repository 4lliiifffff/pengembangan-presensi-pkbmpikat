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
            } else if (Notification.permission === 'granted') {
                // Izin browser sudah diberikan sebelumnya, pulihkan pendaftaran secara otomatis
                await this.subscribe().catch((e) => {
                    console.warn('[Push] Auto-subscribe on granted permission error:', e);
                });
            } else if (Notification.permission === 'default') {
                // Izin belum pernah diminta, tampilkan soft prompt otomatis
                this.checkAndShowSoftPrompt();
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
     * Memeriksa dan menampilkan Soft Prompt otomatis untuk meminta izin notifikasi
     */
    checkAndShowSoftPrompt() {
        if (typeof document === 'undefined') return;

        // Jangan munculkan di halaman login atau jika user belum terautentikasi
        if (document.body.classList.contains('login-html') || window.location.pathname.startsWith('/login')) {
            return;
        }

        // Cek apakah prompt ditunda sementara oleh user
        const dismissedUntil = localStorage.getItem('pikat_push_dismissed_until');
        if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) {
            return;
        }

        // Cek jika prompt sudah ada di DOM
        if (document.getElementById('pikatSoftPushPrompt')) return;

        // Beri jeda 1.2 detik setelah halaman selesai dimuat agar transisi halus
        setTimeout(() => {
            if (this.isSubscribed || Notification.permission !== 'default') return;
            this.renderSoftPromptElement();
        }, 1200);
    }

    /**
     * Render elemen dialog kartu soft prompt di layar
     */
    renderSoftPromptElement() {
        if (document.getElementById('pikatSoftPushPrompt')) return;

        const banner = document.createElement('div');
        banner.id = 'pikatSoftPushPrompt';
        banner.setAttribute('role', 'alert');
        banner.style.cssText = `
            position: fixed;
            bottom: 84px;
            left: 50%;
            transform: translateX(-50%) translateY(30px);
            width: calc(100% - 32px);
            max-width: 440px;
            background: var(--card, #ffffff);
            color: var(--text, #0f172a);
            border: 1px solid var(--border, #e2e8f0);
            border-left: 4px solid #0B5ED7;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            padding: 16px;
            z-index: 9999;
            opacity: 0;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: var(--font-sans, system-ui, sans-serif);
        `;

        banner.innerHTML = `
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(11, 94, 215, 0.12); color: #0B5ED7; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    🔔
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 700; font-size: 14px; margin-bottom: 4px; color: var(--text, #0f172a);">
                        Aktifkan Pengingat Presensi Otomatis
                    </div>
                    <div style="font-size: 12px; line-height: 1.45; color: var(--muted, #64748b); margin-bottom: 12px;">
                        Dapatkan pengingat jadwal KBM, waktu absen masuk & pulang secara otomatis langsung di perangkat Anda tanpa perlu cek manual.
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" id="btnAllowPushSoft" style="background: #0B5ED7; color: #ffffff; border: none; border-radius: 8px; padding: 7px 14px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(11, 94, 215, 0.25);">
                            Aktifkan Sekarang
                        </button>
                        <button type="button" id="btnDismissPushSoft" style="background: transparent; color: var(--muted, #64748b); border: 1px solid var(--border, #e2e8f0); border-radius: 8px; padding: 7px 12px; font-size: 12px; font-weight: 500; cursor: pointer;">
                            Nanti Saja
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(banner);

        // Animate in
        requestAnimationFrame(() => {
            banner.style.opacity = '1';
            banner.style.transform = 'translateX(-50%) translateY(0)';
        });

        // Event listener: Aktifkan
        const btnAllow = document.getElementById('btnAllowPushSoft');
        if (btnAllow) {
            btnAllow.addEventListener('click', async () => {
                btnAllow.disabled = true;
                btnAllow.textContent = 'Mengaktifkan...';
                try {
                    await this.subscribe();
                    this.removeSoftPrompt();
                    if (window.AppNotification && typeof window.AppNotification.toast === 'function') {
                        window.AppNotification.toast({
                            type: 'success',
                            title: 'Pengingat Aktif',
                            message: 'Notifikasi presensi otomatis berhasil diaktifkan untuk perangkat ini!'
                        });
                    }
                } catch (err) {
                    console.warn('[Push] User dismissed or error:', err);
                    this.removeSoftPrompt();
                }
            });
        }

        // Event listener: Nanti saja
        const btnDismiss = document.getElementById('btnDismissPushSoft');
        if (btnDismiss) {
            btnDismiss.addEventListener('click', () => {
                // Tunda selama 3 hari
                localStorage.setItem('pikat_push_dismissed_until', String(Date.now() + 3 * 86400 * 1000));
                this.removeSoftPrompt();
            });
        }
    }

    /**
     * Hapus elemen dialog kartu soft prompt dengan animasi
     */
    removeSoftPrompt() {
        const banner = document.getElementById('pikatSoftPushPrompt');
        if (banner) {
            banner.style.opacity = '0';
            banner.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(() => {
                if (banner.parentNode) banner.parentNode.removeChild(banner);
            }, 350);
        }
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
