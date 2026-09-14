<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $karyawanQuery = User::query();

        if ($search) {
            if (Schema::hasColumn('users', 'nama_lengkap')) {
                $karyawanQuery->where('nama_lengkap', 'like', "%$search%");
            } elseif (Schema::hasColumn('users', 'name')) {
                $karyawanQuery->where('name', 'like', "%$search%");
            }
        }

        if (Schema::hasColumn('users', 'role')) {
            $karyawanQuery->whereIn('role', ['admin', 'tutor', 'kepala_sekolah']);
        }

        $karyawan = $karyawanQuery->latest()->get();

        $total = $karyawan->count();
        $hasIsActive = Schema::hasColumn('users', 'is_active');
        $aktif = $hasIsActive ? $karyawan->where('is_active', 1)->count() : $total;
        $nonaktif = $hasIsActive ? $karyawan->where('is_active', 0)->count() : 0;

        return view('admin.karyawan.index', compact(
            'karyawan',
            'total',
            'aktif',
            'nonaktif'
        ));
    }

    public function create()
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'nik' => 'required|unique:users',
            'password' => 'required',
            'role' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $generatedEmail = null;
        if (Schema::hasColumn('users', 'email')) {
            $nik = (string) $request->nik;
            $generatedEmail = strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';
        }

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'name' => $request->nama_lengkap,
            'nik' => $request->nik,
            'email' => $generatedEmail,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'is_active' => 1,
            'no_hp' => $request->no_hp,
        ];

        // Upload foto jika ada
        if ($request->hasFile('foto') && Schema::hasColumn('users', 'foto')) {
            $file = $request->file('foto');
            $filename = 'foto_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('uploads/foto_karyawan', $file, $filename);
            $data['foto'] = $path;
        }

        // Hapus field yang tidak ada di kolom tabel users
        foreach (array_keys($data) as $key) {
            if (! Schema::hasColumn('users', $key)) {
                unset($data[$key]);
            }
        }

        User::create($data);

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = User::findOrFail($id);

        return view('admin.karyawan.edit', ['karyawan' => $data]);
    }

    public function update(Request $request, $id)
    {
        $karyawan = User::findOrFail($id);

        $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $payload = $request->only(['nama_lengkap', 'nik', 'role', 'no_hp']);
        if ($request->filled('nama_lengkap')) {
            $payload['name'] = $request->nama_lengkap;
        }
        if (Schema::hasColumn('users', 'email') && $request->filled('nik')) {
            $nik = (string) $request->nik;
            $payload['email'] = strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';
        }
        if ($request->filled('password')) {
            $payload['password'] = bcrypt($request->password);
        }

        // Upload foto baru jika ada
        if ($request->hasFile('foto') && Schema::hasColumn('users', 'foto')) {
            // Hapus foto lama jika ada
            if ($karyawan->foto) {
                $cleanPath = ltrim(str_replace('storage/', '', $karyawan->foto), '/');
                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
                if (File::exists(public_path($karyawan->foto))) {
                    File::delete(public_path($karyawan->foto));
                }
            }
            $file = $request->file('foto');
            $filename = 'foto_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('uploads/foto_karyawan', $file, $filename);
            $payload['foto'] = $path;
        }

        foreach (array_keys($payload) as $key) {
            if (! Schema::hasColumn('users', $key)) {
                unset($payload[$key]);
            }
        }

        $karyawan->update($payload);

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return back();
    }

    public function status(Request $request, $id)
    {
        $data = User::findOrFail($id);
        if (! Schema::hasColumn('users', 'is_active')) {
            return redirect()
                ->route('admin.karyawan.index')
                ->with('warning', 'Kolom status (is_active) belum ada di tabel users. Status tidak bisa diubah.');
        }

        $data->update(['is_active' => (int) $request->is_active]);

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Status karyawan berhasil diubah.');
    }
}
