<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0B5ED7">
    <title>@yield('title', 'Smart Presensi')</title>
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

            --topbar-bg: linear-gradient(180deg, #0b5ed7 0%, #1a73e8 100%);
            --avatar-bg: #ffedd5;
            --avatar-text: #9a3412;
            --icon-btn-bg: rgba(255, 255, 255, 0.18);
            --icon-btn-border: rgba(255, 255, 255, 0.18);
            --icon-btn-color: #ffffff;
            --card-alt: #f8fbff;
            --card-alt2: #fbfdff;
            --nav-bg: #ffffff;
            --nav-icon: #64748b;
            --nav-active-bg: rgba(11, 94, 215, 0.10);
            --nav-active-border: rgba(11, 94, 215, 0.16);
            --input-bg: #ffffff;
            --pill-pending: rgba(100, 116, 139, 0.10);
            --pill-pending-border: rgba(100, 116, 139, 0.18);
            --empty-dashed: rgba(100, 116, 139, 0.35);
        }

        [data-theme="dark"] {
            --bg: #0f172a;
            --text: #f8fafc;
            --muted: #94a3b8;
            --card: #1e293b;
            --border: #334155;
            --topbar-bg: #1e293b;
            --avatar-bg: #334155;
            --avatar-text: #f8fafc;
            --icon-btn-bg: #334155;
            --icon-btn-border: #334155;
            --icon-btn-color: #f8fafc;
            --card-alt: #0f172a;
            --card-alt2: #0f172a;
            --nav-bg: #1e293b;
            --nav-icon: #64748b;
            --nav-active-bg: rgba(59, 130, 246, 0.10);
            --nav-active-border: rgba(59, 130, 246, 0.16);
            --input-bg: #0f172a;
            --empty-dashed: rgba(148, 163, 184, 0.35);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        #appCapsule {
            max-width: 430px;
            margin: 0 auto;
            min-height: 100vh;
            background: var(--card);
            padding-bottom: 92px; /* space for bottom nav */
        }

        .topBar {
            background: var(--topbar-bg);
            color: #fff;
            padding: 14px 16px;
            border-bottom-left-radius: var(--radius-xl);
            border-bottom-right-radius: var(--radius-xl);
        }

        .topBarRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .profileGroup {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--avatar-bg);
            display: grid;
            place-items: center;
            font-weight: 800;
            color: var(--avatar-text);
            border: 3px solid var(--border);
        }

        .profileMeta {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .profileMeta .name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        .profileMeta .sub {
            font-size: 11px;
            opacity: 0.9;
        }

        .iconBtn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--icon-btn-bg);
            display: grid;
            place-items: center;
            border: 1px solid var(--icon-btn-border);
            color: var(--icon-btn-color);
        }

        .sectionTitleRow {
            padding: 16px 16px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        h2 {
            margin: 0;
            font-size: 14px;
            letter-spacing: 0.2px;
        }

        .badgeDate {
            font-size: 12px;
            padding: 7px 10px;
            border-radius: 14px;
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            font-weight: 700;
            border: 1px solid rgba(239, 68, 68, 0.2);
            white-space: nowrap;
        }

        .contentPad {
            padding: 0 16px;
        }

        .summaryGrid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 0 16px 14px;
        }

        .summaryCard {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: var(--card-alt);
        }

        .summaryTop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .summaryIcon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 18px;
        }

        .summaryIcon.hadir { background: rgba(22, 163, 74, 0.12); color: var(--success); }
        .summaryIcon.sakit { background: rgba(239, 68, 68, 0.12); color: var(--danger); }
        .summaryIcon.izin { background: rgba(245, 158, 11, 0.14); color: var(--warn); }
        .summaryIcon.terlambat { background: rgba(100, 116, 139, 0.14); color: #334155; }

        .summaryLabel {
            font-size: 12px;
            color: var(--muted);
            font-weight: 800;
            letter-spacing: 0.2px;
        }

        .summaryCount {
            font-size: 26px;
            font-weight: 900;
            color: var(--text);
        }

        .cardBox {
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            background: var(--card-alt);
            margin: 0 16px 14px;
            padding: 12px 12px 14px;
        }

        .cardHeadRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 10px;
        }

        .mutedLink {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .barChart {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 86px;
            padding: 6px 4px 0;
        }

        .barCol {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 24px;
            gap: 6px;
        }

        .bar {
            width: 100%;
            border-radius: 10px 10px 0 0;
            background: #e5e7eb;
        }

        .bar.active {
            background: var(--blue2);
        }

        .barDay {
            font-size: 11px;
            color: var(--muted);
            font-weight: 800;
        }

        .activityList {
            padding: 0 16px 6px;
        }

        .activityHeaderRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 2px 16px 10px;
        }

        .activityRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 12px;
            background: var(--card-alt2);
            margin-bottom: 10px;
        }

        .activityLeft {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .activityAvatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--avatar-bg);
            display: grid;
            place-items: center;
            font-weight: 900;
            color: var(--avatar-text);
            flex-shrink: 0;
        }

        .activityName {
            font-size: 13px;
            font-weight: 900;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 200px;
        }

        .activityMeta {
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
            white-space: nowrap;
        }

        .activityRight {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            flex-shrink: 0;
        }

        .pill {
            font-size: 11px;
            font-weight: 900;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid transparent;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        .pill.hadir { background: rgba(22, 163, 74, 0.12); color: #15803d; border-color: rgba(22, 163, 74, 0.2); }
        .pill.sakit { background: rgba(239, 68, 68, 0.12); color: #dc2626; border-color: rgba(239, 68, 68, 0.2); }
        .pill.izin { background: rgba(245, 158, 11, 0.15); color: #d97706; border-color: rgba(245, 158, 11, 0.2); }
        .pill.terlambat { background: rgba(100, 116, 139, 0.12); color: #475569; border-color: rgba(100, 116, 139, 0.2); }
        .pill.pending { background: var(--pill-pending); color: #64748b; border-color: var(--pill-pending-border); }

        .detailBtn {
            font-size: 11px;
            font-weight: 900;
            padding: 7px 10px;
            border-radius: 12px;
            background: rgba(11, 94, 215, 0.10);
            color: var(--blue2);
            border: 1px solid rgba(11, 94, 215, 0.18);
            text-decoration: none;
        }

        .emptyState {
            margin: 10px 0 20px;
            padding: 14px;
            text-align: center;
            border-radius: 16px;
            border: 1px dashed var(--empty-dashed);
            color: var(--muted);
            font-weight: 800;
            font-size: 12px;
        }

        .appBottomMenu {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0 auto;
            max-width: 430px;
            background: var(--nav-bg);
            border-top: 1px solid var(--border);
            padding: 10px 14px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .appBottomMenu a {
            text-decoration: none;
            color: var(--nav-icon);
            display: grid;
            place-items: center;
            width: 52px;
            height: 44px;
            border-radius: 14px;
        }

        .appBottomMenu a.active {
            color: var(--blue2);
            background: var(--nav-active-bg);
            border: 1px solid var(--nav-active-border);
        }

        .appBottomMenu ion-icon {
            font-size: 24px;
        }

        /* Form utilities for CRUD pages */
        .pageHeaderRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 16px 10px;
        }

        .btnPrimary {
            background: var(--blue2);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 10px 12px;
            font-weight: 900;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btnOutlineDanger {
            background: rgba(239, 68, 68, 0.10);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.22);
            border-radius: 14px;
            padding: 9px 10px;
            font-weight: 900;
            font-size: 12px;
            text-decoration: none;
        }

        .btnOutline {
            background: #fff;
            color: var(--blue2);
            border: 1px solid rgba(11, 94, 215, 0.18);
            border-radius: 14px;
            padding: 9px 10px;
            font-weight: 900;
            font-size: 12px;
            text-decoration: none;
        }

        .formCard {
            margin: 0 16px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            background: var(--card-alt);
            padding: 14px;
        }

        .fieldLabel {
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 6px;
            color: var(--text);
        }

        .input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: var(--input-bg);
            outline: none;
            font-size: 13px;
            color: var(--text);
        }

        .input:focus {
            border-color: rgba(11, 94, 215, 0.45);
            box-shadow: 0 0 0 3px rgba(11, 94, 215, 0.15);
        }

        .formRow {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 12px;
        }

        .errorList {
            margin: 0 16px 12px;
            padding: 10px 12px;
            border-radius: 16px;
            border: 1px solid rgba(239, 68, 68, 0.25);
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            font-weight: 900;
            font-size: 12px;
        }

        .flashAlert {
            margin: 12px 16px 0;
            padding: 10px 12px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 800;
        }

        .flashAlert.success {
            border: 1px solid rgba(22, 163, 74, 0.25);
            background: rgba(22, 163, 74, 0.10);
            color: #166534;
        }

        .flashAlert.warning {
            border: 1px solid rgba(245, 158, 11, 0.25);
            background: rgba(245, 158, 11, 0.12);
            color: #92400e;
        }

        .siswaGrid {
            padding: 0 16px 10px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .siswaCard {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 12px;
            background: var(--card);
        }

        .siswaTop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .siswaName {
            font-weight: 1000;
            font-size: 14px;
            margin-top: 2px;
        }

        .siswaMeta {
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 900;
        }

        .siswaActions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .smallBtn {
            padding: 9px 10px;
            border-radius: 14px;
            font-weight: 1000;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .smallBtn.edit {
            border: 1px solid rgba(11, 94, 215, 0.18);
            color: var(--blue2);
            background: rgba(11, 94, 215, 0.06);
        }

        .smallBtn.delete {
            border: 1px solid rgba(239, 68, 68, 0.22);
            color: #dc2626;
            background: rgba(239, 68, 68, 0.08);
        }
    </style>
</head>
<body>
    <div id="appCapsule">
        @if (session('success'))
            <div class="flashAlert success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="flashAlert warning">{{ session('warning') }}</div>
        @endif
        @yield('content')
    </div>

    <div class="appBottomMenu">
        @php
            $leftHref = \Illuminate\Support\Facades\Route::has('tutor.riwayat')
                ? route('tutor.riwayat')
                : '#';
            $centerHref = \Illuminate\Support\Facades\Route::has('tutor.dashboard')
                ? route('tutor.dashboard')
                : '#';
            $rightHref = \Illuminate\Support\Facades\Route::has('tutor.jadwal')
                ? route('tutor.jadwal')
                : '#';
            $isCenter = request()->routeIs('tutor.dashboard');
            $isLeft = request()->routeIs('tutor.riwayat');
            $isRight = request()->routeIs('tutor.jadwal');
        @endphp

        <a href="{{ $leftHref }}" class="{{ $isLeft ? 'active' : '' }}" aria-label="Riwayat">
            <ion-icon name="{{ $isLeft ? 'time' : 'time-outline' }}">Riwayat</ion-icon>
        </a>

        <a href="{{ $centerHref }}" class="{{ $isCenter ? 'active' : '' }}" aria-label="Dashboard">
            <ion-icon name="{{ $isCenter ? 'grid' : 'grid-outline' }}">Dashboard</ion-icon>
        </a>

        <a href="{{ $rightHref }}" class="{{ $isRight ? 'active' : '' }}" aria-label="Jadwal">
            <ion-icon name="{{ $isRight ? 'calendar' : 'calendar-outline' }}">Agenda</ion-icon>
        </a>
    </div>

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
            updateIcon(); // Call immediately for elements that exist

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

