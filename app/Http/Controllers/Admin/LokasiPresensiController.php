<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiPresensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LokasiPresensiController extends Controller
{
    /**
     * Tampilkan daftar titik lokasi presensi.
     */
    public function index(Request $request): View
    {
        $query = LokasiPresensi::query()->withCount(['presensis', 'presensiKaryawans']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lokasi', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('tipe', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->input('tipe'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $lokasis = $query->orderByDesc('is_active')->orderBy('nama_lokasi')->paginate(15)->withQueryString();
        $allActiveLokasis = LokasiPresensi::active()->get(['id', 'nama_lokasi', 'tipe', 'latitude', 'longitude', 'radius_meter', 'alamat']);

        $stats = [
            'total' => LokasiPresensi::count(),
            'active' => LokasiPresensi::where('is_active', true)->count(),
            'inactive' => LokasiPresensi::where('is_active', false)->count(),
        ];

        return view('admin.lokasi_presensi.index', compact('lokasis', 'allActiveLokasis', 'stats'));
    }

    /**
     * Form tambah titik lokasi presensi baru dengan Leaflet Map Picker.
     */
    public function create(): View
    {
        $defaultLat = (float) config('lokasi.sekolah_lat', -7.8011945);
        $defaultLng = (float) config('lokasi.sekolah_lng', 110.364917);
        $defaultRadius = (int) config('lokasi.radius_meter', 100);

        return view('admin.lokasi_presensi.create', compact('defaultLat', 'defaultLng', 'defaultRadius'));
    }

    /**
     * Simpan data titik lokasi presensi baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:255', 'unique:lokasi_presensis,nama_lokasi'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tipe' => ['required', 'string', 'in:pusat,cabang,mitra,kegiatan,lainnya'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_lokasi.required' => 'Nama titik lokasi wajib diisi.',
            'nama_lokasi.unique' => 'Nama titik lokasi sudah digunakan.',
            'latitude.required' => 'Titik koordinat Latitude wajib ditentukan pada peta.',
            'longitude.required' => 'Titik koordinat Longitude wajib ditentukan pada peta.',
            'radius_meter.required' => 'Radius toleransi geofence wajib ditentukan.',
            'radius_meter.min' => 'Radius minimal adalah 10 meter.',
            'radius_meter.max' => 'Radius maksimal adalah 5000 meter.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        LokasiPresensi::create($validated);

        return redirect()->route('admin.lokasi-presensi.index')
            ->with('success', "Titik lokasi presensi '{$validated['nama_lokasi']}' berhasil ditambahkan.");
    }

    /**
     * Form edit titik lokasi presensi.
     */
    public function edit(LokasiPresensi $lokasiPresensi): View
    {
        return view('admin.lokasi_presensi.edit', compact('lokasiPresensi'));
    }

    /**
     * Perbarui data titik lokasi presensi.
     */
    public function update(Request $request, LokasiPresensi $lokasiPresensi): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:255', 'unique:lokasi_presensis,nama_lokasi,'.$lokasiPresensi->id],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tipe' => ['required', 'string', 'in:pusat,cabang,mitra,kegiatan,lainnya'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_lokasi.required' => 'Nama titik lokasi wajib diisi.',
            'nama_lokasi.unique' => 'Nama titik lokasi sudah digunakan.',
            'latitude.required' => 'Titik koordinat Latitude wajib ditentukan pada peta.',
            'longitude.required' => 'Titik koordinat Longitude wajib ditentukan pada peta.',
            'radius_meter.required' => 'Radius toleransi geofence wajib ditentukan.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $lokasiPresensi->update($validated);

        return redirect()->route('admin.lokasi-presensi.index')
            ->with('success', "Titik lokasi presensi '{$lokasiPresensi->nama_lokasi}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif titik lokasi.
     */
    public function toggleStatus(LokasiPresensi $lokasiPresensi): RedirectResponse
    {
        $lokasiPresensi->update(['is_active' => ! $lokasiPresensi->is_active]);

        $statusText = $lokasiPresensi->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Titik lokasi '{$lokasiPresensi->nama_lokasi}' berhasil {$statusText}.");
    }

    /**
     * Hapus titik lokasi presensi.
     */
    public function destroy(LokasiPresensi $lokasiPresensi): RedirectResponse
    {
        $nama = $lokasiPresensi->nama_lokasi;
        $lokasiPresensi->delete();

        return redirect()->route('admin.lokasi-presensi.index')
            ->with('success', "Titik lokasi '{$nama}' berhasil dihapus.");
    }
}
