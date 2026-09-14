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
