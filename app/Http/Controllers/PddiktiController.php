<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PddiktiController extends Controller
{
    // Base API URL (sama dengan yang digunakan library ilhamrisky/pddiktiapi)
    private const API_BASE = 'https://api-pddikti.kemdiktisaintek.go.id';

    private const HEADERS = [
        'Accept'           => 'application/json, text/plain, */*',
        'Accept-Language'  => 'en-US,en;q=0.9',
        'Origin'           => 'https://pddikti.kemdiktisaintek.go.id',
        'Referer'          => 'https://pddikti.kemdiktisaintek.go.id/',
        'User-Agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36',
    ];

    /**
     * Kirim request GET ke PDDIKTI API dengan SSL verify disabled (untuk Laragon/localhost).
     */
    private function pddiktiGet(string $path): ?array
    {
        try {
            $response = Http::withHeaders(self::HEADERS)
                ->withOptions(['verify' => false])  // bypass SSL cert Windows
                ->timeout(15)
                ->get(self::API_BASE . $path);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('[PDDIKTI] HTTP ' . $response->status() . ' for ' . $path);
            return [];
        } catch (\Exception $e) {
            Log::error('[PDDIKTI] Request failed: ' . $e->getMessage());
            return null;
        }
    }


    public function index(Request $request)
    {
        $keyword  = $request->input('q', '');
        $results  = [];
        $error    = null;
        $searched = false;

        if ($keyword) {
            $searched = true;
            $data = $this->pddiktiGet('/pencarian/mhs/' . urlencode($keyword));

            if ($data === null) {
                $error = 'Tidak dapat menghubungi server PDDIKTI. Cek koneksi internet.';
            } elseif (is_array($data)) {
                // API bisa mengembalikan array langsung atau dalam key 'mahasiswa'
                $raw = $data['mahasiswa'] ?? $data;
                if (isset($raw[0])) {
                    $results = array_map(fn($item) => $this->normalizeItem($item), $raw);
                }
            }
        }

        return view('pddikti.search', compact('keyword', 'results', 'error', 'searched'));
    }

    /**
     * Detail satu mahasiswa dari PDDIKTI berdasarkan ID internal.
     */
    public function detail(Request $request, string $id)
    {
        $data = $this->pddiktiGet('/detail/mhs/' . urlencode($id));

        if ($data === null) {
            return back()->with('error', 'Gagal menghubungi PDDIKTI. Cek koneksi internet.');
        }

        if (empty($data)) {
            return back()->with('error', 'Data tidak ditemukan di PDDIKTI (ID: ' . $id . ').');
        }

        // PDDIKTI bisa return langsung atau dalam wrapper
        $detail = isset($data['nim']) ? $data : ($data[0] ?? $data);

        // Cek apakah sudah ada di database lokal (berdasarkan NIM)
        $nim         = $detail['nim'] ?? null;
        $savedAlumni = $nim ? Alumni::where('nim', $nim)->first() : null;

        return view('pddikti.detail', compact('detail', 'id', 'savedAlumni'));
    }

    /**
     * Simpan hasil PDDIKTI ke database lokal sebagai alumni tracked.
     */
    public function save(Request $request, string $id)
    {
        $request->validate([
            'nama'     => 'required|string',
            'nim'      => 'required|string|unique:alumni,nim',
            'prodi'    => 'required|string',
            'angkatan' => 'required|integer',
        ]);

        // Tentukan status awal berdasarkan status PDDIKTI
        $statusPddikti = strtolower($request->input('status_terakhir', ''));
        $isLulus       = str_starts_with($statusPddikti, 'lulus');
        $statusLocal   = $isLulus ? 'Teridentifikasi dari PDDIKTI' : 'Belum Dilacak';

        $alumni = Alumni::create([
            'nim'             => $request->nim,
            'nama'            => $request->nama,
            'prodi'           => $request->prodi,
            'fakultas'        => $request->input('fakultas'),
            'angkatan'        => $request->angkatan,
            'tahun_lulus'     => $request->input('tahun_lulus'),
            'domisili'        => $request->input('domisili'),
            'status'          => $statusLocal,
            'skor_kecocokan'  => $isLulus ? 0.95 : null,
            'data_pddikti'    => [
                'nama'                      => $request->nama,
                'nim'                       => $request->nim,
                'prodi'                     => $request->prodi,
                'perguruan_tinggi'          => $request->input('perguruan_tinggi'),
                'jenjang'                   => $request->input('jenjang'),
                'status_awal_mahasiswa'     => $request->input('status_awal'),
                'status_terakhir_mahasiswa' => $request->input('status_terakhir'),
                'tanggal_masuk'             => $request->input('tanggal_masuk'),
                'sumber'                    => 'PDDIKTI_REAL',
                'id_pddikti'                => $id,
            ],
            'last_tracked_at' => now(),
        ]);

        return redirect()->route('alumni.show', $alumni)
            ->with('success', "✅ Alumni {$alumni->nama} berhasil disimpan dari data PDDIKTI.");
    }

    // -------------------------------------------------------------------------

    private function normalizeItem(array $item): array
    {
        $status = $item['status'] ?? $item['status_mahasiswa'] ?? $item['status_mahasiswa_saat_ini'] ?? $item['status_terakhir'] ?? null;

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
            'id'       => $item['id'] ?? $item['id_mahasiswa'] ?? null,
            'nama'     => $item['nama_mahasiswa'] ?? $item['nama'] ?? '-',
            'nim'      => $item['nim'] ?? '-',
            'prodi'    => $item['nama_prodi'] ?? $item['prodi'] ?? '-',
            'pt'       => $item['nama_pt'] ?? $item['pt'] ?? '-',
            'jenjang'  => $item['jenjang'] ?? '-',
            'status'   => $status,
            'angkatan' => $angkatan,
            'is_lulus' => $status && str_starts_with(strtolower($status), 'lulus'),
        ];
    }
}

