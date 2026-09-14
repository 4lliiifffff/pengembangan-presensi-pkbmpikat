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

    protected $statusFilter;

    protected $totalHadir = 0;

    protected $totalIzin = 0;

    protected $totalAlpha = 0;

    protected $totalProses = 0;

    protected $totalJamMengajar = 0.0;

    public function __construct($startDate, $endDate, $tutorId = null, $siswaId = null, $statusFilter = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tutorId = $tutorId;
        $this->siswaId = $siswaId;
        $this->statusFilter = $statusFilter;
    }

    public function collection()
    {
        $query = Presensi::with(['siswa.relKelas', 'tutor'])
            ->whereBetween('tgl_presensi', [$this->startDate, $this->endDate]);

        if ($this->tutorId) {
            $query->where('tutor_id', $this->tutorId);
        }
        if ($this->siswaId) {
            $query->where('siswa_id', $this->siswaId);
        }
        if ($this->statusFilter) {
            if ($this->statusFilter === 'hadir') {
                $query->where('status', 'hadir')->whereNotNull('foto_selesai');
            } elseif ($this->statusFilter === 'proses') {
                $query->whereNotNull('foto_mulai')->whereNull('foto_selesai');
            } elseif ($this->statusFilter === 'izin') {
                $query->whereIn('status', ['izin', 'sakit']);
            } elseif ($this->statusFilter === 'alpha') {
                $query->where('status', 'alpha');
            }
        }

        $presensis = $query->orderBy('tgl_presensi')->get();

        $hasStatus = Schema::hasColumn('presensis', 'status');
        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');

        foreach ($presensis as $p) {
            $status = 'alpha';
            if ($hasStatus) {
                $status = strtolower((string) $p->status);
            } elseif ($hasJamMulai && $p->jam_mulai) {
                $status = $p->jam_selesai ? 'hadir' : 'proses';
            }

            // Hitung durasi jam mengajar
            $durasiJam = 0.0;
            if ($p->jam_mulai && $p->jam_selesai) {
                try {
                    $mulai = Carbon::parse($p->jam_mulai);
                    $selesai = Carbon::parse($p->jam_selesai);
                    $durasiMenit = max(0, $mulai->diffInMinutes($selesai));
                    $durasiJam = round($durasiMenit / 60, 2);
                    if ($durasiJam <= 0) {
                        $durasiJam = 1.0;
                    }
                } catch (\Throwable) {
                    $durasiJam = 1.0;
                }
            } elseif ($status === 'hadir') {
                $durasiJam = 1.0;
            }

            if ($status === 'hadir') {
                $this->totalHadir++;
                $this->totalJamMengajar += $durasiJam;
            } elseif ($status === 'izin' || $status === 'sakit') {
                $this->totalIzin++;
            } elseif ($status === 'proses') {
                $this->totalProses++;
            } else {
                $this->totalAlpha++;
            }

            $p->calculated_status = match ($status) {
                'hadir' => 'Hadir',
                'proses' => 'Sedang Berjalan',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                default => 'Alpha',
            };
            $p->calculated_durasi = $durasiJam > 0 ? $durasiJam.' Jam' : '-';
        }

        return $presensis;
    }

    public function map($presensi): array
    {
        static $no = 0;
        $no++;

        $tutorName = $presensi->tutor->nama_lengkap ?? ($presensi->tutor->name ?? 'Tutor');
        $tutorNik = $presensi->tutor->nik ?? '-';
        $siswaName = $presensi->siswa->nama_siswa ?? '-';
        $kelasName = $presensi->siswa->relKelas->nama_kelas ?? '-';
        $modaLabel = $presensi->moda_label ?? ucfirst(str_replace('_', ' ', (string) ($presensi->moda_pembelajaran ?? 'Tatap Muka')));

        return [
            $no,
            Carbon::parse($presensi->tgl_presensi)->format('d/m/Y'),
            $tutorName,
            $tutorNik,
            $siswaName,
            $kelasName,
            $modaLabel,
            $presensi->jam_mulai ? Carbon::parse($presensi->jam_mulai)->format('H:i') : '-',
            $presensi->jam_selesai ? Carbon::parse($presensi->jam_selesai)->format('H:i') : '-',
            $presensi->calculated_durasi,
            $presensi->lokasi_mulai ?? ($presensi->lokasi ?? '-'),
            $presensi->lokasi_selesai ?? '-',
            $presensi->calculated_status,
            $presensi->keterangan ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama Tutor',
            'NIK Tutor',
            'Nama Siswa',
            'Kelas / Rombel',
            'Moda Pembelajaran',
            'Jam Masuk',
            'Jam Keluar',
            'Durasi Mengajar',
            'Lokasi Masuk',
            'Lokasi Keluar',
            'Status Kehadiran',
            'Keterangan',
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
                $sheet->setCellValue('A1', 'REKAPITULASI LAPORAN PRESENSI PKBM PIKAT');
                $sheet->setCellValue('A2', 'Rentang Tanggal: '.$this->startDate->format('d M Y').' s/d '.$this->endDate->format('d M Y'));
                $sheet->setCellValue('A3', 'Total Hadir: '.$this->totalHadir.' Sesi');
                $sheet->setCellValue('C3', 'Sedang Berjalan: '.$this->totalProses.' Sesi');
                $sheet->setCellValue('E3', 'Total Izin/Sakit: '.$this->totalIzin.' Hari');
                $sheet->setCellValue('G3', 'Total Alpha: '.$this->totalAlpha);
                $sheet->setCellValue('I3', 'Total Akumulasi: '.round($this->totalJamMengajar, 2).' Jam');

                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3:K3')->getFont()->setBold(true);
                $sheet->getStyle('A6:N6')->getFont()->setBold(true);

                // Auto-fit kolom agar terbaca rapi
                foreach (range('A', 'N') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
