<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PwaOfflineTest extends TestCase
{
    public function test_pwa_manifest_is_accessible(): void
    {
        $this->assertTrue(File::exists(public_path('manifest.json')));

        $response = $this->get('/manifest.json');
        $response->assertStatus(200);

        $manifestContent = json_decode(File::get(public_path('manifest.json')), true);
        $this->assertEquals('Smart Presensi PKBM Pikat', $manifestContent['name']);
        $this->assertEquals('Presensi Pikat', $manifestContent['short_name']);
        $this->assertEquals('standalone', $manifestContent['display']);
    }

    public function test_service_worker_file_is_accessible(): void
    {
        $this->assertTrue(File::exists(public_path('sw.js')));

        $response = $this->get('/sw.js');
        $response->assertStatus(200);
    }
}
