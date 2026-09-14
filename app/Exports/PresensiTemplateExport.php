<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class PresensiTemplateExport implements FromArray, WithEvents, WithHeadings
{
    public function array(): array
    {
        $today = Carbon::today()->toDateString();

        return [
            [
                '1234567890123456',
                'SISWA-001',
                $today,
                '08:00',
                '10:00',
                'tatap_muka',
                'hadir',
                'PKBM Pikat Ruang Belajar',
                'PKBM Pikat Ruang Belajar',
                'Kegiatan belajar tatap muka berjalan lancar',
            ],
            [
                '1234567890123456',
                'SISWA-002',
                $today,
                '10:30',
                '12:30',
                'home_visit',
                'hadir',
                'Rumah Siswa Sleman',
                'Rumah Siswa Sleman',
                'Home visit materi matematika',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIK Tutor *Wajib',
            'Nomor Absen Siswa (NIS) *Wajib',
            'Tanggal Presensi (YYYY-MM-DD) *Wajib',
            'Jam Mulai (HH:MM) *Wajib',
            'Jam Selesai (HH:MM) *Wajib',
            'Moda Pembelajaran (tatap_muka / home_visit / online) *Wajib',
            'Status (hadir / izin / sakit / alpha) *Wajib',
            'Lokasi Mulai (Opsional)',
            'Lokasi Selesai (Opsional)',
            'Keterangan (Opsional)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:J1')->getFont()->setBold(true);

                $sheet->getStyle('A2:A100')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('B2:B100')->getNumberFormat()->setFormatCode('@');

                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
