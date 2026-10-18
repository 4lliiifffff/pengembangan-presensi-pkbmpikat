<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzinSakit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DokumenIzinController extends Controller
{
    /**
     * Tampilkan atau stream berkas dokumen surat izin/sakit.
     */
    public function show(int $id): Response|RedirectResponse
    {
        $pengajuan = PengajuanIzinSakit::with('tutor')->findOrFail($id);

        $user = Auth::user();

        // Otorisasi: Admin, Kepala Sekolah, atau Tutor pemilik dokumen
        $isAdminOrKepsek = in_array($user->role, ['admin', 'kepala_sekolah', 'kepsek'], true);
        $isOwner = $user->tutor && $user->tutor->id === $pengajuan->tutor_id;

        if (! $isAdminOrKepsek && ! $isOwner) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen ini.');
        }

        if (! $pengajuan->dokumen_surat) {
            abort(404, 'Dokumen lampiran tidak ditemukan.');
        }

        if (str_starts_with($pengajuan->dokumen_surat, 'http://') || str_starts_with($pengajuan->dokumen_surat, 'https://')) {
            return redirect()->away($pengajuan->dokumen_surat);
        }

        $cleanPath = ltrim(str_replace('storage/', '', $pengajuan->dokumen_surat), '/');

        if (! Storage::disk('public')->exists($cleanPath)) {
            abort(404, 'Berkas fisik dokumen tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('public')->response($cleanPath);
    }
}
