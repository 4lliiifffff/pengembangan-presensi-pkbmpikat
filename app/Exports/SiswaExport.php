<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SiswaExport implements FromCollection, WithEvents, WithHeadings, WithMapping
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
            $siswa->nama_wali ?? '-',
            $siswa->no_hp ?? '-',
            $siswa->relKelas->nama_kelas ?? '-',
            $siswa->jenjang_paket_label,
            $siswa->is_abk_label,
            $siswa->tutor->nama_lengkap ?? '-',
            $siswa->tutor->nik ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor Absen (NIS)',
            'Nama Siswa',
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
