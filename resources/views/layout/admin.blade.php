    <!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0B5ED7">
    <title>@yield('title', 'Smart Presensi — Admin')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.jpeg') }}" />

    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="appCapsule">
        <div class="topBar">
            <div class="topBarRow">
                <div class="profileGroup">
                    <a href="{{ route('profil.index') }}" style="text-decoration: none;">
                        @if(auth()->check() && auth()->user()->foto)
                            <img src="{{ str_starts_with(auth()->user()->foto, 'uploads/') ? asset(auth()->user()->foto) : asset('storage/' . auth()->user()->foto) }}" class="avatar" alt="Avatar" style="object-fit:cover;" />
                        @else
                            <div class="avatar" title="Admin">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                        @endif
                    </a>
                    <div class="profileMeta">
                        <div class="name">Admin PKBM</div>
                        <div class="sub">Pintar Presence System</div>
                    </div>
                </div>
                <div class="topIcons">
                    <button class="iconBtn" type="button" aria-label="Tema" id="themeToggleBtn">
                        <ion-icon name="moon-outline" id="themeToggleIcon"></ion-icon>
                    </button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="flashAlert success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="flashAlert warning">{{ session('warning') }}</div>
        @endif

        @yield('content')
    </div>

    {{--  Top Header  --}}


    {{-- ── Bottom Nav Admin ── --}}
    <nav class="bottomNavAdmin">
        <a href="{{ route('admin.dashboard') }}"
           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           aria-label="Dashboard">
            <ion-icon name="{{ request()->routeIs('admin.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('admin.karyawan.index') }}"
           class="{{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}"
           aria-label="Data Karyawan">
            <ion-icon name="{{ request()->routeIs('admin.karyawan.*') ? 'id-card' : 'id-card-outline' }}"></ion-icon>
            <span>Karyawan</span>
        </a>

        <a href="{{ route('admin.siswa.index') }}"
           class="{{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}"
           aria-label="Data Siswa">
            <ion-icon name="{{ request()->routeIs('admin.siswa.*') ? 'school' : 'school-outline' }}"></ion-icon>
            <span>Siswa</span>
        </a>

        <a href="{{ route('admin.jadwal.index') }}"
           class="{{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}"
           aria-label="Jadwal">
            <ion-icon name="{{ request()->routeIs('admin.jadwal.*') ? 'calendar' : 'calendar-outline' }}"></ion-icon>
            <span>Agenda</span>
        </a>

        <a href="{{ route('admin.presensi') }}"
           class="{{ request()->routeIs('admin.presensi*') ? 'active' : '' }}"
           aria-label="Presensi">
            <ion-icon name="{{ request()->routeIs('admin.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
            <span>Absen</span>
        </a>

        <a href="{{ route('admin.izin.index') }}"
           class="{{ request()->routeIs('admin.izin.*') ? 'active' : '' }}"
           aria-label="Izin Tutor">
            <ion-icon name="{{ request()->routeIs('admin.izin.*') ? 'shield-checkmark' : 'shield-checkmark-outline' }}"></ion-icon>
            <span>Izin</span>
        </a>

        <a href="{{ route('admin.laporan.index') }}"
           class="{{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}"
           aria-label="Laporan">
            <ion-icon name="{{ request()->routeIs('admin.laporan.*') ? 'bar-chart' : 'bar-chart-outline' }}"></ion-icon>
            <span>Laporan</span>
        </a>
    </nav>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeToggleIcon');
            const root = document.documentElement;

            function updateIcon() {
                if (root.getAttribute('data-theme') === 'dark') {
                    if (icon) icon.setAttribute('name', 'sunny-outline');
                } else {
                    if (icon) icon.setAttribute('name', 'moon-outline');
                }
            }
            updateIcon();

            if (btn) {
                btn.addEventListener('click', () => {
                    const current = root.getAttribute('data-theme');
                    const next = current === 'dark' ? 'light' : 'dark';
                    root.setAttribute('data-theme', next);
                    localStorage.setItem('theme', next);
                    updateIcon();
                });
            }
        });
    </script>
</body>
</html>
