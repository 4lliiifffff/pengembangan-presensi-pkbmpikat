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
    <title>@yield('title', 'Terjadi Kesalahan') - Presensi PKBM Pikat</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.jpeg') }}" />

    {{-- Script Inisialisasi Tema sebelum render --}}
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Ionicons CDN --}}
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <style>
        .error-page-wrapper {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: var(--bg, #bdefff);
            position: relative;
            overflow: hidden;
            font-family: var(--font-sans, system-ui, sans-serif);
        }

        .error-card-container {
            width: 100%;
            max-width: 520px;
            background: var(--card, #ffffff);
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 24px;
            padding: 36px 28px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.1);
            text-align: center;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(12px);
            box-sizing: border-box;
        }

        .error-code-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .error-icon-wrapper {
            width: 84px;
            height: 84px;
            border-radius: 24px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .error-title {
            font-family: var(--font-display, inherit);
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--text, #0f172a);
            margin: 0 0 10px;
            line-height: 1.3;
        }

        .error-description {
            font-size: 14px;
            color: var(--muted, #64748b);
            line-height: 1.6;
            margin: 0 0 28px;
        }

        .error-actions-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        @media (min-width: 440px) {
            .error-actions-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .btn-error-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 14px;
            background: var(--blue-gradient, linear-gradient(135deg, #0B5ED7 0%, #1A73E8 100%));
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(11, 94, 215, 0.3);
            transition: all 0.2s ease;
        }

        .btn-error-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(11, 94, 215, 0.4);
            color: #ffffff;
        }

        .btn-error-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 14px;
            background: var(--card-alt, #f8fbff);
            border: 1px solid var(--border, #e2e8f0);
            color: var(--text, #0f172a);
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-error-secondary:hover {
            background: var(--border, #e2e8f0);
            color: var(--text, #0f172a);
        }

        .error-theme-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--card, #ffffff);
            border: 1px solid var(--border, #e2e8f0);
            color: var(--text, #0f172a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            z-index: 10;
        }
    </style>
</head>

<body>
    @include('layouts.components.preloader')

    <div class="error-page-wrapper">
        {{-- Theme Switcher Button --}}
        <button type="button" class="error-theme-btn" id="themeToggleBtn" aria-label="Ganti Tema">
            <ion-icon name="moon-outline" id="themeIcon"></ion-icon>
        </button>

        <div class="error-card-container">
            @yield('content')
        </div>
    </div>

    {{-- Script Trigger Toast Otomatis & Theme Toggle --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Theme toggle
            const btn = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeIcon');
            const root = document.documentElement;

            function updateThemeIcon() {
                if (root.getAttribute('data-theme') === 'dark') {
                    if (icon) icon.setAttribute('name', 'sunny-outline');
                } else {
                    if (icon) icon.setAttribute('name', 'moon-outline');
                }
            }
            updateThemeIcon();

            if (btn) {
                btn.addEventListener('click', () => {
                    const current = root.getAttribute('data-theme');
                    const next = current === 'dark' ? 'light' : 'dark';
                    root.setAttribute('data-theme', next);
                    localStorage.setItem('theme', next);
                    updateThemeIcon();
                });
            }

            // Otomatis Munculkan Floating Toast Notifikasi Error
            const toastType = "@yield('toast_type', 'warning')";
            const toastTitle = "@yield('toast_title', 'Perhatian')";
            const toastMessage = "@yield('toast_message', 'Terjadi kesalahan saat memproses halaman ini.')";

            setTimeout(() => {
                if (window.AppNotification && typeof window.AppNotification.toast === 'function') {
                    window.AppNotification.toast({
                        type: toastType,
                        title: toastTitle,
                        message: toastMessage,
                        duration: 6000
                    });
                }
            }, 300);
        });
    </script>
</body>

</html>
