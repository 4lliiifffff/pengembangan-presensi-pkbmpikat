<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0B5ED7">
    <title>@yield('title', 'Smart Presensi - Kepala Sekolah')</title>
    <link rel="manifest" href="/manifest.json" />
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.jpeg') }}" />

    <script>
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

    {{-- Preconnect untuk Resource Eksternal (Fonts & Icons) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @stack('styles')
</head>

<body>
    @include('layouts.components.preloader')

    <div id="appCapsule">
        @include('layouts.components.navigasi_atas', ['roleTitle' => 'Kepala Sekolah', 'titleName' => 'Kepala Sekolah'])

        @yield('content')
    </div>

    @include('layouts.components.navigasi_bawah_kepsek')

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.documentElement;
            const dn = document.getElementById('dn');
            const icon = document.getElementById('themeToggleIcon');

            function syncTheme() {
                const isDark = root.getAttribute('data-theme') === 'dark';
                if (dn) dn.checked = isDark;
                if (icon) icon.setAttribute('name', isDark ? 'sunny-outline' : 'moon-outline');
            }
            syncTheme();

            if (dn && !dn.dataset.localBound) {
                dn.dataset.localBound = 'true';
                dn.addEventListener('change', () => {
                    const next = dn.checked ? 'dark' : 'light';
                    root.setAttribute('data-theme', next);
                    localStorage.setItem('theme', next);
                    syncTheme();
                });
            }
        });
    </script>
</body>

</html>