<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenjangPaket;
use App\Models\kelas as Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with(['jenjangPaket'])->withCount('siswas');

        if ($request->filled('jenjang')) {
            $val = $request->jenjang;
            if (is_numeric($val)) {
                $query->where('jenjang_paket_id', (int) $val);
            } else {
                $query->whereHas('jenjangPaket', fn ($q) => $q->where('kode', $val));
            }
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhere('tingkat', 'like', "%{$search}%");
            });
        }

        $kelas = $query->orderBy('jenjang_paket_id')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(15)
            ->withQueryString();

        $jenjangPakets = JenjangPaket::ordered()->get();

        return view('admin.kelas.index', compact('kelas', 'jenjangPakets'));
    }

    public function create()
    {
        $jenjangPakets = JenjangPaket::aktif()->ordered()->get();

        return view('admin.kelas.create', compact('jenjangPakets'));
    }

    public function store(Request $request)
    {
        // Support backward compatibility jika form mengirim jenjang_paket (kode)
        if ($request->filled('jenjang_paket') && ! $request->filled('jenjang_paket_id')) {
            $jp = JenjangPaket::where('kode', $request->jenjang_paket)->first();
            if ($jp) {
                $request->merge(['jenjang_paket_id' => $jp->id]);
            }
        }

        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100', 'unique:kelas,nama_kelas'],
            'jenjang_paket_id' => ['required', 'exists:jenjang_pakets,id'],
            'tingkat' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        Kelas::create($validated);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        $jenjangPakets = JenjangPaket::ordered()->get();

        return view('admin.kelas.edit', ['kelas' => $kela, 'jenjangPakets' => $jenjangPakets]);
    }

    public function show(Kelas $kela)
    {
        return redirect()->route('admin.kelas.edit', $kela);
    }

    public function update(Request $request, Kelas $kela)
    {
        // Support backward compatibility jika form mengirim jenjang_paket (kode)
        if ($request->filled('jenjang_paket') && ! $request->filled('jenjang_paket_id')) {
            $jp = JenjangPaket::where('kode', $request->jenjang_paket)->first();
            if ($jp) {
                $request->merge(['jenjang_paket_id' => $jp->id]);
            }
        }

        $validated = $request->validate([
            'nama_kelas' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kelas', 'nama_kelas')->ignore($kela->id),
            ],
            'jenjang_paket_id' => ['required', 'exists:jenjang_pakets,id'],
            'tingkat' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $kela->update($validated);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        if ($kela->siswas()->exists()) {
            return redirect()
                ->route('admin.kelas.index')
                ->with('warning', 'Kelas tidak bisa dihapus karena masih dipakai oleh '.$kela->siswas()->count().' data siswa.');
        }

        $kela->delete();

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil dihapus.');
    }
}
