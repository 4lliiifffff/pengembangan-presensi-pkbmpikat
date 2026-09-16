<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenjangPaket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenjangPaketController extends Controller
{
    public function index()
    {
        $jenjangPakets = JenjangPaket::withCount('kelas')
            ->orderBy('urutan')
            ->orderBy('nama_jenjang')
            ->get();

        return view('admin.jenjang_paket.index', compact('jenjangPakets'));
    }

    public function create()
    {
        $nextUrutan = (JenjangPaket::max('urutan') ?? 0) + 1;

        return view('admin.jenjang_paket.create', compact('nextUrutan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:jenjang_pakets,kode'],
            'nama_jenjang' => ['required', 'string', 'max:100'],
            'tingkat_label' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer'],
            'is_aktif' => ['nullable', 'boolean'],
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);
        $validated['urutan'] = $validated['urutan'] ?? ((JenjangPaket::max('urutan') ?? 0) + 1);

        JenjangPaket::create($validated);

        return redirect()
            ->route('admin.jenjang-paket.index')
            ->with('success', 'Master Jenjang & Program Paket berhasil ditambahkan.');
    }

    public function edit(JenjangPaket $jenjangPaket)
    {
        $jenjangPaket->loadCount('kelas');

        return view('admin.jenjang_paket.edit', compact('jenjangPaket'));
    }

    public function show(JenjangPaket $jenjangPaket)
    {
        return redirect()->route('admin.jenjang-paket.edit', $jenjangPaket);
    }

    public function update(Request $request, JenjangPaket $jenjangPaket)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('jenjang_pakets', 'kode')->ignore($jenjangPaket->id),
            ],
            'nama_jenjang' => ['required', 'string', 'max:100'],
            'tingkat_label' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer'],
            'is_aktif' => ['nullable', 'boolean'],
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif');
        $validated['urutan'] = $validated['urutan'] ?? $jenjangPaket->urutan;

        $jenjangPaket->update($validated);

        return redirect()
            ->route('admin.jenjang-paket.index')
            ->with('success', 'Master Jenjang & Program Paket berhasil diperbarui.');
    }

    public function destroy(Request $request, JenjangPaket $jenjangPaket)
    {
        $kelasCount = $jenjangPaket->kelas()->count();
        $targetJenjangId = $request->input('target_jenjang_paket_id');

        if ($kelasCount > 0 && ! $request->boolean('confirm_unlink') && empty($targetJenjangId)) {
            return redirect()
                ->route('admin.jenjang-paket.index')
                ->with('warning', 'Jenjang Paket "'.$jenjangPaket->nama_jenjang.'" memiliki '.$kelasCount.' rombel/kelas terkait. Harap konfirmasi pelepasan foreign key atau pilih jenjang pengganti.');
        }

        $namaJenjang = $jenjangPaket->nama_jenjang;
        $targetJenjang = null;

        if ($kelasCount > 0) {
            if ($targetJenjangId && (int) $targetJenjangId !== (int) $jenjangPaket->id) {
                $targetJenjang = JenjangPaket::find($targetJenjangId);
                if ($targetJenjang) {
                    $jenjangPaket->kelas()->update(['jenjang_paket_id' => $targetJenjang->id]);
                } else {
                    $jenjangPaket->kelas()->update(['jenjang_paket_id' => null]);
                }
            } else {
                $jenjangPaket->kelas()->update(['jenjang_paket_id' => null]);
            }
        }

        $jenjangPaket->delete();

        if ($kelasCount > 0 && $targetJenjang) {
            $message = 'Master Jenjang "'.$namaJenjang.'" berhasil dihapus. Sebanyak '.$kelasCount.' kelas terkait telah berhasil dialihkan ke jenjang "'.$targetJenjang->nama_jenjang.'".';
        } elseif ($kelasCount > 0) {
            $message = 'Master Jenjang "'.$namaJenjang.'" berhasil dihapus. Foreign key pada '.$kelasCount.' kelas terkait telah otomatis diatur menjadi NULL (Umum).';
        } else {
            $message = 'Master Jenjang "'.$namaJenjang.'" berhasil dihapus.';
        }

        return redirect()
            ->route('admin.jenjang-paket.index')
            ->with('success', $message);
    }

    public function toggleStatus(JenjangPaket $jenjangPaket)
    {
        $jenjangPaket->update(['is_aktif' => ! $jenjangPaket->is_aktif]);

        return redirect()
            ->route('admin.jenjang-paket.index')
            ->with('success', 'Status keaktifan jenjang paket "'.$jenjangPaket->nama_jenjang.'" berhasil diubah.');
    }
}
