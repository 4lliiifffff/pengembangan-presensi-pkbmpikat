<?php

namespace Tests\Feature;

use App\Models\KategoriTutorial;
use App\Models\kelas as Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Database\Seeders\KategoriTutorialSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DynamicKategoriTutorialPayrollTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KategoriTutorialSeeder::class);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->tutorUser = User::factory()->create(['role' => 'tutor']);
        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nik' => $this->tutorUser->nik,
            'nama_lengkap' => $this->tutorUser->nama_lengkap,
            'email' => $this->tutorUser->email,
            'no_hp' => '081234567890',
        ]);

        $this->kelas = Kelas::create(['nama_kelas' => 'Paket B']);
    }

    public function test_admin_can_view_and_create_kategori_tutorial(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kategori-tutorial.index'));
        $response->assertStatus(200);
        $response->assertSee('Tutorial Komunitas');
        $response->assertSee('Tutorial Distance Learning (DL)');

        $createResponse = $this->actingAs($this->admin)->post(route('admin.kategori-tutorial.store'), [
            'nama_kategori' => 'Tutorial Khusus Matrikulasi',
            'jenis_layanan' => 'komunitas',
            'durasi_jam' => 2.0,
            'nominal_honor' => 100000,
            'is_abk' => 0,
            'is_gabungan' => 0,
            'is_aktif' => 1,
            'urutan' => 7,
        ]);

        $createResponse->assertRedirect(route('admin.kategori-tutorial.index'));
        $this->assertDatabaseHas('kategori_tutorials', [
            'nama_kategori' => 'Tutorial Khusus Matrikulasi',
            'nominal_honor' => 100000,
        ]);
    }

    public function test_admin_can_toggle_kategori_status_and_update(): void
    {
        $kategori = KategoriTutorial::first();

        $response = $this->actingAs($this->admin)->patch(route('admin.kategori-tutorial.toggleStatus', $kategori));
        $response->assertRedirect(route('admin.kategori-tutorial.index'));
        $this->assertDatabaseHas('kategori_tutorials', [
            'id' => $kategori->id,
            'is_aktif' => false,
        ]);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.kategori-tutorial.update', $kategori), [
            'nama_kategori' => 'Tutorial Komunitas 2 Jam Update',
            'jenis_layanan' => 'komunitas',
            'durasi_jam' => 2.0,
            'nominal_honor' => 85000,
            'is_aktif' => 1,
        ]);

        $updateResponse->assertRedirect(route('admin.kategori-tutorial.index'));
        $this->assertDatabaseHas('kategori_tutorials', [
            'id' => $kategori->id,
            'nama_kategori' => 'Tutorial Komunitas 2 Jam Update',
            'nominal_honor' => 85000,
            'is_aktif' => true,
        ]);
    }

    public function test_admin_can_manage_siswa_abk_status(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.siswa.store'), [
            'no_absen' => 'SISWA-ABK-01',
            'nama_siswa' => 'Budi ABK',
            'is_abk' => 1,
            'no_hp' => '081122334455',
            'nama_wali' => 'Ayah Budi',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $response->assertRedirect(route('admin.siswa.index'));
        $this->assertDatabaseHas('siswas', [
            'no_absen' => 'SISWA-ABK-01',
            'is_abk' => true,
        ]);
    }

    public function test_presensi_auto_resolves_abk_rate_for_abk_student(): void
    {
        Storage::fake('public');

        $siswaAbk = Siswa::create([
            'no_absen' => 'SISWA-ABK-TEST',
            'nama_siswa' => 'Citra ABK',
            'is_abk' => true,
            'no_hp' => '081111222333',
            'nama_wali' => 'Wali Citra',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), [
            'mode' => 'mulai',
            'siswa_id' => [$siswaAbk->id],
            'moda_pembelajaran' => 'kunjungan_rumah',
            'durasi_pilihan' => 2.0,
            'foto' => UploadedFile::fake()->image('bukti_masuk.jpg'),
            'lokasi' => '-7.7828,110.3670',
            'lokasi_akurasi' => 15,
            'is_mock_location' => 0,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));

        $presensi = Presensi::where('siswa_id', $siswaAbk->id)->latest('id')->first();
        $this->assertNotNull($presensi);
        $this->assertEquals(2.0, $presensi->durasi_pilihan);
        // Sesuai SK: Komunitas ABK 2 jam = Rp 100.000
        $this->assertEquals(100000.00, $presensi->nominal_honor_snapshot);
    }

    public function test_presensi_resolves_regular_rate_for_non_abk_student(): void
    {
        Storage::fake('public');

        $siswaReguler = Siswa::create([
            'no_absen' => 'SISWA-REG-TEST',
            'nama_siswa' => 'Doni Reguler',
            'is_abk' => false,
            'no_hp' => '081111222444',
            'nama_wali' => 'Wali Doni',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), [
            'mode' => 'mulai',
            'siswa_id' => [$siswaReguler->id],
            'moda_pembelajaran' => 'kunjungan_rumah',
            'durasi_pilihan' => 2.0,
            'foto' => UploadedFile::fake()->image('bukti_masuk.jpg'),
            'lokasi' => '-7.7828,110.3670',
            'lokasi_akurasi' => 15,
            'is_mock_location' => 0,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));

        $presensi = Presensi::where('siswa_id', $siswaReguler->id)->latest('id')->first();
        $this->assertNotNull($presensi);
        // Sesuai SK: Komunitas Reguler 2 jam = Rp 75.000
        $this->assertEquals(75000.00, $presensi->nominal_honor_snapshot);
    }

    public function test_payroll_service_calculates_with_snapshot_and_sk_rules(): void
    {
        $siswaReg = Siswa::create([
            'no_absen' => 'SISWA-1',
            'nama_siswa' => 'Siswa Reguler',
            'is_abk' => false,
            'no_hp' => '0812345678',
            'nama_wali' => 'Wali 1',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $siswaAbk = Siswa::create([
            'no_absen' => 'SISWA-2',
            'nama_siswa' => 'Siswa ABK',
            'is_abk' => true,
            'no_hp' => '0812345679',
            'nama_wali' => 'Wali 2',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $date = Carbon::now();

        // 1. Sesi Komunitas Reguler 2 jam (Rp 75.000)
        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $siswaReg->id,
            'tgl_presensi' => $date->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'durasi_pilihan' => 2.0,
            'nominal_honor_snapshot' => 75000,
            'status' => 'hadir',
        ]);

        // 2. Sesi Komunitas ABK 2 jam (Rp 100.000)
        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $siswaAbk->id,
            'tgl_presensi' => $date->toDateString(),
            'jam_mulai' => '10:30:00',
            'jam_selesai' => '12:30:00',
            'durasi_pilihan' => 2.0,
            'nominal_honor_snapshot' => 100000,
            'status' => 'hadir',
        ]);

        // 3. Sesi Distance Learning ABK 1.5 jam (Rp 130.000)
        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $siswaAbk->id,
            'tgl_presensi' => $date->toDateString(),
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '14:30:00',
            'durasi_pilihan' => 1.5,
            'nominal_honor_snapshot' => 130000,
            'status' => 'hadir',
        ]);

        $service = app(PayrollService::class);
        $payroll = $service->calculateTutorPayroll($this->tutor, $date->month, $date->year);

        // Total Honor: 75.000 + 100.000 + 130.000 = 305.000
        $this->assertEquals(305000.00, $payroll['total_honor']);
        $this->assertEquals(3, $payroll['total_sesi_hadir']);
    }

    public function test_changing_master_rate_does_not_affect_past_snapshots(): void
    {
        $siswa = Siswa::create([
            'no_absen' => 'SISWA-IMMUTABLE',
            'nama_siswa' => 'Siswa Snapshot',
            'is_abk' => false,
            'no_hp' => '0812345678',
            'nama_wali' => 'Wali',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $date = Carbon::now();

        // Presensi dengan snapshot Rp 75.000
        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => $date->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'durasi_pilihan' => 2.0,
            'nominal_honor_snapshot' => 75000,
            'status' => 'hadir',
        ]);

        // Admin menaikkan tarif master menjadi Rp 90.000
        KategoriTutorial::where('jenis_layanan', 'komunitas')
            ->where('durasi_jam', 2.0)
            ->where('is_abk', false)
            ->update(['nominal_honor' => 90000]);

        // Hitung ulang payroll
        $service = app(PayrollService::class);
        $payroll = $service->calculateTutorPayroll($this->tutor, $date->month, $date->year);

        // Snapshot menjamin honor sesi lama tetap Rp 75.000
        $this->assertEquals(75000.00, $payroll['total_honor']);
    }

    public function test_backward_compatibility_with_legacy_null_category(): void
    {
        $siswa = Siswa::create([
            'no_absen' => 'SISWA-LEGACY',
            'nama_siswa' => 'Siswa Legacy',
            'is_abk' => false,
            'no_hp' => '0812345678',
            'nama_wali' => 'Wali',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $date = Carbon::now();

        // Data presensi legacy tanpa snapshot dan tanpa kategori_tutorial_id
        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => $date->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00', // 2 jam
            'durasi_pilihan' => null,
            'kategori_tutorial_id' => null,
            'nominal_honor_snapshot' => null,
            'status' => 'hadir',
        ]);

        $service = app(PayrollService::class);
        $payroll = $service->calculateTutorPayroll($this->tutor, $date->month, $date->year);

        // Standar SK Tutorial Komunitas = Rp 75.000
        $this->assertEquals(75000.00, $payroll['total_honor']);
    }

    public function test_presensi_online_distance_learning_auto_resolves_honor(): void
    {
        Storage::fake('public');

        $siswa = Siswa::create([
            'no_absen' => 'SISWA-DL-TEST',
            'nama_siswa' => 'Siswa DL ABK',
            'is_abk' => true,
            'no_hp' => '081234567899',
            'nama_wali' => 'Wali DL',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), [
            'mode' => 'mulai',
            'siswa_id' => [$siswa->id],
            'moda_pembelajaran' => 'online',
            'durasi_pilihan' => 1.5,
            'foto' => UploadedFile::fake()->image('bukti_masuk_dl.jpg'),
            'lokasi' => '-7.7828,110.3670',
            'lokasi_akurasi' => 10,
            'is_mock_location' => 0,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));

        $presensi = Presensi::where('siswa_id', $siswa->id)->latest('id')->first();
        $this->assertNotNull($presensi);
        $this->assertEquals(1.5, $presensi->durasi_pilihan);
        // Sesuai SK: Distance Learning 1,5 jam ABK = Rp 130.000
        $this->assertEquals(130000.00, $presensi->nominal_honor_snapshot);
    }

    public function test_admin_can_create_custom_dynamic_jenis_layanan_and_delete_protection(): void
    {
        // 1. Admin bisa membuat kategori dengan jenis layanan baru/kustom (misal: 'vokasi')
        $createResponse = $this->actingAs($this->admin)->post(route('admin.kategori-tutorial.store'), [
            'nama_kategori' => 'Kursus Vokasi Desain Grafis',
            'jenis_layanan' => 'Vokasi Kreatif', // Title case yang akan dinormalisasi
            'durasi_jam' => 3.0,
            'nominal_honor' => 125000,
            'is_abk' => 0,
            'is_gabungan' => 0,
            'is_aktif' => 1,
            'urutan' => 99,
        ]);

        $createResponse->assertRedirect(route('admin.kategori-tutorial.index'));
        $kategoriVokasi = KategoriTutorial::where('nama_kategori', 'Kursus Vokasi Desain Grafis')->first();
        $this->assertNotNull($kategoriVokasi);
        $this->assertEquals('vokasi kreatif', $kategoriVokasi->jenis_layanan);
        $this->assertEquals('Vokasi Kreatif', $kategoriVokasi->jenis_layanan_label);
        $this->assertEquals('badge-layanan-custom', $kategoriVokasi->jenis_layanan_badge_class);

        // 2. Kategori baru yang belum dipakai sama sekali bisa dihapus permanen
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.kategori-tutorial.destroy', $kategoriVokasi));
        $deleteResponse->assertRedirect(route('admin.kategori-tutorial.index'));
        $this->assertDatabaseMissing('kategori_tutorials', ['id' => $kategoriVokasi->id]);

        // 3. Kategori yang sudah memiliki relasi (misal presensi) tidak bisa dihapus permanen, melainkan di-nonaktifkan
        $kategoriAktif = KategoriTutorial::first();
        $dummySiswa = Siswa::create([
            'no_absen' => 'SISWA-DEL-PROT',
            'nama_siswa' => 'Siswa Proteksi Hapus',
            'no_hp' => '081234567800',
            'nama_wali' => 'Wali Proteksi',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $dummySiswa->id,
            'tgl_presensi' => now()->toDateString(),
            'jam_mulai' => '08:00:00',
            'status' => 'hadir',
            'kategori_tutorial_id' => $kategoriAktif->id,
            'nominal_honor_snapshot' => $kategoriAktif->nominal_honor,
        ]);

        $safeDeleteResponse = $this->actingAs($this->admin)->delete(route('admin.kategori-tutorial.destroy', $kategoriAktif));
        $safeDeleteResponse->assertRedirect(route('admin.kategori-tutorial.index'));
        // Data tetap ada di database namun is_aktif menjadi false
        $this->assertDatabaseHas('kategori_tutorials', [
            'id' => $kategoriAktif->id,
            'is_aktif' => false,
        ]);
    }
}
