<?php

namespace App\Exports;

use App\Models\Jadwal;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class JadwalExport implements FromCollection, WithEvents, WithHeadings, WithMapping
{
    public function collection()
    {
        return Jadwal::orderBy('tanggal', 'desc')->get();
    }

    public function map($jadwal): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $jadwal->judul,
            $jadwal->deskripsi ?? '-',
            Carbon::parse($jadwal->tanggal)->format('d/m/Y'),
            $jadwal->lokasi ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Judul Agenda / Kegiatan',
            'Deskripsi',
            'Tanggal Pelaksanaan',
            'Lokasi',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);

                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
