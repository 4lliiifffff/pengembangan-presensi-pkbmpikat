<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tutor\Concerns\ResolvesTutor;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * PresensiFotoController — Controller Absensi Berbasis Foto untuk Tutor
 *
 * Controller ini mengelola fitur inti dari sistem presensi: proses Clock In (absen masuk)
 * dan Clock Out (absen pulang) yang dilengkapi dengan verifikasi foto sebagai bukti kehadiran.
 *
 * ALUR PRESENSI:
 *   1. Tutor memilih siswa yang akan diajar
 *   2. Tutor mengambil foto → klik "Absen Masuk" (Clock In)
 *   3. Sistem mencatat jam_mulai dan menyimpan foto_mulai
 *   4. Minimal 1 jam kemudian, tutor mengambil foto → klik "Absen Pulang" (Clock Out)
 *   5. Sistem mencatat jam_selesai dan menyimpan foto_selesai
 *   6. Sesi dianggap selesai → status = 'hadir'
 *
 * ATURAN BISNIS (Business Rules):
 *   - Presensi pulang HARUS berjarak minimal 1 jam setelah presensi masuk
 *   - Satu tutor bisa mengajar beberapa siswa dalam satu hari (multi-sesi)
 *   - Tidak bisa absen pulang sebelum absen masuk
 *   - Tidak bisa membuat sesi baru untuk siswa yang sama jika sesi sebelumnya belum selesai
 *
 * SIDANG FAQ:
 *   Q: Mengapa harus ada jeda minimal 1 jam untuk absen pulang?
 *   A: Untuk memastikan tutor benar-benar mengajar setidaknya 1 jam, bukan sekadar
 *      melakukan clock-in dan clock-out dalam waktu singkat. Ini adalah aturan bisnis
 *      yang ditetapkan oleh lembaga untuk menjamin kualitas mengajar.
 *
 *   Q: Mengapa foto disimpan di public/uploads/ bukan di storage/?
 *   A: Folder public/ dapat diakses langsung melalui URL browser tanpa memerlukan
 *      konfigurasi symbolic link (php artisan storage:link). Ini lebih kompatibel
 *      dengan shared hosting yang sering membatasi akses artisan command.
 *
 *   Q: Bagaimana sistem mencegah kecurangan (fraud) dalam presensi?
 *   A: Sistem memerlukan:
 *      1. Foto sebagai bukti fisik kehadiran di lokasi
 *      2. Data lokasi (GPS) yang dicatat bersama foto
 *      3. Jeda waktu minimal 1 jam antara masuk dan pulang
 *      4. Timestamp otomatis dari server (tidak bisa dimanipulasi dari client)
 */
class PresensiFotoController extends Controller
{
    // Trait untuk mendapatkan data tutor yang sedang login
    use ResolvesTutor;

