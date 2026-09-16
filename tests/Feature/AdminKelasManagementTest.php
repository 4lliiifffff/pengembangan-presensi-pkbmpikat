<?php

namespace Tests\Feature;

use App\Models\JenjangPaket;
use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\JenjangPaketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminKelasManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(JenjangPaketSeeder::class);
    }

    public function test_admin_can_view_kelas_index_with_stats_and_filters(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jpA = JenjangPaket::where('kode', 'paket_a')->first();
        $jpB = JenjangPaket::where('kode', 'paket_b')->first();

        Kelas::create([
            'nama_kelas' => 'Paket A - Kelas 1',
            'jenjang_paket_id' => $jpA->id,
            'tingkat' => '1',
        ]);

        Kelas::create([
            'nama_kelas' => 'Paket B - Kelas 7',
            'jenjang_paket_id' => $jpB->id,
            'tingkat' => '7',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kelas.index'));
        $response->assertOk();
        $response->assertSee('Paket A - Kelas 1');
        $response->assertSee('Paket B - Kelas 7');

        // Filter by jenjang kode or id
        $filteredResponse = $this->actingAs($admin)->get(route('admin.kelas.index', ['jenjang' => 'paket_b']));
        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Paket B - Kelas 7');
    }

    public function test_admin_can_create_new_kelas_with_structured_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jpB = JenjangPaket::where('kode', 'paket_b')->first();

        $response = $this->actingAs($admin)->post(route('admin.kelas.store'), [
            'nama_kelas' => 'Paket B - Kelas 8 Unggulan',
            'jenjang_paket_id' => $jpB->id,
            'tingkat' => '8',
            'keterangan' => 'Kelas pengayaan IPA & Bahasa Paket B',
        ]);

        $response->assertRedirect(route('admin.kelas.index'));
        $this->assertDatabaseHas('kelas', [
            'nama_kelas' => 'Paket B - Kelas 8 Unggulan',
            'jenjang_paket_id' => $jpB->id,
            'tingkat' => '8',
        ]);
    }

    public function test_admin_can_update_existing_kelas(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jpC = JenjangPaket::where('kode', 'paket_c')->first();

        $kelas = Kelas::create([
            'nama_kelas' => 'Paket C - Kelas 10',
            'jenjang_paket_id' => $jpC->id,
            'tingkat' => '10',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.kelas.update', $kelas), [
            'nama_kelas' => 'Paket C - Kelas 10 Unggulan',
            'jenjang_paket_id' => $jpC->id,
            'tingkat' => '10',
            'keterangan' => 'Kelas peminatan IPA & IT',
        ]);

        $response->assertRedirect(route('admin.kelas.index'));
        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'nama_kelas' => 'Paket C - Kelas 10 Unggulan',
            'jenjang_paket_id' => $jpC->id,
            'keterangan' => 'Kelas peminatan IPA & IT',
        ]);
    }

    public function test_admin_cannot_delete_kelas_that_has_students(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'Paket B - Kelas 8',
            'jenjang_paket' => 'paket_b',
            'tingkat' => '8',
        ]);

        Siswa::create([
            'no_absen' => 'TEST001',
            'nama_siswa' => 'Budi Siswa Test',
            'kelas_id' => $kelas->id,
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali Test',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.kelas.destroy', $kelas));
        $response->assertRedirect(route('admin.kelas.index'));
        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
        ]);
    }
}
