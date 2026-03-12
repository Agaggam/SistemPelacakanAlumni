<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumni::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('prodi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        $alumni = $query->latest()->paginate(15)->withQueryString();
        $statuses = [
            'Belum Dilacak',
            'Teridentifikasi dari PDDIKTI',
            'Perlu Verifikasi Manual',
            'Belum Ditemukan',
        ];
        $angkatanList = Alumni::selectRaw('angkatan')->distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');

        return view('alumni.index', compact('alumni', 'statuses', 'angkatanList'));
    }

    public function create()
    {
        return view('alumni.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:alumni,nim',
            'nama' => 'required|string|max:100',
            'prodi' => 'required|string|max:100',
            'fakultas' => 'nullable|string|max:100',
            'angkatan' => 'required|integer|min:1990|max:' . date('Y'),
            'tahun_lulus' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|string|max:20',
            'domisili' => 'nullable|string|max:100',
        ]);

        Alumni::create($validated);

        return redirect()->route('alumni.index')
            ->with('success', 'Data alumni berhasil ditambahkan.');
    }

    public function show(Alumni $alumni)
    {
        $histories = $alumni->trackingHistories()->latest()->get();
        return view('alumni.show', compact('alumni', 'histories'));
    }

    public function edit(Alumni $alumni)
    {
        return view('alumni.edit', compact('alumni'));
    }

    public function update(Request $request, Alumni $alumni)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:alumni,nim,' . $alumni->id,
            'nama' => 'required|string|max:100',
            'prodi' => 'required|string|max:100',
            'fakultas' => 'nullable|string|max:100',
            'angkatan' => 'required|integer|min:1990|max:' . date('Y'),
            'tahun_lulus' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|string|max:20',
            'domisili' => 'nullable|string|max:100',
        ]);

        $alumni->update($validated);

        return redirect()->route('alumni.show', $alumni)
            ->with('success', 'Data alumni berhasil diperbarui.');
    }

    public function destroy(Alumni $alumni)
    {
        $alumni->delete();
        return redirect()->route('alumni.index')
            ->with('success', 'Data alumni berhasil dihapus.');
    }
}
