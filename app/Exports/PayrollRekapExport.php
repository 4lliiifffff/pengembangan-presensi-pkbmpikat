<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class PayrollRekapExport implements FromCollection, WithCustomStartCell, WithEvents, WithHeadings, WithMapping
{
    public function __construct(protected array $summary) {}

    public function collection()
    {
        return collect($this->summary['payrolls'] ?? []);
    }

    public function map($p): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $p['tutor']->nama_lengkap ?? '-',
            $p['tutor']->nik ?? '-',
            $p['total_sesi_hadir'] ?? 0,
            $p['total_izin_sakit'] ?? 0,
            $p['total_jam'] ?? 0,
            $p['total_honor'] ?? 0,
            'Terverifikasi',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Tutor',
            'NIK Tutor',
            'Total Sesi Mengajar',
            'Total Izin / Sakit (Hari)',
            'Total Jam Mengajar',
            'Total Honorarium (Rp)',
            'Status',
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $periode = $this->summary['periode_label'] ?? '-';
                $totalAnggaran = $this->summary['formatted_total_anggaran'] ?? 'Rp 0';
                $totalJam = $this->summary['total_jam_global'] ?? 0;
                $totalSesi = $this->summary['total_sesi_global'] ?? 0;
                $totalTutor = $this->summary['total_tutor'] ?? 0;

                $sheet->setCellValue('A1', 'REKAPITULASI ANGGARAN PENGGAJIAN & HONORARIUM TUTOR PKBM PIKAT');
                $sheet->setCellValue('A2', 'Periode: '.$periode);
                $sheet->setCellValue('A3', 'Total Anggaran: '.$totalAnggaran);
                $sheet->setCellValue('D3', 'Total Jam: '.$totalJam.' Jam');
                $sheet->setCellValue('F3', 'Total Sesi: '.$totalSesi.' Sesi');
                $sheet->setCellValue('H3', 'Total Tutor: '.$totalTutor.' Orang');

                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3:H3')->getFont()->setBold(true);
                $sheet->getStyle('A6:H6')->getFont()->setBold(true);

                // Format kolom nominal uang
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 7) {
                    $sheet->getStyle('G7:G'.$highestRow)->getNumberFormat()->setFormatCode('#,##0');
                }

                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
