@extends('layouts.presensi')

@section('title', 'Dashboard Tutor')

@php
    $user = auth()->user();
    $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Tutor'));
    $subTitle = $user?->role ? ucfirst(str_replace('_', ' ', (string) $user->role)) : 'Tutor';
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle'  => $subTitle,
    ])
@endpush

@section('content')
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PORTAL AKADEMIK TUTOR</div>
                <h1 class="laporanHeaderTitle">Dashboard Tutor</h1>
                <div class="laporanHeaderSub">Selamat datang kembali, {{ $displayName }}!</div>
                <p class="laporanHeaderDesc">Kelola agenda mengajar, lakukan presensi KBM harian, dan pantau rekapitulasi honorarium.</p>
            </div>
            <div class="laporanHeaderActions">
                <div class="badgeDate">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboardGrid">
        {{-- ── KOLOM KIRI: WAKTU & STATUS PRESENSI HARI INI ── --}}
        <div class="dashboardCol">
            {{-- ── JAM REALTIME ── --}}
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
                    default => 'Belum Mulai Sesi',
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
                                @php
                                    $mSisa = (int) floor($sisaDetikPulang / 60);
                                    $sSisa = (int) ($sisaDetikPulang % 60);
                                    $labelCountdown = sprintf('%02d:%02d', $mSisa, $sSisa);
                                @endphp
                                Selesai dalam: <strong id="dashCountdown">{{ $labelCountdown }}</strong>
                            @else
                                <span class="text-success font-bold">Sudah bisa presensi selesai</span>
                            @endif
                        </div>
                    @endif
                </div>
                @if ($todayStatus !== 'selesai')
                    <a href="{{ route('tutor.presensi') }}" class="todayAbsenBtn {{ $todayStatus === 'proses' ? 'btnSelesai' : '' }}">
                        <ion-icon name="{{ $todayStatus === 'proses' ? 'log-out-outline' : 'log-in-outline' }}"></ion-icon>
                        <span >{{ $todayStatus === 'proses' ? 'Selesai' : 'Mulai' }}</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- ── KOLOM KANAN: AKSI CEPAT & RIWAYAT TERBARU ── --}}
        <div class="dashboardCol">
            {{-- ── AKSI CEPAT ── --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2 >
                        <ion-icon name="flash-outline" class="text-primary"></ion-icon>
                        <span >Aksi Cepat</span>
                    </h2>
                </div>

                <div class="quickGrid">
                    <a href="https://wa.me/6285156452939" target="_blank" rel="noopener noreferrer" class="quickCard" aria-label="Pengajuan Izin">
                        <div class="quickIcon warn">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                        </div>
                        <div class="quickLabel">Izin via WA</div>
                    </a>
                    <a href="{{ route('tutor.lupa-lapor') }}" class="quickCard" aria-label="Lupa Lapor">
                        <div  class="quickIcon text-warning bg-warning-light">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </div>
                        <div class="quickLabel">Lupa Lapor</div>
                    </a>
                    <a href="{{ route('tutor.presensi') }}" aria-label="Presensi Bimbingan" class="quickCard grid-span-2">
                        <div class="quickIcon success">
                            <ion-icon name="{{ $todayStatus === 'proses' ? 'log-out-outline' : 'log-in-outline' }}"></ion-icon>
                        </div>
                        <div class="quickLabel">
                            @if ($todayStatus === 'proses')
                                Presensi Selesai Bimbingan
                            @else
                                Presensi Mulai Bimbingan
                            @endif
                        </div>
                    </a>
                </div>
            </div>

            {{-- ── RIWAYAT TERBARU ── --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2 >
                        <ion-icon name="time-outline" class="text-primary"></ion-icon>
                        <span >Riwayat Terbaru</span>
                    </h2>
                    @if (Route::has('tutor.riwayat'))
                        <a href="{{ route('tutor.riwayat') }}" class="mutedLink">Lihat Semua &rsaquo;</a>
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
                            $st = strtoupper((string) ($p->status ?? 'pending'));
                            $pillClass = 'pillSmall';
                            if ($p->foto_mulai && $p->foto_selesai) {
                                $pillClass .= ' pillOk';
                                $st = 'HADIR';
                            } elseif ($p->foto_mulai && !$p->foto_selesai) {
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
                                <div class="recentCheck {{ ($p->foto_mulai && !$p->foto_selesai) ? 'pending' : '' }}">
                                    <ion-icon name="{{ $p->foto_mulai ? 'checkmark' : 'ellipse-outline' }}"
                                        class="text-md"></ion-icon>
                                </div>
                                <div class="recentMeta">
                                    <div class="recentDay">{{ $hari }}</div>
                                    <div class="recentTime">{{ $timeLine }}</div>
                                </div>
                            </div>
                            <div class="{{ $pillClass }}">{{ $st }}</div>
                        </div>
                    @empty
                        <div class="emptyState m-0 p-4">
                            Belum ada aktivitas presensi hari ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script >
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
                    let sisa = Math.floor({{ (int) $sisaDetikPulang }});
                    const pad = n => String(n).padStart(2, '0');
                    const fmt = s => pad(Math.floor(s / 60)) + ':' + pad(Math.floor(s % 60));
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
