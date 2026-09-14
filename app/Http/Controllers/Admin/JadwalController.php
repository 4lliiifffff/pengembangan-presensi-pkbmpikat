<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($tanggal);

        // Ambil 1 bulan
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $monthDays = collect();
        for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            $monthDays->push($date->copy());
        }

        // Agenda untuk tanggal yang dipilih
        $jadwals = Jadwal::whereDate('tanggal', $selectedDate)
            ->orderBy('created_at')
            ->get();

        // Hitung total agenda per hari dalam 1 bulan
        $monthCounts = Jadwal::selectRaw('DATE(tanggal) as tgl, COUNT(*) as total')
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        return view('admin.jadwal.index', compact(
            'jadwals',
            'selectedDate',
            'monthDays',
            'monthCounts'));
    }

    public function create()
    {
        return view('admin.jadwal.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
        ], [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ]);

        Jadwal::create($request->only(['judul', 'deskripsi', 'tanggal', 'lokasi']));

        return redirect()->route('admin.jadwal.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal)
    {
        return view('admin.jadwal.edit', compact('jadwal'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
        ], [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ]);

        $jadwal->update($request->only(['judul', 'deskripsi', 'tanggal', 'lokasi']));

        return redirect()->route('admin.jadwal.index', ['tanggal' => $jadwal->tanggal])
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $tanggal = $jadwal->tanggal;
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index', ['tanggal' => $tanggal])
            ->with('success', 'Agenda berhasil dihapus.');
    }
}
