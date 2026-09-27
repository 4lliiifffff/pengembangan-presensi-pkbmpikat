<?php

namespace Tests\Feature;

use App\Models\JenjangPaket;
use App\Models\kelas as Kelas;
use App\Models\LokasiPresensi;
use App\Models\Magang;
use App\Models\PengajuanIzinSakit;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\PresensiMandiriSiswa;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPresensiTerpaduTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $kepsek;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected Siswa $siswa;

    protected Kelas $kelas;

    protected JenjangPaket $jenjang;

    protected LokasiPresensi $lokasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'nama_lengkap' => 'Admin Test']);
        $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'nama_lengkap' => 'Kepsek Test']);
        $this->tutorUser = User::factory()->create(['role' => 'tutor', 'nama_lengkap' => 'Tutor Test']);

        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nik' => '3273010101900001',
            'nama_lengkap' => 'Tutor Test',
            'email' => 'tutor@test.com',
            'no_hp' => '08123456789',
        ]);

        $this->jenjang = JenjangPaket::create([
            'kode' => 'paket_a',
            'nama_jenjang' => 'Paket A (Setara SD)',
            'urutan' => 1,
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'Kelas 6 SD',
            'jenjang_paket' => 'paket_a',
            'jenjang_paket_id' => $this->jenjang->id,
        ]);

        $this->siswa = Siswa::create([
            'no_absen' => 'SISWA-001',
            'nama_siswa' => 'Siswa Test 1',
            'nama_wali' => 'Wali Siswa 1',
            'no_hp' => '081298765432',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $this->lokasi = LokasiPresensi::create([
            'nama_lokasi' => 'Pusat PKBM Pikat',
            'latitude' => -6.9308526,
            'longitude' => 107.6074633,
            'radius_meter' => 100,
            'is_aktif' => true,
        ]);
    }

    public function test_admin_can_access_all_six_report_tabs(): void
    {
        $tabs = ['tutor', 'siswa', 'pegawai', 'magang', 'perizinan', 'matriks'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', ['tab' => $tab]));
            $response->assertStatus(200);
            $response->assertViewIs('admin.laporan.index');
            $response->assertViewHas('activeTab', $tab);
        }
    }

    public function test_tutor_and_kbm_report_calculates_duration_and_honor(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'lokasi_presensi_id' => $this->lokasi->id,
            'tgl_presensi' => $today->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'moda_pembelajaran' => 'sekolah',
            'status' => 'hadir',
            'nominal_honor_snapshot' => 75000,
            'lokasi_mulai' => '-6.9308526, 107.6074633',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'tab' => 'tutor',
            'start_date' => $today->toDateString(),
            'end_date' => $today->toDateString(),
        ]));

        $response->assertStatus(200);
        $tutorData = $response->viewData('tutorData');

        $this->assertEquals(1, $tutorData['totalSesi']);
        $this->assertEquals(1, $tutorData['totalHadir']);
        $this->assertEquals(2.0, $tutorData['totalJamKbm']);
        $this->assertEquals(75000, $tutorData['totalHonorEstimasi']);
    }

    public function test_siswa_mandiri_report_shows_records_and_lateness(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        PresensiMandiriSiswa::create([
            'siswa_id' => $this->siswa->id,
            'lokasi_presensi_id' => $this->lokasi->id,
            'tgl_presensi' => $today->toDateString(),
            'jam_masuk' => '08:15',
            'status' => 'hadir',
            'status_kehadiran' => 'terlambat',
            'menit_keterlambatan' => 15,
            'lokasi_masuk' => '-6.9308526, 107.6074633',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'tab' => 'siswa',
            'start_date' => $today->toDateString(),
            'end_date' => $today->toDateString(),
        ]));

        $response->assertStatus(200);
        $siswaData = $response->viewData('siswaData');

        $this->assertEquals(1, $siswaData['totalPresensi']);
        $this->assertEquals(1, $siswaData['totalHadir']);
        $this->assertEquals(1, $siswaData['totalTerlambat']);
    }

    public function test_pegawai_and_magang_reports_display_accurately(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        // Pegawai
        PresensiKaryawan::create([
            'user_id' => $this->admin->id,
            'lokasi_presensi_id' => $this->lokasi->id,
            'tgl_presensi' => $today->toDateString(),
            'jam_mulai' => '07:30',
            'jam_selesai' => '16:00',
            'status' => 'hadir',
        ]);

        // Magang
        $magangUser = User::factory()->create(['role' => 'magang', 'nama_lengkap' => 'Alif Magang']);
        Magang::create([
            'user_id' => $magangUser->id,
            'nim_nisn' => '12345678',
            'nama_lengkap' => 'Alif Magang',
            'asal_instansi' => 'Universitas Pendidikan Indonesia',
            'jurusan' => 'Pendidikan Luar Sekolah',
        ]);

        PresensiKaryawan::create([
            'user_id' => $magangUser->id,
            'lokasi_presensi_id' => $this->lokasi->id,
            'tgl_presensi' => $today->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'status' => 'hadir',
        ]);

        // Test Pegawai Tab
        $resPegawai = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'tab' => 'pegawai',
            'start_date' => $today->toDateString(),
            'end_date' => $today->toDateString(),
        ]));
        $resPegawai->assertStatus(200);
        $pegawaiData = $resPegawai->viewData('pegawaiData');
        $this->assertEquals(1, $pegawaiData['totalHadir']);

        // Test Magang Tab
        $resMagang = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'tab' => 'magang',
            'start_date' => $today->toDateString(),
            'end_date' => $today->toDateString(),
        ]));
        $resMagang->assertStatus(200);
        $magangData = $resMagang->viewData('magangData');
        $this->assertEquals(1, $magangData['totalHadir']);
        $this->assertEquals(4.0, $magangData['totalJamKerja']);
    }

    public function test_perizinan_tab_aggregates_both_izin_and_lupa_lapor(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        PengajuanIzinSakit::create([
            'tutor_id' => $this->tutor->id,
            'jenis' => 'sakit',
            'tgl_mulai' => $today->toDateString(),
            'tgl_selesai' => $today->toDateString(),
            'alasan' => 'Demam tinggi',
            'status' => 'disetujui',
            'disetujui_oleh' => $this->kepsek->id,
        ]);

        PengajuanLupaLapor::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal' => $today->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'alasan' => 'Koneksi internet bermasalah saat check-out',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'tab' => 'perizinan',
            'start_date' => $today->toDateString(),
            'end_date' => $today->toDateString(),
        ]));

        $response->assertStatus(200);
        $perizinanData = $response->viewData('perizinanData');

        $this->assertEquals(1, $perizinanData['totalIzinSakit']);
        $this->assertEquals(1, $perizinanData['totalLupaLapor']);
        $this->assertEquals(1, $perizinanData['totalPending']);
        $this->assertEquals(1, $perizinanData['totalDisetujui']);
    }

    public function test_matriks_bulanan_generates_days_1_to_31_and_percentage(): void
    {
        $bulan = 9;
        $tahun = 2026;

        PresensiMandiriSiswa::create([
            'siswa_id' => $this->siswa->id,
            'lokasi_presensi_id' => $this->lokasi->id,
            'tgl_presensi' => '2026-09-01',
            'jam_masuk' => '08:00',
            'status' => 'hadir',
            'status_kehadiran' => 'tepat_waktu',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'tab' => 'matriks',
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]));

        $response->assertStatus(200);
        $matriksData = $response->viewData('matriksData');

        $this->assertEquals(30, $matriksData['daysInMonth']); // Sept has 30 days
        $this->assertCount(1, $matriksData['matrix']);

        $rowSiswa = $matriksData['matrix'][0];
        $this->assertEquals('H', $rowSiswa['days'][1]['status']);
        $this->assertEquals(1, $rowSiswa['hadir']);
        $this->assertEquals(100, $rowSiswa['keaktifan']);
    }

    public function test_admin_can_export_pdf_and_excel(): void
    {
        // Test PDF Export
        $pdfRes = $this->actingAs($this->admin)->get(route('admin.laporan.exportPdf', [
            'tab' => 'tutor',
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
        ]));
        $pdfRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfRes->headers->get('content-type'));

        // Test PDF Export Matriks
        $pdfMatriksRes = $this->actingAs($this->admin)->get(route('admin.laporan.exportPdf', [
            'tab' => 'matriks',
            'bulan' => 9,
            'tahun' => 2026,
        ]));
        $pdfMatriksRes->assertStatus(200);

        // Test Excel Export
        $excelRes = $this->actingAs($this->admin)->get(route('admin.laporan.exportExcel'));
        $excelRes->assertStatus(200);
    }
}
