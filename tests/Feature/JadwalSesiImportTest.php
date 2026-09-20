<?php

namespace Tests\Feature;

use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\KategoriTutorial;
use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class JadwalSesiImportTest extends TestCase
{
    use DatabaseTransactions;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected KategoriTutorial $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nama_lengkap' => 'Tutor Import Tester',
            'email' => 'tutor_import@pkbmpikat.com',
            'nik' => 'NIKIMPOR01',
        ]);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_c_import'], ['nama_jenjang' => 'Paket C Import', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 10 Paket C Import'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '10']);
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_siswa' => 'Siswa Import Tester',
            'no_absen' => 'IMP-01',
            'no_hp' => '081234567891',
            'is_abk' => false,
            'nama_wali' => 'Wali Import Test',
            'kelas_id' => $kls->id,
            'tutor_id' => $this->tutor->id,
            'status_siswa' => 'aktif',
        ]);

        $this->kategori = KategoriTutorial::firstOrCreate(
            ['nama_kategori' => 'Tutorial Tatap Muka Import'],
            ['jenis_layanan' => 'komunitas', 'durasi_jam' => 2.0, 'nominal_honor' => 75000, 'is_aktif' => true]
        );
    }

    public function test_tutor_can_download_schedule_template(): void
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutor.jadwal-sesi.template'));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains((string) $response->headers->get('content-disposition'), 'template_jadwal_sesi_tutor.xlsx')
        );
    }

    public function test_tutor_can_import_schedule_for_one_month_or_more(): void
    {
        // Siapkan data CSV jadwal KBM selama 1 bulan (4 pertemuan)
        $csvContent = "Tanggal Rencana (YYYY-MM-DD) *Wajib,Nomor Absen Siswa / NIS / Nama *Wajib,Jam Mulai (HH:MM) *Wajib,Jam Selesai (HH:MM) *Wajib,Kategori Tutorial (Opsional),Catatan / Materi (Opsional),NIK Tutor (Opsional)\n"
            ."2026-10-05,IMP-01,08:00,10:00,Tutorial Tatap Muka Import,Pertemuan 1: Aljabar Dasar,NIKIMPOR01\n"
            ."2026-10-12,IMP-01,08:00,10:00,Tutorial Tatap Muka Import,Pertemuan 2: Persamaan Linier,NIKIMPOR01\n"
            ."2026-10-19,IMP-01,08:00,10:00,Tutorial Tatap Muka Import,Pertemuan 3: Geometri Bidang,NIKIMPOR01\n"
            ."2026-10-26,IMP-01,08:00,10:00,Tutorial Tatap Muka Import,Pertemuan 4: Evaluasi Bulanan,NIKIMPOR01\n";

        $file = UploadedFile::fake()->createWithContent('jadwal_kbm_oktober.csv', $csvContent);

        $response = $this->actingAs($this->tutorUser)
            ->post(route('tutor.jadwal-sesi.import'), [
                'file_excel' => $file,
            ]);

        $response->assertRedirect();
        $response->dumpSession();
        $response->assertSessionHas('success');

        // Pastikan 4 sesi berhasil disimpan ke database
        $this->assertDatabaseCount('jadwal_sesi', 4);

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => '2026-10-05',
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '10:00:00',
            'durasi_jam' => '2.00',
            'status' => 'terjadwal',
            'catatan' => 'Pertemuan 1: Aljabar Dasar',
        ]);

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => '2026-10-26',
            'catatan' => 'Pertemuan 4: Evaluasi Bulanan',
        ]);
    }

    public function test_imported_session_can_be_rescheduled_seamlessly(): void
    {
        // 1. Buat sesi KBM hasil import
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'tanggal_rencana' => '2026-10-12',
            'jam_masuk_rencana' => '08:00',
            'jam_pulang_rencana' => '10:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
            'status_kehadiran_siswa' => 'belum_presensi',
            'catatan' => 'Diimpor dari berkas Excel',
        ]);

        // 2. Lakukan reschedule sesi
        $response = $this->actingAs($this->tutorUser)
            ->post(route('tutor.jadwal-sesi.reschedule', $sesi->id), [
                'tanggal_baru' => '2026-10-14',
                'jam_masuk_baru' => '09:00',
                'jam_pulang_baru' => '11:00',
                'alasan_penggantian' => 'Tutor ada keperluan dinas luar mendadak',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 3. Verifikasi sesi lama dibatalkan dengan riwayat alasan
        $sesi->refresh();
        $this->assertEquals('dibatalkan', $sesi->status);
        $this->assertEquals('Tutor ada keperluan dinas luar mendadak', $sesi->alasan_penggantian);

        // 4. Verifikasi sesi pengganti baru terbentuk
        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => '2026-10-14',
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'jenis_sesi' => 'pengganti',
            'status' => 'terjadwal',
            'tanggal_asli' => '2026-10-12',
            'alasan_penggantian' => 'Tutor ada keperluan dinas luar mendadak',
        ]);
    }

    public function test_duplicate_import_updates_or_skips_instead_of_creating_duplicate(): void
    {
        $csvContent = "Tanggal,Nomor Absen Siswa,Jam Mulai,Jam Selesai,Catatan\n"
            ."2026-11-02,IMP-01,08:00,10:00,Pertemuan Pekan 1\n";

        $file1 = UploadedFile::fake()->createWithContent('jadwal_minggu1.csv', $csvContent);

        // Import pertama
        $this->actingAs($this->tutorUser)
            ->post(route('tutor.jadwal-sesi.import'), ['file_excel' => $file1]);

        $this->assertDatabaseCount('jadwal_sesi', 1);

        // Import kedua dengan catatan diperbarui pada jam yang sama
        $csvContentUpdated = "Tanggal,Nomor Absen Siswa,Jam Mulai,Jam Selesai,Catatan\n"
            ."2026-11-02,IMP-01,08:00,10:00,Pertemuan Pekan 1 (Materi Diperbarui)\n";

        $file2 = UploadedFile::fake()->createWithContent('jadwal_minggu1_rev.csv', $csvContentUpdated);

        $response = $this->actingAs($this->tutorUser)
            ->post(route('tutor.jadwal-sesi.import'), ['file_excel' => $file2]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan tidak ada duplikasi record (tetap 1 sesi)
        $this->assertDatabaseCount('jadwal_sesi', 1);
        $this->assertDatabaseHas('jadwal_sesi', [
            'tanggal_rencana' => '2026-11-02',
            'catatan' => 'Pertemuan Pekan 1 (Materi Diperbarui)',
        ]);
    }

    public function test_tutor_can_export_monthly_schedules_to_excel(): void
    {
        JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => '2026-10-05',
            'jam_masuk_rencana' => '08:00',
            'jam_pulang_rencana' => '10:00',
            'durasi_jam' => 2.00,
            'status' => 'terjadwal',
        ]);

        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutor.jadwal-sesi.export', ['tanggal' => '2026-10-05']));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains((string) $response->headers->get('content-disposition'), 'jadwal_sesi_')
        );
    }
}
