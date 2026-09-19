<?php

namespace App\Exports;

use App\Models\JadwalSesi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class JadwalSesiExport implements FromCollection, WithEvents, WithHeadings, WithMapping
{
    public function __construct(
        public int $tutorId,
        public ?int $bulan = null,
        public ?int $tahun = null
    ) {}

    public function collection()
    {
        $query = JadwalSesi::with(['siswa', 'kategoriTutorial'])
            ->where('tutor_id', $this->tutorId);

        if ($this->bulan && $this->tahun) {
            $query->whereMonth('tanggal_rencana', $this->bulan)
                ->whereYear('tanggal_rencana', $this->tahun);
        }

        return $query->orderBy('tanggal_rencana')
            ->orderBy('jam_masuk_rencana')
            ->get();
    }

    /**
     * @param  JadwalSesi  $sesi
     */
    public function map($sesi): array
    {
        $statusLabel = match ($sesi->status) {
            'selesai' => 'Selesai Presensi',
            'dibatalkan' => 'Dibatalkan / Reschedule',
            default => 'Terjadwal',
        };

        return [
            $sesi->tanggal_rencana ? $sesi->tanggal_rencana->format('Y-m-d') : '',
            $sesi->siswa?->no_absen ?? '',
            $sesi->siswa?->nama_siswa ?? '',
            substr((string) $sesi->jam_masuk_rencana, 0, 5),
            substr((string) $sesi->jam_pulang_rencana, 0, 5),
            (string) $sesi->durasi_jam,
            $sesi->kategoriTutorial?->nama_kategori ?? '-',
            $sesi->jenis_label,
            $statusLabel,
            $sesi->catatan ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal Sesi (YYYY-MM-DD)',
            'Nomor Absen Siswa',
            'Nama Siswa',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi (Jam)',
            'Kategori Tutorial',
            'Jenis Sesi',
            'Status Sesi',
            'Catatan / Materi',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:J1')->getFont()->setBold(true);

                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
