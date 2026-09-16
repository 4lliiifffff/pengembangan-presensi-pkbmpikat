<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $agendas = [
            [
                'judul' => 'Tutorial Tatap Muka Komunitas Paket B & C',
                'deskripsi' => 'Pembelajaran tatap muka materi Matematika dan Bahasa Indonesia di ruang kelas utama PKBM Pikat.',
                'tanggal' => $today->toDateString(),
                'lokasi' => 'Kampus Utama PKBM Pikat, Surabaya',
            ],
            [
                'judul' => 'Workshop Keterampilan Digital & Desain Grafis',
                'deskripsi' => 'Pelatihan pengoperasian aplikasi desain dan komputer untuk seluruh peserta didik Paket C dan Vokasi.',
                'tanggal' => $today->copy()->addDays(2)->toDateString(),
                'lokasi' => 'Lab Komputer PKBM Pikat',
            ],
            [
                'judul' => 'Evaluasi Belajar Tengah Semester (STS) Ganjil',
                'deskripsi' => 'Pelaksanaan asesmen sumatif tengah semester untuk seluruh jenjang Paket A, Paket B, dan Paket C.',
                'tanggal' => $today->copy()->addDays(7)->toDateString(),
                'lokasi' => 'PKBM Pikat Surabaya',
            ],
            [
                'judul' => 'Rapat Koordinasi & Pembinaan Tutor Bulanan',
                'deskripsi' => 'Evaluasi kinerja tutor, rekapitulasi kehadiran peserta didik, dan briefing kurikulum kesetaraan.',
                'tanggal' => $today->copy()->addDays(14)->toDateString(),
                'lokasi' => 'Ruang Rapat Utama PKBM Pikat',
            ],
            [
                'judul' => 'Sosialisasi Uji Kesetaraan & Pembekalan Siswa Akhir',
                'deskripsi' => 'Pengarahan bagi peserta didik tingkat akhir (Kelas 6, 9, dan 12) menghadapi asesmen nasional.',
                'tanggal' => $today->copy()->subDays(3)->toDateString(),
                'lokasi' => 'Aula PKBM Pikat',
            ],
        ];

        foreach ($agendas as $agenda) {
            Jadwal::updateOrCreate(
                [
                    'judul' => $agenda['judul'],
                    'tanggal' => $agenda['tanggal'],
                ],
                $agenda
            );
        }
    }
}
