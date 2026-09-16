<!doctype html>
<html lang="id">
<head >
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0B5ED7">
    <title >@yield('title', 'Smart Presensi — Kepala Sekolah')</title>
    <link rel="manifest" href="/manifest.json" />
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.jpeg') }}" />

    <script >
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    @if(session('success'))
        <meta name="flash-success" content="{{ session('success') }}">
    @endif
    @if(session('warning'))
        <meta name="flash-warning" content="{{ session('warning') }}">
    @endif
    @if(session('error'))
        <meta name="flash-error" content="{{ session('error') }}">
    @endif
    @if(session('info'))
        <meta name="flash-info" content="{{ session('info') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body >
    <div id="appCapsule">
        @include('layouts.components.navigasi_atas', ['roleTitle' => 'Kepala Sekolah', 'titleName' => 'Kepala Sekolah'])

        @yield('content')
    </div>

    @include('layouts.components.navigasi_bawah_kepsek')

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script >
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
