<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class TutorExport implements FromCollection, WithEvents, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::orderBy('nama_lengkap')->get();
    }

    public function map($user): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $user->nik,
            $user->nama_lengkap ?? $user->name,
            $user->email ?? '-',
            $user->no_hp ?? '-',
            ucfirst(str_replace('_', ' ', (string) $user->role)),
            $user->is_active ? 'Aktif' : 'Non-Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Lengkap',
            'Email',
            'No HP / WhatsApp',
            'Role / Jabatan',
            'Status Akun',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:G1')->getFont()->setBold(true);

                $sheet->getStyle('B2:B500')->getNumberFormat()->setFormatCode('@');

                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
