<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMuridFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Tutor $tutorA;

    protected Tutor $tutorB;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'Paket A Tingkat 1',
            'tingkat' => 1,
            'jenjang_paket' => 'paket_a',
        ]);

        $userTutorA = User::factory()->create(['role' => 'tutor', 'nama_lengkap' => 'Tutor Andi']);
        $this->tutorA = Tutor::create([
            'user_id' => $userTutorA->id,
            'nama_lengkap' => 'Tutor Andi',
            'email' => 'tutor.andi@example.com',
            'nik' => '1234567890123456',
            'status' => 'aktif',
        ]);

        $userTutorB = User::factory()->create(['role' => 'tutor', 'nama_lengkap' => 'Tutor Budi']);
        $this->tutorB = Tutor::create([
            'user_id' => $userTutorB->id,
            'nama_lengkap' => 'Tutor Budi',
            'email' => 'tutor.budi@example.com',
            'nik' => '1234567890123457',
            'status' => 'aktif',
        ]);

        // Murid 1: Dibimbing Tutor A
        Murid::create([
            'no_absen' => 'M001',
            'nama_murid' => 'Bintang Pratama',
            'status_murid' => 'aktif',
            'no_hp' => '08123456789',
            'nama_wali' => 'Wali Bintang',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutorA->id,
        ]);

        // Murid 2: Dibimbing Tutor B
        Murid::create([
            'no_absen' => 'M002',
            'nama_murid' => 'Citra Lestari',
            'status_murid' => 'aktif',
            'no_hp' => '08123456788',
            'nama_wali' => 'Wali Citra',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutorB->id,
        ]);

        // Murid 3: Belum memiliki pembimbing
        Murid::create([
            'no_absen' => 'M003',
            'nama_murid' => 'Dimas Anggara',
            'status_murid' => 'aktif',
            'no_hp' => '08123456787',
            'nama_wali' => 'Wali Dimas',
            'kelas_id' => $this->kelas->id,
            'tutor_id' => null,
        ]);
    }

    public function test_admin_can_filter_murid_who_have_pembimbing(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.murid.index', [
            'pembimbing' => 'ada',
        ]));

        $response->assertOk();
        $response->assertSee('Bintang Pratama');
        $response->assertSee('Citra Lestari');
        $response->assertDontSee('Dimas Anggara');
    }

    public function test_admin_can_filter_murid_who_do_not_have_pembimbing(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.murid.index', [
            'pembimbing' => 'belum',
        ]));

        $response->assertOk();
        $response->assertSee('Dimas Anggara');
        $response->assertDontSee('Bintang Pratama');
        $response->assertDontSee('Citra Lestari');
        $response->assertSee('Belum Ditugaskan');
    }

    public function test_admin_can_filter_murid_by_specific_tutor(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.murid.index', [
            'tutor_id' => $this->tutorA->id,
        ]));

        $response->assertOk();
        $response->assertSee('Bintang Pratama');
        $response->assertDontSee('Citra Lestari');
        $response->assertDontSee('Dimas Anggara');
    }

    public function test_admin_can_export_filtered_murid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.murid.exportExcel', [
            'pembimbing' => 'belum',
        ]));

        $response->assertOk();
    }
}
