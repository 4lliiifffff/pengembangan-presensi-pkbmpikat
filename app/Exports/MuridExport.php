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
    public function __construct(protected array $filters = []) {}

    public function collection()
    {
        $query = Murid::with(['relKelas', 'tutor'])->orderBy('nama_murid');

        $status = $this->filters['status'] ?? 'aktif';
        if ($status !== 'semua' && ! empty($status)) {
            $query->where('status_murid', $status);
        }

        $pembimbing = $this->filters['pembimbing'] ?? null;
        if ($pembimbing === 'ada') {
            $query->whereNotNull('tutor_id');
        } elseif ($pembimbing === 'belum') {
            $query->whereNull('tutor_id');
        }

        if (! empty($this->filters['tutor_id'])) {
            $query->where('tutor_id', $this->filters['tutor_id']);
        }

        if (! empty($this->filters['kelas_id'])) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if (! empty($this->filters['q'])) {
            $search = $this->filters['q'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_murid', 'like', "%{$search}%")
                    ->orWhere('no_absen', 'like', "%{$search}%")
                    ->orWhere('nama_wali', 'like', "%{$search}%")
                    ->orWhereHas('tutor', function ($tq) use ($search) {
                        $tq->where('nama_lengkap', 'like', "%{$search}%");
                    })
                    ->orWhereHas('relKelas', function ($kq) use ($search) {
                        $kq->where('nama_kelas', 'like', "%{$search}%");
                    });
            });
        }

        return $query->get();
    }

    public function map($murid): array
    {
        return [
            $murid->no_absen,
            $murid->nis ?? '-',
            $murid->nisn ?? '-',
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
            'Nomor Absen',
            'NIS',
            'NISN',
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
                $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                $sheet->getStyle('A1:K1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E0E7FF');

                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
