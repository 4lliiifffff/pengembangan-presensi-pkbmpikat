/**
 * AppNotification - Sistem Notifikasi Pop-Up, Toast, & Dialog Terpadu
 * Desain konsisten untuk PKBM Pikat (Light/Dark Mode, Mobile & Desktop Responsive)
 */
class AppNotificationManager {
    constructor() {
        this.toastContainer = null;
        this.activeModal = null;
        this.toastQueue = [];
        this.init();
    }

    init() {
        if (typeof document === 'undefined') return;

        // Buat container toast jika belum ada
        if (!document.getElementById('appToastContainer')) {
            this.toastContainer = document.createElement('div');
            this.toastContainer.id = 'appToastContainer';
            this.toastContainer.className = 'app-toast-container';
            this.toastContainer.setAttribute('aria-live', 'polite');
            document.body.appendChild(this.toastContainer);
        } else {
            this.toastContainer = document.getElementById('appToastContainer');
        }

        // Listener global untuk event keydown (Escape untuk menutup modal)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.closeModal(false);
            }
        });

        // Auto-hook untuk form dengan data-confirm
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const confirmMsg = form.getAttribute('data-confirm');
            if (confirmMsg && !form.dataset.confirmed) {
                e.preventDefault();
                const confirmTitle = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
                const confirmType = form.getAttribute('data-confirm-type') || 'warning';
                const confirmBtn = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
                const cancelBtn = form.getAttribute('data-cancel-btn') || 'Batal';

                this.confirm({
                    title: confirmTitle,
                    message: confirmMsg,
                    type: confirmType,
                    confirmText: confirmBtn,
                    cancelText: cancelBtn,
                    isDanger: confirmType === 'danger' || confirmType === 'delete' || /hapus|tolak|batal/i.test(confirmBtn + confirmMsg)
                }).then((confirmed) => {
                    if (confirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            }
        });

        // Auto-hook untuk link / button dengan data-confirm
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[data-confirm], button[data-confirm]');
            if (link && link.tagName === 'A' && !link.dataset.confirmed && !link.closest('form')) {
                const confirmMsg = link.getAttribute('data-confirm');
                if (confirmMsg) {
                    e.preventDefault();
                    const confirmTitle = link.getAttribute('data-confirm-title') || 'Konfirmasi';
                    const confirmType = link.getAttribute('data-confirm-type') || 'warning';
                    const confirmBtn = link.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
                    const cancelBtn = link.getAttribute('data-cancel-btn') || 'Batal';

                    this.confirm({
                        title: confirmTitle,
                        message: confirmMsg,
                        type: confirmType,
                        confirmText: confirmBtn,
                        cancelText: cancelBtn,
                        isDanger: confirmType === 'danger' || /keluar|hapus/i.test(confirmBtn + confirmMsg)
                    }).then((confirmed) => {
                        if (confirmed) {
                            link.dataset.confirmed = 'true';
                            if (link.href) {
                                window.location.href = link.href;
                            } else {
                                link.click();
                            }
                        }
                    });
                }
            }
        });

        // Periksa flash session saat DOM siap
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.checkServerFlashMessages());
        } else {
            this.checkServerFlashMessages();
        }
    }

    /**
     * Memeriksa dan menampilkan flash session dari server Laravel
     */
    checkServerFlashMessages() {
        const flashSuccess = document.querySelector('meta[name="flash-success"]')?.getAttribute('content');
        const flashWarning = document.querySelector('meta[name="flash-warning"]')?.getAttribute('content');
        const flashError = document.querySelector('meta[name="flash-error"]')?.getAttribute('content');
        const flashInfo = document.querySelector('meta[name="flash-info"]')?.getAttribute('content');

        if (flashSuccess) {
            this.toast({ type: 'success', title: 'Berhasil', message: flashSuccess });
        }
        if (flashWarning) {
            this.toast({ type: 'warning', title: 'Perhatian', message: flashWarning });
        }
        if (flashError) {
            this.toast({ type: 'error', title: 'Terjadi Kesalahan', message: flashError });
        }
        if (flashInfo) {
            this.toast({ type: 'info', title: 'Informasi', message: flashInfo });
        }
    }

    /**
     * Helper untuk mendapatkan konfigurasi badge & ikon berdasarkan tipe
     */
    getTypeConfig(type = 'info') {
        const t = (type || 'info').toLowerCase();
        switch (t) {
            case 'success':
                return {
                    icon: 'checkmark-circle',
                    badgeBg: 'rgba(22, 163, 74, 0.15)',
                    badgeColor: '#16a34a',
                    border: 'rgba(22, 163, 74, 0.25)',
                    title: 'Berhasil'
                };
            case 'warning':
                return {
                    icon: 'alert-circle',
                    badgeBg: 'rgba(245, 158, 11, 0.15)',
                    badgeColor: '#f59e0b',
                    border: 'rgba(245, 158, 11, 0.25)',
                    title: 'Peringatan'
                };
            case 'danger':
            case 'error':
            case 'delete':
                return {
                    icon: 'close-circle',
                    badgeBg: 'rgba(239, 68, 68, 0.15)',
                    badgeColor: '#ef4444',
                    border: 'rgba(239, 68, 68, 0.25)',
                    title: 'Perhatian'
                };
            case 'info':
            default:
                return {
                    icon: 'information-circle',
                    badgeBg: 'rgba(11, 94, 215, 0.15)',
                    badgeColor: '#0B5ED7',
                    border: 'rgba(11, 94, 215, 0.25)',
                    title: 'Informasi'
                };
        }
    }

    /**
     * Menampilkan Floating Toast Notification
     */
    toast({ type = 'info', title, message = '', duration = 4000 }) {
        if (!this.toastContainer) this.init();

        const config = this.getTypeConfig(type);
        const toastEl = document.createElement('div');
        toastEl.className = `app-toast app-toast-${type}`;
        toastEl.setAttribute('role', 'status');

        const toastTitle = title || config.title;

        toastEl.innerHTML = `
            <div class="app-toast-icon" style="background: ${config.badgeBg}; color: ${config.badgeColor}; border: 1px solid ${config.border};">
                <ion-icon name="${config.icon}"></ion-icon>
            </div>
            <div class="app-toast-content">
                <div class="app-toast-title">${this.escapeHtml(toastTitle)}</div>
                ${message ? `<div class="app-toast-msg">${this.escapeHtml(message)}</div>` : ''}
            </div>
            <button type="button" class="app-toast-close" aria-label="Tutup notifikasi">
                <ion-icon name="close-outline"></ion-icon>
            </button>
            <div class="app-toast-progress">
                <div class="app-toast-progress-bar" style="background: ${config.badgeColor}; animation-duration: ${duration}ms;"></div>
            </div>
        `;

        const closeBtn = toastEl.querySelector('.app-toast-close');
        let dismissTimer = null;

        const dismissToast = () => {
            if (dismissTimer) clearTimeout(dismissTimer);
            toastEl.classList.remove('app-toast-enter');
            toastEl.classList.add('app-toast-exit');
            let removed = false;
            const removeEl = () => {
                if (removed) return;
                removed = true;
                if (toastEl && toastEl.parentNode) {
                    toastEl.parentNode.removeChild(toastEl);
                }
            };
            setTimeout(removeEl, 250);
            toastEl.addEventListener('animationend', removeEl, { once: true });
        };

        closeBtn.addEventListener('click', dismissToast);

        // Pause on hover
        toastEl.addEventListener('mouseenter', () => {
            if (dismissTimer) clearTimeout(dismissTimer);
            const pb = toastEl.querySelector('.app-toast-progress-bar');
            if (pb) pb.style.animationPlayState = 'paused';
        });

        toastEl.addEventListener('mouseleave', () => {
            const pb = toastEl.querySelector('.app-toast-progress-bar');
            if (pb) pb.style.animationPlayState = 'running';
            dismissTimer = setTimeout(dismissToast, 1800);
        });

        this.toastContainer.appendChild(toastEl);

        // Animasi masuk
        requestAnimationFrame(() => {
            toastEl.classList.add('app-toast-enter');
        });

        if (duration > 0) {
            dismissTimer = setTimeout(dismissToast, duration);
        }

        return toastEl;
    }

    /**
     * Menampilkan Modal Dialog Alert Pop-Up (menggantikan window.alert)
     */
    alert({ type = 'info', title, message = '', confirmText = 'Mengerti' }) {
        return new Promise((resolve) => {
            const config = this.getTypeConfig(type);
            const alertTitle = title || config.title;

            const modalContent = `
                <div class="app-modal-dialog app-modal-alert">
                    <div class="app-modal-icon-wrap" style="background: ${config.badgeBg}; color: ${config.badgeColor}; border: 1px solid ${config.border};">
                        <ion-icon name="${config.icon}"></ion-icon>
                    </div>
                    <div class="app-modal-title">${this.escapeHtml(alertTitle)}</div>
                    <div class="app-modal-desc">${this.escapeHtml(message).replace(/\n/g, '<br>')}</div>
                    <div class="app-modal-actions">
                        <button type="button" class="app-modal-btn app-modal-btn-primary" id="appModalConfirmBtn">
                            ${this.escapeHtml(confirmText)}
                        </button>
                    </div>
                </div>
            `;

            this.createModal(modalContent, (confirmed) => {
                resolve(confirmed);
            });
        });
    }

    /**
     * Menampilkan Modal Dialog Konfirmasi Pop-Up (menggantikan window.confirm)
     */
    confirm({
        type = 'warning',
        title = 'Konfirmasi',
        message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        confirmText = 'Ya, Lanjutkan',
        cancelText = 'Batal',
        isDanger = false
    }) {
        return new Promise((resolve) => {
            const config = this.getTypeConfig(isDanger ? 'danger' : type);

            const modalContent = `
                <div class="app-modal-dialog app-modal-confirm">
                    <div class="app-modal-icon-wrap" style="background: ${config.badgeBg}; color: ${config.badgeColor}; border: 1px solid ${config.border};">
                        <ion-icon name="${isDanger ? 'alert-circle' : config.icon}"></ion-icon>
                    </div>
                    <div class="app-modal-title">${this.escapeHtml(title)}</div>
                    <div class="app-modal-desc">${this.escapeHtml(message).replace(/\n/g, '<br>')}</div>
                    <div class="app-modal-actions app-modal-actions-dual">
                        <button type="button" class="app-modal-btn app-modal-btn-secondary" id="appModalCancelBtn">
                            ${this.escapeHtml(cancelText)}
                        </button>
                        <button type="button" class="app-modal-btn ${isDanger ? 'app-modal-btn-danger' : 'app-modal-btn-primary'}" id="appModalConfirmBtn">
                            ${this.escapeHtml(confirmText)}
                        </button>
                    </div>
                </div>
            `;

            this.createModal(modalContent, (confirmed) => {
                resolve(confirmed);
            });
        });
    }

    /**
     * Internal Modal Builder & Event Dispatcher
     */
    createModal(htmlContent, callback) {
        if (this.activeModal) {
            this.closeModal(false);
        }

        const backdrop = document.createElement('div');
        backdrop.className = 'app-modal-backdrop active';
        backdrop.style.display = 'flex';
        backdrop.innerHTML = htmlContent;
        backdrop.setAttribute('role', 'dialog');
        backdrop.setAttribute('aria-modal', 'true');

        document.body.appendChild(backdrop);
        document.body.classList.add('app-modal-open');
        this.activeModal = { backdrop, callback };

        const confirmBtn = backdrop.querySelector('#appModalConfirmBtn');
        const cancelBtn = backdrop.querySelector('#appModalCancelBtn');

        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                this.closeModal(true);
            });
            setTimeout(() => confirmBtn.focus(), 50);
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.closeModal(false);
            });
        }

        // Klik pada backdrop luar dialog untuk cancel
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                this.closeModal(false);
            }
        });

        requestAnimationFrame(() => {
            backdrop.classList.add('app-modal-enter');
        });
    }

    /**
     * Menutup Modal Dialog yang Sedang Terbuka
     */
    closeModal(result) {
        if (!this.activeModal) return;

        const { backdrop, callback } = this.activeModal;
        this.activeModal = null;

        backdrop.classList.remove('app-modal-enter');
        backdrop.classList.add('app-modal-exit');
        backdrop.style.pointerEvents = 'none';

        let cleanedUp = false;
        const cleanup = () => {
            if (cleanedUp) return;
            cleanedUp = true;
            if (backdrop && backdrop.parentNode) {
                backdrop.parentNode.removeChild(backdrop);
            }
            if (!this.activeModal) {
                document.body.classList.remove('app-modal-open');
            }
            if (typeof callback === 'function') {
                callback(result);
            }
        };

        const timer = setTimeout(cleanup, 260);
        backdrop.addEventListener('transitionend', () => {
            clearTimeout(timer);
            cleanup();
        }, { once: true });
        backdrop.addEventListener('animationend', () => {
            clearTimeout(timer);
            cleanup();
        }, { once: true });
    }

    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}

// Inisialisasi Global Instance
const appNotification = new AppNotificationManager();
window.AppNotification = appNotification;
window.showAppToast = (params) => appNotification.toast(typeof params === 'string' ? { message: params } : params);
window.showAppAlert = (params) => appNotification.alert(typeof params === 'string' ? { message: params } : params);
window.showAppConfirm = (params) => appNotification.confirm(typeof params === 'string' ? { message: params } : params);

// Override native window.alert agar secara otomatis menggunakan Pop-up Modal elegan
window.alert = function(message) {
    let msg = message;
    let type = 'info';
    let title = 'Informasi';

    if (typeof msg === 'string') {
        if (/akses ditolak|gagal|peringatan|fake gps|mock location|tidak bisa|izin ditolak/i.test(msg)) {
            type = 'warning';
            title = 'Perhatian';
        }
        if (/berhasil|sukses/i.test(msg)) {
            type = 'success';
            title = 'Berhasil';
        }
    }

    return appNotification.alert({
        type: type,
        title: title,
        message: String(msg || '')
    });
};

export default appNotification;
