<?php

namespace Tests\Feature;

use App\Exports\PresensiExport;
use App\Models\kelas as Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SpreadsheetImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $kepsek;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
    }

    public function test_presensi_export_contains_new_columns_and_metrics(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = Kelas::create(['nama_kelas' => 'Paket A']);
        $siswa = Siswa::create([
            'no_absen' => 'SISWA-EXP-1',
            'nama_siswa' => 'Siswa Excel',
            'nama_wali' => 'Wali Excel',
            'no_hp' => '081298765432',
            'kelas_id' => $kelas->id,
            'tutor_id' => $tutor->id,
            'tarif_per_jam' => 50000,
        ]);

        $today = Carbon::today();
        Presensi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => $today->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'moda_pembelajaran' => 'kunjungan_rumah',
            'status' => 'hadir',
            'lokasi_mulai' => 'Rumah Siswa',
            'lokasi_selesai' => 'Rumah Siswa',
        ]);

        $export = new PresensiExport($today->copy()->startOfDay(), $today->copy()->endOfDay());
        $headings = $export->headings();

        $this->assertContains('NIK Tutor', $headings);
        $this->assertContains('Kelas / Rombel', $headings);
        $this->assertContains('Moda Pembelajaran', $headings);
        $this->assertContains('Durasi Mengajar', $headings);
        $this->assertContains('Lokasi Masuk', $headings);
        $this->assertContains('Lokasi Keluar', $headings);
        $this->assertContains('Keterangan', $headings);

        $collection = $export->collection();
        $this->assertCount(1, $collection);

        $mapped = $export->map($collection->first());
        $this->assertEquals($user->nik, $mapped[3]); // NIK Tutor
        $this->assertEquals('Siswa Excel', $mapped[4]);
        $this->assertEquals('Paket A', $mapped[5]);
        $this->assertEquals('Kunjungan Rumah (Home Visit)', $mapped[6]);
        $this->assertEquals('2 Jam', $mapped[9]); // Durasi Mengajar
    }

    public function test_admin_can_download_all_spreadsheet_templates(): void
    {
        $this->actingAs($this->admin)->get(route('admin.siswa.downloadTemplate'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.karyawan.downloadTemplate'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.jadwal.downloadTemplate'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.payroll.download-tarif-template'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.laporan.downloadTemplate'))->assertStatus(200);
    }

    public function test_admin_and_kepsek_can_export_all_modules_to_excel(): void
    {
        $this->actingAs($this->admin)->get(route('admin.siswa.exportExcel'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.karyawan.exportExcel'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.jadwal.exportExcel'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.payroll.rekap-excel'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.laporan.exportExcel'))->assertStatus(200);

        $this->actingAs($this->kepsek)->get(route('kepsek.payroll.rekap-excel'))->assertStatus(200);
    }

    public function test_bulk_import_siswa_creates_and_updates_records(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'Paket B']);
        $csvContent = "nomor_absen_nis_wajib,nama_siswa_wajib,nama_wali_murid_wajib,no_whatsapp_wali_wajib,nama_kelas_rombel_wajib,nik_tutor_pembimbing_opsional,tarif_honor_per_jam_rp_wajib\n";
        $csvContent .= "SISWA-NEW-1,Rudi Hartono,Pak Hartono,0812334455,Paket B,,65000\n";

        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.siswa.importExcel'), [
            'file_excel' => $file,
        ]);

        $response->assertRedirect(route('admin.siswa.index'));
        $this->assertDatabaseHas('siswas', [
            'no_absen' => 'SISWA-NEW-1',
            'nama_siswa' => 'Rudi Hartono',
            'tarif_per_jam' => 65000,
        ]);
    }

    public function test_bulk_update_tarif_honor_siswa_via_excel(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'Paket C']);
        $siswa = Siswa::create([
            'no_absen' => 'SISWA-TARIF-1',
            'nama_siswa' => 'Siswa Tarif',
            'nama_wali' => 'Wali Tarif',
            'no_hp' => '08123456789',
            'kelas_id' => $kelas->id,
            'tarif_per_jam' => 50000,
        ]);

        $csvContent = "nomor_absen_nis,nama_siswa,kelas_rombel,tutor_pembimbing,tarif_honor_per_jam_rp\n";
        $csvContent .= "SISWA-TARIF-1,Siswa Tarif,Paket C,-,85000\n";

        $file = UploadedFile::fake()->createWithContent('tarif.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.payroll.import-tarif'), [
            'file_excel' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(85000, $siswa->fresh()->tarif_per_jam);
    }

    public function test_bulk_import_tutor_creates_user_and_tutor_records(): void
    {
        $csvContent = "nik_nomor_induk_kependudukan_wajib,nama_lengkap_beserta_gelar_wajib,email_aktif_opsional,nomor_whatsapp_hp_wajib,peran_role_tutor_admin_kepala_sekolah_wajib,password_akun_kosongkan_jika_default_nik\n";
        $csvContent .= "3304123456780001,Dr. Hendra Wijaya,hendra@gmail.com,081288990011,tutor,secret123\n";

        $file = UploadedFile::fake()->createWithContent('tutors.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.karyawan.importExcel'), [
            'file_excel' => $file,
        ]);

        $response->assertRedirect(route('admin.karyawan.index'));
        $this->assertDatabaseHas('users', [
            'nik' => '3304123456780001',
            'role' => 'tutor',
        ]);
        $this->assertDatabaseHas('tutors', [
            'nik' => '3304123456780001',
            'nama_lengkap' => 'Dr. Hendra Wijaya',
        ]);
    }

    public function test_bulk_import_jadwal_creates_agenda_records(): void
    {
        $csvContent = "judul_agenda_kegiatan_wajib,deskripsi_keterangan_opsional,tanggal_kegiatan_format_yyyy_mm_dd_wajib,lokasi_kegiatan_opsional\n";
        $csvContent .= "Workshop Persiapan Ujian,Materi kisi-kisi UPK,2026-10-15,Aula PKBM Pikat\n";

        $file = UploadedFile::fake()->createWithContent('jadwal.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.importExcel'), [
            'file_excel' => $file,
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('jadwals', [
            'judul' => 'Workshop Persiapan Ujian',
            'tanggal' => '2026-10-15',
            'lokasi' => 'Aula PKBM Pikat',
        ]);
    }

    public function test_bulk_import_presensi_retroaktif_creates_attendance_records(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = Kelas::create(['nama_kelas' => 'Paket A']);
        $siswa = Siswa::create([
            'no_absen' => 'SISWA-PRE-1',
            'nama_siswa' => 'Siswa Presensi Import',
            'nama_wali' => 'Wali Presensi',
            'no_hp' => '081234567890',
            'kelas_id' => $kelas->id,
            'tutor_id' => $tutor->id,
            'tarif_per_jam' => 50000,
        ]);

        $csvContent = "nik_tutor_wajib,nomor_absen_siswa_nis_wajib,tanggal_presensi_yyyy_mm_dd_wajib,jam_mulai_hh_mm_wajib,jam_selesai_hh_mm_wajib,moda_pembelajaran_tatap_muka_home_visit_online_wajib,status_hadir_izin_sakit_alpha_wajib,lokasi_mulai_opsional,lokasi_selesai_opsional,keterangan_opsional\n";
        $csvContent .= "{$user->nik},SISWA-PRE-1,2026-09-10,08:00,10:00,home_visit,hadir,Rumah Siswa,Rumah Siswa,Kegiatan Belajar Khusus\n";

        $file = UploadedFile::fake()->createWithContent('presensi.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.laporan.importExcel'), [
            'file_excel' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => '2026-09-10',
            'moda_pembelajaran' => 'kunjungan_rumah',
            'status' => 'hadir',
        ]);
    }
}
