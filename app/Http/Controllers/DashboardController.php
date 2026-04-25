<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    private const API_BASE = 'https://api-pddikti.kemdiktisaintek.go.id';
    private const HEADERS  = [
        'Accept'          => 'application/json, text/plain, */*',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Origin'          => 'https://pddikti.kemdiktisaintek.go.id',
        'Referer'         => 'https://pddikti.kemdiktisaintek.go.id/',
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36',
    ];

    public function index()
    {
        // ── Stats utama ────────────────────────────────────────────────────────
        $total = Alumni::count();

        $sudahLulus = Alumni::where(function ($q) {
            $q->whereNotNull('tahun_lulus')
              ->orWhere('status', 'Teridentifikasi dari PDDIKTI');
        })->count();

        $dariPddikti = Alumni::whereNotNull('data_pddikti')
            ->where('data_pddikti->sumber', 'PDDIKTI_REAL')
            ->count();

        $diinputManual = $total - $dariPddikti;

        $distribusiAngkatan = Alumni::select('angkatan', DB::raw('count(*) as total'))
            ->whereNotNull('angkatan')
            ->groupBy('angkatan')
            ->orderBy('angkatan', 'desc')
            ->limit(8)
            ->get();

        $distribusiProdi = Alumni::select('prodi', DB::raw('count(*) as total'))
            ->whereNotNull('prodi')
            ->groupBy('prodi')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $recentAlumni = Alumni::orderBy('created_at', 'desc')->take(8)->get();

        $sudahAdaTahunLulus = Alumni::whereNotNull('tahun_lulus')->count();

        $tahunLulusTerbanyak = Alumni::select('tahun_lulus', DB::raw('count(*) as total'))
            ->whereNotNull('tahun_lulus')
            ->groupBy('tahun_lulus')
            ->orderBy('total', 'desc')
            ->first()?->tahun_lulus;

        // Ambil daftar alumni yang punya PDDIKTI ID untuk refresh async
        $alumniDenganPddiktiId = Alumni::whereNotNull('data_pddikti')
            ->get()
            ->filter(fn($a) => !empty($a->data_pddikti['id_pddikti']))
            ->map(fn($a) => [
                'id'         => $a->id,
                'nama'       => $a->nama,
                'pddikti_id' => $a->data_pddikti['id_pddikti'],
            ])
            ->values()
            ->toArray();

        return view('dashboard.index', compact(
            'total', 'sudahLulus', 'dariPddikti', 'diinputManual',
            'distribusiAngkatan', 'distribusiProdi', 'recentAlumni',
            'sudahAdaTahunLulus', 'tahunLulusTerbanyak',
            'alumniDenganPddiktiId'
        ));
    }

    /**
     * AJAX endpoint: refresh status satu alumni dari PDDIKTI secara real-time.
     * Dipanggil oleh JS di dashboard saat widget load.
     */
    public function pddiktiStatus(string $pddiktiId)
    {
        try {
            $response = Http::withHeaders(self::HEADERS)
                ->withOptions(['verify' => false])
                ->timeout(10)
                ->get(self::API_BASE . '/detail/mhs/' . urlencode($pddiktiId));

            if (!$response->successful()) {
                return response()->json(['error' => 'HTTP ' . $response->status()], 200);
            }

            $data = $response->json();
            if (empty($data)) {
                return response()->json(['error' => 'Tidak ditemukan'], 200);
            }

            // Normalize response
            $detail = isset($data['nim']) ? $data : ($data[0] ?? $data);
            $status = $detail['status_saat_ini']
                ?? $detail['status_terakhir']
                ?? $detail['status_mahasiswa_saat_ini']
                ?? null;

            return response()->json([
                'nama'        => $detail['nama'] ?? $detail['nama_mahasiswa'] ?? '-',
                'nim'         => $detail['nim'] ?? '-',
                'prodi'       => $detail['nama_prodi'] ?? $detail['prodi'] ?? '-',
                'pt'          => $detail['nama_pt'] ?? $detail['pt'] ?? '-',
                'status'      => $status,
                'is_lulus'    => $status && str_starts_with(strtolower($status), 'lulus'),
                'tanggal_masuk' => $detail['tanggal_masuk'] ?? $detail['tgl_masuk'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::warning('[Dashboard PDDIKTI] ' . $e->getMessage());
            return response()->json(['error' => 'Timeout / koneksi gagal'], 200);
        }
    }

    /**
     * AJAX endpoint: search PDDIKTI untuk widget "Live PDDIKTI Search" di dashboard.
     */
    public function pddiktiSearch(string $keyword)
    {
        try {
            $response = Http::withHeaders(self::HEADERS)
                ->withOptions(['verify' => false])
                ->timeout(12)
                ->get(self::API_BASE . '/pencarian/mhs/' . urlencode($keyword));

            if (!$response->successful()) {
                return response()->json(['results' => [], 'error' => 'HTTP ' . $response->status()]);
            }

            $raw  = $response->json();
            $items = $raw['mahasiswa'] ?? (isset($raw[0]) ? $raw : []);

            $results = array_slice(array_map(function($item) {
                // Prioritas angkatan: tahun dari tanggal_masuk > field angkatan > NIM
                $tglMasuk = $item['tanggal_masuk'] ?? $item['tgl_masuk'] ?? $item['mulai_kuliah'] ?? null;
                if ($tglMasuk && preg_match('/^(19|20)\d{2}/', $tglMasuk, $m)) {
                    $angkatan = $m[0];
                } else {
                    $angkatan = $item['angkatan'] ?? null;
                    if (!$angkatan && isset($item['nim']) && preg_match('/^(20[0-9]{2}|19[0-9]{2})/', $item['nim'], $matches)) {
                        $angkatan = $matches[1];
                    }
                }
                return [
                    'id'       => $item['id'] ?? null,
                    'nama'     => $item['nama_mahasiswa'] ?? $item['nama'] ?? '-',
                    'nim'      => $item['nim'] ?? '-',
                    'prodi'    => $item['nama_prodi'] ?? $item['prodi'] ?? '-',
                    'pt'       => $item['nama_pt'] ?? $item['pt'] ?? '-',
                    'status'   => $item['status_mahasiswa'] ?? null,
                    'angkatan' => $angkatan,
                    'is_lulus' => str_starts_with(strtolower($item['status_mahasiswa'] ?? ''), 'lulus'),
                ];
            }, $items), 0, 10);

            return response()->json(['results' => $results, 'total' => count($items)]);
        } catch (\Exception $e) {
            Log::warning('[Dashboard PDDIKTI Search] ' . $e->getMessage());
            return response()->json(['results' => [], 'error' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
    }
}
