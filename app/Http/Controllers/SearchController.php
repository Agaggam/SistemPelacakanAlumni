<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    private const API_BASE = 'https://api-pddikti.kemdiktisaintek.go.id';
    private const HEADERS  = [
        'Accept'          => 'application/json, text/plain, */*',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Origin'          => 'https://pddikti.kemdiktisaintek.go.id',
        'Referer'         => 'https://pddikti.kemdiktisaintek.go.id/',
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36',
    ];

    /**
     * Halaman Search Mahasiswa utama — landing page aplikasi.
     * Publik (tanpa login), gabungan data lokal + PDDIKTI real-time.
     */
    public function index(Request $request)
    {
        $keyword   = trim($request->input('q', ''));
        $angkatan  = $request->input('angkatan');
        $prodi     = $request->input('prodi');
        $sort      = $request->input('sort', 'nama_asc');
        $sumber    = $request->input('sumber', 'semua'); // semua | lokal | pddikti

        // ── 1. Data Lokal (alumni tersimpan dari DB) ──────────────────────────
        $localQuery = Alumni::query();
        if ($keyword) {
            $localQuery->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('nim', 'like', "%{$keyword}%")
                  ->orWhere('prodi', 'like', "%{$keyword}%");
            });
        }
        if ($angkatan)   $localQuery->where('angkatan', $angkatan);
        if ($prodi)      $localQuery->where('prodi', 'like', "%{$prodi}%");

        $localAlumniModels = $localQuery->get();
        // Convert to shared array format
        $localAlumni = [];
        foreach ($localAlumniModels as $a) {
            $localAlumni[] = [
                'id'       => $a->id,
                'nama'     => $a->nama,
                'nim'      => $a->nim,
                'prodi'    => $a->prodi,
                'pt'       => 'Universitas Brawijaya', // or get from config/model if needed
                'jenjang'  => '-',
                'status'   => $a->status,
                'angkatan' => $a->angkatan,
                'tahun_lulus' => $a->tahun_lulus,
                'sumber'   => 'lokal',
                'model'    => $a, // pass model for route generation in view
            ];
        }

        // ── 2. Data PDDIKTI (real-time, hanya jika ada keyword & sumber ≠ lokal) ─
        $pddiktiResults = [];
        $pddiktiError   = null;
        $pddiktiSearched = false;

        if ($keyword && $sumber !== 'lokal') {
            $pddiktiSearched = true;
            try {
                $response = Http::withHeaders(self::HEADERS)
                    ->withOptions(['verify' => false])
                    ->timeout(12)
                    ->get(self::API_BASE . '/pencarian/mhs/' . urlencode($keyword));

                if ($response->successful()) {
                    $raw = $response->json();
                    $items = $raw['mahasiswa'] ?? (isset($raw[0]) ? $raw : []);
                    
                    // Log the first item to see the data structure
                    if (!empty($items) && is_array($items[0])) {
                        \Illuminate\Support\Facades\Log::info('PDDIKTI ITEM SAMPLE:', $items[0]);
                    }

                    foreach ($items as $item) {
                        if (!is_array($item)) continue;
                        $nim = $item['nim'] ?? null;
                        
                        // Extract angkatan: try explicit field, then from 'terdaftar', then fallback to 4 chars of NIM if it looks like a year
                        $angkatanApi = $item['angkatan'] ?? $item['terdaftar']['angkatan'] ?? null;
                        if (!$angkatanApi && $nim && preg_match('/^(20[0-9]{2}|19[0-9]{2})/', $nim, $matches)) {
                            $angkatanApi = $matches[1];
                        }

                        // Filter manually for PDDIKTI results based on the search form
                        $passAngkatan = !$angkatan || $angkatanApi == $angkatan;
                        $passProdi    = !$prodi || stripos($item['nama_prodi'] ?? $item['prodi'] ?? '', $prodi) !== false;

                        $adaDiLokal = $nim && $localAlumniModels->where('nim', $nim)->isNotEmpty();
                        if (!$adaDiLokal && $passAngkatan && $passProdi) {
                            $pddiktiResults[] = [
                                'id'       => $item['id'] ?? null,
                                'nama'     => $item['nama_mahasiswa'] ?? $item['nama'] ?? '-',
                                'nim'      => $nim ?? '-',
                                'prodi'    => $item['nama_prodi'] ?? $item['prodi'] ?? '-',
                                'pt'       => $item['nama_pt'] ?? $item['pt'] ?? '-',
                                'jenjang'  => $item['jenjang'] ?? null,
                                'status'   => '-',
                                'status_mhs' => $item['status'] ?? $item['status_mahasiswa'] ?? $item['status_mahasiswa_saat_ini'] ?? $item['status_terakhir'] ?? null,
                                'angkatan' => $angkatanApi,
                                'tahun_lulus' => null, // PDDIKTI search list doesn't return tahun_lulus
                                'sumber'   => 'pddikti',
                                'model'    => null,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[Search PDDIKTI] ' . $e->getMessage());
                $pddiktiError = 'Tidak dapat menghubungi PDDIKTI saat ini.';
            }
        }

        // ── 3. Combine and Sort Results ──────────────────────────────────────────
        $allResults = array_merge($localAlumni, $pddiktiResults);
        
        // Sorting logic for array
        usort($allResults, function($a, $b) use ($sort) {
            $angkatanA = (int) ($a['angkatan'] ?? 0);
            $angkatanB = (int) ($b['angkatan'] ?? 0);
            $namaA     = $a['nama'] ?? '';
            $namaB     = $b['nama'] ?? '';

            switch($sort) {
                case 'angkatan_asc':
                    return $angkatanA <=> $angkatanB;
                case 'angkatan_desc':
                    return $angkatanB <=> $angkatanA;
                case 'nama_desc':
                    return strcasecmp($namaB, $namaA);
                case 'nama_asc':
                default:
                    return strcasecmp($namaA, $namaB);
            }
        });

        // ── 4. Filter opsi untuk dropdown (Hanya dari hasil pencarian saat ini) ──────
        $angkatanList = collect(array_unique(array_filter(array_column($allResults, 'angkatan'))))->sortDesc()->values();
        $prodiList    = collect(array_unique(array_filter(array_column($allResults, 'prodi'))))->sort()->values();

        return view('search.index', compact(
            'keyword', 'angkatan', 'prodi', 'sort', 'sumber',
            'localAlumni', 'pddiktiResults', 'allResults', 'pddiktiError', 'pddiktiSearched',
            'angkatanList', 'prodiList'
        ));
    }

    /**
     * AJAX endpoint: ambil detail satu mahasiswa dari PDDIKTI real-time.
     */
    public function pddiktiDetailAjax(string $id)
    {
        try {
            $response = Http::withHeaders(self::HEADERS)
                ->withOptions(['verify' => false])
                ->timeout(10)
                ->get(self::API_BASE . '/detail/mhs/' . urlencode($id));

            if (!$response->successful()) {
                return response()->json(['error' => 'HTTP ' . $response->status()], 200);
            }

            $data = $response->json();
            if (empty($data)) {
                return response()->json(['error' => 'Tidak ditemukan'], 200);
            }

            $detail = isset($data['nim']) ? $data : ($data[0] ?? $data);
            
            $status = $detail['status_saat_ini']
                ?? $detail['status_terakhir']
                ?? $detail['status_mahasiswa_saat_ini']
                ?? $detail['status_mahasiswa']
                ?? null;

            // Extract angkatan
            $angkatan = $detail['angkatan'] ?? $detail['terdaftar']['angkatan'] ?? null;
            if (!$angkatan && isset($detail['nim'])) {
                if (preg_match('/^(20[0-9]{2}|19[0-9]{2})/', $detail['nim'], $matches)) {
                    $angkatan = $matches[1];
                }
            }

            return response()->json([
                'id'       => $id,
                'nama'     => $detail['nama_mahasiswa'] ?? $detail['nama'] ?? '-',
                'nim'      => $detail['nim'] ?? '-',
                'angkatan' => $angkatan,
                'status'   => $status,
                'is_lulus' => $status && str_starts_with(strtolower($status), 'lulus'),
                'tahun_lulus' => $detail['tahun_lulus'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('[Public PDDIKTI Detail AJAX] ' . $e->getMessage());
            return response()->json(['error' => 'Koneksi gagal'], 200);
        }
    }
}
