<?php

namespace App\Services;

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
}
