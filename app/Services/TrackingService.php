<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\TrackingHistory;
use ilhamrisky\PddiktiApi\Api;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    private Api $pddiktiApi;

    // Threshold tahun: jika selisih angkatan ≥ ini → dianggap sudah waktunya lulus
    private const TAHUN_LULUS_THRESHOLD = 4;

    // Status mahasiswa di PDDIKTI yang dianggap sudah lulus
    private const STATUS_LULUS = ['lulus', 'graduate', 'wisuda', 'keluar'];

    public function __construct()
    {
        $this->pddiktiApi = new Api();
    }

    /**
     * Tentukan apakah alumni ini "sudah seharusnya lulus" berdasarkan angkatan.
     * Misal: angkatan 2020 → sudah 4+ tahun → kemungkinan lulus.
     */
    public function isLikelyGraduated(Alumni $alumni): bool
    {
        $selisihTahun = (int) date('Y') - (int) $alumni->angkatan;
        return $selisihTahun >= self::TAHUN_LULUS_THRESHOLD;
    }

    /**
     * Build search profile: variasi nama untuk query ke PDDIKTI.
     */
    public function buildSearchProfile(Alumni $alumni): array
    {
        $namaParts = explode(' ', $alumni->nama);
        $namaVariasi = array_unique([
            $alumni->nama,
            // Inisial depan + nama belakang: "D. Lestari"
            count($namaParts) > 1
                ? strtoupper(substr($namaParts[0], 0, 1)) . '. ' . implode(' ', array_slice($namaParts, 1))
                : $alumni->nama,
            // Nama belakang saja (jika lebih dari 1 kata)
            count($namaParts) > 1 ? end($namaParts) : $alumni->nama,
        ]);

        return [
            'nama_variasi' => array_values($namaVariasi),
            'nim' => $alumni->nim,
            'prodi' => $alumni->prodi,
            'angkatan' => $alumni->angkatan,
            'sudah_seharusnya_lulus' => $this->isLikelyGraduated($alumni),
        ];
    }

    /**
     * Generate query string untuk pencarian PDDIKTI.
     */
    public function generateSearchQuery(Alumni $alumni): string
    {
        return trim($alumni->nama . ($alumni->prodi ? ' ' . $alumni->prodi : ''));
    }

    /**
     * Cari alumni di PDDIKTI. Mengembalikan:
     *   - array hasil (kosong jika tidak ditemukan)
     *   - null jika API error/tidak bisa diakses → trigger fallback simulasi
     */
    public function searchPddikti(Alumni $alumni): ?array
    {
        $profile = $this->buildSearchProfile($alumni);
        $sudahSharusnyaLulus = $profile['sudah_seharusnya_lulus'];

        try {
            foreach ($profile['nama_variasi'] as $namaQuery) {
                Log::info("[PDDIKTI] Mencari: '{$namaQuery}'");
                $response = $this->pddiktiApi->searchMahasiswa($namaQuery);

                if (!empty($response) && is_array($response)) {
                    $normalized = $this->normalizeAndFilter($response, $alumni, $sudahSharusnyaLulus);
                    if (!empty($normalized)) {
                        Log::info("[PDDIKTI] Ditemukan " . count($normalized) . " kandidat untuk '{$namaQuery}'");
                        return $normalized;
                    }
                }
            }

            Log::info("[PDDIKTI] Tidak ada kandidat untuk: {$alumni->nama}");
            return [];

        } catch (\Exception $e) {
            Log::warning("[PDDIKTI] API error ({$alumni->nama}): " . $e->getMessage() . " — pakai simulasi");
            return null;
        }
    }

    /**
     * Normalisasi & filter hasil PDDIKTI:
     *
     * Strategi kelulusan:
     *   1. Jika status di PDDIKTI = "Lulus" → sudah lulus (valid target)
     *   2. Jika angkatan alumni ≤ tahun_sekarang - 4 → dianggap sudah lulus/perlu verifikasi
     *   3. Jika angkatan alumni < 4 tahun → masih aktif, mungkin belum lulus (filter lebih ketat)
     */
    private function normalizeAndFilter(array $rawResults, Alumni $alumni, bool $sudahSharusnyaLulus): array
    {
        $normalized = [];

        foreach ($rawResults as $item) {
            if (!is_array($item)) continue;

            $namaPddikti = $item['nama_mahasiswa'] ?? $item['nama'] ?? '';
            if (empty($namaPddikti)) continue;

            // Filter awal: nama harus mirip minimal 25%
            similar_text(strtolower($alumni->nama), strtolower($namaPddikti), $similarPct);
            if ($similarPct < 25) continue;

            // Ambil info status & angkatan dari PDDIKTI
            $statusPddikti = strtolower($item['status_mahasiswa'] ?? $item['status'] ?? '');
            $angkatanPddikti = (int) ($item['angkatan'] ?? $item['tahun_masuk'] ?? 0);

            // Tentukan apakah kandidat ini relevan sebagai alumni (sudah lulus):
            $isLulus = in_array($statusPddikti, self::STATUS_LULUS, true);
            $isAngkatanLama = $angkatanPddikti > 0
                && ((int) date('Y') - $angkatanPddikti) >= self::TAHUN_LULUS_THRESHOLD;
            $isAngkatanCocok = $angkatanPddikti === 0
                || abs($angkatanPddikti - (int) $alumni->angkatan) <= 1;

            // Jika alumni harusnya sudah lulus → include yang statusnya "Lulus" atau angkatan lama
            // Jika alumni belum waktunya lulus → hanya include yang statusnya "Lulus"
            $relevan = $sudahSharusnyaLulus
                ? ($isLulus || $isAngkatanLama || $isAngkatanCocok)
                : $isLulus;

            if (!$relevan) continue;

            $normalized[] = [
                'nama'              => $namaPddikti,
                'nim'               => $item['nim'] ?? null,
                'prodi'             => $item['nama_prodi'] ?? $item['prodi'] ?? null,
                'perguruan_tinggi'  => $item['nama_pt'] ?? $item['pt'] ?? null,
                'jenjang'           => $item['jenjang'] ?? null,
                'status_mahasiswa'  => $item['status_mahasiswa'] ?? $statusPddikti ?: null,
                'angkatan'          => $angkatanPddikti ?: null,
                'id_mahasiswa'      => $item['id'] ?? $item['id_mahasiswa'] ?? null,
                'sumber'            => 'PDDIKTI_REAL',
                'kemiripan_nama'    => round($similarPct, 1),
                'is_lulus'          => $isLulus,
            ];
        }

        // Urutkan: yang sudah "Lulus" di PDDIKTI diprioritaskan, lalu kemiripan nama tertinggi
        usort($normalized, function ($a, $b) {
            if ($a['is_lulus'] !== $b['is_lulus']) {
                return $b['is_lulus'] <=> $a['is_lulus'];
            }
            return $b['kemiripan_nama'] <=> $a['kemiripan_nama'];
        });

        return $normalized;
    }

    /**
     * Ambil detail lengkap mahasiswa dari PDDIKTI berdasarkan ID internal.
     */
    public function getDetailMahasiswaPddikti(string $idMahasiswa): ?array
    {
        try {
            $detail = $this->pddiktiApi->getDetailMahasiswa($idMahasiswa);
            return (!empty($detail) && is_array($detail)) ? $detail : null;
        } catch (\Exception $e) {
            Log::warning("[PDDIKTI] getDetailMahasiswa error ({$idMahasiswa}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fallback simulasi — digunakan HANYA jika API PDDIKTI tidak dapat diakses.
     */
    public function simulatePddiktiSearch(Alumni $alumni): array
    {
        $seed = crc32($alumni->nim . $alumni->nama);
        srand($seed);

        $isLikelyGrad = $this->isLikelyGraduated($alumni);

        // Jika angkatan > 4 tahun, probabilitas "ditemukan" lebih tinggi
        $probFound     = $isLikelyGrad ? 70 : 40;
        $probStrong    = $isLikelyGrad ? 45 : 25;

        $roll = rand(0, 99);

        if ($roll >= $probFound) return []; // tidak ditemukan

        $scenario = $roll < $probStrong ? 'found_strong' : 'found_weak';
        $namaNoise = $scenario === 'found_strong' ? $alumni->nama : $this->addNoise($alumni->nama);
        $statusSim = $isLikelyGrad ? 'Lulus' : 'Aktif';

        return [[
            'nama'             => $namaNoise,
            'nim'              => $alumni->nim,
            'prodi'            => $alumni->prodi,
            'perguruan_tinggi' => 'Universitas ' . ($alumni->domisili ?? 'Indonesia'),
            'status_mahasiswa' => $statusSim,
            'angkatan'         => $alumni->angkatan,
            'id_mahasiswa'     => null,
            'sumber'           => 'SIMULASI',
            'kemiripan_nama'   => $scenario === 'found_strong' ? 95.0 : 60.0,
            'is_lulus'         => $isLikelyGrad,
        ]];
    }

    private function addNoise(string $name): string
    {
        $parts = explode(' ', $name);
        return count($parts) > 1
            ? $parts[0] . ' ' . strtoupper(substr($parts[1], 0, 1)) . '.'
            : $name;
    }

    /**
     * Hitung skor kecocokan antara data alumni lokal dan satu kandidat PDDIKTI.
     *
     * Bobot:
     *   - Nama         : 35%
     *   - Status Lulus : 25%  (kalau di PDDIKTI statusnya "Lulus" → bonus besar)
     *   - Prodi        : 20%
     *   - Angkatan     : 10%
     *   - NIM          : 10%
     */
    public function calculateMatchScore(Alumni $alumni, array $result): float
    {
        $score    = 0.0;
        $maxScore = 100.0;

        // Nama (35)
        similar_text(strtolower($alumni->nama), strtolower($result['nama'] ?? ''), $namaSim);
        $score += ($namaSim / 100) * 35;

        // Status Lulus di PDDIKTI (25)
        if (!empty($result['is_lulus'])) {
            $score += 25;
        } elseif ($this->isLikelyGraduated($alumni) && !empty($result['angkatan'])) {
            // jika angkatan lama, beri setengah poin
            $score += 12;
        }

        // Prodi (20)
        if (!empty($result['prodi'])) {
            similar_text(strtolower($alumni->prodi), strtolower($result['prodi']), $prodiSim);
            $score += ($prodiSim / 100) * 20;
        }

        // Angkatan (10)
        if (!empty($result['angkatan']) && $alumni->angkatan) {
            $diff = abs((int) $result['angkatan'] - (int) $alumni->angkatan);
            if ($diff === 0) $score += 10;
            elseif ($diff === 1) $score += 5;
        }

        // NIM (10)
        if (!empty($result['nim']) && $result['nim'] === $alumni->nim) {
            $score += 10;
        }

        return round(min($score, $maxScore) / $maxScore, 4);
    }

    public function classifyResult(float $score): string
    {
        if ($score >= 0.70) return 'Kemungkinan kuat';
        if ($score >= 0.40) return 'Perlu verifikasi';
        return 'Tidak cocok';
    }

    public function determineStatus(string $classification, array $results): string
    {
        if (empty($results)) return 'Belum Ditemukan';
        return match($classification) {
            'Kemungkinan kuat' => 'Teridentifikasi dari PDDIKTI',
            'Perlu verifikasi' => 'Perlu Verifikasi Manual',
            default            => 'Belum Ditemukan',
        };
    }

    /**
     * Pipeline pelacakan lengkap untuk satu alumni.
     */
    public function runTracking(Alumni $alumni): array
    {
        $statusSebelum      = $alumni->status;
        $query              = $this->generateSearchQuery($alumni);
        $usedFallback       = false;
        $sudahSharusnyaLulus = $this->isLikelyGraduated($alumni);

        // 1. Coba API PDDIKTI nyata
        $results = $this->searchPddikti($alumni);

        // 2. Fallback simulasi jika API tidak bisa diakses
        if ($results === null) {
            $results      = $this->simulatePddiktiSearch($alumni);
            $usedFallback = true;
        }

        // 3. Scoring — cari kandidat terbaik
        $bestResult = null;
        $bestScore  = 0.0;

        foreach ($results as $result) {
            $score = $this->calculateMatchScore($alumni, $result);
            if ($score > $bestScore) {
                $bestScore  = $score;
                $bestResult = $result;
            }
        }

        // 4. Classify & tentukan status baru
        $classification = $this->classifyResult($bestScore);
        $statusBaru     = $this->determineStatus($classification, $results);

        // 5. Jika kandidat kuat & punya ID → ambil detail tambahan
        if ($bestResult && !empty($bestResult['id_mahasiswa']) && $classification === 'Kemungkinan kuat') {
            $detail = $this->getDetailMahasiswaPddikti($bestResult['id_mahasiswa']);
            if ($detail) {
                $bestResult['detail_pddikti'] = $detail;
            }
        }

        // 6. Update data alumni
        $alumni->update([
            'status'          => $statusBaru,
            'skor_kecocokan'  => $bestScore > 0 ? $bestScore : null,
            'data_pddikti'    => $bestResult,
            'last_tracked_at' => now(),
        ]);

        // 7. Simpan riwayat tracking
        $sumber          = $usedFallback ? '[SIMULASI]' : '[PDDIKTI Real]';
        $graduasiInfo    = $sudahSharusnyaLulus ? 'Angkatan lama (≥4 thn)' : 'Angkatan baru (<4 thn)';
        $jumlahKandidat  = count($results);

        TrackingHistory::create([
            'alumni_id'       => $alumni->id,
            'status_sebelum'  => $statusSebelum,
            'status_sesudah'  => $statusBaru,
            'skor_kecocokan'  => $bestScore > 0 ? $bestScore : null,
            'query_pencarian' => $query,
            'hasil_mentah'    => $bestResult
                ? json_encode($bestResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : 'Tidak ada kandidat ditemukan.',
            'catatan' => "{$sumber} {$graduasiInfo} | Klasifikasi: {$classification} | Skor: "
                . round($bestScore * 100) . "% | {$jumlahKandidat} kandidat",
        ]);

        return [
            'alumni'           => $alumni->fresh(),
            'status_baru'      => $statusBaru,
            'skor'             => $bestScore,
            'classification'   => $classification,
            'used_real_api'    => !$usedFallback,
            'jumlah_kandidat'  => $jumlahKandidat,
            'sudah_seharusnya_lulus' => $sudahSharusnyaLulus,
        ];
    }

    /**
     * Batch tracking: lacak semua alumni yang perlu diperbarui.
     */
    public function runBatchTracking(): array
    {
        $results = [];
        foreach (Alumni::perluDiTracking()->get() as $alumni) {
            $results[] = $this->runTracking($alumni);
        }
        return $results;
    }
}
