<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class JadwalTemplateExport implements FromArray, WithEvents, WithHeadings
{
    public function array(): array
    {
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        return [
            [
                'Kegiatan Belajar Mengajar Paket A',
                'Pembelajaran Tematik & Matematika Dasar',
                $today,
                'Gedung PKBM Pikat Ruang 1',
            ],
            [
                'Simulasi Ujian Pendidikan Kesetaraan (UPK)',
                'Try out persiapan ujian akhir semester Paket B & C',
                $tomorrow,
                'Lab Komputer PKBM Pikat',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Judul Agenda / Kegiatan *Wajib',
            'Deskripsi / Keterangan (Opsional)',
            'Tanggal Kegiatan (Format: YYYY-MM-DD) *Wajib',
            'Lokasi Kegiatan (Opsional)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:D1')->getFont()->setBold(true);

                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
