<!doctype html>
<html lang="id" class="login-html">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="theme-color" content="#0B5ED7" />
    <title>Masuk - Presensi PKBM Pikat</title>
    <meta name="description" content="Sistem Informasi dan Presensi Terpadu PKBM Pintar Berbakat Bandung" />
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.jpeg') }}" />

    {{-- Meta Flash Session untuk Sistem Toast Global --}}
    @if(session('success'))
        <meta name="flash-success" content="{{ session('success') }}">
    @endif
    @if(session('warning'))
        <meta name="flash-warning" content="{{ session('warning') }}">
    @endif
    @if(session('error'))
        <meta name="flash-error" content="{{ session('error') }}">
    @endif
    @if($errors->any())
        <meta name="flash-error" content="{{ $errors->first() }}">
    @endif
    @if(session('info'))
        <meta name="flash-info" content="{{ session('info') }}">
    @endif

    {{-- Script inisialisasi tema sebelum render untuk mencegah efek flicker --}}
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Ionicons CDN --}}
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>

<body class="login-page">
    @include('layouts.components.preloader')

    {{-- Background Ambient Glow terisolasi (fixed) agar tidak menyebabkan scroll ganda --}}
    <div class="login-bg-glow" aria-hidden="true"></div>

    <div class="login-wrapper">

        <div class="login-card">

            {{-- ── Tombol Switcher Dark/Light Mode (Pojok Kanan Atas Kartu Login) ── --}}
            <div class="login-theme-toggle-wrap" title="Ganti Mode Gelap / Terang">
                <div class="toggleWrapper">
                    <input class="input dn-input" id="dnLogin" type="checkbox" aria-label="Ganti Mode Gelap Terang" />
                    <label class="toggle" for="dnLogin">
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
                    const dnLoginEl = document.getElementById('dnLogin');
                    if (dnLoginEl) dnLoginEl.checked = true;
                }
            </script>

            {{-- ── Panel Kiri: Brand Showcase (Desktop / Tablet) ── --}}
            <div class="login-brand-panel">
                <div class="login-brand-header">
                    <img src="{{ asset('assets/img/Logo.jpeg') }}" alt="Logo PKBM Pikat" class="login-brand-logo" />
                    <h1 class="login-brand-title">PKBM Pintar Berbakat</h1>
                    <p class="login-brand-subtitle">Homeschooling &amp; Pusat Kegiatan Belajar Masyarakat Berbasis
                        Keberbakatan</p>
                </div>

                <!-- <div class="login-brand-features">
                    <div class="login-feature-item">
                        <div class="login-feature-icon">
                            <ion-icon name="location-outline"></ion-icon>
                        </div>
                        <span>Presensi Cerdas Berbasis Geofencing &amp; Selfie</span>
                    </div>
                    <div class="login-feature-item">
                        <div class="login-feature-icon">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                        <span>Jadwal Belajar &amp; Kalender Sesi Terintegrasi</span>
                    </div>
                    <div class="login-feature-item">
                        <div class="login-feature-icon">
                            <ion-icon name="shield-checkmark-outline"></ion-icon>
                        </div>
                        <span>Rekapitulasi Kehadiran &amp; Payroll Akurat</span>
                    </div>
                </div> -->

                <div class="login-brand-footer">
                    &copy; {{ date('Y') }} PKBM Pintar Berbakat. Hak Cipta Dilindungi.
                </div>
            </div>

            {{-- ── Panel Kanan: Form Autentikasi ── --}}
            <div class="login-form-panel">

                {{-- Mobile Header --}}
                <div class="login-mobile-header">
                    <img src="{{ asset('assets/img/Logo.jpeg') }}" alt="Logo PKBM Pikat" class="login-mobile-logo" />
                    <h2 class="login-form-title">PKBM Pintar Berbakat</h2>
                    <p class="login-form-desc mb-0">Sistem Informasi &amp; Presensi Terpadu</p>
                </div>

                {{-- Desktop Heading --}}
                <div class="d-none d-md-block">
                    <h2 class="login-form-title">Selamat Datang</h2>
                    <p class="login-form-desc">Silakan masukkan identitas akun Anda untuk masuk ke sistem portal.</p>
                </div>

                {{-- ── Formulir Login ── --}}
                <form action="{{ route('login.process') }}" method="POST" autocomplete="on" id="loginForm">
                    @csrf

                    {{-- Username / NIK / Email --}}
                    <div class="login-field">
                        <label for="username" class="login-label">Username, NIK, atau Email</label>
                        <div class="login-input-group">
                            <span class="login-input-icon">
                                <ion-icon name="person-outline"></ion-icon>
                            </span>
                            <input type="text" name="username" id="username" class="login-input"
                                placeholder="Contoh: 12345, username, atau email" value="{{ old('username') }}"
                                autocomplete="username" inputmode="text" enterkeyhint="next" required autofocus />
                        </div>
                        <!-- <div class="d-flex align-items-center gap-1 mt-1 text-muted" style="font-size: 0.75rem;">
                            <ion-icon name="checkmark-circle-outline" class="text-primary"></ion-icon>
                            <span>Mendukung login NIK, No. Absen, &amp; Akun SIMPIKAT</span>
                        </div> -->
                    </div>

                    {{-- Password --}}
                    <div class="login-field">
                        <label for="password" class="login-label">Password</label>
                        <div class="login-input-group">
                            <span class="login-input-icon">
                                <ion-icon name="lock-closed-outline"></ion-icon>
                            </span>
                            <input type="password" name="password" id="password"
                                class="login-input login-input-has-toggle" placeholder="Masukkan kata sandi akun"
                                autocomplete="current-password" enterkeyhint="go" required />
                            <button type="button" class="login-toggle-password" id="togglePasswordBtn"
                                aria-label="Tampilkan Password" title="Tampilkan / Sembunyikan Password">
                                <ion-icon name="eye-outline" id="togglePasswordIcon"></ion-icon>
                            </button>
                        </div>
                    </div>

                    {{-- Opsi Remember & Lupa Password --}}
                    @php
                        $adminWaNumber = \App\Models\User::getAdminWaContact();
                    @endphp
                    <div class="login-options-row">
                        <label class="login-remember-label">
                            <input type="checkbox" name="remember" id="remember" value="1"
                                class="login-remember-checkbox" {{ old('remember') ? 'checked' : '' }} />
                            <span>Ingat Saya</span>
                        </label>
                        <a href="https://wa.me/{{ $adminWaNumber }}?text=Halo%20Admin%20PKBM%20Pikat,%20saya%20butuh%20bantuan%20kredensial%20akun%20login."
                            target="_blank" rel="noopener noreferrer" class="login-forgot-link">
                            <ion-icon name="logo-whatsapp"></ion-icon>
                            <span>Lupa Password?</span>
                        </a>
                    </div>

                    {{-- Tombol Submit --}}
                    <button type="submit" class="login-btn-submit" id="btnSubmitLogin">
                        <span id="btnSubmitText">Masuk ke Sistem</span>
                        <ion-icon name="arrow-forward-outline" id="btnSubmitIcon"></ion-icon>
                    </button>
                </form>

                {{-- ── Panel Pengujian: Quick Login Multi-Role (Testing & Dev Mode) ── --}}
                @if(\App\Models\User::isQuickLoginEnabled())
                    @php
                        $adminUser = \App\Models\User::where('role', 'admin')->first();
                        $kepsekUser = \App\Models\User::where('role', 'kepala_sekolah')->first();
                        $tutorUser = \App\Models\User::where('role', 'tutor')->first();
                        $magangUser = \App\Models\User::where('role', 'magang')->first();
                        $muridUser = \App\Models\User::whereIn('role', ['murid', 'siswa'])->first();

                        $quickRoles = [
                            [
                                'role' => 'admin',
                                'class' => 'role-admin',
                                'icon' => 'shield-checkmark-outline',
                                'tag' => 'ADMINISTRATOR',
                                'name' => $adminUser->nama_lengkap ?? 'Natasya Dwi Putri',
                                'account' => $adminUser->email ?? 'admin@pkbmpikat.com',
                                'fillRole' => 'Admin',
                            ],
                            [
                                'role' => 'kepala_sekolah',
                                'class' => 'role-kepsek',
                                'icon' => 'ribbon-outline',
                                'tag' => 'KEPALA SEKOLAH',
                                'name' => $kepsekUser->nama_lengkap ?? 'Dara Agstiana, M.Pd.',
                                'account' => $kepsekUser->email ?? 'kepsek@pkbmpikat.com',
                                'fillRole' => 'Kepala Sekolah',
                            ],
                            [
                                'role' => 'tutor',
                                'class' => 'role-tutor',
                                'icon' => 'school-outline',
                                'tag' => 'TUTOR / GURU',
                                'name' => $tutorUser->nama_lengkap ?? 'Sendi Lestari, S.Pd.',
                                'account' => $tutorUser->email ?? 'sendi.lestari@pkbmpikat.com',
                                'fillRole' => 'Tutor',
                            ],
                            [
                                'role' => 'magang',
                                'class' => 'role-magang',
                                'icon' => 'id-card-outline',
                                'tag' => 'MAHASISWA MAGANG',
                                'name' => $magangUser->nama_lengkap ?? 'Rizky Pratama',
                                'account' => $magangUser->email ?? 'magang@pkbmpikat.com',
                                'fillRole' => 'Magang',
                            ],
                            [
                                'role' => 'murid',
                                'class' => 'role-siswa',
                                'icon' => 'book-outline',
                                'tag' => 'MURID HOMESCHOOLING',
                                'name' => $muridUser->nama_lengkap ?? 'Ari Tri Agusta (001)',
                                'account' => $muridUser->email ?? 'murid1@pkbmpikat.com',
                                'fillRole' => 'Murid',
                            ],
                        ];
                    @endphp

                    <div class="login-quick-section" id="quickLoginSection">
                        <div class="login-quick-divider">
                            <span>AKSES PENGUJIAN CEPAT (DEMO ROLE)</span>
                        </div>

                        <div class="login-quick-header-row">
                            <div class="login-quick-badge">
                                <ion-icon name="flash"></ion-icon>
                                <span>Mode Pengujian</span>
                            </div>
                            <div class="login-quick-subtitle">Masuk instan 1-klik atau salin kredensial untuk setiap role:</div>
                        </div>

                        <div class="login-quick-grid">
                            @foreach($quickRoles as $qr)
                                <div class="quick-role-card {{ $qr['class'] }}" data-role="{{ $qr['role'] }}">
                                    <div class="qrc-top">
                                        <div class="qrc-badge-icon">
                                            <ion-icon name="{{ $qr['icon'] }}"></ion-icon>
                                        </div>
                                        <div class="qrc-info">
                                            <div class="qrc-tag">{{ $qr['tag'] }}</div>
                                            <div class="qrc-name">{{ $qr['name'] }}</div>
                                            <div class="qrc-account">{{ $qr['account'] }}</div>
                                        </div>
                                    </div>
                                    <div class="qrc-btn-group">
                                        <form action="{{ route('login.quick') }}" method="POST" class="w-100">
                                            @csrf
                                            <input type="hidden" name="role" value="{{ $qr['role'] }}">
                                            <button type="submit" class="qrc-btn-instant" title="Masuk langsung sebagai {{ $qr['fillRole'] }}">
                                                <ion-icon name="flash"></ion-icon>
                                                <span>Masuk</span>
                                            </button>
                                        </form>
                                        <button type="button" class="qrc-btn-fill"
                                            onclick="fillLoginForm('{{ $qr['account'] }}', 'password123', '{{ $qr['fillRole'] }}')"
                                            title="Isi form dengan akun {{ $qr['fillRole'] }}">
                                            <ion-icon name="create-outline"></ion-icon>
                                            <span>Isi</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- Script interaktivitas halaman login --}}
    <script>
        (function () {
            // ── 1. Toggle Show / Hide Password ──
            const passwordInput = document.getElementById('password');
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    if (togglePasswordIcon) {
                        togglePasswordIcon.setAttribute('name', isPassword ? 'eye-off-outline' : 'eye-outline');
                    }
                });
            }

            // ── 2. Keyboard Navigation ──
            const usernameInput = document.getElementById('username');
            const loginForm = document.getElementById('loginForm');

            if (usernameInput && passwordInput) {
                usernameInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        passwordInput.focus();
                    }
                });
            }

            // ── 3. Submit Loading State & Anti Double-Submit ──
            if (loginForm) {
                loginForm.addEventListener('submit', function () {
                    const btn = document.getElementById('btnSubmitLogin');
                    const text = document.getElementById('btnSubmitText');
                    const icon = document.getElementById('btnSubmitIcon');

                    if (btn) {
                        btn.disabled = true;
                        if (text) text.textContent = 'Memverifikasi...';
                        if (icon) icon.setAttribute('name', 'sync-outline');
                        btn.classList.add('opacity-80');
                    }
                });
            }

            // ── 4. Toggle Dark / Light Mode ──
            const dnLogin = document.getElementById('dnLogin');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');

            function syncLoginTheme(theme) {
                const isDark = theme === 'dark';
                if (dnLogin) dnLogin.checked = isDark;
                if (themeIcon) {
                    themeIcon.setAttribute('name', isDark ? 'sunny-outline' : 'moon-outline');
                }
            }

            const currentTheme = document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme') || 'light';
            syncLoginTheme(currentTheme);

            if (dnLogin && !dnLogin.dataset.loginBound) {
                dnLogin.dataset.loginBound = 'true';
                dnLogin.addEventListener('change', function () {
                    const next = dnLogin.checked ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', next);
                    localStorage.setItem('theme', next);
                    syncLoginTheme(next);
                });
            }

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function () {
                    const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
                    const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    syncLoginTheme(newTheme);
                });
            }

            // ── 5. Mobile Smooth Scroll saat keyboard virtual muncul ──
            const inputs = document.querySelectorAll('.login-input');
            inputs.forEach(function (input) {
                input.addEventListener('focus', function () {
                    setTimeout(function () {
                        input.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 300);
                });
            });

            @if(\App\Models\User::isQuickLoginEnabled())
            // ── 6. Helper Isi Form Pengujian Cepat (Quick Fill) ──
            window.fillLoginForm = function (username, password, roleLabel) {
                if (usernameInput) {
                    usernameInput.value = username;
                    usernameInput.classList.remove('input-highlight-pulse');
                    void usernameInput.offsetWidth; // Force reflow
                    usernameInput.classList.add('input-highlight-pulse');
                }
                if (passwordInput) {
                    passwordInput.value = password;
                    passwordInput.classList.remove('input-highlight-pulse');
                    void passwordInput.offsetWidth; // Force reflow
                    passwordInput.classList.add('input-highlight-pulse');
                }

                if (window.AppNotification && typeof window.AppNotification.toast === 'function') {
                    window.AppNotification.toast({
                        type: 'info',
                        message: 'Akun ' + roleLabel + ' dimasukkan ke form login.',
                        duration: 2500
                    });
                }

                // Scroll perlahan ke atas jika di mobile
                if (usernameInput && window.innerWidth < 768) {
                    usernameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            };
            @endif
        })();
    </script>

</body>

</html>