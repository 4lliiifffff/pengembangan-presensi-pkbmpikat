<?php

namespace Tests\Feature;

use App\Models\JadwalKerja;
use App\Models\KategoriTutorial;
use App\Models\PresensiKaryawan;
use App\Models\User;
use App\Services\ShiftPresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalKerjaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $tutorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->tutorUser = User::factory()->create([
            'role' => 'tutor',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_jadwal_kerja_crud(): void
    {
        $response = $this->actingAs($this->tutorUser)->get(route('admin.jadwal-kerja.index'));
        $response->assertRedirect(route('tutor.dashboard'));
    }

    public function test_admin_can_view_jadwal_kerja_index(): void
    {
        JadwalKerja::create([
            'nama_shift' => 'Shift Pagi Kantor',
            'kode_shift' => 'PAGI_TEST',
            'jenis_shift' => 'umum',
            'jam_masuk' => '07:30',
            'jam_pulang' => '15:30',
            'durasi_jam' => 8.00,
            'earliest_minutes' => 30,
            'tolerance_minutes' => 30,
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.jadwal-kerja.index'));
        $response->assertOk();
        $response->assertSee('Shift Pagi Kantor');
        $response->assertSee('PAGI_TEST');
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.jadwal-kerja.create'));
        $response->assertOk();
        $response->assertSee('Tambah Jadwal');
    }

    public function test_admin_can_store_new_shift_with_kategori_integration(): void
    {
        $kategori = KategoriTutorial::create([
            'kode_kategori' => 'KAT_TEST',
            'nama_kategori' => 'Tutorial Kelompok',
            'honor_per_jam' => 50000,
            'durasi_jam' => 2.00,
        ]);

        $payload = [
            'nama_shift' => 'Shift Tutorial Kelompok',
            'kode_shift' => 'TUT_KELOMPOK',
            'jenis_shift' => 'kbm',
            'kategori_tutorial_id' => $kategori->id,
            'jam_masuk' => '09:00',
            'jam_pulang' => '11:00',
            'durasi_jam' => 5.00, // Should be overridden by kategori's 2.00
            'earliest_minutes' => 30,
            'tolerance_minutes' => 30,
            'is_aktif' => '1',
            'urutan' => 1,
            'keterangan' => 'Shift KBM Kelompok',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.jadwal-kerja.store'), $payload);
        $response->assertRedirect(route('admin.jadwal-kerja.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_kerjas', [
            'kode_shift' => 'TUT_KELOMPOK',
            'durasi_jam' => 2.00,
            'kategori_tutorial_id' => $kategori->id,
        ]);
    }

    public function test_admin_can_update_existing_shift(): void
    {
        $shift = JadwalKerja::create([
            'nama_shift' => 'Shift Awal',
            'kode_shift' => 'SHIFT_AWAL',
            'jenis_shift' => 'umum',
            'jam_masuk' => '08:00',
            'jam_pulang' => '16:00',
            'durasi_jam' => 8.00,
            'earliest_minutes' => 30,
            'tolerance_minutes' => 30,
            'is_aktif' => true,
        ]);

        $payload = [
            'nama_shift' => 'Shift Diperbarui',
            'kode_shift' => 'SHIFT_AWAL',
            'jenis_shift' => 'umum',
            'jam_masuk' => '08:30',
            'jam_pulang' => '16:30',
            'durasi_jam' => 8.00,
            'earliest_minutes' => 45,
            'tolerance_minutes' => 15,
            'is_aktif' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('admin.jadwal-kerja.update', $shift), $payload);
        $response->assertRedirect(route('admin.jadwal-kerja.index'));

        $this->assertDatabaseHas('jadwal_kerjas', [
            'id' => $shift->id,
            'nama_shift' => 'Shift Diperbarui',
            'jam_masuk' => '08:30:00',
            'earliest_minutes' => 45,
            'tolerance_minutes' => 15,
        ]);
    }

    public function test_admin_can_toggle_shift_status(): void
    {
        $shift = JadwalKerja::create([
            'nama_shift' => 'Shift Toggle',
            'kode_shift' => 'TOGGLE_TEST',
            'jenis_shift' => 'umum',
            'jam_masuk' => '08:00',
            'jam_pulang' => '16:00',
            'durasi_jam' => 8.00,
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->patch(route('admin.jadwal-kerja.toggleStatus', $shift));
        $response->assertRedirect();

        $this->assertDatabaseHas('jadwal_kerjas', [
            'id' => $shift->id,
            'is_aktif' => false,
        ]);
    }

    public function test_admin_can_delete_unused_shift(): void
    {
        $shift = JadwalKerja::create([
            'nama_shift' => 'Shift Delete',
            'kode_shift' => 'DEL_TEST',
            'jenis_shift' => 'umum',
            'jam_masuk' => '08:00',
            'jam_pulang' => '16:00',
            'durasi_jam' => 8.00,
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.jadwal-kerja.destroy', $shift));
        $response->assertRedirect(route('admin.jadwal-kerja.index'));

        $this->assertDatabaseMissing('jadwal_kerjas', [
            'id' => $shift->id,
        ]);
    }

    public function test_deleting_used_shift_deactivates_it_safely(): void
    {
        $shift = JadwalKerja::create([
            'nama_shift' => 'Shift Digunakan',
            'kode_shift' => 'USED_SHIFT',
            'jenis_shift' => 'umum',
            'jam_masuk' => '08:00',
            'jam_pulang' => '16:00',
            'durasi_jam' => 8.00,
            'is_aktif' => true,
        ]);

        PresensiKaryawan::create([
            'user_id' => $this->adminUser->id,
            'jadwal_kerja_id' => $shift->id,
            'tgl_presensi' => now()->toDateString(),
            'jam_masuk' => '08:00:00',
            'shift_nama' => $shift->nama_shift,
            'status_kehadiran' => 'tepat_waktu',
            'menit_keterlambatan' => 0,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.jadwal-kerja.destroy', $shift));
        $response->assertRedirect(route('admin.jadwal-kerja.index'));

        // Still exists in DB but deactivated
        $this->assertDatabaseHas('jadwal_kerjas', [
            'id' => $shift->id,
            'is_aktif' => false,
        ]);
    }

    public function test_shift_presensi_service_evaluates_status_and_late_minutes(): void
    {
        $shift = JadwalKerja::create([
            'nama_shift' => 'Shift Uji Masuk',
            'kode_shift' => 'UJI_MASUK',
            'jenis_shift' => 'umum',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'durasi_jam' => 8.00,
            'earliest_minutes' => 30,
            'tolerance_minutes' => 30,
            'is_aktif' => true,
            'urutan' => 1,
        ]);

        $service = new ShiftPresensiService;

        // On time (07:50 WIB for 08:00 shift with 30 min tolerance)
        $evalOnTime = $service->evaluateCheckIn(
            time: Carbon::parse(now()->toDateString().' 07:50:00', 'Asia/Jakarta'),
            shiftKey: 'UJI_MASUK'
        );
        $this->assertEquals('tepat_waktu', $evalOnTime['status_kehadiran']);
        $this->assertEquals(0, $evalOnTime['menit_keterlambatan']);

        // Late (08:35 WIB for 08:00 shift with 30 min tolerance -> 35 min late past start)
        $evalLate = $service->evaluateCheckIn(
            time: Carbon::parse(now()->toDateString().' 08:35:00', 'Asia/Jakarta'),
            shiftKey: 'UJI_MASUK'
        );
        $this->assertEquals('terlambat', $evalLate['status_kehadiran']);
        $this->assertEquals(35, $evalLate['menit_keterlambatan']);

        // Early (07:15 WIB for 08:00 shift with 30 min earliest window)
        $evalEarly = $service->evaluateCheckIn(
            time: Carbon::parse(now()->toDateString().' 07:15:00', 'Asia/Jakarta'),
            shiftKey: 'UJI_MASUK'
        );
        $this->assertEquals('lebih_awal', $evalEarly['status_kehadiran']);
        $this->assertEquals(0, $evalEarly['menit_keterlambatan']);
    }
}
