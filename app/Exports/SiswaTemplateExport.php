<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class SiswaTemplateExport implements FromArray, WithEvents, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'SISWA-001',
                'Ahmad Fauzi',
                'Bambang Subagyo',
                '081234567891',
                'Paket A',
                '1234567890123456',
                50000,
            ],
            [
                'SISWA-002',
                'Siti Nurhaliza',
                'Hartono',
                '081298765432',
                'Paket B',
                '',
                60000,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor Absen (NIS) *Wajib',
            'Nama Siswa *Wajib',
            'Nama Wali Murid *Wajib',
            'No WhatsApp Wali *Wajib',
            'Nama Kelas / Rombel *Wajib',
            'NIK Tutor Pembimbing (Opsional)',
            'Tarif Honor Per Jam (Rp) *Wajib',
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
