<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0B5ED7">
    <title>@yield('title', 'Smart Presensi — Kepala Sekolah')</title>
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
                            <div class="avatar" title="Kepala Sekolah">{{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}</div>
                        @endif
                    </a>
                    <div class="profileMeta">
                        <div class="name">Kepala Sekolah</div>
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

    {{-- ── Bottom Nav Kepsek ── --}}
    <nav class="bottomNavAdmin">
        <a href="{{ route('kepsek.dashboard') }}"
           class="{{ request()->routeIs('kepsek.dashboard') ? 'active' : '' }}"
           aria-label="Dashboard">
            <ion-icon name="{{ request()->routeIs('kepsek.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('kepsek.laporan') }}"
           class="{{ request()->routeIs('kepsek.laporan*') ? 'active' : '' }}"
           aria-label="Laporan">
            <ion-icon name="{{ request()->routeIs('kepsek.laporan*') ? 'bar-chart' : 'bar-chart-outline' }}"></ion-icon>
            <span>Laporan</span>
        </a>

        <a href="{{ route('kepsek.lupa-lapor') }}"
           class="{{ request()->routeIs('kepsek.lupa-lapor*') ? 'active' : '' }}"
           aria-label="Lupa Lapor">
            <ion-icon name="{{ request()->routeIs('kepsek.lupa-lapor*') ? 'document-text' : 'document-text-outline' }}"></ion-icon>
            <span>Lupa Presensi</span>
        </a>

        <a href="{{ route('kepsek.presensi-tutor') }}"
           class="{{ request()->routeIs('kepsek.presensi-tutor*') ? 'active' : '' }}"
           aria-label="Presensi Tutor">
            <ion-icon name="{{ request()->routeIs('kepsek.presensi-tutor*') ? 'newspaper' : 'newspaper-outline' }}"></ion-icon>
            <span>Data</span>
        </a>

        <a href="{{ route('kepsek.presensi') }}"
           class="{{ request()->routeIs('kepsek.presensi') || request()->routeIs('kepsek.presensi.store') ? 'active' : '' }}"
           aria-label="Absen">
            <ion-icon name="{{ request()->routeIs('kepsek.presensi') || request()->routeIs('kepsek.presensi.store') ? 'camera' : 'camera-outline' }}"></ion-icon>
            <span>Absen</span>
        </a>
    </nav>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

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
