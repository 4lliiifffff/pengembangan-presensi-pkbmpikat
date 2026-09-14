<?php

namespace Tests\Feature;

use App\Models\kelas;
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
        $kelas = kelas::create(['nama_kelas' => 'Kelas Test', 'tingkat' => 'SMA']);
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
        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => '2026-09-20',
            'status' => 'izin',
        ]);

        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => '2026-09-21',
            'status' => 'izin',
        ]);
    }
}
