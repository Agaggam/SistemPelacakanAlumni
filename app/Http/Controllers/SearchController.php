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
     * HALAMAN 1 — Search Mahasiswa (PDDIKTI)
     * Fokus: Data Resmi (Real-Time) dari API PDDIKTI.
     */
    public function index(Request $request)
    {
        $keyword = trim($request->input('q', ''));
        $angkatan = $request->input('angkatan');
        $prodi = $request->input('prodi');
        $sort = $request->input('sort', 'nama_asc');

        // ── 1. Data PDDIKTI (Layer 1 - Official) ──────────────────────────
        $results = [];
        $pddiktiError = null;
        $pddiktiSearched = false;

        if ($keyword) {
            $pddiktiSearched = true;
            try {
                $response = Http::withHeaders(self::HEADERS)
                    ->withOptions(['verify' => false])
                    ->timeout(12)
                    ->get(self::API_BASE . '/pencarian/mhs/' . urlencode($keyword));

                if ($response->successful()) {
                    $raw = $response->json();
                    $items = $raw['mahasiswa'] ?? (isset($raw[0]) ? $raw : []);
                    
                    foreach ($items as $item) {
                        if (!is_array($item)) continue;
                        $nim = $item['nim'] ?? null;
                        
                        // Prioritas: tahun dari tanggal_masuk > field angkatan > NIM
                        $tglMasuk = $item['tanggal_masuk'] ?? $item['tgl_masuk'] ?? $item['mulai_kuliah'] ?? null;
                        if ($tglMasuk && preg_match('/^(19|20)\d{2}/', $tglMasuk, $m)) {
                            $angkatanApi = $m[0];
                        } else {
                            $angkatanApi = $item['angkatan'] ?? $item['terdaftar']['angkatan'] ?? null;
                        }
                        if (!$angkatanApi && $nim && preg_match('/^(20[0-9]{2}|19[0-9]{2})/', $nim, $matches)) {
                            $angkatanApi = $matches[1];
                        }

                        // Filter by dropdowns
                        // Toleransi jika API tidak mengembalikan angkatan: biarkan lolos agar tidak menyebabkan "No results found"
                        $passAngkatan = !$angkatan || $angkatanApi == $angkatan || empty($angkatanApi);
                        $passProdi = !$prodi || stripos($item['nama_prodi'] ?? $item['prodi'] ?? '', $prodi) !== false;

                        if ($passAngkatan && $passProdi) {
                            $results[] = [
                                'id' => $item['id'] ?? null,
                                'nama' => $item['nama_mahasiswa'] ?? $item['nama'] ?? '-',
                                'nim' => $nim ?? '-',
                                'prodi' => $item['nama_prodi'] ?? $item['prodi'] ?? '-',
                                'pt' => $item['nama_pt'] ?? $item['pt'] ?? '-',
                                'jenjang' => $item['jenjang'] ?? null,
                                'status' => $item['status'] ?? $item['status_mahasiswa'] ?? $item['status_terakhir'] ?? '-',
                                'angkatan' => $angkatanApi,
                                'sumber' => 'pddikti',
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[Search PDDIKTI] ' . $e->getMessage());
                $pddiktiError = 'Layanan PDDIKTI sedang sibuk, silakan coba lagi.';
            }
        }

        // ── 2. Sorting ────────────────────────────────────────────────────
        usort($results, function($a, $b) use ($sort) {
            if ($sort == 'nama_asc') return strcasecmp($a['nama'], $b['nama']);
            if ($sort == 'nama_desc') return strcasecmp($b['nama'], $a['nama']);
            return 0;
        });

        $angkatanList = collect(array_unique(array_filter(array_column($results, 'angkatan'))))->sortDesc()->values();
        $prodiList = collect(array_unique(array_filter(array_column($results, 'prodi'))))->sort()->values();

        return view('search.index', compact('keyword', 'angkatan', 'prodi', 'sort', 'results', 'pddiktiError', 'pddiktiSearched', 'angkatanList', 'prodiList'));
    }

    /**
     * Public detail page: tampilkan detail mahasiswa dari PDDIKTI (public layout).
     */
    public function pddiktiDetail(string $id)
    {
        try {
            $response = Http::withHeaders(self::HEADERS)
                ->withOptions(['verify' => false])
                ->timeout(12)
                ->get(self::API_BASE . '/detail/mhs/' . urlencode($id));

            if (!$response->successful() || empty($response->json())) {
                return back()->with('error', 'Data tidak ditemukan di PDDIKTI.');
            }

            $data = $response->json();
            $detail = isset($data['nim']) ? $data : ($data[0] ?? $data);

            return view('search.detail', compact('detail', 'id'));
        } catch (\Exception $e) {
            Log::warning('[Public PDDIKTI Detail] ' . $e->getMessage());
            return back()->with('error', 'Gagal menghubungi server PDDIKTI.');
        }
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

            // Extract angkatan: prioritas dari tanggal_masuk
            $tglMasuk = $detail['tanggal_masuk'] ?? $detail['tgl_masuk'] ?? $detail['mulai_kuliah'] ?? null;
            if ($tglMasuk && preg_match('/^(19|20)\d{2}/', $tglMasuk, $m)) {
                $angkatan = $m[0];
            } else {
                $angkatan = $detail['angkatan'] ?? $detail['terdaftar']['angkatan'] ?? null;
            }
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
