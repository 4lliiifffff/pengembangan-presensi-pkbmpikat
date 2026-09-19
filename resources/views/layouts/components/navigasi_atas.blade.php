<div class="topBar">
    <div class="topBarRow">
        <div class="profileGroup">
            @if(isset($backRoute))
                <a href="{{ $backRoute }}" aria-label="Kembali" title="Kembali" class="iconBtn flex-shrink-0">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                </a>
            @endif
            <a href="{{ route('profil.index') }}" aria-label="Buka Profil" class="text-no-decor flex-shrink-0">
                @if(auth()->check() && auth()->user()->foto)
                    <img src="{{ auth()->user()->foto_url }}" class="avatar" alt="Avatar" class="object-cover" />
                @else
                    <div class="avatar" title="{{ $roleTitle ?? 'Profil' }}">
                        {{ strtoupper(substr(auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </a>
            <div class="profileMeta">
                <div class="name">
                    {{ $titleName ?? (auth()->check() ? (auth()->user()->nama_lengkap ?? (auth()->user()->name ?? 'Pengguna')) : 'Pengguna') }}
                </div>
                <div class="sub">
                    {{ $subTitle ?? (auth()->check() ? (auth()->user()->role === 'admin' ? 'Admin PKBM' : (auth()->user()->role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Tutor PKBM')) : 'Sistem Presensi PKBM Pikat') }}
                </div>
            </div>
        </div>
        <div class="topIcons">
            
            @if(app()->environment('local', 'testing') || config('app.debug') || env('APP_QUICK_LOGIN', false))
                <div class="quick-switcher-wrap" style="position: relative;">
                    <button class="iconBtn quick-switch-trigger" type="button" id="quickRoleSwitcherBtn" aria-label="Ganti Role Cepat (Dev)" title="Ganti Role Cepat (Dev / Testing)">
                        <ion-icon name="flash"></ion-icon>
                    </button>
                    <div class="quick-switcher-dropdown" id="quickRoleSwitcherMenu">
                        <div class="qsd-header">
                            <ion-icon name="flash-outline"></ion-icon>
                            <span>Ganti Role Pengujian</span>
                        </div>
                        <a href="{{ route('login.quick.get', ['role' => 'admin']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'admin' ? 'active' : '' }}">
                            <span class="qsd-badge admin"><ion-icon name="shield-checkmark-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Admin</span>
                                <span class="qsd-name">Kak Tasya</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'kepala_sekolah']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'kepala_sekolah' ? 'active' : '' }}">
                            <span class="qsd-badge kepsek"><ion-icon name="ribbon-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Kepsek</span>
                                <span class="qsd-name">Bu Dara</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'tutor']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'tutor' ? 'active' : '' }}">
                            <span class="qsd-badge tutor"><ion-icon name="school-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Tutor</span>
                                <span class="qsd-name">Kak Tari</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'magang']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'magang' ? 'active' : '' }}">
                            <span class="qsd-badge magang"><ion-icon name="id-card-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Magang</span>
                                <span class="qsd-name">Alif</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'siswa']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'siswa' ? 'active' : '' }}">
                            <span class="qsd-badge siswa"><ion-icon name="book-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Siswa</span>
                                <span class="qsd-name">Zeldi (001)</span>
                            </span>
                        </a>
                    </div>
                </div>
                <script>
                    (function() {
                        const btn = document.getElementById('quickRoleSwitcherBtn');
                        const menu = document.getElementById('quickRoleSwitcherMenu');
                        if (btn && menu) {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                menu.classList.toggle('open');
                            });
                            document.addEventListener('click', function(e) {
                                if (!menu.contains(e.target) && e.target !== btn) {
                                    menu.classList.remove('open');
                                }
                            });
                        }
                    })();
                </script>
            @endif
            <button class="iconBtn" type="button" aria-label="Tema" id="themeToggleBtn" title="Ganti Tema">
                <ion-icon name="moon-outline" id="themeToggleIcon"></ion-icon>
            </button>
            <form action="{{ route('logout') }}" method="POST" id="navLogoutForm" class="d-none">
                @csrf
            </form>
            <a href="{{ route('logout') }}" class="iconBtn" aria-label="Keluar" title="Keluar dari Akun" onclick="event.preventDefault(); (window.AppNotification ? window.AppNotification.confirm({ title: 'Konfirmasi Keluar', message: 'Apakah Anda yakin ingin keluar dari akun ini?', confirmText: 'Keluar Akun', cancelText: 'Batal', isDanger: true }) : Promise.resolve(confirm('Apakah Anda yakin ingin keluar dari akun?'))).then(ok => { if(ok) { const f = document.getElementById('navLogoutForm'); if(f) f.submit(); } });">
                <ion-icon name="log-out-outline"></ion-icon>
            </a>
        </div>
    </div>
</div>