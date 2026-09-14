<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Lapor_Lapor;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LupaLaporController extends Controller
{
    /**
     * Tampilkan form + riwayat pengajuan milik tutor yang sedang login.
     */
    public function index()
    {
        $tutor = Auth::user()->tutor;

        // Daftar siswa untuk pilihan (semua siswa)
        $siswaList = Siswa::orderBy('nama_siswa')->get();

        // Riwayat pengajuan milik tutor ini, terbaru dulu
        $riwayat = Lapor_Lapor::with('siswa')
            ->where('tutor_id', $tutor->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('tutor.lupa_lapor', compact('siswaList', 'riwayat'));
    }

    /**
     * Simpan pengajuan baru.
     */
    public function store(Request $request)
    {
        $tutor = Auth::user()->tutor;

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'siswa_id.required' => 'Pilih siswa terlebih dahulu.',
            'siswa_id.exists' => 'Siswa tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'alasan.required' => 'Alasan wajib diisi.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
        ]);

        Lapor_Lapor::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $data['siswa_id'],
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'alasan' => $data['alasan'],
        ]);

        return redirect()->route('tutor.lupa-lapor')
            ->with('success', 'Pengajuan lupa lapor berhasil dikirim.');
    }

    /**
     * Hapus pengajuan (hanya milik tutor sendiri).
     */
    public function destroy(int $id)
    {
        $tutor = Auth::user()->tutor;

        $item = Lapor_Lapor::where('id', $id)
            ->where('tutor_id', $tutor->id)
            ->firstOrFail();

        $item->delete();

        return redirect()->route('tutor.lupa-lapor')
            ->with('success', 'Pengajuan berhasil dihapus.');
    }
}
