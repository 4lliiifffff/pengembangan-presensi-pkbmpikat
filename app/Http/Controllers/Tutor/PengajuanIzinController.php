<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzinSakit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinController extends Controller
{
    /**
     * Tampilkan form pengajuan & riwayat izin/sakit milik tutor yang sedang login.
     */
    public function index()
    {
        $tutor = Auth::user()->tutor;

        $riwayat = PengajuanIzinSakit::where('tutor_id', $tutor->id)
            ->orderByDesc('tgl_mulai')
            ->orderByDesc('id')
            ->get();

        return view('tutor.pengajuan_izin', compact('riwayat'));
    }

    /**
     * Simpan pengajuan izin/sakit baru.
     */
    public function store(Request $request)
    {
        $tutor = Auth::user()->tutor;

        $data = $request->validate([
            'jenis' => ['required', 'in:izin,sakit'],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after_or_equal:tgl_mulai'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
            'dokumen_surat' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ], [
            'jenis.required' => 'Jenis pengajuan wajib dipilih.',
            'jenis.in' => 'Jenis pengajuan tidak valid.',
            'tgl_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tgl_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tgl_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'alasan.required' => 'Alasan wajib diisi.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
            'dokumen_surat.mimes' => 'Dokumen surat harus format PDF, JPG, JPEG, atau PNG.',
            'dokumen_surat.max' => 'Ukuran file dokumen maksimal 2MB.',
        ]);

        $path = null;
        if ($request->hasFile('dokumen_surat')) {
            $path = $request->file('dokumen_surat')->store('dokumen_izin', 'public');
        }

        PengajuanIzinSakit::create([
            'tutor_id' => $tutor->id,
            'jenis' => $data['jenis'],
            'tgl_mulai' => $data['tgl_mulai'],
            'tgl_selesai' => $data['tgl_selesai'],
            'alasan' => $data['alasan'],
            'dokumen_surat' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('tutor.pengajuan-izin')
            ->with('success', 'Pengajuan '.ucfirst($data['jenis']).' berhasil dikirim dan menunggu verifikasi.');
    }

    /**
     * Hapus pengajuan (hanya jika masih status pending).
     */
    public function destroy(int $id)
    {
        $tutor = Auth::user()->tutor;

        $pengajuan = PengajuanIzinSakit::where('id', $id)
            ->where('tutor_id', $tutor->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $pengajuan->delete();

        return redirect()->route('tutor.pengajuan-izin')
            ->with('success', 'Pengajuan berhasil dibatalkan/dihapus.');
    }
}
