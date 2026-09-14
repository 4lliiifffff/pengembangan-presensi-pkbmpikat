<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_service_calculates_honorarium_based_on_student_hourly_rates(): void
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
        $kelas = kelas::create(['nama_kelas' => 'Kelas Payroll', 'tingkat' => 'SMA']);

        // Siswa A: tarif Rp 60.000 / jam
        $siswaA = Siswa::create([
            'nama_siswa' => 'Siswa A',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '101',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali A',
            'tarif_per_jam' => 60000.00,
        ]);

        // Siswa B: tarif Rp 75.000 / jam
        $siswaB = Siswa::create([
            'nama_siswa' => 'Siswa B',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '102',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali B',
            'tarif_per_jam' => 75000.00,
        ]);

        // Presensi 1: Siswa A (2 jam: 08:00 - 10:00) -> 2 x 60.000 = 120.000
        Presensi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaA->id,
            'tgl_presensi' => '2026-09-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'status' => 'hadir',
        ]);

        // Presensi 2: Siswa B (2 jam: 10:30 - 12:30) -> 2 x 75.000 = 150.000
        Presensi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaB->id,
            'tgl_presensi' => '2026-09-11',
            'jam_mulai' => '10:30',
            'jam_selesai' => '12:30',
            'status' => 'hadir',
        ]);

        $service = new PayrollService;
        $payroll = $service->calculateTutorPayroll($tutor, 9, 2026);

        $this->assertEquals(2, $payroll['total_sesi_hadir']);
        $this->assertEquals(4.0, $payroll['total_jam']);
        $this->assertEquals(270000.0, $payroll['total_honor']);
        $this->assertEquals('Rp 270.000', $payroll['formatted_total_honor']);
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
}
