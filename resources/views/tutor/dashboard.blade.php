@extends('layout.presensi')

@section('title', 'Dashboard Tutor')

@section('content')
    @php
        $user = auth()->user();
        $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Tutor'));
        $roleLabel = 'Tutor';
        $subTitle = $user?->role ? ucfirst(str_replace('_', ' ', (string) $user->role)) : 'Tutor';
        $initial = strtoupper(substr($displayName, 0, 1));
    @endphp

    <style>
        .tutorTop {
            background: var(--card-alt, #f3f4f6);
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
        }

        .tutorTopRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .tutorLeft {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .tutorAvatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--text, #111827);
            color: var(--card, #fff);
            display: grid;
            place-items: center;
            font-weight: 900;
            overflow: hidden;
        }

        .tutorMeta {
            min-width: 0;
        }

        .tutorName {
            font-size: 13px;
            font-weight: 900;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tutorSub {
            font-size: 11px;
            color: var(--muted);
            font-weight: 700;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tutorIconBtn {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: none;
            background: var(--icon-btn-bg, rgba(15, 23, 42, 0.06));
            display: grid;
            place-items: center;
            color: var(--text);
            cursor: pointer;
        }

        .clockCard {
            margin: 12px 14px 0;
            border-radius: 18px;
            background: var(--blue, #1f3b8a);
            padding: 14px 16px;
            color: #fff;
            text-align: center;
            box-shadow: 0 10px 24px rgba(31, 59, 138, 0.22);
        }

        .clockLabel {
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 900;
            opacity: 0.9;
        }

        .clockTime {
            font-size: 34px;
            font-weight: 1000;
            margin-top: 4px;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .clockTz {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 4px;
            font-weight: 800;
        }

        .sectionCaps {
            padding: 14px 14px 8px;
        }

        .sectionCapTitle {
            font-size: 11px;
            font-weight: 1000;
            letter-spacing: 1.2px;
            color: var(--text);
        }

        .quickGrid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 0 14px;
        }

        .quickCard {
            border-radius: 16px;
            padding: 14px 12px;
            background: var(--card-alt, #e8fbff);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
            text-decoration: none;
            color: var(--text);
            border: 1px solid var(--border);
            min-height: 104px;
        }

        .quickIcon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: var(--card-alt2, #e0f2fe);
            color: var(--blue2);
        }

        .quickIcon.warn {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warn);
        }

        .quickLabel {
            font-size: 12px;
            font-weight: 900;
        }

        .rowHeader {
            padding: 14px 14px 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .rowHeaderTitle {
            font-size: 11px;
            font-weight: 1000;
            letter-spacing: 1.2px;
            color: var(--text);
        }

        .rowHeaderLink {
            font-size: 11px;
            color: var(--muted);
            font-weight: 900;
            text-decoration: none;
        }

        .recentWrap {
            padding: 0 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .recentItem {
            background: var(--card-alt, #e8fbff);
            border-radius: 16px;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid var(--border);
        }

        .recentLeft {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .recentCheck {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(22, 163, 74, 0.16);
            color: var(--success);
            flex-shrink: 0;
        }

        .recentMeta {
            min-width: 0;
        }

        .recentDay {
            font-size: 12px;
            font-weight: 1000;
            color: var(--text);
        }

        .recentTime {
            font-size: 10px;
            color: var(--muted);
            font-weight: 900;
            margin-top: 2px;
        }

        .pillSmall {
            font-size: 10px;
            font-weight: 1000;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(22, 163, 74, 0.14);
            color: #15803d;
            border: 1px solid rgba(22, 163, 74, 0.20);
            flex-shrink: 0;
        }

        .pillSmall.pillOk {
            background: rgba(22, 163, 74, 0.14);
            color: #15803d;
            border-color: rgba(22, 163, 74, 0.20);
        }

        .pillSmall.pillPending {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
            border-color: rgba(245, 158, 11, 0.25);
        }

        .pillSmall.pillMuted {
            background: var(--pill-pending, rgba(100, 116, 139, 0.12));
            color: var(--muted);
            border-color: var(--pill-pending-border, rgba(100, 116, 139, 0.2));
        }

        .pillSmall.pillAlpha {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
            border-color: rgba(239, 68, 68, 0.22);
        }

        .agendaWrap {
            padding: 0 14px 110px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .agendaItem {
            background: var(--card);
            border-radius: 16px;
            padding: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .agendaBar {
            width: 4px;
            border-radius: 999px;
            background: var(--warn);
            margin-top: 2px;
            flex-shrink: 0;
            height: 36px;
        }

        .agendaTitle {
            font-size: 12px;
            font-weight: 1000;
            color: var(--text);
        }

        .agendaSub {
            font-size: 10px;
            color: var(--muted);
            font-weight: 900;
            margin-top: 3px;
        }

        /* ── Today Status Card ── */
        .todayCard {
            margin: 12px 14px 0;
            border-radius: 18px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .todayCard.belum {
            background: rgba(11, 94, 215, 0.07);
            border-color: rgba(11, 94, 215, 0.18);
        }

        .todayCard.proses {
            background: rgba(245, 158, 11, 0.09);
            border-color: rgba(245, 158, 11, 0.25);
        }

        .todayCard.selesai {
            background: rgba(22, 163, 74, 0.08);
            border-color: rgba(22, 163, 74, 0.22);
        }

        .todayIcon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .todayIcon.belum {
            background: rgba(11, 94, 215, 0.12);
            color: #1A73E8;
        }

        .todayIcon.proses {
            background: rgba(245, 158, 11, 0.18);
            color: #d97706;
        }

        .todayIcon.selesai {
            background: rgba(22, 163, 74, 0.15);
            color: #16a34a;
        }

        .todayBody {
            flex: 1;
            min-width: 0;
        }

        .todayStatusLabel {
            font-size: 11px;
            font-weight: 1000;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .todayStatusLabel.belum {
            color: #1A73E8;
        }

        .todayStatusLabel.proses {
            color: #d97706;
        }

        .todayStatusLabel.selesai {
            color: #16a34a;
        }

        .todayTimeRow {
            display: flex;
            gap: 16px;
            margin-top: 6px;
        }

        .todayTimeChip {
            display: flex;
            flex-direction: column;
        }

        .todayTimeVal {
            font-size: 15px;
            font-weight: 1000;
            color: var(--text);
        }

        .todayTimeLbl {
            font-size: 10px;
            font-weight: 800;
            color: var(--muted);
            margin-top: 1px;
        }

        .todayCountdown {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 800;
            color: #d97706;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .todayAbsenBtn {
            flex-shrink: 0;
            background: var(--blue, #1f3b8a);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 9px 14px;
            font-size: 11px;
            font-weight: 1000;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .todayAbsenBtn.btnSelesai {
            background: #dc2626;
        }
    </style>

    <div class="tutorTop">
        <div class="tutorTopRow">
            <div class="tutorLeft">
                <a href="{{ route('profil.index') }}" style="text-decoration: none;">
                    @if (auth()->user()->foto)
                        <img src="{{ str_starts_with(auth()->user()->foto, 'uploads/') ? asset(auth()->user()->foto) : asset('storage/' . auth()->user()->foto) }}" class="tutorAvatar" alt="Avatar"
                            style="object-fit:cover;" />
                    @else
                        <div class="tutorAvatar" aria-label="Avatar">{{ $initial }}</div>
                    @endif
                </a>
                <div class="tutorMeta">
                    <div class="tutorName">{{ $displayName }}</div>
                    <div class="tutorSub">{{ $subTitle }}</div>
                </div>
            </div>
            <button class="tutorIconBtn" type="button" aria-label="Tema" id="themeToggleBtn">
                <ion-icon name="moon-outline" style="font-size:20px;" id="themeToggleIcon"></ion-icon>
            </button>
        </div>
    </div>

    <div class="clockCard">
        <div class="clockLabel">WAKTU SAAT INI</div>
        <div class="clockTime" id="clockTime">--:--:--</div>
        <div class="clockTz">Waktu Indonesia Barat (WIB)</div>
    </div>

    {{-- ── STATUS PRESENSI HARI INI ── --}}
    @php
        $statusClass = match ($todayStatus) {
            'proses' => 'proses',
            'selesai' => 'selesai',
            default => 'belum',
        };
        $statusIcon = match ($todayStatus) {
            'proses' => 'time-outline',
            'selesai' => 'checkmark-circle-outline',
            default => 'radio-button-off-outline',
        };
        $statusText = match ($todayStatus) {
            'proses' => 'Sedang Berlangsung',
            'selesai' => 'Selesai Hari Ini',
            default => 'Belum Mulai',
        };
        $jamMasukToday = $todayPresensi?->jam_mulai ? substr((string) $todayPresensi->jam_mulai, 0, 5) : '—';
        $jamPulangToday = $todayPresensi?->jam_selesai ? substr((string) $todayPresensi->jam_selesai, 0, 5) : '—';
    @endphp

    <div class="todayCard {{ $statusClass }}">
        <div class="todayIcon {{ $statusClass }}">
            <ion-icon name="{{ $statusIcon }}"></ion-icon>
        </div>
        <div class="todayBody">
            <div class="todayStatusLabel {{ $statusClass }}">{{ $statusText }}</div>
            <div class="todayTimeRow">
                <div class="todayTimeChip">
                    <span class="todayTimeVal">{{ $jamMasukToday }}</span>
                    <span class="todayTimeLbl">Jam Mulai</span>
                </div>
                <div class="todayTimeChip">
                    <span class="todayTimeVal">{{ $jamPulangToday }}</span>
                    <span class="todayTimeLbl">Jam Selesai</span>
                </div>
            </div>
            @if ($todayStatus === 'proses')
                <div class="todayCountdown">
                    <ion-icon name="timer-outline"></ion-icon>
                    @if ($sisaDetikPulang > 0)
                        Selesai dalam: <strong id="dashCountdown">{{ gmdate('i:s', $sisaDetikPulang) }}</strong>
                    @else
                        <span style="color:#16a34a;">✔ Sudah bisa absen pulang sekarang</span>
                    @endif
                </div>
            @endif
        </div>
        @if ($todayStatus !== 'selesai')
            <a href="{{ route('tutor.presensi') }}"
                class="todayAbsenBtn {{ $todayStatus === 'proses' ? 'btnSelesai' : '' }}">
                <ion-icon name="{{ $todayStatus === 'proses' ? 'log-out-outline' : 'log-in-outline' }}"></ion-icon>
                {{ $todayStatus === 'proses' ? 'Selesai' : 'Mulai' }}
            </a>
        @endif
    </div>

    <div class="sectionCaps">
        <div class="sectionCapTitle">AKSI CEPAT</div>
    </div>

    <div class="quickGrid">
        <a href="https://wa.me/6285156452939" class="quickCard" aria-label="Izin">
            <div class="quickIcon warn">
                <ion-icon name="alert-circle-outline" style="font-size:26px;"></ion-icon>
            </div>
            <div class="quickLabel">Izin</div>
        </a>
        <a href="{{ route('tutor.lupa-lapor') }}" class="quickCard" aria-label="Lupa Lapor">
            <div class="quickIcon" style="background: rgba(245,158,11,0.15); color: var(--warn);">
                <ion-icon name="document-text-outline" style="font-size:26px;"></ion-icon>
            </div>
            <div class="quickLabel">Lupa Lapor</div>
        </a>
        <a href="{{ route('tutor.presensi') }}" class="quickCard" aria-label="Absen" style="grid-column: span 2;">
            <div class="quickIcon">
                <ion-icon name="{{ $todayStatus === 'proses' ? 'log-out-outline' : 'log-in-outline' }}"
                    style="font-size:26px;"></ion-icon>
            </div>
            <div class="quickLabel">
                @if ($todayStatus === 'proses')
                    Selesai
                @else
                    Mulai
                @endif
            </div>
        </a>
    </div>

    <div class="rowHeader">
        <div class="rowHeaderTitle">RIWAYAT TERBARU</div>
        @if (Route::has('tutor.riwayat'))
            <a href="{{ route('tutor.riwayat') }}" class="rowHeaderLink">Lihat Semua</a>
        @endif
    </div>

    <div class="recentWrap">
        @forelse($recentPresensi as $p)
            @php
                $siswaLabel = $p->siswa->nama_siswa ?? 'Siswa #' . $p->siswa_id;
                $hari = \Carbon\Carbon::parse($p->tgl_presensi)->locale('id')->translatedFormat('l, d F Y');
                $jamMulai = $p->jam_mulai ? substr((string) $p->jam_mulai, 0, 5) : '—';
                $jamSelesai = $p->jam_selesai ? substr((string) $p->jam_selesai, 0, 5) : '—';
                if ($p->foto_mulai && $p->foto_selesai) {
                    $timeLine = $jamMulai . ' – ' . $jamSelesai . ' • ' . $siswaLabel;
                } elseif ($p->foto_mulai) {
                    $timeLine = 'Masuk • ' . $jamMulai . ' • ' . $siswaLabel;
                } else {
                    $timeLine = $siswaLabel;
                }
                // Hadir hanya jika foto_selesai sudah terisi
                $st = strtoupper((string) ($p->status ?? 'pending'));
                $pillClass = 'pillSmall';
                if ($p->foto_mulai && $p->foto_selesai) {
                    // Sesi selesai lengkap
                    $pillClass .= ' pillOk';
                    $st = 'HADIR';
                } elseif ($p->foto_mulai && !$p->foto_selesai) {
                    // Sudah masuk tapi belum pulang → Proses
                    $pillClass .= ' pillPending';
                    $st = 'PROSES';
                } elseif ($st === 'ALPHA') {
                    $pillClass .= ' pillAlpha';
                } else {
                    $pillClass .= ' pillMuted';
                }
            @endphp
            <div class="recentItem">
                <div class="recentLeft">
                    <div class="recentCheck">
                        <ion-icon name="{{ $p->foto_mulai ? 'checkmark' : 'ellipse-outline' }}"
                            style="font-size:14px;"></ion-icon>
                    </div>
                    <div class="recentMeta">
                        <div class="recentDay">{{ $hari }}</div>
                        <div class="recentTime">{{ $timeLine }}</div>
                    </div>
                </div>
                <div class="{{ $pillClass }}">{{ $st }}</div>
            </div>
        @empty
            <div class="recentItem" style="justify-content:center;color:#64748b;font-weight:800;font-size:12px;">
                Belum ada absensi hari ini.
            </div>
        @endforelse
    </div>

    <script>
        (function() {
            // ── Live clock ──
            const el = document.getElementById('clockTime');
            if (el) {
                const fmt = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                const tick = () => el.textContent = fmt.format(new Date());
                tick();
                setInterval(tick, 1000);
            }

            // ── Countdown sisa waktu absen pulang (hanya jika status proses) ──
            @if ($todayStatus === 'proses' && $sisaDetikPulang > 0)
                (function() {
                    const cdEl = document.getElementById('dashCountdown');
                    if (!cdEl) return;
                    let sisa = {{ $sisaDetikPulang }};
                    const pad = n => String(n).padStart(2, '0');
                    const fmt = s => pad(Math.floor(s / 60)) + ':' + pad(s % 60);
                    const iv = setInterval(() => {
                        sisa--;
                        if (sisa <= 0) {
                            clearInterval(iv);
                            // Reload agar badge & pesan berubah real-time
                            window.location.reload();
                            return;
                        }
                        cdEl.textContent = fmt(sisa);
                    }, 1000);
                })();
            @endif
        })();
    </script>
@endsection
