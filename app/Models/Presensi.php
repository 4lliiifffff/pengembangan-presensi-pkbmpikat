<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Presensi — Representasi Data Kehadiran Tutor
 *
 * Model ini merepresentasikan satu sesi kehadiran (absensi) seorang tutor
 * ketika mengajar seorang siswa. Sistem presensi menggunakan pendekatan
 * "Clock In / Clock Out" dengan verifikasi foto pada setiap tahap.
 */
class Presensi extends Model
{
    /**
     * Daftar kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     * Kolom yang tidak ada di sini TIDAK BISA diisi melalui Presensi::create() atau update().
     *
     * @var array<string>
     */
    protected $fillable =
        [
            'tutor_id',               // ID tutor yang melakukan presensi
            'siswa_id',               // ID siswa yang diajar dalam sesi ini
            'lokasi_presensi_id',     // ID titik lokasi presensi yang dipilih (opsional/nullable)
            'moda_pembelajaran',      // Moda: 'sekolah', 'kunjungan_rumah', atau 'online'
            'link_daring',            // Link ruang pertemuan online (misal: Zoom/GMeet link)
            'durasi_pilihan',         // Durasi sesi yang disepakati (misal: 1.5, 2.0, 3.0 jam)
            'kategori_tutorial_id',   // Foreign key ke tabel kategori_tutorials
            'nominal_honor_snapshot', // Snapshot nominal honor per sesi saat presensi dibuat
            'tgl_presensi',           // Tanggal sesi mengajar berlangsung
            'jam_mulai',              // Jam tutor mulai mengajar (clock-in)
            'jam_selesai',            // Jam tutor selesai mengajar (clock-out) — null jika masih berjalan
            'foto_mulai',             // Path foto bukti absen masuk
            'foto_selesai',           // Path foto bukti absen pulang — null jika belum selesai
            'lokasi_mulai',           // Koordinat GPS / deskripsi lokasi saat masuk (opsional)
            'lokasi_selesai',         // Koordinat GPS / deskripsi lokasi saat pulang (opsional)
            'lokasi_akurasi',         // Akurasi sinyal GPS dalam meter
            'is_mocked',              // Flag terdeteksi Fake GPS / mock location
            'status',                 // Status: 'hadir', 'izin', atau 'alpha'
        ];

    /**
     * Tipe data casts atribut Presensi.
     */
    protected function casts(): array
    {
        return [
            'durasi_pilihan' => 'float',
            'nominal_honor_snapshot' => 'float',
            'lokasi_akurasi' => 'float',
            'is_mocked' => 'boolean',
        ];
    }

    /**
     * Accessor: Label Moda Pembelajaran dalam Bahasa Indonesia baku.
     */
    public function getModaLabelAttribute(): string
    {
        return match ($this->moda_pembelajaran) {
            'kunjungan_rumah' => 'Kunjungan Rumah (Home Visit)',
            'online' => 'Pembelajaran Online (Daring)',
            default => 'Sekolah (Tatap Muka)',
        };
    }

    /**
     * Relasi: Presensi terikat pada satu Kategori Tutorial SK (BelongsTo).
     */
    public function kategoriTutorial(): BelongsTo
    {
        return $this->belongsTo(KategoriTutorial::class, 'kategori_tutorial_id');
    }

    /**
     * Relasi: Presensi dimiliki oleh satu Tutor (Many-to-One / BelongsTo).
     *
     * Dengan relasi ini, kita bisa mengakses data tutor dari presensi:
     *   $presensi->tutor->nama_lengkap
     *
     * @return BelongsTo
     */
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Relasi: Presensi terkait dengan satu Siswa (Many-to-One / BelongsTo).
     *
     * Dengan relasi ini, kita bisa mengakses data siswa dari presensi:
     *   $presensi->siswa->nama_siswa
     *
     * @return BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Relasi: Presensi terikat pada satu Titik Lokasi Presensi (BelongsTo).
     */
    public function lokasiPresensi(): BelongsTo
    {
        return $this->belongsTo(LokasiPresensi::class, 'lokasi_presensi_id');
    }

    /**
     * Local Query Scope: Filter presensi berdasarkan periode waktu.
     *
     * Scope ini memudahkan pembuatan query laporan tanpa harus menulis
     * logika filter berulang di setiap controller.
     *
     * PENGGUNAAN:
     *   Presensi::laporanNgajar('hari')       // Hari ini
     *   Presensi::laporanNgajar('minggu')     // Minggu ini
     *   Presensi::laporanNgajar('bulan')      // Bulan ini
     *   Presensi::laporanNgajar('tahun')      // Tahun ini
     *   Presensi::laporanNgajar('hari', '2024-01-15') // Tanggal tertentu
     *
     * SIDANG FAQ:
     *   Q: Apa itu Query Scope di Eloquent?
     *   A: Scope adalah method khusus di Model yang memperluas (extend) query builder
     *      dengan logika filter yang bisa digunakan kembali (reusable). Namanya diawali
     *      dengan 'scope', tapi dipanggil tanpa awalan tersebut.
     *
     * @param  Builder  $query
     * @param  string  $periode  Periode filter: 'hari', 'minggu', 'bulan', 'tahun'
     * @param  string|null  $tanggal  Tanggal spesifik untuk filter 'hari' (opsional)
     * @return Builder
     */
    public function scopeLaporanNgajar($query, $periode = 'hari', $tanggal = null)
    {
        switch ($periode) {
            case 'hari':
                // Filter presensi untuk tanggal tertentu atau hari ini
                return $query->whereDate('tgl_presensi', $tanggal ?? now()->toDateString());
            case 'minggu':
                // Filter presensi untuk minggu berjalan (Senin s/d Minggu)
                return $query->whereBetween('tgl_presensi', [now()->startOfWeek(), now()->endOfWeek()]);
            case 'bulan':
                // Filter presensi untuk bulan dan tahun saat ini
                return $query->whereMonth('tgl_presensi', now()->month)->whereYear('tgl_presensi', now()->year);
            case 'tahun':
                // Filter presensi untuk tahun saat ini
                return $query->whereYear('tgl_presensi', now()->year);
            default:
                // Jika periode tidak dikenali, kembalikan query tanpa filter tambahan
                return $query;
        }
    }

    /**
     * Accessor: Mengembalikan URL foto masuk dengan backward compatibility.
     */
    public function getFotoMulaiUrlAttribute(): ?string
    {
        if (! $this->foto_mulai) {
            return null;
        }

        if (str_starts_with($this->foto_mulai, 'http://') || str_starts_with($this->foto_mulai, 'https://')) {
            return $this->foto_mulai;
        }

        if (str_starts_with($this->foto_mulai, 'storage/')) {
            return asset($this->foto_mulai);
        }

        if (file_exists(public_path($this->foto_mulai))) {
            return asset($this->foto_mulai);
        }

        return asset('storage/'.ltrim($this->foto_mulai, '/'));
    }

    /**
     * Accessor: Mengembalikan URL foto pulang dengan backward compatibility.
     */
    public function getFotoSelesaiUrlAttribute(): ?string
    {
        if (! $this->foto_selesai) {
            return null;
        }

        if (str_starts_with($this->foto_selesai, 'http://') || str_starts_with($this->foto_selesai, 'https://')) {
            return $this->foto_selesai;
        }

        if (str_starts_with($this->foto_selesai, 'storage/')) {
            return asset($this->foto_selesai);
        }

        if (file_exists(public_path($this->foto_selesai))) {
            return asset($this->foto_selesai);
        }

        return asset('storage/'.ltrim($this->foto_selesai, '/'));
    }
}
