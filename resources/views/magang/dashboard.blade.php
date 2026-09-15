@extends('layouts.presensi')

@section('title', 'Dashboard Magang / PKL')

@php
    $user = auth()->user();
    $magang = $user->magang;
    $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Mahasiswa Magang'));
    $subTitle = 'Mahasiswa/Siswa Magang';
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle'  => $subTitle,
    ])
@endpush

@section('content')
    <div class="dashboardGrid">
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
                    'proses' => 'Sedang Berjalan (Sudah Masuk)',
                    'selesai' => 'Selesai Hari Ini (Sudah Pulang)',
                    default => 'Belum Presensi Hari Ini',
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
                            <span class="todayTimeLbl">Jam Masuk</span>
                        </div>
                        <div class="todayTimeChip">
                            <span class="todayTimeVal">{{ $jamPulangToday }}</span>
                            <span class="todayTimeLbl">Jam Pulang</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── CARD SESI AKTIF ATAU TOMBOL PRESENSI ── --}}
            @if ($activeSesi)
                @php
                    $jamMulaiDt = \Carbon\Carbon::parse($today . ' ' . $activeSesi->jam_mulai, 'Asia/Jakarta');
                    $nowDt = \Carbon\Carbon::now('Asia/Jakarta');
                    $diffDetik = $jamMulaiDt->diffInSeconds($nowDt, false);
                    $bisaPulang = $diffDetik >= 3600;
                    $sisaDetik = max(0, 3600 - $diffDetik);
                @endphp
                <div class="activeCard">
                    <div class="activePulse"></div>
                    <div class="activeBody">
                        <div class="activeTitle">Sesi Magang Sedang Berlangsung</div>
                        <div class="activeSub">
                            Masuk pukul <strong>{{ substr((string) $activeSesi->jam_mulai, 0, 5) }} WIB</strong>
                        </div>
                        <div class="activeLoc">
                            <ion-icon name="location-outline"></ion-icon>
                            <span>Lokasi Masuk Terverifikasi (PKBM Pikat)</span>
                        </div>
                    </div>
                    <a href="{{ route('magang.presensi.foto') }}" class="activeActionBtn">
                        <ion-icon name="camera-outline"></ion-icon>
                        <span>{{ $bisaPulang ? 'Absen Pulang Sekarang' : 'Absen Pulang' }}</span>
                    </a>
                </div>
            @elseif ($todayStatus !== 'selesai')
                <div class="card" style="padding: 18px; border-radius: 16px; margin-bottom: 20px; background: linear-gradient(135deg, rgba(11, 94, 215, 0.08), rgba(11, 94, 215, 0.02)); border: 1px dashed rgba(11, 94, 215, 0.3); text-align: center;">
                    <div style="font-size: 15px; font-weight: 600; color: var(--text-primary, #0f172a); margin-bottom: 6px;">
                        Siap Memulai Aktivitas Magang?
                    </div>
                    <p style="font-size: 13px; color: var(--text-secondary, #64748b); margin-bottom: 14px;">
                        Ambil foto selfie dan pastikan Anda berada di area PKBM Pikat.
                    </p>
                    <a href="{{ route('magang.presensi.foto') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; font-weight: 600; border-radius: 12px; text-decoration: none; background: #0B5ED7; color: white;">
                        <ion-icon name="camera-outline" style="font-size: 20px;"></ion-icon>
                        <span>Absen Masuk Sekarang</span>
                    </a>
                </div>
            @endif

            {{-- ── INFORMASI MAGANG / INSTANSI ── --}}
            @if ($magang)
                <div class="card" style="padding: 16px; border-radius: 16px; margin-bottom: 20px; background: var(--bg-card, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(11, 94, 215, 0.1); color: #0B5ED7; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            <ion-icon name="school-outline"></ion-icon>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">
                                {{ $magang->asal_instansi }}
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary, #64748b);">
                                {{ $magang->jurusan ?: 'Peserta Magang' }} • {{ $magang->nim_nisn ?: $user->nik }}
                            </div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; border-top: 1px solid var(--border-color, #f1f5f9); pt-2; padding-top: 10px;">
                        <div>
                            <span style="color: var(--text-secondary, #64748b);">Mulai Magang:</span><br>
                            <strong>{{ $magang->tgl_mulai ? \Carbon\Carbon::parse($magang->tgl_mulai)->translatedFormat('d M Y') : '—' }}</strong>
                        </div>
                        <div>
                            <span style="color: var(--text-secondary, #64748b);">Selesai Magang:</span><br>
                            <strong>{{ $magang->tgl_selesai ? \Carbon\Carbon::parse($magang->tgl_selesai)->translatedFormat('d M Y') : '—' }}</strong>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── STATISTIK KEHADIRAN BULAN INI ── --}}
            <div class="section-title" style="font-size: 14px; font-weight: 700; margin-bottom: 10px; color: var(--text-primary, #0f172a);">
                Statistik Kehadiran ({{ \Carbon\Carbon::parse($today)->translatedFormat('F Y') }})
            </div>
            <div class="statsGrid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 24px;">
                <div class="statCard" style="background: var(--bg-card, #fff); padding: 14px; border-radius: 14px; text-align: center; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="font-size: 20px; color: #10b981; margin-bottom: 4px;"><ion-icon name="checkmark-done-circle-outline"></ion-icon></div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $hadirBulanIni }}</div>
                    <div style="font-size: 11px; color: var(--text-secondary, #64748b);">Hari Hadir</div>
                </div>
                <div class="statCard" style="background: var(--bg-card, #fff); padding: 14px; border-radius: 14px; text-align: center; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="font-size: 20px; color: #0b5ed7; margin-bottom: 4px;"><ion-icon name="calendar-outline"></ion-icon></div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $totalHariKerja }}</div>
                    <div style="font-size: 11px; color: var(--text-secondary, #64748b);">Hari Kerja</div>
                </div>
                <div class="statCard" style="background: var(--bg-card, #fff); padding: 14px; border-radius: 14px; text-align: center; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="font-size: 20px; color: #f59e0b; margin-bottom: 4px;"><ion-icon name="pie-chart-outline"></ion-icon></div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $persentaseKehadiran }}%</div>
                    <div style="font-size: 11px; color: var(--text-secondary, #64748b);">Kehadiran</div>
                </div>
            </div>

            {{-- ── RIWAYAT TERAKHIR ── --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">Riwayat Presensi Terbaru</div>
                <a href="{{ route('magang.riwayat') }}" style="font-size: 12px; font-weight: 600; color: #0B5ED7; text-decoration: none;">Lihat Semua</a>
            </div>

            @forelse($recentPresensi as $p)
                @php
                    $tgl = \Carbon\Carbon::parse($p->tgl_presensi);
                    $hari = $tgl->translatedFormat('l, d M Y');
                    $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                    $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                @endphp
                <div class="card" style="padding: 14px; border-radius: 14px; margin-bottom: 10px; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: {{ $p->jam_selesai ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; color: {{ $p->jam_selesai ? '#10b981' : '#f59e0b' }}; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            <ion-icon name="{{ $p->jam_selesai ? 'checkmark-circle-outline' : 'time-outline' }}"></ion-icon>
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-primary, #0f172a);">{{ $hari }}</div>
                            <div style="font-size: 12px; color: var(--text-secondary, #64748b);">
                                Masuk: <strong>{{ $masuk }}</strong> • Pulang: <strong>{{ $pulang }}</strong>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge" style="padding: 4px 8px; border-radius: 8px; font-size: 11px; font-weight: 600; background: {{ $p->jam_selesai ? '#d1fae5' : '#fef3c7' }}; color: {{ $p->jam_selesai ? '#065f46' : '#92400e' }};">
                            {{ $p->jam_selesai ? 'Selesai' : 'Proses' }}
                        </span>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 30px 20px; color: var(--text-secondary, #64748b); background: var(--bg-card, #fff); border-radius: 14px; border: 1px solid var(--border-color, #e2e8f0);">
                    <ion-icon name="calendar-clear-outline" style="font-size: 32px; opacity: 0.5; margin-bottom: 6px;"></ion-icon>
                    <div style="font-size: 13px;">Belum ada riwayat presensi bulan ini.</div>
                </div>
            @endforelse

        </div>
    </div>

    @push('head')
        <script>
            function updateLiveClock() {
                const clockEl = document.getElementById('clockTime');
                if (!clockEl) return;
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            }
            setInterval(updateLiveClock, 1000);
            updateLiveClock();
        </script>
    @endpush
@endsection
