<?php

namespace App\Console\Commands;

use App\Models\JadwalSesi;
use App\Models\Magang;
use App\Models\PengajuanIzinSakit;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\PresensiMandiriSiswa;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAbsenReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presensi:send-reminder 
                            {--type=morning : Tipe pengingat: morning, clockout, pending-approvals} 
                            {--role=all : Target role: all, tutor, siswa, magang, kepsek} 
                            {--user_id= : ID user spesifik} 
                            {--tutor_id= : ID spesifik tutor (kompatibilitas)} 
                            {--message= : Custom pesan pengingat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat presensi dan jadwal Web Push multi-role (Siswa, Tutor, Magang, Kepsek)';

    /**
     * Execute the console command.
     */
    public function handle(WebPushService $webPushService): int
    {
        $tz = 'Asia/Jakarta';
        $today = Carbon::today($tz)->toDateString();
        $now = Carbon::now($tz);

        $type = (string) $this->option('type');
        $targetRole = (string) $this->option('role');
        $userId = $this->option('user_id');
        $tutorId = $this->option('tutor_id');
        $customMessage = $this->option('message');

        if ($tutorId) {
            $targetRole = 'tutor';
            $tutorObj = Tutor::find($tutorId);
            if ($tutorObj?->user_id) {
                $userId = $tutorObj->user_id;
            }
        }

        $totalSent = 0;

        $this->info("Memulai dispatch reminder [Type: {$type}, Role: {$targetRole}] pada {$now->toDateTimeString()} WIB");

        if ($type === 'morning') {
            $totalSent += $this->handleMorningReminders($webPushService, $today, $targetRole, $userId, $customMessage);
        } elseif ($type === 'clockout') {
            $totalSent += $this->handleClockoutReminders($webPushService, $today, $now, $targetRole, $userId, $customMessage);
        } elseif ($type === 'pending-approvals') {
            $totalSent += $this->handlePendingApprovals($webPushService, $customMessage);
        } else {
            $this->error("Tipe pengingat '{$type}' tidak valid. Gunakan: morning, clockout, atau pending-approvals.");

            return self::FAILURE;
        }

        $this->info("Selesai. Total notifikasi berhasil dikirim: {$totalSent}");

        return self::SUCCESS;
    }

    /**
     * Kirim pengingat pagi hari (Jadwal Mengajar, Sesi Belajar Murid, Shift Magang).
     */
    protected function handleMorningReminders(WebPushService $webPushService, string $today, string $targetRole, ?string $userId, ?string $customMessage): int
    {
        $sentCount = 0;

        // 1. Pengingat Sesi Mengajar untuk Tutor
        if (in_array($targetRole, ['all', 'tutor'])) {
            $tutors = Tutor::with('user')
                ->whereHas('user', fn ($q) => $q->where('is_active', 1)->when($userId, fn ($u) => $u->where('id', $userId)))
                ->get();

            foreach ($tutors as $tutor) {
                if (! $tutor->user) {
                    continue;
                }

                // Cek jadwal sesi hari ini
                $sesiHariIni = JadwalSesi::with(['siswa', 'kategoriTutorial'])
                    ->where('tutor_id', $tutor->id)
                    ->whereDate('tanggal_rencana', $today)
                    ->where('status', 'terjadwal')
                    ->orderBy('jam_masuk_rencana')
                    ->get();

                $hasAbsenToday = Presensi::where('tutor_id', $tutor->id)
                    ->whereDate('tgl_presensi', $today)
                    ->exists();

                if (! $hasAbsenToday) {
                    if ($sesiHariIni->isNotEmpty()) {
                        $firstSesi = $sesiHariIni->first();
                        $jamMulai = substr((string) $firstSesi->jam_masuk_rencana, 0, 5);
                        $count = $sesiHariIni->count();
                        $body = $customMessage ?: "Halo {$tutor->nama_lengkap}, hari ini Anda memiliki {$count} sesi mengajar. Sesi pertama mulai pukul {$jamMulai} WIB.";
                    } else {
                        $body = $customMessage ?: "Halo {$tutor->nama_lengkap}, jangan lupa melakukan presensi Clock-In jika ada jadwal mengajar hari ini.";
                    }

                    $sent = $webPushService->sendToUser($tutor->user, [
                        'title' => '⏰ Pengingat Mengajar Hari Ini',
                        'body' => $body,
                        'url' => route('tutor.presensi'),
                    ]);

                    if ($sent > 0) {
                        $this->line(" → Tutor {$tutor->nama_lengkap}: terkirim {$sent} perangkat");
                        $sentCount += $sent;
                    }
                }
            }
        }

        // 2. Pengingat Sesi Belajar untuk Siswa
        if (in_array($targetRole, ['all', 'siswa'])) {
            $siswas = Siswa::with('user')
                ->where('status_siswa', 'aktif')
                ->whereHas('user', fn ($q) => $q->where('is_active', 1)->when($userId, fn ($u) => $u->where('id', $userId)))
                ->get();

            foreach ($siswas as $siswa) {
                if (! $siswa->user) {
                    continue;
                }

                // Cek apakah siswa punya sesi hari ini
                $sesiHariIni = JadwalSesi::with(['tutor', 'kategoriTutorial'])
                    ->where('siswa_id', $siswa->id)
                    ->whereDate('tanggal_rencana', $today)
                    ->where('status', 'terjadwal')
                    ->orderBy('jam_masuk_rencana')
                    ->first();

                $hasAbsenToday = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
                    ->whereDate('tgl_presensi', $today)
                    ->exists();

                if ($sesiHariIni && ! $hasAbsenToday) {
                    $jamMulai = substr((string) $sesiHariIni->jam_masuk_rencana, 0, 5);
                    $tutorName = $sesiHariIni->tutor?->nama_lengkap ?? 'Tutor PKBM';
                    $mapel = $sesiHariIni->kategoriTutorial?->nama_kategori ?? 'Tutorial KBM';

                    $body = $customMessage ?: "Halo {$siswa->nama_siswa}, Anda memiliki jadwal belajar {$mapel} bersama Tutor {$tutorName} pukul {$jamMulai} WIB hari ini. Semangat belajar!";

                    $sent = $webPushService->sendToUser($siswa->user, [
                        'title' => '📚 Jadwal Belajar Hari Ini',
                        'body' => $body,
                        'url' => route('siswa.jadwal'),
                    ]);

                    if ($sent > 0) {
                        $this->line(" → Siswa {$siswa->nama_siswa}: terkirim {$sent} perangkat");
                        $sentCount += $sent;
                    }
                }
            }
        }

        // 3. Pengingat Shift Masuk untuk Magang
        if (in_array($targetRole, ['all', 'magang'])) {
            $magangs = Magang::with('user')
                ->where('status', 'aktif')
                ->whereHas('user', fn ($q) => $q->where('is_active', 1)->when($userId, fn ($u) => $u->where('id', $userId)))
                ->get();

            foreach ($magangs as $m) {
                if (! $m->user) {
                    continue;
                }

                $hasAbsenToday = PresensiKaryawan::where('user_id', $m->user_id)
                    ->whereDate('tgl_presensi', $today)
                    ->exists();

                if (! $hasAbsenToday) {
                    $body = $customMessage ?: "Halo {$m->nama_lengkap}, jangan lupa melakukan presensi masuk magang PKBM Pikat hari ini.";

                    $sent = $webPushService->sendToUser($m->user, [
                        'title' => '⏰ Pengingat Presensi Magang',
                        'body' => $body,
                        'url' => route('magang.presensi'),
                    ]);

                    if ($sent > 0) {
                        $this->line(" → Magang {$m->nama_lengkap}: terkirim {$sent} perangkat");
                        $sentCount += $sent;
                    }
                }
            }
        }

        return $sentCount;
    }

    /**
     * Kirim pengingat Clock-Out / Presensi Pulang bagi yang belum checkout.
     */
    protected function handleClockoutReminders(WebPushService $webPushService, string $today, Carbon $now, string $targetRole, ?string $userId, ?string $customMessage): int
    {
        $sentCount = 0;

        // 1. Pengingat Pulang untuk Tutor yang sedang mengajar (sudah >= 1 jam)
        if (in_array($targetRole, ['all', 'tutor'])) {
            $activeTutorPresensi = Presensi::with('tutor.user')
                ->whereDate('tgl_presensi', $today)
                ->whereNotNull('foto_mulai')
                ->whereNull('foto_selesai')
                ->get();

            foreach ($activeTutorPresensi as $presensi) {
                $user = $presensi->tutor?->user;
                if (! $user || ($userId && $user->id != $userId)) {
                    continue;
                }

                try {
                    $jamMulaiDt = Carbon::parse($today.' '.$presensi->jam_mulai, 'Asia/Jakarta');
                    $diffMinutes = $jamMulaiDt->diffInMinutes($now, false);

                    // Jika sesi sudah berlangsung minimal 60 menit
                    if ($diffMinutes >= 60) {
                        $body = $customMessage ?: "Halo {$presensi->tutor->nama_lengkap}, sesi mengajar Anda telah berjalan. Jangan lupa ambil foto presensi pulang untuk verifikasi honor ya!";

                        $sent = $webPushService->sendToUser($user, [
                            'title' => '🏁 Pengingat Presensi Pulang',
                            'body' => $body,
                            'url' => route('tutor.presensi'),
                        ]);

                        if ($sent > 0) {
                            $this->line(" → Tutor Checkout {$presensi->tutor->nama_lengkap}: terkirim {$sent} perangkat");
                            $sentCount += $sent;
                        }
                    }
                } catch (\Throwable) {
                }
            }
        }

        // 2. Pengingat Pulang untuk Magang (jika jam >= 15:30)
        if (in_array($targetRole, ['all', 'magang']) && $now->hour >= 15) {
            $activeMagangPresensi = PresensiKaryawan::with('user')
                ->whereDate('tgl_presensi', $today)
                ->whereNotNull('foto_mulai')
                ->whereNull('foto_selesai')
                ->get();

            foreach ($activeMagangPresensi as $presensi) {
                $user = $presensi->user;
                if (! $user || ($userId && $user->id != $userId)) {
                    continue;
                }

                $nama = $user->nama_lengkap ?? ($user->name ?? 'Peserta Magang');
                $body = $customMessage ?: "Halo {$nama}, jam operasional magang hari ini telah selesai. Silakan lakukan presensi pulang.";

                $sent = $webPushService->sendToUser($user, [
                    'title' => '🏁 Pengingat Checkout Magang',
                    'body' => $body,
                    'url' => route('magang.presensi'),
                ]);

                if ($sent > 0) {
                    $this->line(" → Magang Checkout {$nama}: terkirim {$sent} perangkat");
                    $sentCount += $sent;
                }
            }
        }

        return $sentCount;
    }

    /**
     * Kirim ringkasan permohonan pending (Izin & Lupa Lapor) kepada Kepala Sekolah.
     */
    protected function handlePendingApprovals(WebPushService $webPushService, ?string $customMessage): int
    {
        $pendingIzin = PengajuanIzinSakit::where('status', 'pending')->count();
        $pendingLupaLapor = PengajuanLupaLapor::where('status', 'pending')->count();
        $totalPending = $pendingIzin + $pendingLupaLapor;

        if ($totalPending <= 0) {
            $this->line('Tidak ada permohonan izin atau lupa lapor yang pending.');

            return 0;
        }

        $body = $customMessage ?: "Terdapat {$totalPending} permohonan yang menunggu persetujuan Anda ({$pendingIzin} izin, {$pendingLupaLapor} lupa lapor). Klik untuk meninjau.";

        $sent = $webPushService->sendToKepsek([
            'title' => '📋 Ringkasan Permohonan Menunggu Persetujuan',
            'body' => $body,
            'url' => route('kepsek.dashboard'),
        ]);

        if ($sent > 0) {
            $this->line(" → Ringkasan Approval ke Kepsek: terkirim {$sent} perangkat");
        }

        return $sent;
    }
}
