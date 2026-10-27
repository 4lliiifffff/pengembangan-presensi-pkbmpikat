<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class MuridSeeder extends Seeder
{
    /**
     * Run the database seeds for Peserta Didik PKBM Pintar Berbakat.
     */
    public function run(): void
    {
        // 1. Ambil Cache / Map Tutor ID berdasarkan kata kunci nama
        $tutorSendi = Tutor::where('nama_lengkap', 'like', '%Sendi%')->first()?->id;
        $tutorTary = Tutor::where('nama_lengkap', 'like', '%Lestary%')->first()?->id;
        $tutorInka = Tutor::where('nama_lengkap', 'like', '%Inka%')->first()?->id;
        $tutorWina = Tutor::where('nama_lengkap', 'like', '%Wina%')->first()?->id;
        $tutorNailul = Tutor::where('nama_lengkap', 'like', '%Nailul%')->first()?->id;
        $tutorDani = Tutor::where('nama_lengkap', 'like', '%Dani%')->first()?->id;
        $tutorMaura = Tutor::where('nama_lengkap', 'like', '%Maura%')->first()?->id;
        $tutorNova = Tutor::where('nama_lengkap', 'like', '%Nova%')->first()?->id;

        // 2. Ambil Map Kelas ID berdasarkan tingkat
        $kelasMap = Kelas::all()->pluck('id', 'tingkat')->toArray();

        // 3. Daftar Peserta Didik (Tahun Ajaran 2026/2027)
        $muridList = [
            // --- DATA 25 PESERTA DIDIK UTAMA ---
            // Paket A
            [
                'no_absen' => 1,
                'nama_murid' => 'Ari Tri Agusta',
                'tingkat' => '1',
                'tutor_id' => $tutorSendi,
                'nis' => null,
                'nisn' => null,
                'is_abk' => false,
            ],
            [
                'no_absen' => 2,
                'nama_murid' => 'Rafardhan Izzatul Ibad Barliant',
                'tingkat' => '2',
                'tutor_id' => $tutorTary,
                'nis' => '25070090',
                'nisn' => '3174248579',
                'is_abk' => false,
            ],
            [
                'no_absen' => 3,
                'nama_murid' => 'Reiya Arfan Faizan',
                'tingkat' => '2',
                'tutor_id' => $tutorTary,
                'nis' => '26040106',
                'nisn' => '3183215853',
                'is_abk' => false,
            ],
            [
                'no_absen' => 4,
                'nama_murid' => 'Gelvan Purwo Yonanta',
                'tingkat' => '3',
                'tutor_id' => $tutorTary,
                'nis' => '26040107',
                'nisn' => '3180297643',
                'is_abk' => false,
            ],
            [
                'no_absen' => 5,
                'nama_murid' => 'Zefa Aryan Pranata Indra',
                'tingkat' => '3',
                'tutor_id' => $tutorTary,
                'nis' => '25090097',
                'nisn' => '3167270518',
                'is_abk' => false,
            ],
            [
                'no_absen' => 6,
                'nama_murid' => 'Muhammad Rianza Fariz',
                'tingkat' => '4',
                'tutor_id' => $tutorInka, // Tutor pengampu: Kak Inka
                'nis' => '23070080',
                'nisn' => '3167684838',
                'is_abk' => false,
            ],
            [
                'no_absen' => 7,
                'nama_murid' => 'Ratu Hazel Kevamouza',
                'tingkat' => '4',
                'tutor_id' => $tutorSendi,
                'nis' => '26040108',
                'nisn' => '3170169549',
                'is_abk' => false,
            ],
            [
                'no_absen' => 8,
                'nama_murid' => 'Treivenn Wee',
                'tingkat' => '4',
                'tutor_id' => $tutorInka,
                'nis' => '26010105',
                'nisn' => '3168612555',
                'is_abk' => false,
            ],
            [
                'no_absen' => 9,
                'nama_murid' => 'Violeta Yasmin',
                'tingkat' => '5',
                'tutor_id' => $tutorWina,
                'nis' => '26040109',
                'nisn' => '3141214948',
                'is_abk' => false,
            ],
            [
                'no_absen' => 10,
                'nama_murid' => 'Muhammad Gibran Al Mughni',
                'tingkat' => '6',
                'tutor_id' => $tutorWina,
                'nis' => '24070082',
                'nisn' => '3134281675',
                'is_abk' => false,
            ],

            // Paket B
            [
                'no_absen' => 11,
                'nama_murid' => 'Raeesa Qamra Ramandita',
                'tingkat' => '8',
                'tutor_id' => $tutorTary,
                'nis' => '26010104',
                'nisn' => null,
                'is_abk' => false,
            ],
            [
                'no_absen' => 12,
                'nama_murid' => 'Audric Sebastian Tiago',
                'tingkat' => '9',
                'tutor_id' => $tutorSendi, // Tutor pengampu: Kak Sendi
                'nis' => '25090096',
                'nisn' => '0113131545',
                'is_abk' => false,
            ],

            // Paket C
            [
                'no_absen' => 13,
                'nama_murid' => 'Andrean Nur Rauf',
                'tingkat' => '10',
                'tutor_id' => null, // Program Mandiri
                'nis' => null,
                'nisn' => '0095161665',
                'is_abk' => false,
            ],
            [
                'no_absen' => 14,
                'nama_murid' => 'Muhammad Labibul Akmal',
                'tingkat' => '10',
                'tutor_id' => $tutorNailul,
                'nis' => '25070092',
                'nisn' => '0095569642',
                'is_abk' => false,
            ],
            [
                'no_absen' => 15,
                'nama_murid' => 'Sandi Agung Pamungkas',
                'tingkat' => '10',
                'tutor_id' => null, // Program Mandiri
                'nis' => null,
                'nisn' => '9987568925',
                'is_abk' => false,
            ],
            [
                'no_absen' => 16,
                'nama_murid' => 'Arini Ulvy Khairunnisa',
                'tingkat' => '11',
                'tutor_id' => $tutorWina,
                'nis' => '25080095',
                'nisn' => '0093023125',
                'is_abk' => false,
            ],
            [
                'no_absen' => 17,
                'nama_murid' => 'Najmi Abyakta Wiguna',
                'tingkat' => '11',
                'tutor_id' => $tutorInka,
                'nis' => '26010103',
                'nisn' => '0091949940',
                'is_abk' => false,
            ],
            [
                'no_absen' => 18,
                'nama_murid' => 'Raihan Bestari Putra',
                'tingkat' => '11',
                'tutor_id' => $tutorNailul,
                'nis' => '23020061',
                'nisn' => '0079455858',
                'is_abk' => false,
            ],
            [
                'no_absen' => 19,
                'nama_murid' => 'Shakira Aurasatriani Muhaimin',
                'tingkat' => '11',
                'tutor_id' => $tutorTary,
                'nis' => '25060089',
                'nisn' => '0107082312',
                'is_abk' => false,
            ],
            [
                'no_absen' => 20,
                'nama_murid' => 'Alika Futhri Azkiatunnisa',
                'tingkat' => '12',
                'tutor_id' => $tutorInka,
                'nis' => '25070091',
                'nisn' => '0097796769',
                'is_abk' => false,
            ],
            [
                'no_absen' => 21,
                'nama_murid' => 'Felicia Nuni Morozin',
                'tingkat' => '12',
                'tutor_id' => $tutorTary,
                'nis' => '24070081',
                'nisn' => '3087962498',
                'is_abk' => false,
            ],
            [
                'no_absen' => 22,
                'nama_murid' => 'Melody Saisha Galenina Satyanagh',
                'tingkat' => '12',
                'tutor_id' => $tutorSendi,
                'nis' => '24070084',
                'nisn' => '0086828897',
                'is_abk' => false,
            ],
            [
                'no_absen' => 23,
                'nama_murid' => 'Muhammad Akasyah Nur Zaidan',
                'tingkat' => '12',
                'tutor_id' => $tutorInka,
                'nis' => '22120059',
                'nisn' => '0074596517',
                'is_abk' => false,
            ],
            [
                'no_absen' => 24,
                'nama_murid' => 'Thalyta Nayla Putri Nugraha',
                'tingkat' => '12',
                'tutor_id' => $tutorInka,
                'nis' => '25060088',
                'nisn' => '0085845008',
                'is_abk' => false,
            ],
            [
                'no_absen' => 25,
                'nama_murid' => 'Zahira Fatina Putri Erwina',
                'tingkat' => '12',
                'tutor_id' => $tutorDani,
                'nis' => '24070085',
                'nisn' => '3096843185',
                'is_abk' => false,
            ],

            // --- 67 PESERTA DIDIK ROMBEL BIMBINGAN KAK MAURA ---
            // Rombel Kelas 4 (Paket A)
            ['no_absen' => 26, 'nama_murid' => 'Yasir Bazaz', 'tingkat' => '4', 'tutor_id' => $tutorMaura, 'is_abk' => false],

            // Rombel Kelas 6 (Paket A)
            ['no_absen' => 27, 'nama_murid' => 'Helena Clarisa', 'tingkat' => '6', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 28, 'nama_murid' => 'Mia Angelina', 'tingkat' => '6', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 29, 'nama_murid' => 'Mochamad Hafidz Erdiansyah', 'tingkat' => '6', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 30, 'nama_murid' => 'Muhamad Arsyakara', 'tingkat' => '6', 'tutor_id' => $tutorMaura, 'is_abk' => false],

            // Rombel Kelas 7 (Paket B)
            ['no_absen' => 31, 'nama_murid' => 'Christopher', 'tingkat' => '7', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 32, 'nama_murid' => 'Ferdi Wisnu', 'tingkat' => '7', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 33, 'nama_murid' => 'Muhamad Bagja Permana', 'tingkat' => '7', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 34, 'nama_murid' => 'Muhamad Najib Fauzan', 'tingkat' => '7', 'tutor_id' => $tutorMaura, 'is_abk' => false],

            // Rombel Kelas 8 (Paket B)
            ['no_absen' => 35, 'nama_murid' => 'Angga Saputra', 'tingkat' => '8', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 36, 'nama_murid' => 'Enan Jaka Suhara', 'tingkat' => '8', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 37, 'nama_murid' => 'Ilwa Adam Assyauri', 'tingkat' => '8', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 38, 'nama_murid' => 'Jeni Melani', 'tingkat' => '8', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 39, 'nama_murid' => 'Muhamad Fazri', 'tingkat' => '8', 'tutor_id' => $tutorMaura, 'is_abk' => false],

            // Rombel Kelas 9 (Paket B)
            ['no_absen' => 40, 'nama_murid' => 'Anne Denia Agustina', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 41, 'nama_murid' => 'Ardli Hidayat', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 42, 'nama_murid' => 'Faisal Azhar', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 43, 'nama_murid' => 'Fitriani Aulianisa', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 44, 'nama_murid' => 'Fitria Putri Hidayanti', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 45, 'nama_murid' => 'Maman Sulaeman', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 46, 'nama_murid' => 'Muhammad Khadafi', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 47, 'nama_murid' => 'Patimah', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 48, 'nama_murid' => 'Rasya Chandra Putra', 'tingkat' => '9', 'tutor_id' => $tutorMaura, 'is_abk' => false],

            // Rombel Kelas 10 (Paket C)
            ['no_absen' => 49, 'nama_murid' => 'Ade Mulyani', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 50, 'nama_murid' => 'Aliza Rizki Chrissanti', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 51, 'nama_murid' => 'Anah', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 52, 'nama_murid' => 'Ardhan Syafutra', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 53, 'nama_murid' => 'Hendru Gunawan Septian', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 54, 'nama_murid' => 'Kenny Destiana Nugraha', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 55, 'nama_murid' => 'Keyza Pratama', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 56, 'nama_murid' => 'Mochmad Restu Maulana', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 57, 'nama_murid' => 'Nabila Putri Patarani', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 58, 'nama_murid' => 'Nendi Nurzaman', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 59, 'nama_murid' => 'Ridho Aliansyah', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 60, 'nama_murid' => 'Rizky Ardiansah', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 61, 'nama_murid' => 'Sifa Indra Pratama Putri', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 62, 'nama_murid' => 'Sundafa Ashar Alkatiri', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 63, 'nama_murid' => 'Supriadin', 'tingkat' => '10', 'tutor_id' => $tutorMaura, 'is_abk' => false],

            // Rombel Kelas 11 (Paket C)
            ['no_absen' => 64, 'nama_murid' => 'Agung Rizki Nurrahman', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 65, 'nama_murid' => 'Ardhi Annizah', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 66, 'nama_murid' => 'Elsa Oktaviani Ramadhan', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 67, 'nama_murid' => 'Evi Kurniawati', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 68, 'nama_murid' => 'Faqih Sendo Maulana', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 69, 'nama_murid' => 'Muhammad Nur Rizki', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 70, 'nama_murid' => 'Putra Azis Satriani', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 71, 'nama_murid' => 'Syalwa Nur Fithria', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 72, 'nama_murid' => 'Suherlan', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 73, 'nama_murid' => 'Taufik', 'tingkat' => '11', 'tutor_id' => $tutorMaura, 'is_abk' => false],

            // Rombel Kelas 12 (Paket C)
            ['no_absen' => 74, 'nama_murid' => 'Am Ridwan', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 75, 'nama_murid' => 'Asep Ugan Suhara', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 76, 'nama_murid' => 'Azidjah Noer Djannah', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 77, 'nama_murid' => 'Christina Winata Suryanata', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 78, 'nama_murid' => 'Dhea Putri Rahmawati', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 79, 'nama_murid' => 'Fizky Bilqist Azzahra', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 80, 'nama_murid' => 'Ghaida Khansa Nabalah', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 81, 'nama_murid' => 'Halimah', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 82, 'nama_murid' => 'Hasanah', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 83, 'nama_murid' => 'Lilis Kurniawati', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 84, 'nama_murid' => 'Mochammad Guntur Merinda S', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 85, 'nama_murid' => 'Nuri Kartini', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 86, 'nama_murid' => 'Risma Yanti Apriella', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 87, 'nama_murid' => 'Riyanti', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 88, 'nama_murid' => 'Rizky Ruskandi', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 89, 'nama_murid' => 'Salma Nurhaerani', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 90, 'nama_murid' => 'Sumarni', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 91, 'nama_murid' => 'Suningsih', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
            ['no_absen' => 92, 'nama_murid' => 'Viky Rizkyaldy', 'tingkat' => '12', 'tutor_id' => $tutorMaura, 'is_abk' => false],
        ];

        foreach ($muridList as $data) {
            $kelasId = $kelasMap[$data['tingkat']] ?? null;

            Murid::updateOrCreate(
                ['no_absen' => $data['no_absen']],
                [
                    'nama_murid' => $data['nama_murid'],
                    'nis' => $data['nis'] ?? null,
                    'nisn' => $data['nisn'] ?? null,
                    'kelas_id' => $kelasId,
                    'tutor_id' => $data['tutor_id'],
                    'is_abk' => $data['is_abk'],
                    'no_hp' => $data['no_hp'] ?? '-',
                    'nama_wali' => $data['nama_wali'] ?? '-',
                    'status_murid' => 'aktif',
                ]
            );
        }
    }
}
