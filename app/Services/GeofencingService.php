<?php

namespace App\Services;

use App\Models\LokasiPresensi;
use Illuminate\Support\Facades\Http;

class GeofencingService
{
    /**
     * Jari-jari rata-rata bumi dalam satuan meter.
     */
    public const EARTH_RADIUS_METERS = 6371000;

    /**
     * Mengekstrak latitude & longitude dari string "lat,lng" (contoh: "-7.8011945,110.364917").
     *
     * @return array{lat: float, lng: float}|null
     */
    public function parseCoordinates(?string $lokasi): ?array
    {
        if (! $lokasi) {
            return null;
        }

        $parts = explode(',', $lokasi);
        if (count($parts) < 2) {
            return null;
        }

        $lat = trim($parts[0]);
        $lng = trim($parts[1]);

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng,
        ];
    }

    /**
     * Menghitung jarak antara dua titik koordinat GPS menggunakan Rumus Haversine.
     *
     * @param  float  $lat1  Latitude titik 1 (derajat)
     * @param  float  $lng1  Longitude titik 1 (derajat)
     * @param  float  $lat2  Latitude titik 2 (derajat)
     * @param  float  $lng2  Longitude titik 2 (derajat)
     * @return float Jarak dalam satuan meter
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        // Konversi koordinat dari derajat ke radian
        $latRad1 = deg2rad($lat1);
        $lngRad1 = deg2rad($lng1);
        $latRad2 = deg2rad($lat2);
        $lngRad2 = deg2rad($lng2);

        // Selisih koordinat
        $deltaLat = $latRad2 - $latRad1;
        $deltaLng = $lngRad2 - $lngRad1;

        // Formula Haversine
        $a = sin($deltaLat / 2) ** 2
            + cos($latRad1) * cos($latRad2) * (sin($deltaLng / 2) ** 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Memeriksa apakah lokasi presensi berada dalam radius toleransi titik lokasi yang dipilih.
     *
     * @param  string|null  $lokasi  String koordinat "lat,lng" dari user
     * @param  int|null  $lokasiPresensiId  ID titik lokasi yang dipilih dari tabel lokasi_presensis
     * @param  float|null  $fallbackLat  Fallback Latitude jika ID tidak ditemukan / null
     * @param  float|null  $fallbackLng  Fallback Longitude jika ID tidak ditemukan / null
     * @param  float|null  $fallbackRadius  Fallback radius meter
     * @return array{is_valid: bool, distance: float, max_radius: float, lokasi_presensi: LokasiPresensi|null, message: string|null}
     */
    public function checkSelectedLokasiRadius(
        ?string $lokasi,
        ?int $lokasiPresensiId = null,
        ?float $fallbackLat = null,
        ?float $fallbackLng = null,
        ?float $fallbackRadius = null
    ): array {
        $lokasiModel = $lokasiPresensiId ? LokasiPresensi::find($lokasiPresensiId) : null;

        if (! $lokasiModel && ! $fallbackLat) {
            // Ambil lokasi aktif pertama jika ada
            $lokasiModel = LokasiPresensi::where('is_active', true)->first();
        }

        $namaLokasi = $lokasiModel ? $lokasiModel->nama_lokasi : config('lokasi.sekolah_nama', 'PKBM Pikat');
        $targetLat = $lokasiModel ? (float) $lokasiModel->latitude : ($fallbackLat ?? (float) config('lokasi.sekolah_lat', -7.8011945));
        $targetLng = $lokasiModel ? (float) $lokasiModel->longitude : ($fallbackLng ?? (float) config('lokasi.sekolah_lng', 110.364917));
        $maxRadius = $lokasiModel ? (float) $lokasiModel->radius_meter : ($fallbackRadius ?? (float) config('lokasi.radius_meter', 100));

        $coords = $this->parseCoordinates($lokasi);

        if (! $coords) {
            return [
                'is_valid' => false,
                'distance' => 0.0,
                'max_radius' => $maxRadius,
                'lokasi_presensi' => $lokasiModel,
                'message' => 'Koordinat lokasi GPS tidak valid atau tidak terdeteksi. Aktifkan GPS pada perangkat Anda.',
            ];
        }

        $distance = $this->calculateDistance(
            $coords['lat'],
            $coords['lng'],
            $targetLat,
            $targetLng
        );

        $isValid = $distance <= $maxRadius;
        $formattedDistance = round($distance, 1);

        $message = $isValid
            ? null
            : "Lokasi Anda berada di luar radius titik {$namaLokasi} (Jarak: {$formattedDistance} meter, Batas Maksimal: {$maxRadius} meter). Harap mendekat ke lokasi titik yang dipilih.";

        return [
            'is_valid' => $isValid,
            'distance' => $distance,
            'max_radius' => $maxRadius,
            'lokasi_presensi' => $lokasiModel,
            'message' => $message,
        ];
    }

    /**
     * Memeriksa apakah lokasi presensi berada dalam radius toleransi titik lokasi sekolah.
     *
     * @param  string|null  $lokasi  String koordinat "lat,lng" dari user
     * @param  float|null  $targetLat  Latitude pusat geofence (default: dari config lokasi.sekolah_lat)
     * @param  float|null  $targetLng  Longitude pusat geofence (default: dari config lokasi.sekolah_lng)
     * @param  float|null  $maxRadius  Radius maksimal meter (default: dari config lokasi.radius_meter)
     * @return array{is_valid: bool, distance: float, max_radius: float, message: string|null}
     */
    public function checkSekolahRadius(
        ?string $lokasi,
        ?float $targetLat = null,
        ?float $targetLng = null,
        ?float $maxRadius = null
    ): array {
        $targetLat ??= (float) config('lokasi.sekolah_lat', -7.8011945);
        $targetLng ??= (float) config('lokasi.sekolah_lng', 110.364917);
        $maxRadius ??= (float) config('lokasi.radius_meter', 100);

        $coords = $this->parseCoordinates($lokasi);

        if (! $coords) {
            return [
                'is_valid' => false,
                'distance' => 0.0,
                'max_radius' => $maxRadius,
                'message' => 'Koordinat lokasi GPS tidak valid atau tidak terdeteksi. Aktifkan GPS pada perangkat Anda.',
            ];
        }

        $distance = $this->calculateDistance(
            $coords['lat'],
            $coords['lng'],
            $targetLat,
            $targetLng
        );

        $isValid = $distance <= $maxRadius;
        $formattedDistance = round($distance, 1);

        $message = $isValid
            ? null
            : "Lokasi Anda berada di luar radius sekolah PKBM Pikat (Jarak: {$formattedDistance} meter, Maksimal: {$maxRadius} meter). Harap mendekat ke lokasi sekolah.";

        return [
            'is_valid' => $isValid,
            'distance' => $distance,
            'max_radius' => $maxRadius,
            'message' => $message,
        ];
    }

    /**
     * Memvalidasi integritas GPS dari manipulasi Fake GPS / Mock Location dan batas akurasi sinyal.
     *
     * @param  string|null  $lokasi  Koordinat "lat,lng"
     * @param  float|null  $accuracy  Tingkat akurasi GPS perangkat (meter)
     * @param  bool  $isMocked  Flag terdeteksi provider lokasi buatan
     * @param  float|null  $maxAccuracy  Batas toleransi akurasi maksimal (default: dari config lokasi.max_accuracy_meter)
     * @return array{is_valid: bool, message: string|null}
     */
    public function validateGpsIntegrity(
        ?string $lokasi,
        ?float $accuracy = null,
        bool $isMocked = false,
        ?float $maxAccuracy = null
    ): array {
        $maxAccuracy ??= (float) config('lokasi.max_accuracy_meter', 200);

        if ($isMocked) {
            return [
                'is_valid' => false,
                'message' => 'Terdeteksi penggunaan aplikasi pemalsu lokasi (Fake GPS) pada perangkat Anda. Harap nonaktifkan aplikasi tersebut untuk melakukan presensi.',
            ];
        }

        if ($accuracy !== null) {
            if ($accuracy <= 0) {
                return [
                    'is_valid' => false,
                    'message' => 'Sinyal GPS terdeteksi tidak valid (akurasi 0 meter). Harap gunakan sinyal GPS fisik perangkat asli.',
                ];
            }

            if ($accuracy > $maxAccuracy) {
                $formattedAccuracy = round($accuracy, 1);

                return [
                    'is_valid' => false,
                    'message' => "Akurasi GPS perangkat Anda terlalu rendah ({$formattedAccuracy} meter, maksimal toleransi {$maxAccuracy} meter). Pastikan lokasi/GPS HP aktif dalam mode Akurasi Tinggi dan berada di area terbuka.",
                ];
            }
        }

        return [
            'is_valid' => true,
            'message' => null,
        ];
    }

    /**
     * Menghasilkan tautan URL navigasi petunjuk arah (Google Maps Directions).
     */
    public function getGoogleMapsDirectionsUrl(
        float $destLat,
        float $destLng,
        ?float $originLat = null,
        ?float $originLng = null
    ): string {
        $url = "https://www.google.com/maps/dir/?api=1&destination={$destLat},{$destLng}";

        if ($originLat !== null && $originLng !== null) {
            $url .= "&origin={$originLat},{$originLng}";
        }

        return $url;
    }

    /**
     * Melakukan reverse geocoding dari koordinat GPS ke alamat teks (OpenStreetMap Nominatim).
     * Disertai fallback jika offline atau timeout.
     */
    public function reverseGeocode(?float $lat, ?float $lng): ?string
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        try {
            $response = Http::timeout(3)
                ->withHeaders([
                    'User-Agent' => 'PKBM-Pikat-Presensi/2.0 (admin@pkbmpikat.sch.id)',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'jsonv2',
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['display_name'] ?? null;
            }
        } catch (\Throwable) {
            // Fallback gracefully without breaking attendance flow
        }

        return null;
    }
}
