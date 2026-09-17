<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Penjadwalan Notifikasi & Pengingat Otomatis PKBM PIKAT ──

// 1. Pengingat Pagi Hari (06:30 WIB): Jadwal Mengajar Tutor, Sesi Belajar Siswa, & Shift Magang
Schedule::command('presensi:send-reminder --type=morning --role=all')
    ->dailyAt('06:30')
    ->timezone('Asia/Jakarta');

// 2. Pengingat Clock-Out / Presensi Pulang (Setiap 30 menit antara jam 09:00 - 18:00 WIB)
Schedule::command('presensi:send-reminder --type=clockout')
    ->everyThirtyMinutes()
    ->between('09:00', '18:00')
    ->timezone('Asia/Jakarta');

// 3. Ringkasan Permohonan Izin & Lupa Lapor Menunggu Persetujuan Kepala Sekolah (16:00 WIB)
Schedule::command('presensi:send-reminder --type=pending-approvals')
    ->dailyAt('16:00')
    ->timezone('Asia/Jakarta');
