<?php

namespace App\Exports;

use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class PresensiExport implements FromCollection, WithCustomStartCell, WithEvents, WithHeadings, WithMapping
{
    protected $startDate;

    protected $endDate;

    protected $tutorId;

    protected $siswaId;

    protected $totalHadir = 0;

    protected $totalIzin = 0;

    protected $totalAlpha = 0;

    public function __construct($startDate, $endDate, $tutorId = null, $siswaId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tutorId = $tutorId;
        $this->siswaId = $siswaId;
    }

    public function collection()
    {
        $query = Presensi::with(['siswa', 'tutor'])
            ->whereBetween('tgl_presensi', [$this->startDate, $this->endDate]);

        if ($this->tutorId) {
            $query->where('tutor_id', $this->tutorId);
        }
        if ($this->siswaId) {
            $query->where('siswa_id', $this->siswaId);
        }

        $presensis = $query->orderBy('tgl_presensi')->get();

        $hasStatus = Schema::hasColumn('presensis', 'status');
        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');

        foreach ($presensis as $p) {
            $status = 'alpha';
            if ($hasStatus) {
                $status = strtolower($p->status);
            } elseif ($hasJamMulai && $p->jam_mulai) {
                $status = 'hadir';
            }

            if ($status === 'hadir') {
                $this->totalHadir++;
            } elseif ($status === 'izin') {
                $this->totalIzin++;
            } else {
                $this->totalAlpha++;
            }

            $p->calculated_status = ucfirst($status);
        }

        return $presensis;
    }

    public function map($presensi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            Carbon::parse($presensi->tgl_presensi)->format('d/m/Y'),
            $presensi->tutor->nama_lengkap ?? 'Tutor',
            $presensi->siswa->nama_siswa ?? '-',
            $presensi->jam_mulai ? Carbon::parse($presensi->jam_mulai)->format('H:i') : '-',
            $presensi->jam_selesai ? Carbon::parse($presensi->jam_selesai)->format('H:i') : '-',
            $presensi->lokasi_mulai ?? '-',
            $presensi->calculated_status,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama Tutor',
            'Nama Siswa',
            'Jam Masuk',
            'Jam Keluar',
            'Lokasi',
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
                $sheet->setCellValue('A1', 'REKAP LAPORAN PRESENSI');
                $sheet->setCellValue('A2', 'Rentang Tanggal: '.$this->startDate->format('d M Y').' s/d '.$this->endDate->format('d M Y'));
                $sheet->setCellValue('A3', 'Total Hadir: '.$this->totalHadir);
                $sheet->setCellValue('B3', 'Total Izin: '.$this->totalIzin);
                $sheet->setCellValue('C3', 'Total Alpha: '.$this->totalAlpha);

                $sheet->getStyle('A1:A2')->getFont()->setBold(true);
                $sheet->getStyle('A3:C3')->getFont()->setBold(true);
                $sheet->getStyle('A6:H6')->getFont()->setBold(true);
            },
        ];
    }
}
