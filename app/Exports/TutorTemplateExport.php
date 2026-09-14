<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class TutorTemplateExport implements FromArray, WithEvents, WithHeadings
{
    public function array(): array
    {
        return [
            [
                '1234567890123456',
                'Budi Santoso, S.Pd.',
                'budi.santoso@gmail.com',
                '081234567890',
                'tutor',
                'password123',
            ],
            [
                '3201234567890001',
                'Dewi Lestari, M.Pd.',
                'dewi.lestari@gmail.com',
                '085678901234',
                'tutor',
                '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIK (Nomor Induk Kependudukan) *Wajib',
            'Nama Lengkap Beserta Gelar *Wajib',
            'Email Aktif (Opsional)',
            'Nomor WhatsApp / HP *Wajib',
            'Peran / Role (tutor / admin / kepala_sekolah) *Wajib',
            'Password Akun (Kosongkan jika default = NIK)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:F1')->getFont()->setBold(true);

                // Format NIK sebagai teks agar angka 16 digit tidak terpotong ilmiah
                $sheet->getStyle('A2:A100')->getNumberFormat()->setFormatCode('@');

                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
