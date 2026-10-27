<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\PengajuanIzinSakit;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanIzinSakitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_tutor_can_submit_pengajuan_izin_with_document(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor Test Izin',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);

        $file = UploadedFile::fake()->create('surat_dokter.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('tutor.pengajuan-izin.store'), [
            'jenis' => 'sakit',
            'tgl_mulai' => now()->toDateString(),
            'tgl_selesai' => now()->addDays(1)->toDateString(),
            'alasan' => 'Demam tinggi dan disarankan istirahat oleh dokter.',
            'dokumen_surat' => $file,
        ]);

        $response->assertRedirect(route('tutor.pengajuan-izin'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengajuan_izin_sakit', [
            'tutor_id' => $tutor->id,
            'jenis' => 'sakit',
            'status' => 'pending',
        ]);

        $pengajuan = PengajuanIzinSakit::first();
        $this->assertNotNull($pengajuan->dokumen_surat);
        Storage::disk('public')->assertExists($pengajuan->dokumen_surat);
    }

    public function test_kepsek_can_approve_pengajuan_izin_and_syncs_presensis(): void
    {
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nik' => $tutorUser->nik,
            'nama_tutor' => 'Tutor Test Izin 2',
            'nama_lengkap' => $tutorUser->nama_lengkap,
            'email' => $tutorUser->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Test', 'tingkat' => 'SMA']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Test',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '099',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali Test',
        ]);

        $pengajuan = PengajuanIzinSakit::create([
            'tutor_id' => $tutor->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-09-20',
            'tgl_selesai' => '2026-09-21',
            'alasan' => 'Mengikuti kegiatan pelatihan daerah.',
            'status' => 'pending',
        ]);

        $kepsekUser = User::factory()->create(['role' => 'kepala_sekolah']);

        $response = $this->actingAs($kepsekUser)->patch(route('kepsek.pengajuan-izin.setujui', $pengajuan->id), [
            'catatan_verifikasi' => 'Pengajuan disetujui, selamat bertugas.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengajuan_izin_sakit', [
            'id' => $pengajuan->id,
            'status' => 'disetujui',
            'disetujui_oleh' => $kepsekUser->id,
        ]);

        // Auto-synced records in presensis for 2026-09-20 and 2026-09-21
        $this->assertDatabaseHas('presensi', [
            'tutor_id' => $tutor->id,
            'murid_id' => $siswa->id,
            'tgl_presensi' => '2026-09-20',
            'status' => 'izin',
        ]);

        $this->assertDatabaseHas('presensi', [
            'tutor_id' => $tutor->id,
            'murid_id' => $siswa->id,
            'tgl_presensi' => '2026-09-21',
            'status' => 'izin',
        ]);
    }

    public function test_admin_can_view_incoming_pengajuan_izin(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nik' => 'TTR999',
            'nama_tutor' => 'Tutor Sendi',
            'nama_lengkap' => 'Sendi Lestari',
            'email' => 'sendi@test.com',
            'no_hp' => '081234567890',
        ]);

        $pengajuan = PengajuanIzinSakit::create([
            'tutor_id' => $tutor->id,
            'jenis' => 'sakit',
            'tgl_mulai' => '2026-10-16',
            'tgl_selesai' => '2026-10-18',
            'alasan' => 'Izin tidak bisa masuk karena sakit.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.izin.index'));
        $response->assertOk();
        $response->assertSee('Sendi Lestari');
        $response->assertSee('Izin tidak bisa masuk karena sakit.');
        $response->assertSee('MENUNGGU VERIFIKASI');
    }

    public function test_admin_can_approve_pengajuan_izin_and_syncs_presensis(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nik' => 'TTR998',
            'nama_tutor' => 'Tutor Budi',
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'no_hp' => '081234567891',
        ]);
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Admin Izin', 'tingkat' => 'SMA']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Murid Budi',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '098',
            'no_hp' => '081234567891',
            'nama_wali' => 'Wali Murid',
        ]);

        $pengajuan = PengajuanIzinSakit::create([
            'tutor_id' => $tutor->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-20',
            'tgl_selesai' => '2026-10-20',
            'alasan' => 'Ada keperluan mendadak di luar kota.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($adminUser)->patch(route('admin.pengajuan-izin.setujui', $pengajuan->id), [
            'catatan_verifikasi' => 'Disetujui oleh Admin.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengajuan_izin_sakit', [
            'id' => $pengajuan->id,
            'status' => 'disetujui',
            'disetujui_oleh' => $adminUser->id,
        ]);

        $this->assertDatabaseHas('presensi', [
            'tutor_id' => $tutor->id,
            'murid_id' => $siswa->id,
            'tgl_presensi' => '2026-10-20',
            'status' => 'izin',
        ]);
    }

    public function test_admin_can_reject_pengajuan_izin(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nik' => 'TTR997',
            'nama_tutor' => 'Tutor Siti',
            'nama_lengkap' => 'Siti Nurhaliza',
            'email' => 'siti@test.com',
            'no_hp' => '081234567892',
        ]);

        $pengajuan = PengajuanIzinSakit::create([
            'tutor_id' => $tutor->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-22',
            'tgl_selesai' => '2026-10-22',
            'alasan' => 'Liburan pribadi ke luar kota.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($adminUser)->patch(route('admin.pengajuan-izin.tolak', $pengajuan->id), [
            'catatan_verifikasi' => 'Tidak memenuhi syarat izin KBM.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('pengajuan_izin_sakit', [
            'id' => $pengajuan->id,
            'status' => 'ditolak',
            'disetujui_oleh' => $adminUser->id,
        ]);
    }

    public function test_authorized_users_can_view_uploaded_dokumen(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('surat_dokter.pdf', 100, 'application/pdf');
        $path = $file->store('dokumen_izin', 'public');

        $adminUser = User::factory()->create(['role' => 'admin']);
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nik' => 'TTR996',
            'nama_tutor' => 'Tutor Rina',
            'nama_lengkap' => 'Rina Nose',
            'email' => 'rina@test.com',
            'no_hp' => '081234567899',
        ]);

        $otherTutorUser = User::factory()->create(['role' => 'tutor']);
        Tutor::create([
            'user_id' => $otherTutorUser->id,
            'nik' => 'TTR995',
            'nama_tutor' => 'Tutor Doni',
            'nama_lengkap' => 'Doni Salmanan',
            'email' => 'doni@test.com',
            'no_hp' => '081234567898',
        ]);

        $pengajuan = PengajuanIzinSakit::create([
            'tutor_id' => $tutor->id,
            'jenis' => 'sakit',
            'tgl_mulai' => '2026-10-25',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Rawat inap demam berdarah.',
            'dokumen_surat' => $path,
            'status' => 'pending',
        ]);

        // Dokumen URL points to pengajuan-izin.dokumen route
        $this->assertEquals(route('pengajuan-izin.dokumen', $pengajuan->id), $pengajuan->dokumen_url);

        // Admin can view
        $resAdmin = $this->actingAs($adminUser)->get(route('pengajuan-izin.dokumen', $pengajuan->id));
        $resAdmin->assertOk();

        // Tutor owner can view
        $resOwner = $this->actingAs($tutorUser)->get(route('pengajuan-izin.dokumen', $pengajuan->id));
        $resOwner->assertOk();

        // Another tutor cannot view (403 Forbidden)
        $resOther = $this->actingAs($otherTutorUser)->get(route('pengajuan-izin.dokumen', $pengajuan->id));
        $resOther->assertForbidden();

        // Guest redirected to login
        auth()->logout();
        $resGuest = $this->get(route('pengajuan-izin.dokumen', $pengajuan->id));
        $resGuest->assertRedirect(route('login'));
    }
}
