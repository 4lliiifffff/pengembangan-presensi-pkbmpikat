<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\PayrollService;
use Database\Seeders\KategoriTutorialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new KategoriTutorialSeeder)->run();
    }

    public function test_payroll_service_calculates_honorarium_based_on_sk_categories(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor Payroll',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket' => 'paket_c', 'tingkat' => 10]);

        // Siswa Reguler
        $siswaA = Siswa::create([
            'nama_siswa' => 'Siswa A Reguler',
            'is_abk' => false,
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '101',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali A',
        ]);

        // Siswa ABK
        $siswaB = Siswa::create([
            'nama_siswa' => 'Siswa B ABK',
            'is_abk' => true,
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '102',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali B',
        ]);

        // Presensi 1: Siswa A (Tutorial Komunitas Reguler 2 jam) -> Rp 75.000,-
        Presensi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaA->id,
            'tgl_presensi' => '2026-09-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'nominal_honor_snapshot' => 75000.00,
            'status' => 'hadir',
        ]);

        // Presensi 2: Siswa B (Tutorial Komunitas ABK 2 jam) -> Rp 100.000,-
        Presensi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaB->id,
            'tgl_presensi' => '2026-09-11',
            'jam_mulai' => '10:30',
            'jam_selesai' => '12:30',
            'nominal_honor_snapshot' => 100000.00,
            'status' => 'hadir',
        ]);

        $service = new PayrollService;
        $payroll = $service->calculateTutorPayroll($tutor, 9, 2026);

        $this->assertEquals(2, $payroll['total_sesi_hadir']);
        $this->assertEquals(4.0, $payroll['total_jam']);
        $this->assertEquals(175000.0, $payroll['total_honor']);
        $this->assertEquals('Rp 175.000', $payroll['formatted_total_honor']);
        $this->assertCount(2, $payroll['siswa_summary']);
    }

    public function test_admin_can_access_payroll_dashboard_and_export_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.payroll.index'));
        $response->assertStatus(200);

        $rekapPdfResponse = $this->actingAs($admin)->get(route('admin.payroll.rekap-pdf'));
        $rekapPdfResponse->assertStatus(200);
        $rekapPdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_tutor_can_access_own_payroll_and_export_pdf(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor Mandiri',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);

        $response = $this->actingAs($user)->get(route('tutor.payroll.index'));
        $response->assertStatus(200);

        $pdfResponse = $this->actingAs($user)->get(route('tutor.payroll.pdf'));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_kepsek_can_access_payroll_and_export_pdf(): void
    {
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);

        $response = $this->actingAs($kepsek)->get(route('kepsek.payroll.index'));
        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Penggajian');

        $rekapPdfResponse = $this->actingAs($kepsek)->get(route('kepsek.payroll.rekap-pdf'));
        $rekapPdfResponse->assertStatus(200);
        $rekapPdfResponse->assertHeader('Content-Type', 'application/pdf');
    }
}
