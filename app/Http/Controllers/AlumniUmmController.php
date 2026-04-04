<?php

namespace App\Http\Controllers;

use App\Models\AlumniUmm;
use Illuminate\Http\Request;

class AlumniUmmController extends Controller
{
    /**
     * HALAMAN 2 — Tracking Mahasiswa UMM (Local Database Search)
     */
    public function tracking(Request $request)
    {
        $keyword = $request->input('keyword');
        $prodi = $request->input('prodi');
        $fakultas = $request->input('fakultas');
        $sort = $request->input('sort', 'nama_asc');
        
        $query = AlumniUmm::query();
        
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('nim', 'LIKE', "%{$keyword}%");
            });
        }
        
        if ($prodi) {
            $query->where('prodi', $prodi);
        }

        if ($fakultas) {
            $query->where('fakultas', $fakultas);
        }

        // Sorting
        if ($sort == 'nama_desc') {
            $query->orderBy('nama', 'desc');
        } else {
            $query->orderBy('nama', 'asc');
        }
        
        $alumni = $query->paginate(20);
        
        $prodiList = AlumniUmm::distinct()->pluck('prodi')->filter()->sort()->values();
        $fakultasList = AlumniUmm::distinct()->pluck('fakultas')->filter()->sort()->values();
        $totalAlumni = AlumniUmm::count();
        
        return view('alumni_umm.tracking', compact('alumni', 'keyword', 'prodi', 'fakultas', 'sort', 'prodiList', 'fakultasList', 'totalAlumni'));
    }

    /**
     * HALAMAN 2 — Search Alumni UMM (ENRICHMENT)
     * Fokus: Data tambahan/enrichment dari database lokal.
     * Flow: Ambil nama dari Halaman 1 -> Cari di DB Lokal -> Tampilkan.
     */
    public function show(Request $request, string $nama)
    {
        // Matching logic: Nama sebagai key utama (Case-Insensitive)
        // Sesuai spec: Kalau ada -> tampilkan, kalau tidak -> tampilkan "Tidak ditemukan"
        $alumni = AlumniUmm::whereRaw('LOWER(nama) = ?', [strtolower($nama)])->first();

        return view('alumni_umm.detail', [
            'alumni' => $alumni,
            'searchNama' => $nama
        ]);
    }

    /**
     * Admin bisa tambah data manual jika tidak ditemukan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'nullable|string|max:50',
            'prodi' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'tiktok' => 'nullable|string',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
            'tempat_kerja' => 'nullable|string',
            'alamat_kerja' => 'nullable|string',
            'posisi' => 'nullable|string',
            'status_kerja' => 'nullable|in:PNS,Swasta,Wirausaha,BUMN',
            'sosmed_perusahaan' => 'nullable|string',
        ]);

        AlumniUmm::create($validated);

        return back()->with('success', 'Data alumni berhasil ditambahkan.');
    }

    /**
     * Admin bisa edit data.
     */
    public function update(Request $request, $id)
    {
        $alumni = AlumniUmm::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'nullable|string|max:50',
            'prodi' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'tiktok' => 'nullable|string',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
            'tempat_kerja' => 'nullable|string',
            'alamat_kerja' => 'nullable|string',
            'posisi' => 'nullable|string',
            'status_kerja' => 'nullable|in:PNS,Swasta,Wirausaha,BUMN',
            'sosmed_perusahaan' => 'nullable|string',
        ]);

        $alumni->update($validated);

        return back()->with('success', 'Data alumni berhasil diperbarui.');
    }
}
