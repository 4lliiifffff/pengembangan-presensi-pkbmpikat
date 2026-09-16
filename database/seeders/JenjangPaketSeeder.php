<?php

namespace Database\Seeders;

use App\Models\JenjangPaket;
use App\Models\kelas;
use Illuminate\Database\Seeder;

class JenjangPaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenjangList = [
            [
                'kode' => 'paket_a',
                'nama_jenjang' => 'Paket A (Setara SD)',
                'tingkat_label' => 'Kelas 1 - 6',
                'keterangan' => 'Pendidikan kesetaraan tingkat dasar setara Sekolah Dasar (SD/MI).',
                'urutan' => 1,
                'is_aktif' => true,
            ],
            [
                'kode' => 'paket_b',
                'nama_jenjang' => 'Paket B (Setara SMP)',
                'tingkat_label' => 'Kelas 7 - 9',
                'keterangan' => 'Pendidikan kesetaraan tingkat menengah pertama setara SMP/MTs.',
                'urutan' => 2,
                'is_aktif' => true,
            ],
            [
                'kode' => 'paket_c',
                'nama_jenjang' => 'Paket C (Setara SMA)',
                'tingkat_label' => 'Kelas 10 - 12',
                'keterangan' => 'Pendidikan kesetaraan tingkat menengah atas setara SMA/MA/SMK.',
                'urutan' => 3,
                'is_aktif' => true,
            ],
        ];

        $allowedKodes = ['paket_a', 'paket_b', 'paket_c'];

        // 1. Buat atau perbarui 3 Jenjang Pokok
        foreach ($jenjangList as $item) {
            JenjangPaket::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }

        // 2. Ambil ID Paket C sebagai fallback jika ada kelas lama yang terikat ke jenjang non-A/B/C
        $paketC = JenjangPaket::where('kode', 'paket_c')->first();

        // 3. Bersihkan jenjang di luar Paket A, B, C
        $obsoleteJenjangs = JenjangPaket::whereNotIn('kode', $allowedKodes)->get();
        foreach ($obsoleteJenjangs as $obsolete) {
            if ($paketC) {
                kelas::where('jenjang_paket_id', $obsolete->id)->update([
                    'jenjang_paket_id' => $paketC->id,
                ]);
            }
            $obsolete->delete();
        }
    }
}
