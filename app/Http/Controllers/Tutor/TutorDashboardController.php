<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tutor\Concerns\ResolvesTutor;
use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TutorDashboardController — Controller Dashboard & Riwayat untuk Tutor
 *
 * Controller ini mengelola semua halaman yang dapat diakses oleh pengguna dengan role 'tutor':
 *  1. Dashboard utama tutor (rekap status presensi hari ini, jadwal mendatang)
 *  2. Halaman riwayat presensi tutor (dengan filter bulan dan status)
 *  3. Halaman jadwal kegiatan
 *
 * SIDANG FAQ:
 *   Q: Apa itu Trait 'ResolvesTutor' dan mengapa digunakan?
 *   A: ResolvesTutor adalah Trait yang mengandung method resolveTutor() untuk mendapatkan
 *      data tutor yang sedang login. Menggunakan Trait memungkinkan logika ini dibagi
 *      ke banyak controller (DRY - Don't Repeat Yourself) tanpa inheritance.
 *      Trait lebih fleksibel daripada inheritance karena PHP tidak mendukung multiple inheritance.
 *
 *   Q: Mengapa ada pengecekan sesi 'proses' dan 'selesai' di dashboard?
 *   A: Karena tutor bisa mengajar beberapa sesi dalam satu hari (multi-sesi).
 *      Dashboard menampilkan status paling relevan: jika ada sesi yang masih berjalan,
 *      itu yang diprioritaskan. Jika semua sudah selesai, tampilkan sesi terakhir yang selesai.
 */
class TutorDashboardController extends Controller
{
    // Trait untuk mendapatkan data tutor berdasarkan user yang sedang login
    use ResolvesTutor;

    /**
     * Membuat query presensi dasar untuk tutor tertentu.
     * Helper method untuk menghindari pengulangan kondisi where tutor_id.
     *
     * @param  Tutor  $tutor  Objek tutor yang akan di-query
     * @return Builder
     */
    protected function presensiQueryForTutor(Tutor $tutor)
    {
        return Presensi::where('tutor_id', $tutor->id);
    }

    /**
     * Menampilkan halaman dashboard utama tutor.
     *
     * Data yang dikirim ke view:
     *  - tutor          : Data profil tutor yang sedang login
     *  - recentPresensi : Semua presensi hari ini milik tutor (untuk tabel riwayat)
     *  - upcomingJadwal : Jadwal kegiatan yang akan datang (maks 8 item)
     *  - todayPresensi  : Satu presensi paling relevan hari ini (untuk kartu status)
     *  - todayStatus    : Status hari ini: 'belum_mulai', 'proses', atau 'selesai'
     *  - sisaDetikPulang: Sisa detik sebelum bisa absen pulang (untuk countdown timer)
     *
     * @return View
     */
    public function index()
    {
        $tutor = $this->resolveTutor();

        // Inisialisasi variabel default jika tutor belum terhubung
        $recentPresensi = collect(); // Koleksi kosong
        $upcomingJadwal = collect(); // Koleksi kosong
        $todayPresensi = null;
        $todayStatus = 'belum_mulai'; // Status default: belum ada presensi hari ini
        $sisaDetikPulang = 0;            // 0 = tidak ada hitungan mundur

        if ($tutor) {
            $tz = 'Asia/Jakarta'; // Timezone WIB untuk konsistensi
            $today = Carbon::now($tz)->toDateString();
            $now = Carbon::now($tz);

            // Ambil semua presensi hari ini milik tutor (beserta relasi siswa untuk ditampilkan)
            $recentPresensi = $this->presensiQueryForTutor($tutor)
                ->with('siswa')
                ->whereDate('tgl_presensi', $today)
                ->orderByDesc('id')
                ->get();

            // Ambil jadwal mendatang (dari hari ini ke depan, maks 8 item)
            $upcomingJadwal = Jadwal::query()
                ->whereDate('tanggal', '>=', $today)
                ->orderBy('tanggal')
                ->limit(8)
                ->get();

            // ── Tentukan status presensi hari ini ──────────────────────────────
            // Strategi: ambil semua presensi hari ini, lalu tentukan mana yang
            // paling relevan untuk ditampilkan di kartu status dashboard

            $allTodayPresensi = Presensi::query()
                ->where('tutor_id', $tutor->id)
                ->whereDate('tgl_presensi', $today)
                ->orderByDesc('id')
                ->get();

            // Cari sesi yang MASIH BERJALAN (foto masuk ada, foto pulang belum ada)
            $activeSesi = $allTodayPresensi->first(fn ($p) => $p->foto_mulai && ! $p->foto_selesai);

            // Cari sesi yang sudah SELESAI (keduanya sudah ada)
            $doneSesi = $allTodayPresensi->first(fn ($p) => $p->foto_mulai && $p->foto_selesai);

            if ($activeSesi) {
                // Ada sesi yang masih berlangsung — tampilkan info sesi ini
                $todayPresensi = $activeSesi;
                $todayStatus = 'proses';

                // Hitung countdown: berapa detik lagi tutor bisa absen pulang
                // (minimal 1 jam = 3600 detik dari jam mulai)
                try {
                    $jamMulaiDt = Carbon::parse($today.' '.$activeSesi->jam_mulai, $tz);
                    $diffDetik = (int) $jamMulaiDt->diffInSeconds($now, false);
                    // max(0, ...) memastikan tidak negatif jika sudah lewat 1 jam
                    $sisaDetikPulang = (int) max(0, 3600 - $diffDetik);
                } catch (\Throwable) {
                    // Abaikan error parsing (misal: format jam tidak valid)
                }

            } elseif ($doneSesi) {
                // Tidak ada sesi berjalan, tapi ada yang sudah selesai
                $todayPresensi = $doneSesi;
                $todayStatus = 'selesai';
            }
            // Jika keduanya null → todayStatus tetap 'belum_mulai' (default)
        }

        return view('tutor.dashboard', [
            'tutor' => $tutor,
            'recentPresensi' => $recentPresensi,
            'upcomingJadwal' => $upcomingJadwal,
            'todayPresensi' => $todayPresensi,
            'todayStatus' => $todayStatus,
            'sisaDetikPulang' => $sisaDetikPulang, // Digunakan oleh JavaScript countdown di view
        ]);
    }

    /**
     * Menampilkan halaman riwayat presensi tutor dengan filter bulan dan status.
     *
     * Fitur halaman ini:
     *  - Filter berdasarkan bulan (parameter GET 'tanggal' format YYYY-MM)
     *  - Filter berdasarkan status presensi (hadir/proses/izin)
     *  - Statistik ringkas bulan yang dipilih (hadir, izin, persentase)
     *  - Pagination 20 item per halaman
     *
     * SIDANG FAQ:
     *   Q: Apa itu Pagination di Laravel?
     *   A: Pagination adalah fitur untuk memecah data besar menjadi halaman-halaman kecil.
     *      Laravel otomatis membuat query LIMIT/OFFSET dan menyediakan link navigasi halaman.
     *      paginate(20) = tampilkan 20 item per halaman.
     *      withQueryString() = pertahankan parameter filter di URL saat pindah halaman.
     *
     *   Q: Bagaimana cara menghitung persentase kehadiran?
     *   A: Persentase = (jumlah hadir / total sesi) × 100.
     *      Total sesi = semua record presensi pada bulan tersebut (hadir + izin + proses).
     *      Jika total = 0, persentase juga = 0 (untuk menghindari division by zero).
     *
     * @param  Request  $request  Parameter filter: 'tanggal', 'status'
     * @return View|RedirectResponse
     */
    public function riwayat(Request $request)
    {
        $tutor = $this->resolveTutor();
        if (! $tutor) {
            return redirect()
                ->route('tutor.dashboard')
                ->with('warning', 'Data tutor belum terhubung ke akun ini.');
        }

        // Ambil tanggal dari parameter GET, default: bulan ini
        // Format yang diharapkan: 'YYYY-MM-DD' atau 'YYYY-MM'
        $selectedDate = $request->get('tanggal', now()->toDateString());
        $date = Carbon::parse($selectedDate);

        // Ambil filter status (hadir/proses/izin), null jika tidak ada filter
        $statusFilter = request('status');

        // Query dasar: presensi milik tutor ini, pada bulan dan tahun yang dipilih
        $query = $this->presensiQueryForTutor($tutor)
            ->whereMonth('tgl_presensi', $date->month)
            ->whereYear('tgl_presensi', $date->year);

        // Terapkan filter status jika dipilih
        if ($statusFilter === 'hadir') {
            // Hadir = status hadir DAN sudah punya jam selesai (sesi selesai)
            $query->where('status', 'hadir')->whereNotNull('foto_selesai');
        } elseif ($statusFilter === 'proses') {
            // Proses = sudah masuk tapi belum pulang
            $query->whereNotNull('foto_mulai')->whereNull('foto_selesai');
        } elseif ($statusFilter === 'izin') {
            // Izin = status izin (ditetapkan oleh admin)
            $query->where('status', 'izin');
        }

        // Ambil data dengan relasi siswa, diurutkan terbaru dulu, dengan pagination
        // withQueryString() memastikan parameter filter ikut terbawa saat ganti halaman
        $items = $query->with('siswa')
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // ── Hitung statistik untuk bulan yang dipilih (tanpa filter status) ──
        // Menggunakan clone agar query utama tidak terpengaruh
        $qBase = $this->presensiQueryForTutor($tutor)
            ->whereMonth('tgl_presensi', $date->month)
            ->whereYear('tgl_presensi', $date->year);

        $hadir = (clone $qBase)->where('status', 'hadir')->whereNotNull('foto_selesai')->count();
        $izin = (clone $qBase)->where('status', 'izin')->count();
        $total = (clone $qBase)->count();
        // Persentase kehadiran: hadir / total × 100, rounded ke integer
        $persentase = $total > 0 ? round(($hadir / $total) * 100) : 0;

        return view('tutor.riwayat', [
            'tutor' => $tutor,
            'items' => $items,         // Data presensi terfilter (paginasi)
            'hadir' => $hadir,         // Jumlah sesi hadir di bulan ini
            'izin' => $izin,          // Jumlah sesi izin di bulan ini
            'persentase' => $persentase,    // Persentase kehadiran (0-100)
            'selectedDate' => $selectedDate,  // Tanggal/bulan yang sedang dilihat
            'statusFilter' => $statusFilter,  // Filter status yang aktif
        ]);
    }

    /**
     * Tampilkan agenda PKBM (Telah dilebur ke Jadwal Sesi tab agenda).
     */
    public function jadwal(Request $request): RedirectResponse
    {
        return redirect()->route('tutor.jadwal-sesi.index', array_merge(
            ['tab' => 'agenda'],
            $request->only('tanggal')
        ));
    }
}
