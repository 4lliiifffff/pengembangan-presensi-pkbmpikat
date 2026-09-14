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

    <style>
        :root {
            --blue: #0B5ED7;
            --blue2: #1A73E8;
            --bg: #bdefff;
            --text: #0f172a;
            --muted: #64748b;
            --card: #ffffff;
            --border: #e5e7eb;
            --success: #16a34a;
            --warn: #f59e0b;
            --danger: #ef4444;
            --radius-xl: 18px;
            --radius-lg: 14px;
            --radius-md: 12px;

            --topbar-bg: #ffffff;
            --avatar-bg: #ffedd5;
            --avatar-text: #9a3412;
            --icon-btn-bg: transparent;
            --icon-btn-color: #0f172a;
            --card-alt: #f8fbff;
            --card-alt2: #fbfdff;
            --nav-bg: #ffffff;
            --nav-icon: #94a3b8;
            --nav-active: #1A73E8;
            --input-bg: #ffffff;
            --pill-pending: rgba(100,116,139,0.10);
            --pill-pending-border: rgba(100,116,139,0.18);
            --empty-dashed: rgba(100,116,139,0.35);
        }

        [data-theme="dark"] {
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --border: #334155;
            --topbar-bg: #1e293b;
            --avatar-bg: #334155;
            --avatar-text: #f8fafc;
            --icon-btn-bg: #334155;
            --icon-btn-color: #f8fafc;
            --card-alt: #0f172a;
            --card-alt2: #0f172a;
            --nav-bg: #1e293b;
            --nav-icon: #64748b;
            --nav-active: #60a5fa;
            --input-bg: #0f172a;
            --empty-dashed: rgba(148,163,184,0.35);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0; padding: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        #appCapsule {
            max-width: 430px;
            margin: 0 auto;
            min-height: 100vh;
            background: var(--card);
            padding-bottom: 92px;
        }

        /* ── Top Bar ── */
        .topBar {
            background: var(--topbar-bg);
            color: var(--text);
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }

        .topBarRow { display: flex; align-items: center; justify-content: space-between; gap: 12px; }

        .profileGroup { display: flex; align-items: center; gap: 12px; }

        .avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: var(--avatar-bg);
            display: grid; place-items: center;
            font-weight: 800; color: var(--avatar-text);
            border: 3px solid var(--border);
        }

        .profileMeta { display: flex; flex-direction: column; line-height: 1.1; }
        .profileMeta .name { font-size: 13px; font-weight: 700; color: var(--text); }
        .profileMeta .sub  { font-size: 11px; color: var(--muted); }

        .topIcons { display: flex; align-items: center; gap: 6px; }

        .iconBtn {
            width: 36px; height: 36px;
            border-radius: 999px;
            background: var(--icon-btn-bg);
            display: grid; place-items: center;
            border: none;
            color: var(--icon-btn-color);
            cursor: pointer;
            position: relative;
            padding: 0;
        }

        .iconBtn ion-icon {
            font-size: 20px;
            color: inherit;
        }

        .iconBtn::after {
            content: "";
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 999px;
            top: 5px;
            right: 6px;
            border: 2px solid #fff;
        }

        .iconBtn--notify::after { background: #ef4444; }
        .iconBtn--search::after { background: #1d4ed8; }

        .fabAdd {
            position: fixed;
            right: max(18px, calc((100vw - 430px) / 2 + 18px));
            bottom: 86px;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            border: 1px solid rgba(11,94,215,0.25);
            background: #001a72;
            color: #ffffff;
            display: grid;
            place-items: center;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(1, 16, 74, 0.28);
            z-index: 950;
        }

        .fabAdd ion-icon {
            font-size: 26px;
            font-weight: 900;
        }

        /* ── Section Title ── */
        .sectionTitleRow {
            padding: 16px 16px 10px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }

        h2 { margin: 0; font-size: 14px; letter-spacing: 0.2px; }

        .badgeDate {
            font-size: 12px; padding: 7px 10px;
            border-radius: 14px;
            background: rgba(239,68,68,0.12); color: #dc2626;
            font-weight: 700; border: 1px solid rgba(239,68,68,0.2);
            white-space: nowrap;
        }

        /* ── Summary Grid ── */
        .summaryGrid {
            display: grid; grid-template-columns: repeat(2, minmax(0,1fr));
            gap: 12px; padding: 0 16px 14px;
        }

        .summaryCard {
            border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 12px; display: flex; flex-direction: column; gap: 8px;
            background: var(--card-alt);
        }

        .summaryTop { display: flex; align-items: center; justify-content: space-between; gap: 10px; }

        .summaryIcon { width: 42px; height: 42px; border-radius: 14px; display: grid; place-items: center; font-size: 18px; }
        .summaryIcon.hadir     { background: rgba(22,163,74,0.12);   color: var(--success); }
        .summaryIcon.sakit     { background: rgba(239,68,68,0.12);   color: var(--danger);  }
        .summaryIcon.izin      { background: rgba(245,158,11,0.14);  color: var(--warn);    }
        .summaryIcon.terlambat { background: rgba(100,116,139,0.14); color: #334155;        }

        .summaryLabel { font-size: 12px; color: var(--muted); font-weight: 800; letter-spacing: 0.2px; }
        .summaryCount { font-size: 26px; font-weight: 900; color: var(--text); }

        /* ── Card Box ── */
        .cardBox {
            border: 1px solid var(--border); border-radius: var(--radius-xl);
            background: var(--card-alt); margin: 0 16px 14px; padding: 12px 12px 14px;
        }

        .cardHeadRow { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 10px; }

        .mutedLink { color: var(--muted); font-size: 12px; font-weight: 700; text-decoration: none; }

        /* ── Bar Chart ── */
        .barChart { display: flex; align-items: flex-end; gap: 10px; height: 86px; padding: 6px 4px 0; }
        .barCol { display: flex; flex-direction: column; align-items: center; width: 24px; gap: 6px; }
        .bar { width: 100%; border-radius: 10px 10px 0 0; background: #e5e7eb; }
        .bar.active { background: var(--blue2); }
        .barDay { font-size: 11px; color: var(--muted); font-weight: 800; }

        /* ── Activity ── */
        .activityHeaderRow {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; padding: 2px 16px 10px;
        }

        .activityList { padding: 0 16px 6px; }

        .activityRow {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; border: 1px solid var(--border);
            border-radius: 16px; padding: 12px; background: var(--card-alt2); margin-bottom: 10px;
        }

        .activityLeft { display: flex; align-items: center; gap: 12px; min-width: 0; }

        .activityAvatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: var(--avatar-bg); display: grid; place-items: center;
            font-weight: 900; color: var(--avatar-text); flex-shrink: 0;
        }

        .activityName { font-size: 13px; font-weight: 900; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px; }
        .activityMeta { font-size: 11px; color: var(--muted); margin-top: 3px; white-space: nowrap; }

        .activityRight { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }

        /* ── Pills ── */
        .pill { font-size: 11px; font-weight: 900; padding: 6px 10px; border-radius: 999px; border: 1px solid transparent; letter-spacing: 0.2px; white-space: nowrap; }
        .pill.hadir     { background: rgba(22,163,74,0.12);   color: #15803d; border-color: rgba(22,163,74,0.2);   }
        .pill.sakit     { background: rgba(239,68,68,0.12);   color: #dc2626; border-color: rgba(239,68,68,0.2);   }
        .pill.izin      { background: rgba(245,158,11,0.15);  color: #d97706; border-color: rgba(245,158,11,0.2);  }
        .pill.terlambat { background: rgba(100,116,139,0.12); color: #475569; border-color: rgba(100,116,139,0.2); }
        .pill.pending   { background: var(--pill-pending); color: #64748b; border-color: var(--pill-pending-border);}
        .pill.alpha     { background: rgba(239,68,68,0.12);   color: #b91c1c; border-color: rgba(239,68,68,0.22);   }

        .detailBtn {
            font-size: 11px; font-weight: 900; padding: 7px 10px;
            border-radius: 12px; background: rgba(11,94,215,0.10);
            color: var(--blue2); border: 1px solid rgba(11,94,215,0.18); text-decoration: none;
        }

        .emptyState {
            margin: 10px 0 20px; padding: 14px; text-align: center;
            border-radius: 16px; border: 1px dashed var(--empty-dashed);
            color: var(--muted); font-weight: 800; font-size: 12px;
        }

        /* ── Flash Alert ── */
        .flashAlert { margin: 12px 16px 0; padding: 10px 12px; border-radius: 14px; font-size: 12px; font-weight: 800; }
        .flashAlert.success { border: 1px solid rgba(22,163,74,0.25);  background: rgba(22,163,74,0.10);  color: #166534; }
        .flashAlert.warning { border: 1px solid rgba(245,158,11,0.25); background: rgba(245,158,11,0.12); color: #92400e; }

        /* ── Form / CRUD Utilities ── */
        .pageHeaderRow { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 16px 10px; }

        .btnPrimary {
            background: var(--blue2); color: #fff; border: none;
            border-radius: 14px; padding: 10px 12px;
            font-weight: 900; font-size: 12px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
        }

        .btnOutlineDanger {
            background: rgba(239,68,68,0.10); color: #dc2626;
            border: 1px solid rgba(239,68,68,0.22);
            border-radius: 14px; padding: 9px 10px;
            font-weight: 900; font-size: 12px; text-decoration: none;
        }

        .btnOutline {
            background: #fff; color: var(--blue2);
            border: 1px solid rgba(11,94,215,0.18);
            border-radius: 14px; padding: 9px 10px;
            font-weight: 900; font-size: 12px; text-decoration: none;
        }

        .formCard { margin: 0 16px 16px; border: 1px solid var(--border); border-radius: var(--radius-xl); background: var(--card-alt); padding: 14px; }

        .fieldLabel { font-size: 12px; font-weight: 900; margin-bottom: 6px; color: var(--text); }

        .input {
            width: 100%; padding: 11px 12px;
            border-radius: 14px; border: 1px solid var(--border);
            background: var(--input-bg); outline: none; font-size: 13px; color: var(--text);
        }

        .input:focus { border-color: rgba(11,94,215,0.45); box-shadow: 0 0 0 3px rgba(11,94,215,0.15); }

        .formRow { display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px; }

        .errorList {
            margin: 0 16px 12px; padding: 10px 12px;
            border-radius: 16px; border: 1px solid rgba(239,68,68,0.25);
            background: rgba(239,68,68,0.08); color: #dc2626; font-weight: 900; font-size: 12px;
        }

        .siswaGrid { padding: 0 16px 10px; display: grid; grid-template-columns: 1fr; gap: 10px; }
        .siswaCard { border: 1px solid var(--border); border-radius: 18px; padding: 12px; background: var(--card); }
        .siswaTop { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .siswaName { font-weight: 1000; font-size: 14px; margin-top: 2px; }
        .siswaMeta { margin-top: 3px; color: var(--muted); font-size: 11px; font-weight: 900; }
        .siswaActions { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
        .smallBtn { padding: 9px 10px; border-radius: 14px; font-weight: 1000; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .smallBtn.edit   { border: 1px solid rgba(11,94,215,0.18); color: var(--blue2); background: rgba(11,94,215,0.06); }
        .smallBtn.delete { border: 1px solid rgba(239,68,68,0.22); color: #dc2626;      background: rgba(239,68,68,0.08); }

        /* Pagination styling to match app UI (remove default bullets) */
        .pagination {
            list-style: none;
            padding: 0;
            margin: 12px 0;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .pagination li { display: inline-block; }
        .pagination li a,
        .pagination li span,
        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            text-decoration: none;
            font-weight: 900;
            font-size: 13px;
        }
        .pagination li.active span,
        .pagination li.active a,
        .pagination .active .page-link {
            background: var(--blue2);
            color: #ffffff;
            border-color: transparent;
        }
        .pagination li.disabled span,
        .pagination li.disabled a {
            opacity: 0.45; cursor: default;
        }

        /* ============================================================
           BOTTOM NAV — ADMIN (sesuai desain foto)
           Dashboard | ID Card | Kelas | Kalender | Statistik
        ============================================================ */
        .bottomNavAdmin {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            margin: 0 auto;
            max-width: 430px;
            background: var(--nav-bg);
            border-top: 1px solid var(--border);
            padding: 10px 10px 14px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 999;
        }

        .bottomNavAdmin a {
            text-decoration: none;
            color: var(--nav-icon);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            font-weight: 700;
            font-size: 9px;
            flex: 1;
            padding: 4px 2px;
            border-radius: 12px;
            transition: color 0.15s;
        }

        .bottomNavAdmin a ion-icon { font-size: 22px; }
        .bottomNavAdmin a.active   { color: var(--nav-active); }
    </style>
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
