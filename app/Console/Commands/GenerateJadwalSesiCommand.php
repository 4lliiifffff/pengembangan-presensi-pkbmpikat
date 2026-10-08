<?php

namespace App\Console\Commands;

use App\Services\JadwalRutinService;
use Illuminate\Console\Command;

class GenerateJadwalSesiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwal:generate-sesi
                            {--weeks=4 : Jumlah minggu ke depan yang akan digenerate}
                            {--murid_id= : Filter khusus murid tertentu}
                            {--siswa_id= : Filter legacy khusus murid tertentu}
                            {--tutor_id= : Filter khusus tutor tertentu}
                            {--skip-holidays : Lewati hari libur tanpa membuat sesi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate instance Jadwal Sesi dari Master Jadwal Rutin mingguan untuk murid dan tutor';

    /**
     * Execute the console command.
     */
    public function handle(JadwalRutinService $service): int
    {
        $weeks = (int) $this->option('weeks') ?: 4;
        $muridId = $this->option('murid_id') ? (int) $this->option('murid_id') : ($this->option('siswa_id') ? (int) $this->option('siswa_id') : null);
        $tutorId = $this->option('tutor_id') ? (int) $this->option('tutor_id') : null;
        $skipHolidays = (bool) $this->option('skip-holidays');

        $this->info("Menjalankan generator sesi KBM untuk {$weeks} minggu ke depan...");

        $result = $service->generateForNextWeeks($weeks, $muridId, $tutorId, $skipHolidays);

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Sesi Baru Dibuat', $result['created']],
                ['Sesi Sudah Ada (Dilewati)', $result['skipped']],
                ['Hari Libur Ditemukan', $result['holidays']],
                ['Total Diproses', $result['total_processed']],
            ]
        );

        $this->info('Proses generate jadwal sesi KBM selesai dengan sukses.');

        return Command::SUCCESS;
    }
}
