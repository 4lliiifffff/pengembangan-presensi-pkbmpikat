<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PayrollBulkTarifTemplateExport implements FromCollection, WithEvents, WithHeadings, WithMapping
{
    public function collection()
    {
        return Siswa::with(['relKelas', 'tutor'])->orderBy('nama_siswa')->get();
    }

    public function map($siswa): array
    {
        return [
            $siswa->no_absen,
            $siswa->nama_siswa,
            $siswa->relKelas->nama_kelas ?? '-',
            $siswa->jenjang_paket_label,
            $siswa->is_abk_label,
            $siswa->skema_tarif_label,
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor Absen (NIS)',
            'Nama Siswa',
            'Kelas / Rombel',
            'Jenjang Paket',
            'Status ABK (ABK / Reguler)',
            'Skema Master Tarif SK Terhubung',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                $sheet->getStyle('A1:F1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E0E7FF');

                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
