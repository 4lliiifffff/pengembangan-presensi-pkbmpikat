<?php

namespace App\Console\Commands;

use App\Models\Presensi;
use App\Models\User;
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
    protected $signature = 'presensi:send-reminder {--tutor_id= : ID spesifik tutor yang ingin dikirimkan pengingat} {--message= : Custom pesan pengingat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat absen Web Push kepada Tutor';

    /**
     * Execute the console command.
     */
    public function handle(WebPushService $webPushService): int
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $tutorId = $this->option('tutor_id');
        $customMessage = $this->option('message');

        $query = User::where('role', 'tutor')->where('is_active', 1);
        if ($tutorId) {
            $query->where('id', $tutorId);
        }

        $tutors = $query->get();
        $totalSent = 0;

        foreach ($tutors as $tutor) {
            // Cek apakah tutor sudah absen hari ini
            $hasAbsenToday = Presensi::where('tutor_id', $tutor->id)
                ->where('tgl_presensi', $today)
                ->exists();

            if (! $hasAbsenToday || $tutorId) {
                $body = $customMessage ?: "Halo {$tutor->nama_lengkap}, jangan lupa melakukan Clock-In presensi mengajar hari ini ya!";

                $sent = $webPushService->sendToUser($tutor, [
                    'title' => '⏰ Pengingat Presensi Mengajar',
                    'body' => $body,
                    'url' => route('tutor.presensi'),
                ]);

                if ($sent > 0) {
                    $this->info("Pengingat berhasil dikirim ke {$tutor->nama_lengkap} ({$sent} perangkat)");
                    $totalSent += $sent;
                }
            }
        }

        $this->info("Selesai. Total notifikasi terkirim: {$totalSent}");

        return self::SUCCESS;
    }
}
