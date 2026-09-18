<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="theme-color" content="#0B5ED7" />
    <title>Masuk — Smart Presensi PKBM Pikat</title>
    <meta name="description" content="Sistem Informasi & Presensi Terpadu PKBM Pintar Berbakat Homeschooling Bandung" />
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

<body>

    <div class="login-wrapper">

        {{-- ── Tombol Switcher Dark/Light Mode ── --}}
        <button type="button" class="login-theme-toggle" id="themeToggleBtn" aria-label="Ganti Tema" title="Ganti Mode Tampilan">
            <ion-icon name="moon-outline" id="themeIcon"></ion-icon>
        </button>

        <div class="login-card">

            {{-- ── Panel Kiri: Brand Showcase (Desktop / Tablet) ── --}}
            <div class="login-brand-panel">
                <div class="login-brand-header">
                    <img src="{{ asset('assets/img/Logo.jpeg') }}" alt="Logo PKBM Pikat" class="login-brand-logo" />
                    <h1 class="login-brand-title">PKBM Pintar Berbakat</h1>
                    <p class="login-brand-subtitle">Homeschooling &amp; Pusat Kegiatan Belajar Masyarakat Berbasis Keberbakatan</p>
                </div>

                <div class="login-brand-features">
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
                </div>

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
                            <input 
                                type="text" 
                                name="username" 
                                id="username" 
                                class="login-input" 
                                placeholder="Contoh: 12345 atau email@pkbmpikat.com" 
                                value="{{ old('username') }}" 
                                autocomplete="username" 
                                inputmode="text" 
                                enterkeyhint="next" 
                                required 
                                autofocus
                            />
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="login-field">
                        <label for="password" class="login-label">Password</label>
                        <div class="login-input-group">
                            <span class="login-input-icon">
                                <ion-icon name="lock-closed-outline"></ion-icon>
                            </span>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="login-input login-input-has-toggle" 
                                placeholder="Masukkan kata sandi akun" 
                                autocomplete="current-password" 
                                enterkeyhint="go" 
                                required
                            />
                            <button type="button" class="login-toggle-password" id="togglePasswordBtn" aria-label="Tampilkan Password" title="Tampilkan / Sembunyikan Password">
                                <ion-icon name="eye-outline" id="togglePasswordIcon"></ion-icon>
                            </button>
                        </div>
                    </div>

                    {{-- Opsi Remember & Lupa Password --}}
                    <div class="login-options-row">
                        <label class="login-remember-label">
                            <input type="checkbox" name="remember" id="remember" value="1" class="login-remember-checkbox" {{ old('remember') ? 'checked' : '' }} />
                            <span>Ingat Saya</span>
                        </label>
                        <a href="https://wa.me/6285156452939?text=Halo%20Admin%20PKBM%20Pikat,%20saya%20butuh%20bantuan%20kredensial%20akun%20login." target="_blank" rel="noopener noreferrer" class="login-forgot-link">
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

            </div>
        </div>

    </div>

    {{-- Script interaktivitas halaman login --}}
    <script>
        (function() {
            // ── 1. Toggle Show / Hide Password ──
            const passwordInput = document.getElementById('password');
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function() {
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
                usernameInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        passwordInput.focus();
                    }
                });
            }

            // ── 3. Submit Loading State & Anti Double-Submit ──
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
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
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');

            function updateThemeIcon(theme) {
                if (!themeIcon) return;
                themeIcon.setAttribute('name', theme === 'dark' ? 'sunny-outline' : 'moon-outline');
            }

            // Sinkronkan icon awal
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeIcon(currentTheme);

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function() {
                    const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
                    const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeIcon(newTheme);
                });
            }

            // ── 5. Mobile Smooth Scroll saat keyboard virtual muncul ──
            const inputs = document.querySelectorAll('.login-input');
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    setTimeout(function() {
                        input.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 300);
                });
            });
        })();
    </script>

</body>

</html>