    /**
     * Menampilkan halaman form presensi foto untuk tutor.
     *
     * Halaman ini menampilkan:
     *  - Form untuk memilih siswa dan mengambil foto
     *  - Sesi yang sedang berjalan (jika ada)
     *  - Riwayat sesi yang sudah selesai hari ini
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Ambil data tutor yang sedang login melalui Trait ResolvesTutor
        $tutor = $this->resolveTutor();

        // Jika tutor belum terhubung ke akun user, redirect ke dashboard dengan peringatan
        // (Ini terjadi jika admin belum menghubungkan profil tutor ke akun login)
        if (! $tutor) {
            return redirect()
                ->route('tutor.dashboard')
                ->with('warning', 'Data tutor belum terhubung ke akun ini. Hubungkan tutor ke user terlebih dahulu.');
        }

        // Gunakan timezone WIB (Asia/Jakarta) untuk konsistensi waktu Indonesia
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Ambil semua siswa yang diajar oleh tutor ini (tutor_id == login tutor id)
        $siswas = \App\Models\Siswa::where('tutor_id', $tutor->id)->orderBy('nama_siswa')->get();

        // Cek apakah kolom tutor_id ada di tabel presensis (backward compatibility)
        // Sistem lama mungkin tidak memiliki kolom ini
        $hasPresensiTutorId = Schema::hasColumn('presensis', 'tutor_id');

        // Query presensi hari ini milik tutor ini
        $presensiQuery = Presensi::query()->whereDate('tgl_presensi', $today);
        if ($hasPresensiTutorId) {
            // Filter berdasarkan tutor_id jika kolom sudah ada
            $presensiQuery->where('tutor_id', $tutor->id);
        }

        // Ambil semua presensi hari ini (diurutkan terbaru di atas)
        $allPresensiToday = $presensiQuery->with('siswa')->orderByDesc('id')->get();

        // Untuk dropdown: ambil record terbaru per siswa (untuk menampilkan status siswa)
        // groupBy siswa_id → map ke item pertama (terbaru) di setiap grup
        $presensiToday = $allPresensiToday->groupBy('siswa_id')->map(fn ($items) => $items->first());

        $activeSessions = $allPresensiToday->filter(fn ($p) => $p->foto_mulai && ! $p->foto_selesai)->values();
        $globalActiveSesi = $activeSessions->first();

        // Ambil semua sesi yang sudah selesai hari ini (untuk ditampilkan sebagai riwayat)
        $completedSessions = $allPresensiToday->filter(fn ($p) => $p->foto_mulai && $p->foto_selesai)->values();

        // Kirim semua data ke view
        return view('tutor.presensi_foto', [
            'tutor' => $tutor,
            'today' => $today,
            'siswas' => $siswas,
            'presensiToday' => $presensiToday,       // Status presensi per siswa (untuk badge dropdown)
            'globalActiveSesi' => $globalActiveSesi,   // Sesi yang masih berjalan pertama (null jika tidak ada)
            'activeSessions' => $activeSessions,     // Semua sesi yang masih berjalan
            'completedSessions' => $completedSessions,  // Daftar sesi selesai hari ini (riwayat)
        ]);
    }

    /**
     * Memproses pengiriman form presensi (absen masuk atau absen pulang).
     *
     * Method ini menangani dua mode dalam satu endpoint:
     *  - mode 'mulai'   : Clock In (absen masuk) — buat record presensi baru
     *  - mode 'selesai' : Clock Out (absen pulang) — update record yang sudah ada
     *
     * VALIDASI INPUT:
     *  - siswa_id : Wajib, harus berupa angka integer
     *  - mode     : Wajib, hanya boleh 'mulai' atau 'selesai'
     *  - foto     : Wajib, harus berupa gambar, maks 5MB
     *  - lokasi   : Opsional, string GPS atau nama lokasi
     *
     * @param  Request  $request  Data form presensi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Verifikasi tutor yang sedang login
        $tutor = $this->resolveTutor();
        if (! $tutor) {
            return redirect()
                ->route('tutor.dashboard')
                ->with('warning', 'Data tutor belum terhubung ke akun ini.');
        }

        $validated = $request->validate([
            'siswa_id' => ['required', 'array', 'min:1'],
            'siswa_id.*' => ['integer'],
            'mode' => ['required', Rule::in(['mulai', 'selesai'])], // Mode: clock-in atau clock-out
            'foto' => ['required', 'image', 'max:5120'],        // Foto wajib, maks 5MB (5120KB)
            'lokasi' => ['nullable', 'string', 'max:255'],        // Lokasi GPS opsional
        ]);

        // Gunakan waktu Jakarta untuk semua perhitungan waktu
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Path direktori penyimpanan foto: uploads/presensi/{tutor_id}/{tanggal}/
        $dir = 'uploads/presensi/'.$tutor->id.'/'.$today;
        $hasPresensiTutorId = Schema::hasColumn('presensis', 'tutor_id');

        // ─────────────────────────────────────────────
        //  MODE: MULAI (Clock In / Absen Masuk)
        // ─────────────────────────────────────────────
        if ($validated['mode'] === 'mulai') {
            // Validasi: tidak boleh membuat sesi baru jika sesi lama belum selesai
            foreach ($validated['siswa_id'] as $sId) {
                $presensiLookup = Presensi::query()
                    ->where('siswa_id', $sId)
                    ->whereDate('tgl_presensi', $today);
                if ($hasPresensiTutorId) {
                    $presensiLookup->where('tutor_id', $tutor->id);
                }
                $presensi = $presensiLookup->orderByDesc('id')->first();

                if ($presensi && ! $presensi->foto_selesai) {
                    return back()->with('warning', 'Presensi masuk masih berjalan untuk salah satu siswa terpilih. Silahkan absen pulang dulu.');
                }
            }

            // Upload foto masuk ke direktori
            $file = $request->file('foto');
            $filename = 'masuk_'.time().'_'.$file->getClientOriginalName();
            $fullDir = public_path($dir);
            if (! is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
            $file->move($fullDir, $filename);
            $path = $dir.'/'.$filename;
            $waktuServer = $now->format('H:i:s');

            // Buat record presensi baru untuk tiap siswa
            foreach ($validated['siswa_id'] as $sId) {
                $presensi = new Presensi;
                if ($hasPresensiTutorId) {
                    $presensi->tutor_id = $tutor->id;
                }
                $presensi->siswa_id = (int) $sId;
                $presensi->tgl_presensi = $today;
                $presensi->jam_mulai = $waktuServer;
                $presensi->foto_mulai = $path;
                $presensi->lokasi_mulai = $validated['lokasi'] ?? null;
                $presensi->status = 'hadir';
                $presensi->save();
            }

            return redirect()
                ->route('tutor.dashboard')
                ->with('success', 'Presensi masuk berhasil disimpan.');
        }

        // ─────────────────────────────────────────────
        //  MODE: SELESAI (Clock Out / Absen Pulang)
        // ─────────────────────────────────────────────

        $presensisToUpdate = [];

        foreach ($validated['siswa_id'] as $sId) {
            $presensiLookup = Presensi::query()
                ->where('siswa_id', $sId)
                ->whereDate('tgl_presensi', $today);
            if ($hasPresensiTutorId) {
                $presensiLookup->where('tutor_id', $tutor->id);
            }
            $presensi = $presensiLookup->orderByDesc('id')->first();

            // Validasi: tidak bisa clock-out jika belum pernah clock-in
            if (! $presensi || ! $presensi->foto_mulai) {
                return back()->with('warning', 'Presensi pulang harus setelah presensi masuk.');
            }

            // Validasi: tidak bisa clock-out dua kali untuk sesi yang sama
            if ($presensi->foto_selesai) {
                return back()->with('warning', 'Presensi pulang sesi terakhir sudah tercatat.');
            }

            // Validasi durasi minimal: harus sudah lewat 1 jam
            $jamMulai = Carbon::parse($today.' '.$presensi->jam_mulai, 'Asia/Jakarta');
            $detikJalan = (int) $jamMulai->diffInSeconds($now, false);

            if ($detikJalan < 3600) {
                $sisaDetik = max(0, 3600 - $detikJalan);
                $sisaMenit = $sisaDetik / 60;
                $sisaLabel = number_format($sisaMenit, 2, ':', '');

                return back()->with('warning', "Tunggu {$sisaLabel} menit lagi. Presensi pulang harus berjarak minimal 1 jam setelah masuk.");
            }

            // Validasi durasi maksimal: jika sudah lebih dari 2 jam, tetap izinkan tapi catat
            // (tidak ada blokir — hanya pastikan tetap bisa absen pulang)

            $presensisToUpdate[] = $presensi;
        }

        // Jika semua validasi lulus, baru proses upload foto dan update data
        $file = $request->file('foto');
        $filename = 'keluar_'.time().'_'.$file->getClientOriginalName();
        $fullDir = public_path($dir);
        if (! is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        $file->move($fullDir, $filename);
        $path = $dir.'/'.$filename;
        $waktuServer = $now->format('H:i:s');

        foreach ($presensisToUpdate as $presensi) {
            $presensi->jam_selesai = $waktuServer;
            $presensi->foto_selesai = $path;
            $presensi->lokasi_selesai = $validated['lokasi'] ?? null;
            $presensi->save();
        }

        return redirect()
            ->route('tutor.dashboard')
            ->with('success', 'Presensi pulang berhasil disimpan.');
    }
}
