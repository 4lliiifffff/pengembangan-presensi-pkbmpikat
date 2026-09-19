<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class JadwalSesiTemplateExport implements FromArray, WithEvents, WithHeadings
{
    public function array(): array
    {
        $today = Carbon::today('Asia/Jakarta');
        $sampleDate1 = $today->copy()->addDay()->toDateString();
        $sampleDate2 = $today->copy()->addDays(3)->toDateString();
        $sampleDate3 = $today->copy()->addDays(8)->toDateString();
        $sampleDate4 = $today->copy()->addDays(15)->toDateString();
        $sampleDate5 = $today->copy()->addDays(22)->toDateString();

        return [
            [
                $sampleDate1,
                '001',
                '08:00',
                '10:00',
                'Tutorial Tatap Muka',
                'Matematika Dasar & Logika',
                '',
            ],
            [
                $sampleDate2,
                '002',
                '10:00',
                '12:00',
                'Tutorial Tatap Muka',
                'Bahasa Indonesia - Teks Eksposisi',
                '',
            ],
            [
                $sampleDate3,
                '001',
                '08:00',
                '10:00',
                'Tutorial Tatap Muka',
                'Matematika Lanjutan (Aljabar)',
                '',
            ],
            [
                $sampleDate4,
                '003',
                '13:00',
                '15:00',
                'Tutorial Tatap Muka',
                'Ilmu Pengetahuan Alam',
                '',
            ],
            [
                $sampleDate5,
                '001',
                '08:00',
                '10:00',
                'Tutorial Tatap Muka',
                'Persiapan Ujian Evaluasi KBM',
                '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal Rencana (YYYY-MM-DD) *Wajib',
            'Nomor Absen Siswa / NIS / Nama *Wajib',
            'Jam Mulai (HH:MM) *Wajib',
            'Jam Selesai (HH:MM) *Wajib',
            'Kategori Tutorial (Opsional)',
            'Catatan / Materi (Opsional)',
            'NIK Tutor (Opsional)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:G1')->getFont()->setBold(true);

                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
