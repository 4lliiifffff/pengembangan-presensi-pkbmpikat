<?php

namespace Database\Seeders;

use App\Models\JenjangPaket;
use App\Models\Kelas;
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

        // 1. Buat atau perbarui 3 Jenjang Pokok (Paket A, B, C)
        $jenjangModels = [];
        foreach ($jenjangList as $item) {
            $jenjangModels[$item['kode']] = JenjangPaket::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }

        $paketA = $jenjangModels['paket_a'];
        $paketB = $jenjangModels['paket_b'];
        $paketC = $jenjangModels['paket_c'];

        // 2. Bersihkan jenjang di luar Paket A, B, C jika ada
        $obsoleteJenjangs = JenjangPaket::whereNotIn('kode', $allowedKodes)->get();
        foreach ($obsoleteJenjangs as $obsolete) {
            if ($paketC) {
                Kelas::where('jenjang_paket_id', $obsolete->id)->update([
                    'jenjang_paket_id' => $paketC->id,
                ]);
            }
            $obsolete->delete();
        }

        // 3. Buat atau Sinkronkan Master 12 Kelas (Kelas 1 s/d 12)
        $classes = [
            // Paket A (Kelas 1 - 6)
            ['nama_kelas' => 'Paket A - Kelas 1', 'tingkat' => '1', 'jenjang_paket_id' => $paketA->id, 'keterangan' => 'Tingkat 1 Paket A'],
            ['nama_kelas' => 'Paket A - Kelas 2', 'tingkat' => '2', 'jenjang_paket_id' => $paketA->id, 'keterangan' => 'Tingkat 2 Paket A'],
            ['nama_kelas' => 'Paket A - Kelas 3', 'tingkat' => '3', 'jenjang_paket_id' => $paketA->id, 'keterangan' => 'Tingkat 3 Paket A'],
            ['nama_kelas' => 'Paket A - Kelas 4', 'tingkat' => '4', 'jenjang_paket_id' => $paketA->id, 'keterangan' => 'Tingkat 4 Paket A'],
            ['nama_kelas' => 'Paket A - Kelas 5', 'tingkat' => '5', 'jenjang_paket_id' => $paketA->id, 'keterangan' => 'Tingkat 5 Paket A'],
            ['nama_kelas' => 'Paket A - Kelas 6', 'tingkat' => '6', 'jenjang_paket_id' => $paketA->id, 'keterangan' => 'Tingkat 6 Paket A'],

            // Paket B (Kelas 7 - 9)
            ['nama_kelas' => 'Paket B - Kelas 7', 'tingkat' => '7', 'jenjang_paket_id' => $paketB->id, 'keterangan' => 'Tingkat 7 Paket B'],
            ['nama_kelas' => 'Paket B - Kelas 8', 'tingkat' => '8', 'jenjang_paket_id' => $paketB->id, 'keterangan' => 'Tingkat 8 Paket B'],
            ['nama_kelas' => 'Paket B - Kelas 9', 'tingkat' => '9', 'jenjang_paket_id' => $paketB->id, 'keterangan' => 'Tingkat 9 Paket B'],

            // Paket C (Kelas 10 - 12)
            ['nama_kelas' => 'Paket C - Kelas 10', 'tingkat' => '10', 'jenjang_paket_id' => $paketC->id, 'keterangan' => 'Tingkat 10 Paket C'],
            ['nama_kelas' => 'Paket C - Kelas 11', 'tingkat' => '11', 'jenjang_paket_id' => $paketC->id, 'keterangan' => 'Tingkat 11 Paket C'],
            ['nama_kelas' => 'Paket C - Kelas 12', 'tingkat' => '12', 'jenjang_paket_id' => $paketC->id, 'keterangan' => 'Tingkat 12 Paket C'],
        ];

        foreach ($classes as $cls) {
            Kelas::updateOrCreate(
                ['nama_kelas' => $cls['nama_kelas']],
                $cls
            );
        }
    }
}
