<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

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
            $siswa->tutor->nama_lengkap ?? '-',
            (float) ($siswa->tarif_per_jam ?? 50000),
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor Absen (NIS)',
            'Nama Siswa',
            'Kelas / Rombel',
            'Tutor Pembimbing',
            'Tarif Honor Per Jam (Rp)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);

                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 2) {
                    $sheet->getStyle('E2:E'.$highestRow)->getNumberFormat()->setFormatCode('#,##0');
                }

                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
