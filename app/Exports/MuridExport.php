<?php

namespace App\Exports;

use App\Models\Murid;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MuridExport implements FromCollection, WithEvents, WithHeadings, WithMapping
{
    public function collection()
    {
        return Murid::with(['relKelas', 'tutor'])->orderBy('nama_murid')->get();
    }

    public function map($murid): array
    {
        return [
            $murid->no_absen,
            $murid->nama_murid,
            $murid->nama_wali ?? '-',
            $murid->no_hp ?? '-',
            $murid->relKelas->nama_kelas ?? '-',
            $murid->jenjang_paket_label,
            $murid->is_abk_label,
            $murid->tutor->nama_lengkap ?? '-',
            $murid->tutor->nik ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor Absen (NIS)',
            'Nama Murid',
            'Nama Wali Murid',
            'No WhatsApp Wali',
            'Kelas / Rombel',
            'Jenjang Paket',
            'Status ABK',
            'Nama Tutor Pembimbing',
            'NIK Tutor Pembimbing',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:I1')->getFont()->setBold(true);
                $sheet->getStyle('A1:I1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E0E7FF');

                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
