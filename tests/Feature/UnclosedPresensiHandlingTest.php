<?php

namespace Tests\Feature;

use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\KategoriTutorial;
use App\Models\kelas as Kelas;
use App\Models\LokasiPresensi;
use App\Models\Magang;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnclosedPresensiHandlingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected User $karyawanUser;

    protected User $magangUser;

    protected Magang $magang;

    protected User $kepsekUser;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected LokasiPresensi $lokasi;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        Config::set('lokasi.sekolah_lat', -7.8011945);
        Config::set('lokasi.sekolah_lng', 110.364917);
        Config::set('lokasi.radius_meter', 100);

        $this->tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nama_lengkap' => 'Tutor Unclosed Test',
            'email' => 'tutor_unclosed@pkbmpikat.com',
            'nik' => 'NIKUNC01',
        ]);

        $this->karyawanUser = User::factory()->create(['role' => 'karyawan', 'is_active' => 1]);

        $this->magangUser = User::factory()->create(['role' => 'magang', 'is_active' => 1]);
        $this->magang = Magang::create([
            'user_id' => $this->magangUser->id,
            'nama_lengkap' => 'Magang Unclosed Test',
            'asal_kampus' => 'Universitas Negeri Yogyakarta',
            'jurusan' => 'Teknologi Pendidikan',
            'status' => 'aktif',
            'tanggal_mulai' => now()->subMonth(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $this->kepsekUser = User::factory()->create(['role' => 'kepala_sekolah', 'is_active' => 1]);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_c_unc'], ['nama_jenjang' => 'Paket C Unclosed', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 11 Paket C Unclosed'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '11']);
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_siswa' => 'Siswa Unclosed Test',
            'nama_wali' => 'Wali Unclosed Test',
            'kelas_id' => $kls->id,
            'tutor_id' => $this->tutor->id,
            'status_siswa' => 'aktif',
            'no_absen' => 'UNC-01',
            'no_hp' => '081233445566',
            'is_abk' => false,
        ]);

        $this->lokasi = LokasiPresensi::create([
            'nama_lokasi' => 'Pusat PKBM Pikat',
            'latitude' => -7.8011945,
            'longitude' => 110.364917,
            'radius_meter' => 100,
            'is_aktif' => true,
        ]);
    }

    public function test_tutor_with_unclosed_session_yesterday_sees_warning_banner_and_can_still_clock_in_today(): void
    {
        $yesterday = Carbon::now('Asia/Jakarta')->subDay()->toDateString();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Presensi kemarin yang lupa clock out
        Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tgl_presensi' => $yesterday,
            'jam_mulai' => '08:30:00',
            'foto_mulai' => 'presensi/test/masuk.jpg',
            'jam_selesai' => null,
            'foto_selesai' => null,
            'status' => 'proses',
        ]);

        // Kunjungi halaman presensi hari ini
        $response = $this->actingAs($this->tutorUser)->get(route('tutor.presensi'));
        $response->assertStatus(200);
        $response->assertSee('Presensi Pulang Terlewat pada');
        $response->assertSee('Ajukan Lupa Lapor Sesi Ini');
        $response->assertViewHas('unclosedPastSession', function ($session) use ($yesterday) {
            return $session && (string) $session->tgl_presensi === $yesterday;
        });

        // Absen masuk hari ini tetap berhasil tanpa diblokir
        $postResponse = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), [
            'siswa_id' => [$this->siswa->id],
            'mode' => 'mulai',
            'foto' => UploadedFile::fake()->image('today_in.jpg'),
            'lokasi' => '-7.8011945,110.364917',
            'lokasi_presensi_id' => $this->lokasi->id,
            'moda_pembelajaran' => 'sekolah',
        ]);

        $postResponse->assertRedirect(route('tutor.dashboard'));

        $this->assertDatabaseHas('presensi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'tgl_presensi' => $today,
        ]);
    }

    public function test_karyawan_with_unclosed_session_yesterday_sees_warning_banner(): void
    {
        $yesterday = Carbon::now('Asia/Jakarta')->subDay()->toDateString();
        $adminUser = User::factory()->create(['role' => 'admin', 'is_active' => 1]);

        PresensiKaryawan::create([
            'user_id' => $adminUser->id,
            'tgl_presensi' => $yesterday,
            'jam_mulai' => '08:00:00',
            'foto_mulai' => 'presensi_karyawan/test/masuk.jpg',
            'jam_selesai' => null,
            'foto_selesai' => null,
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.presensi'));
        $response->assertStatus(200);
        $response->assertSee('Presensi Pulang Terlewat pada');
        $response->assertViewHas('unclosedPastSession');
    }

    public function test_magang_with_unclosed_session_yesterday_sees_warning_banner(): void
    {
        $yesterday = Carbon::now('Asia/Jakarta')->subDay()->toDateString();

        PresensiKaryawan::create([
            'user_id' => $this->magangUser->id,
            'tgl_presensi' => $yesterday,
            'jam_mulai' => '08:00:00',
            'foto_mulai' => 'presensi_karyawan/test/masuk.jpg',
            'jam_selesai' => null,
            'foto_selesai' => null,
        ]);

        $response = $this->actingAs($this->magangUser)->get(route('magang.presensi'));
        $response->assertStatus(200);
        $response->assertSee('Presensi Pulang Terlewat pada');
        $response->assertViewHas('unclosedPastSession');
    }

    public function test_lupa_lapor_form_is_prefilled_with_query_parameters(): void
    {
        $targetDate = '2026-09-19';

        $response = $this->actingAs($this->tutorUser)->get(route('tutor.lupa-lapor', [
            'tanggal' => $targetDate,
            'siswa_id' => $this->siswa->id,
            'jam_mulai' => '09:15',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetDate);
        $response->assertSee('09:15');
        $response->assertSee('Formulir telah diisi otomatis');
    }

    public function test_kepsek_approval_of_lupa_lapor_syncs_presensi_and_jadwal_sesi_to_selesai_and_counts_in_payroll(): void
    {
        $sessionDate = Carbon::now('Asia/Jakarta')->subDays(2)->toDateString();

        $kat = KategoriTutorial::firstOrCreate(
            ['durasi_jam' => 2.0],
            ['nama_kategori' => 'KBM 2 Jam Test', 'nominal_honor' => 75000, 'is_active' => true]
        );

        // Jadwal sesi KBM yang masih berstatus 'berlangsung' karena belum clock out
        $jadwalSesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $kat->id,
            'tanggal_rencana' => $sessionDate,
            'jam_masuk_rencana' => '10:00:00',
            'jam_pulang_rencana' => '12:00:00',
            'durasi_jam' => 2.0,
            'status' => 'berlangsung',
        ]);

        // Tutor membuat pengajuan Lupa Lapor
        $lupaLapor = PengajuanLupaLapor::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal' => $sessionDate,
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'alasan' => 'Lupa klik selesai karena langsung koordinasi dengan orang tua murid.',
            'status' => 'pending',
        ]);

        // Kepala sekolah menyetujui
        $response = $this->actingAs($this->kepsekUser)->patch(route('kepsek.lupa-lapor.setujui', $lupaLapor->id), [
            'catatan_kepsek' => 'Disetujui dan telah diverifikasi.',
        ]);

        $response->assertRedirect();

        // 1. Presensi terupdate dengan jam selesai
        $this->assertDatabaseHas('presensi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'tgl_presensi' => $sessionDate,
            'jam_selesai' => '12:00',
            'status' => 'hadir',
        ]);

        // 2. JadwalSesi terupdate ke selesai
        $jadwalSesi->refresh();
        $this->assertEquals('selesai', $jadwalSesi->status);

        // 3. PayrollService menghitung sesi ini
        $payrollService = app(PayrollService::class);
        $carbonDate = Carbon::parse($sessionDate);
        $payrollData = $payrollService->calculateTutorPayroll($this->tutor, (int) $carbonDate->format('m'), (int) $carbonDate->format('Y'));

        $this->assertGreaterThan(0, $payrollData['total_sesi_hadir']);
        $this->assertGreaterThan(0, $payrollData['total_honor']);
    }
}
