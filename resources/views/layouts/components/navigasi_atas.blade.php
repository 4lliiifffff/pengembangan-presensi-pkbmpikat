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
            @if(\App\Models\User::isQuickLoginEnabled())
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
                                <span class="qsd-name">Natasya Dwi Putri</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'kepala_sekolah']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'kepala_sekolah' ? 'active' : '' }}">
                            <span class="qsd-badge kepsek"><ion-icon name="ribbon-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Kepsek</span>
                                <span class="qsd-name">Dara Agstiana, M.Pd.</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'tutor']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'tutor' ? 'active' : '' }}">
                            <span class="qsd-badge tutor"><ion-icon name="school-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Tutor</span>
                                <span class="qsd-name">Sendi Lestari, S.Pd.</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'magang']) }}" class="qsd-item {{ auth()->check() && auth()->user()->role === 'magang' ? 'active' : '' }}">
                            <span class="qsd-badge magang"><ion-icon name="id-card-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Magang</span>
                                <span class="qsd-name">Rizky Pratama</span>
                            </span>
                        </a>
                        <a href="{{ route('login.quick.get', ['role' => 'murid']) }}" class="qsd-item {{ auth()->check() && in_array(auth()->user()->role, ['murid', 'siswa']) ? 'active' : '' }}">
                            <span class="qsd-badge murid"><ion-icon name="book-outline"></ion-icon></span>
                            <span class="qsd-meta">
                                <span class="qsd-role">Murid</span>
                                <span class="qsd-name">Ari Tri Agusta (001)</span>
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
            <div class="topbar-toggle-wrapper" title="Ganti Mode Gelap / Terang">
                <div class="toggleWrapper">
                    <input class="input dn-input" id="dn" type="checkbox" aria-label="Ganti Mode Gelap Terang" />
                    <label class="toggle" for="dn">
                        <span class="toggle__handler">
                            <span class="crater crater--1"></span>
                            <span class="crater crater--2"></span>
                            <span class="crater crater--3"></span>
                        </span>
                        <span class="star star--1"></span>
                        <span class="star star--2"></span>
                        <span class="star star--3"></span>
                        <span class="star star--4"></span>
                        <span class="star star--5"></span>
                        <span class="star star--6"></span>
                    </label>
                </div>
            </div>
            <script>
                if (localStorage.getItem('theme') === 'dark') {
                    const dnEl = document.getElementById('dn');
                    if (dnEl) dnEl.checked = true;
                }
            </script>
            <form action="{{ route('logout') }}" method="POST" id="navLogoutForm" class="d-none">
                @csrf
            </form>
            <a href="{{ route('logout') }}" class="iconBtn" aria-label="Keluar" title="Keluar dari Akun" onclick="event.preventDefault(); (window.AppNotification ? window.AppNotification.confirm({ title: 'Konfirmasi Keluar', message: 'Apakah Anda yakin ingin keluar dari akun ini?', confirmText: 'Keluar Akun', cancelText: 'Batal', isDanger: true }) : Promise.resolve(confirm('Apakah Anda yakin ingin keluar dari akun?'))).then(ok => { if(ok) { const f = document.getElementById('navLogoutForm'); if(f) f.submit(); } });">
                <ion-icon name="log-out-outline"></ion-icon>
            </a>
        </div>
    </div>
</div>