<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriTutorial;
use Illuminate\Http\Request;

class KategoriTutorialController extends Controller
{
    /**
     * Tampilkan daftar seluruh kategori tutorial & tarif SK.
     */
    public function index()
    {
        $kategoriList = KategoriTutorial::withCount('presensis')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return view('admin.kategori_tutorial.index', compact('kategoriList'));
    }

    /**
     * Tampilkan form pembuatan kategori tutorial baru.
     */
    public function create()
    {
        $nextUrutan = (KategoriTutorial::max('urutan') ?? 0) + 1;

        return view('admin.kategori_tutorial.create', compact('nextUrutan'));
    }

    /**
     * Simpan data kategori tutorial baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:150'],
            'jenis_layanan' => ['required', 'in:komunitas,dl,lainnya'],
            'durasi_jam' => ['required', 'numeric', 'min:0.5', 'max:12'],
            'nominal_honor' => ['required', 'numeric', 'min:0'],
            'is_abk' => ['nullable', 'boolean'],
            'is_gabungan' => ['nullable', 'boolean'],
            'is_aktif' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_abk'] = $request->boolean('is_abk');
        $validated['is_gabungan'] = $request->boolean('is_gabungan');
        $validated['is_aktif'] = $request->boolean('is_aktif', true);
        $validated['urutan'] = $validated['urutan'] ?? 0;

        KategoriTutorial::create($validated);

        return redirect()
            ->route('admin.kategori-tutorial.index')
            ->with('success', 'Kategori tutorial baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail atau redirect ke form edit.
     */
    public function show(KategoriTutorial $kategoriTutorial)
    {
        return redirect()->route('admin.kategori-tutorial.edit', $kategoriTutorial);
    }

    /**
     * Tampilkan form edit kategori tutorial.
     */
    public function edit(KategoriTutorial $kategoriTutorial)
    {
        $kategoriTutorial->loadCount('presensis');

        return view('admin.kategori_tutorial.edit', compact('kategoriTutorial'));
    }

    /**
     * Perbarui data kategori tutorial.
     */
    public function update(Request $request, KategoriTutorial $kategoriTutorial)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:150'],
            'jenis_layanan' => ['required', 'in:komunitas,dl,lainnya'],
            'durasi_jam' => ['required', 'numeric', 'min:0.5', 'max:12'],
            'nominal_honor' => ['required', 'numeric', 'min:0'],
            'is_abk' => ['nullable', 'boolean'],
            'is_gabungan' => ['nullable', 'boolean'],
            'is_aktif' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_abk'] = $request->boolean('is_abk');
        $validated['is_gabungan'] = $request->boolean('is_gabungan');
        $validated['is_aktif'] = $request->boolean('is_aktif');
        $validated['urutan'] = $validated['urutan'] ?? $kategoriTutorial->urutan;

        $kategoriTutorial->update($validated);

        return redirect()
            ->route('admin.kategori-tutorial.index')
            ->with('success', 'Kategori tutorial berhasil diperbarui.');
    }

    /**
     * Hapus kategori tutorial (hanya jika belum berelasi dengan presensi).
     */
    public function destroy(KategoriTutorial $kategoriTutorial)
    {
        if ($kategoriTutorial->presensis()->exists()) {
            // Jika ada riwayat, cukup nonaktifkan saja untuk menjaga integritas data
            $kategoriTutorial->update(['is_aktif' => false]);

            return redirect()
                ->route('admin.kategori-tutorial.index')
                ->with('warning', 'Kategori memiliki riwayat presensi, status otomatis diubah menjadi Non-Aktif.');
        }

        $kategoriTutorial->delete();

        return redirect()
            ->route('admin.kategori-tutorial.index')
            ->with('success', 'Kategori tutorial berhasil dihapus.');
    }

    /**
     * Toggle status aktif/non-aktif secara cepat.
     */
    public function toggleStatus(KategoriTutorial $kategoriTutorial)
    {
        $kategoriTutorial->update([
            'is_aktif' => ! $kategoriTutorial->is_aktif,
        ]);

        $statusText = $kategoriTutorial->is_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.kategori-tutorial.index')
            ->with('success', "Kategori {$kategoriTutorial->nama_kategori} berhasil {$statusText}.");
    }
}
