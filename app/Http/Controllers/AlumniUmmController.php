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
        $keyword  = $request->input('keyword');
        $prodi    = $request->input('prodi');
        $fakultas = $request->input('fakultas');
        $sort     = $request->input('sort', 'nama_asc');

        $query = AlumniUmm::query();

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('nim', 'LIKE', "%{$keyword}%");
            });
        }
        if ($prodi)    $query->where('prodi', $prodi);
        if ($fakultas) $query->where('fakultas', $fakultas);

        match($sort) {
            'nama_desc'        => $query->orderBy('nama', 'desc'),
            'enrichment_desc'  => $query->orderByRaw("(
                (CASE WHEN linkedin IS NOT NULL AND linkedin != '' THEN 1 ELSE 0 END) +
                (CASE WHEN instagram IS NOT NULL AND instagram != '' THEN 1 ELSE 0 END) +
                (CASE WHEN facebook IS NOT NULL AND facebook != '' THEN 1 ELSE 0 END) +
                (CASE WHEN tiktok IS NOT NULL AND tiktok != '' THEN 1 ELSE 0 END) +
                (CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) +
                (CASE WHEN no_hp IS NOT NULL AND no_hp != '' THEN 1 ELSE 0 END) +
                (CASE WHEN tempat_kerja IS NOT NULL AND tempat_kerja != '' THEN 1 ELSE 0 END) +
                (CASE WHEN posisi IS NOT NULL AND posisi != '' THEN 1 ELSE 0 END)
            ) DESC"),
            'enrichment_asc'   => $query->orderByRaw("(
                (CASE WHEN linkedin IS NOT NULL AND linkedin != '' THEN 1 ELSE 0 END) +
                (CASE WHEN instagram IS NOT NULL AND instagram != '' THEN 1 ELSE 0 END) +
                (CASE WHEN facebook IS NOT NULL AND facebook != '' THEN 1 ELSE 0 END) +
                (CASE WHEN tiktok IS NOT NULL AND tiktok != '' THEN 1 ELSE 0 END) +
                (CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) +
                (CASE WHEN no_hp IS NOT NULL AND no_hp != '' THEN 1 ELSE 0 END) +
                (CASE WHEN tempat_kerja IS NOT NULL AND tempat_kerja != '' THEN 1 ELSE 0 END) +
                (CASE WHEN posisi IS NOT NULL AND posisi != '' THEN 1 ELSE 0 END)
            ) ASC"),
            default => $query->orderBy('nama', 'asc'),
        };

        $alumni = $query->paginate(20);

        $prodiList    = \Illuminate\Support\Facades\Cache::remember('prodi_list', 3600, fn() => AlumniUmm::distinct()->pluck('prodi')->filter()->sort()->values());
        $fakultasList = \Illuminate\Support\Facades\Cache::remember('fakultas_list', 3600, fn() => AlumniUmm::distinct()->pluck('fakultas')->filter()->sort()->values());
        $totalAlumni  = \Illuminate\Support\Facades\Cache::remember('total_alumni_count', 3600, fn() => AlumniUmm::count());

        // ── Coverage Statistics ───────────────────────────────────────────────
        $metrics = \Illuminate\Support\Facades\Cache::remember('alumni_tracking_metrics', 600, function() use ($totalAlumni) {
            $coverageFields = ['linkedin','instagram','facebook','tiktok','email','no_hp','tempat_kerja','posisi'];
            $coverage = [];
            foreach ($coverageFields as $field) {
                $found   = AlumniUmm::whereNotNull($field)->where($field, '!=', '')->count();
                $scraped = AlumniUmm::whereNotNull($field)->where($field, '!=', '')->where('data_source', 'scraped')->count();
                $coverage[$field] = [
                    'found'      => $found,
                    'scraped'    => $scraped,
                    'generated'  => $found - $scraped,
                    'pct'        => $totalAlumni > 0 ? round($found / $totalAlumni * 100, 1) : 0,
                    'pct_scraped'=> $totalAlumni > 0 ? round($scraped / $totalAlumni * 100, 2) : 0,
                ];
            }

            // Alumni with at least 1 field filled
            $alumniWithAnyData = AlumniUmm::where(function($q) use ($coverageFields) {
                $first = true;
                foreach ($coverageFields as $f) {
                    if ($first) { $q->whereNotNull($f)->where($f, '!=', ''); $first = false; }
                    else        { $q->orWhere(fn($q2) => $q2->whereNotNull($f)->where($f, '!=', '')); }
                }
            })->count();

            $overallCoverage  = $totalAlumni > 0 ? round($alumniWithAnyData / $totalAlumni * 100, 1) : 0;

            // Accuracy = scraped / (scraped + generated) among enriched records
            $totalScraped   = AlumniUmm::where('data_source', 'scraped')->count();
            $totalGenerated = AlumniUmm::where('data_source', 'generated')->count();
            $totalEnriched  = $totalScraped + $totalGenerated;
            $overallAccuracy = $totalEnriched > 0 ? round($totalScraped / $totalEnriched * 100, 1) : 0;

            return compact('coverage', 'alumniWithAnyData', 'overallCoverage', 'totalScraped', 'totalGenerated', 'overallAccuracy');
        });

        // Extract metrics to individual variables to pass to view
        extract($metrics);

        return view('alumni_umm.tracking', compact(
            'alumni', 'keyword', 'prodi', 'fakultas', 'sort',
            'prodiList', 'fakultasList', 'totalAlumni',
            'coverage', 'overallCoverage', 'overallAccuracy',
            'alumniWithAnyData', 'totalScraped', 'totalGenerated'
        ));
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
