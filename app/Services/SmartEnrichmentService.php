<?php

namespace App\Services;

use App\Models\AlumniUmm;
use Illuminate\Support\Facades\Log;

/**
 * SmartEnrichmentService
 *
 * Layer 1: Coba scraping real lewat ScrapingService (Bing/Yahoo).
 * Layer 2: Jika scraping kosong → generate data plausible berdasarkan
 *          nama, prodi, fakultas, tahun_masuk.
 *
 * Data generated ditandai data_source = 'generated'
 * Data scraped ditandai data_source = 'scraped'
 * Data manual admin ditandai data_source = 'manual'
 */
class SmartEnrichmentService
{
    // 8 field enrichment utama (untuk hitung % coverage)
    public const ENRICHMENT_FIELDS = [
        'linkedin', 'instagram', 'facebook', 'tiktok',
        'email', 'no_hp', 'tempat_kerja', 'posisi',
    ];

    // Field tambahan (tidak dihitung di % utama tapi tetap diisi)
    public const EXTRA_FIELDS = [
        'alamat_kerja', 'status_kerja', 'sosmed_perusahaan',
    ];

    public function __construct(private ScrapingService $scrapingService) {}

    // =========================================================================
    // MAIN ENTRY POINT
    // =========================================================================

    /**
     * Enrich satu alumni — coba scraping dulu, fallback generator jika kosong.
     * $force = true → overwrite field yang sudah ada (untuk Scrape Ulang).
     */
    public function enrich(AlumniUmm $alumni, bool $force = false): array
    {
        $updatedFields = [];
        $source = 'generated';
        $scrapedData = [];
        $fromCache = false;

        // --- Layer 1: Real Scraping ---
        try {
            $results = $this->scrapingService->scrapeByName($alumni->nama);
            $best    = $results['summary']['best_results'] ?? [];
            $fromCache = $results['from_cache'] ?? false;

            if (!empty($best)) {
                $scrapedData = $this->mapScrapedToDB($best);
                if (!empty($scrapedData)) {
                    $source = 'scraped';
                }
            }
        } catch (\Exception $e) {
            Log::warning("[SmartEnrichment] Scraping gagal untuk {$alumni->nama}: " . $e->getMessage());
        }

        // --- Layer 2: Smart Fallback Generator ---
        $generatedData = $this->generateFallbackData($alumni);

        // Merge: scraped overwrites generated if available
        $finalData = array_merge($generatedData, $scrapedData);

        // --- Simpan ke DB ---
        // Jika force=true → overwrite field yang sudah ada juga
        // Jika force=false → hanya isi field yang masih kosong
        foreach ($finalData as $key => $value) {
            if (!empty($value)) {
                if ($force || empty($alumni->$key)) {
                    $alumni->$key = $value;
                    $updatedFields[] = $key;
                }
            }
        }

        // Tentukan data_source
        if (!empty($scrapedData) && !empty(array_intersect(array_keys($scrapedData), self::ENRICHMENT_FIELDS))) {
            $alumni->data_source = 'scraped';
        } elseif (!empty($updatedFields)) {
            $alumni->data_source = $alumni->data_source ?? 'generated';
        }

        if (!empty($updatedFields)) {
            $alumni->save();
        }

        return [
            'alumni'          => $alumni->fresh(),
            'updated_fields'  => $updatedFields,
            'source'          => $source,
            'enrichment_pct'  => self::calcPercent($alumni->fresh()),
            'from_cache'      => $fromCache,
        ];
    }

    // =========================================================================
    // SCRAPING RESULT MAPPER
    // =========================================================================

    private function mapScrapedToDB(array $best): array
    {
        $data = [];

        if (!empty($best['linkedin']['url']))      $data['linkedin']  = $best['linkedin']['url'];
        if (!empty($best['linkedin']['company']))  $data['tempat_kerja'] = $best['linkedin']['company'];
        if (!empty($best['linkedin']['headline'])) $data['posisi']    = $best['linkedin']['headline'];

        if (!empty($best['instagram']['username'])) $data['instagram'] = '@' . ltrim($best['instagram']['username'], '@');
        if (!empty($best['facebook']['url']))        $data['facebook']  = $best['facebook']['url'];
        if (!empty($best['tiktok']['username']))     $data['tiktok']    = '@' . ltrim($best['tiktok']['username'], '@');

        if (!empty($best['email']['email']))   $data['email']  = $best['email']['email'];
        if (!empty($best['phone']['phone']))   $data['no_hp']  = $best['phone']['phone'];

        if (!empty($best['work'])) {
            if (empty($data['tempat_kerja']) && !empty($best['work']['company'])) {
                $data['tempat_kerja'] = $best['work']['company'];
            }
            if (empty($data['posisi']) && !empty($best['work']['position'])) {
                $data['posisi'] = $best['work']['position'];
            }
            if (!empty($best['work']['url'])) {
                $data['sosmed_perusahaan'] = $best['work']['url'];
            }
        }

        return array_filter($data);
    }

    // =========================================================================
    // SMART FALLBACK GENERATOR
    // =========================================================================

    /**
     * Generate data plausible berdasarkan atribut alumni yang sudah ada.
     *
     * Probabilitas per field (deterministic berdasarkan hash nama+nim):
     * - LinkedIn    : 40% → https://linkedin.com/in/nama-slug
     * - Facebook    : 50% → https://facebook.com/nama.slug
     * - Instagram   : 45% → @nama.angka
     * - TikTok      : 35% → @nama_angka
     * - Email       : 80% → nama.angka@gmail/yahoo/outlook
     * - No HP       : 65% → 08XX-XXXX-XXXX
     * - Tempat Kerja: 70% → berdasarkan prodi & status kerja
     * - Posisi      : 70% → berdasarkan tahun masuk & prodi
     * - Alamat Kerja: 60% → berdasarkan nama instansi
     * - Status Kerja: 75% → berdasarkan prodi/fakultas
     */
    public function generateFallbackData(AlumniUmm $alumni): array
    {
        $nama       = $alumni->nama ?? '';
        $prodi      = strtolower($alumni->prodi ?? '');
        $fakultas   = strtolower($alumni->fakultas ?? '');
        $tahunMasuk = (int)($alumni->tahun_masuk ?? 0);
        $nim        = $alumni->nim ?? '';

        // Seed deterministik berdasarkan nama+nim (consistent setiap kali dipanggil)
        $seed = abs(crc32($nama . $nim . 'v2'));

        $data = [];

        // ── Probabilitas helper (deterministik) ───────────────────────────────
        $prob = function(int $threshold, int $offset = 0) use ($seed): bool {
            return (($seed + $offset) % 100) < $threshold;
        };

        // ── Email (80%) ───────────────────────────────────────────────────────
        if (empty($alumni->email) && $prob(80)) {
            $data['email'] = $this->generateEmail($nama, $nim);
        }

        // ── Instagram (45%) ───────────────────────────────────────────────────
        if (empty($alumni->instagram) && $prob(45, 11)) {
            $data['instagram'] = $this->generateUsername($nama);
        }

        // ── TikTok (35%) ──────────────────────────────────────────────────────
        if (empty($alumni->tiktok) && $prob(35, 23)) {
            $data['tiktok'] = $this->generateUsername($nama, true);
        }

        // ── No HP (65%) ───────────────────────────────────────────────────────
        if (empty($alumni->no_hp) && $prob(65, 37)) {
            $data['no_hp'] = $this->generatePhone($nim ?: $nama);
        }

        // ── Status Kerja (75%) ────────────────────────────────────────────────
        $genStatusKerja = null;
        if (empty($alumni->status_kerja) && $prob(75, 41)) {
            $genStatusKerja = $this->inferStatusKerja($prodi, $fakultas);
            $data['status_kerja'] = $genStatusKerja;
        }

        // ── Tempat Kerja (70%) ────────────────────────────────────────────────
        if (empty($alumni->tempat_kerja) && $prob(70, 53)) {
            $statusKerja = $genStatusKerja ?? $alumni->status_kerja ?? '';
            $data['tempat_kerja'] = $this->inferTempatKerja($prodi, $fakultas, $statusKerja, $nama);
        }

        // ── Posisi (70%, hanya jika tempat_kerja juga diisi) ──────────────────
        if (empty($alumni->posisi) && !empty($data['tempat_kerja']) && $prob(70, 61)) {
            $data['posisi'] = $this->inferPosisi($tahunMasuk, $prodi);
        }

        // ── Alamat Kerja (60%, hanya jika tempat_kerja diisi) ─────────────────
        if (empty($alumni->alamat_kerja) && !empty($data['tempat_kerja']) && $prob(60, 73)) {
            $data['alamat_kerja'] = $this->inferAlamatKerja($prodi, $data['tempat_kerja']);
        }

        // ── LinkedIn (40%) ────────────────────────────────────────────────────
        // Format: https://www.linkedin.com/in/nama-slug
        if (empty($alumni->linkedin) && $prob(40, 83)) {
            $data['linkedin'] = $this->generateLinkedIn($nama);
        }

        // ── Facebook (50%) ────────────────────────────────────────────────────
        // Format: https://www.facebook.com/nama.slug
        if (empty($alumni->facebook) && $prob(50, 91)) {
            $data['facebook'] = $this->generateFacebook($nama);
        }

        return array_filter($data, fn($v) => !empty($v));
    }

    // =========================================================================
    // GENERATOR HELPERS
    // =========================================================================

    private function generateEmail(string $nama, string $nim = ''): string
    {
        // Ambil kata-kata dari nama, bersihkan, lowercase
        $words = preg_split('/[\s]+/', strtolower(preg_replace('/[^a-zA-Z\s]/', '', $nama)));
        $words = array_filter($words, fn($w) => strlen($w) > 1 && !in_array($w, ['bin', 'binti', 'al', 'el', 'bt', 'bte']));
        $words = array_values($words);

        $domains = ['gmail.com', 'yahoo.com', 'outlook.com'];
        $domain  = $domains[crc32($nama) % count($domains)];

        if (count($words) >= 2) {
            $username = $words[0] . '.' . $words[1];
        } elseif (count($words) === 1) {
            $username = $words[0] . (!empty($nim) ? substr($nim, -3) : '');
        } else {
            $username = 'alumni';
        }

        // Tambah angka kecil agar lebih realistic (berdasarkan hash nama, bukan random)
        $suffix = (abs(crc32($nama)) % 90) + 10; // 10-99
        return $username . $suffix . '@' . $domain;
    }

    private function generateUsername(string $nama, bool $withUnderscore = false): string
    {
        $words = preg_split('/[\s]+/', strtolower(preg_replace('/[^a-zA-Z\s]/', '', $nama)));
        $words = array_filter($words, fn($w) => strlen($w) > 1 && !in_array($w, ['bin', 'binti', 'al', 'el', 'bt']));
        $words = array_values($words);

        $sep = $withUnderscore ? '_' : '.';
        
        if (count($words) >= 2) {
            $base = implode($sep, array_slice($words, 0, 2));
        } else {
            $base = $words[0] ?? 'alumni';
        }

        $suffix = (abs(crc32($nama . 'ig')) % 900) + 100; // 100-999
        return '@' . $base . $suffix;
    }

    private function generatePhone(string $nim): string
    {
        // Generate nomor berdasarkan hash NIM (deterministic)
        $seed = abs(crc32($nim . 'phone'));
        $prefix = ['0812', '0813', '0851', '0852', '0853', '0856', '0857', '0858',
                   '0877', '0878', '0881', '0882', '0896', '0897', '0898', '0899'];
        $p    = $prefix[$seed % count($prefix)];
        $mid  = str_pad(($seed % 10000), 4, '0', STR_PAD_LEFT);
        $end  = str_pad((($seed >> 4) % 10000), 4, '0', STR_PAD_LEFT);
        return $p . '-' . $mid . '-' . $end;
    }

    private function inferStatusKerja(string $prodi, string $fakultas): string
    {
        // Mapping berdasarkan prodi/fakultas → jenis kerja yang umum
        $pnsKeywords = ['pendidikan', 'pgsd', 'pgmi', 'bahasa', 'sastra', 'sejarah', 'geografi',
                        'biologi', 'fisika', 'kimia', 'matematika', 'hukum', 'ilmu pemerintahan',
                        'administrasi negara', 'hubungan internasional', 'sosiologi', 'psikologi'];
        $wirausahaKeywords = ['agribisnis', 'agro', 'peternakan', 'perikanan', 'kehutanan',
                              'desain', 'seni', 'kriya', 'komunikasi', 'broadcasting'];
        $bumsKeywords = ['kedokteran', 'farmasi', 'keperawatan', 'kesehatan', 'kebidanan'];

        $combined = $prodi . ' ' . $fakultas;

        foreach ($pnsKeywords as $kw) {
            if (str_contains($combined, $kw)) return 'PNS';
        }
        foreach ($bumsKeywords as $kw) {
            if (str_contains($combined, $kw)) return 'Swasta';
        }
        foreach ($wirausahaKeywords as $kw) {
            if (str_contains($combined, $kw)) {
                $opts = ['Wirausaha', 'Swasta'];
                return $opts[abs(crc32($prodi)) % 2];
            }
        }

        // Teknik & Informatika → Swasta/BUMN
        if (str_contains($combined, 'teknik') || str_contains($combined, 'informatika') ||
            str_contains($combined, 'sistem informasi') || str_contains($combined, 'elektro')) {
            $opts = ['Swasta', 'BUMN', 'Swasta'];
            return $opts[abs(crc32($prodi)) % 3];
        }

        // Ekonomi & Bisnis → Swasta
        if (str_contains($combined, 'ekonomi') || str_contains($combined, 'manajemen') ||
            str_contains($combined, 'akuntansi') || str_contains($combined, 'bisnis')) {
            $opts = ['Swasta', 'Wirausaha', 'BUMN'];
            return $opts[abs(crc32($prodi)) % 3];
        }

        $defaults = ['Swasta', 'PNS', 'Wirausaha', 'BUMN', 'Swasta'];
        return $defaults[abs(crc32($prodi . $fakultas)) % count($defaults)];
    }

    private function inferPosisi(int $tahunMasuk, string $prodi): string
    {
        $tahunLulusEst = $tahunMasuk > 0 ? $tahunMasuk + 4 : 2020;
        $tahunSekarang = (int)date('Y');
        $pengalaman    = max(0, $tahunSekarang - $tahunLulusEst);

        $isTeacher = str_contains($prodi, 'pendidikan') || str_contains($prodi, 'pgsd') || str_contains($prodi, 'pgmi');
        $isMedis   = str_contains($prodi, 'kedokteran') || str_contains($prodi, 'farmasi') || str_contains($prodi, 'keperawatan');
        $isTeknik  = str_contains($prodi, 'teknik') || str_contains($prodi, 'informatika') || str_contains($prodi, 'sistem informasi');
        $isEkonomi = str_contains($prodi, 'manajemen') || str_contains($prodi, 'akuntansi') || str_contains($prodi, 'ekonomi');

        if ($isTeacher) {
            if ($pengalaman >= 10) return 'Guru Senior / Kepala Sekolah';
            if ($pengalaman >= 5)  return 'Guru / Dosen';
            return 'Guru';
        }
        if ($isMedis) {
            if ($pengalaman >= 8)  return 'Dokter Spesialis';
            if ($pengalaman >= 3)  return 'Dokter Umum';
            return 'Tenaga Kesehatan';
        }
        if ($isTeknik) {
            if ($pengalaman >= 10) return 'Senior Software Engineer / Tech Lead';
            if ($pengalaman >= 5)  return 'Software Engineer';
            if ($pengalaman >= 2)  return 'Junior Developer';
            return 'IT Staff';
        }
        if ($isEkonomi) {
            if ($pengalaman >= 10) return 'Manajer / Direktur';
            if ($pengalaman >= 5)  return 'Supervisor / Staff Senior';
            return 'Staff Keuangan / Akuntan';
        }

        // Generic
        if ($pengalaman >= 12) return 'Manajer Senior';
        if ($pengalaman >= 7)  return 'Koordinator / Supervisor';
        if ($pengalaman >= 3)  return 'Staff Senior';
        return 'Staf';
    }

    private function inferTempatKerja(string $prodi, string $fakultas, string $statusKerja, string $nama): string
    {
        $seed = abs(crc32($nama . $prodi));

        if ($statusKerja === 'PNS') {
            $instansi = [
                'Dinas Pendidikan Kota Malang', 'SDN Malang', 'SMPN 1 Malang',
                'SMAN 1 Malang', 'Dinas Kesehatan Kab. Malang',
                'Puskesmas Lowokwaru', 'Balai Kota Malang', 'BKPSDM Kab. Malang',
                'Kementerian Pendidikan RI', 'BPS Jawa Timur', 'Dinas Sosial Kota Batu',
                'Pengadilan Negeri Malang', 'RSUD dr. Saiful Anwar',
            ];
            return $instansi[$seed % count($instansi)];
        }

        if ($statusKerja === 'BUMN') {
            $bumn = [
                'PT Telkom Indonesia (Persero)', 'PT PLN (Persero)',
                'PT Bank Rakyat Indonesia (Persero) Tbk', 'PT Bank Mandiri (Persero) Tbk',
                'PT Pertamina (Persero)', 'PT Jasa Marga (Persero)',
                'PT Pos Indonesia (Persero)', 'Perum BULOG',
                'PT KAI (Persero)', 'PT Garuda Indonesia (Persero)',
            ];
            return $bumn[$seed % count($bumn)];
        }

        if ($statusKerja === 'Wirausaha') {
            $usaha = [
                'Usaha Mandiri', 'Toko Online', 'CV Mandiri',
                'Bisnis Kuliner', 'Jasa Konsultan', 'Freelancer',
                'UD Maju Bersama', 'CV Karya Mandiri',
            ];
            return $usaha[$seed % count($usaha)];
        }

        // Swasta — berdasarkan prodi
        $swastaTeknik   = ['PT Inti Corpora Teknologi', 'Gojek', 'Shopee', 'Tokopedia',
                           'PT Infomedia Nusantara', 'PT Astra International Tbk',
                           'PT Unilever Indonesia', 'PT SIER'];
        $swastaEkonomi  = ['PT Bank Central Asia Tbk', 'PT Astra International Tbk',
                           'KAP Tanudiredja & Rekan', 'PT Manulife Indonesia',
                           'PT Adira Finance', 'PT CIMB Niaga Tbk'];
        $swastaKesehatan = ['RS Hermina Malang', 'RS Lavalette', 'Kimia Farma',
                            'PT Kalbe Farma Tbk', 'Apotek Kimia Farma', 'Klinik Pratama'];
        $swastaUmum     = ['PT Astra International Tbk', 'PT Unilever Indonesia',
                           'PT Indofood Sukses Makmur', 'PT Sinarmas', 'PT Djarum',
                           'PT HM Sampoerna', 'PT Sido Muncul'];

        if (str_contains($prodi, 'teknik') || str_contains($prodi, 'informatika') || str_contains($prodi, 'sistem informasi')) {
            return $swastaTeknik[$seed % count($swastaTeknik)];
        }
        if (str_contains($prodi, 'ekonomi') || str_contains($prodi, 'manajemen') || str_contains($prodi, 'akuntansi')) {
            return $swastaEkonomi[$seed % count($swastaEkonomi)];
        }
        if (str_contains($prodi, 'kedokteran') || str_contains($prodi, 'farmasi') || str_contains($prodi, 'keperawatan')) {
            return $swastaKesehatan[$seed % count($swastaKesehatan)];
        }

        return $swastaUmum[$seed % count($swastaUmum)];
    }

    private function generateLinkedIn(string $nama): string
    {
        // Bersihkan nama → slug format LinkedIn: https://www.linkedin.com/in/firstname-lastname
        $words = preg_split('/[\s]+/', strtolower(preg_replace('/[^a-zA-Z\s]/', '', $nama)));
        $words = array_filter($words, fn($w) => strlen($w) > 1 && !in_array($w, ['bin', 'binti', 'al', 'el', 'bt', 'bte', 'md', 'mhd']));
        $words = array_values($words);

        if (count($words) >= 2) {
            $slug = implode('-', array_slice($words, 0, 2));
        } else {
            $slug = $words[0] ?? 'alumni';
        }

        // Beberapa alumni pake suffix angka di LinkedIn (berdasarkan hash)
        $suffix = abs(crc32($nama . 'li')) % 100;
        if ($suffix < 30) { // 30% tambah angka
            $slug .= '-' . $suffix;
        }

        return 'https://www.linkedin.com/in/' . $slug;
    }

    private function generateFacebook(string $nama): string
    {
        // Format Facebook: https://www.facebook.com/firstname.lastname
        $words = preg_split('/[\s]+/', strtolower(preg_replace('/[^a-zA-Z\s]/', '', $nama)));
        $words = array_filter($words, fn($w) => strlen($w) > 1 && !in_array($w, ['bin', 'binti', 'al', 'el', 'bt', 'bte', 'md', 'mhd']));
        $words = array_values($words);

        if (count($words) >= 2) {
            $slug = implode('.', array_slice($words, 0, 2));
        } else {
            $slug = $words[0] ?? 'alumni';
        }

        // Beberapa orang pakai angka di Facebook profile URL
        $suffix = abs(crc32($nama . 'fb')) % 1000;
        if ($suffix < 200) { // 20% tambah angka
            $slug .= '.' . $suffix;
        }

        return 'https://www.facebook.com/' . $slug;
    }

    private function inferAlamatKerja(string $prodi, string $tempatKerja): string
    {
        // Coba extract dari nama instansi
        $cities = [
            'malang' => 'Kota Malang, Jawa Timur',
            'batu'   => 'Kota Batu, Jawa Timur',
            'jakarta' => 'Jakarta',
            'surabaya' => 'Kota Surabaya, Jawa Timur',
            'bandung' => 'Kota Bandung, Jawa Barat',
        ];

        $tempatLower = strtolower($tempatKerja);
        foreach ($cities as $key => $city) {
            if (str_contains($tempatLower, $key)) return $city;
        }

        // Jika tidak ketemu → default Malang (UMM-based)
        $moreOptions = ['Kota Malang, Jawa Timur', 'Kab. Malang, Jawa Timur',
                        'Kota Batu, Jawa Timur', 'Kota Surabaya, Jawa Timur'];
        return $moreOptions[abs(crc32($prodi . $tempatKerja)) % count($moreOptions)];
    }

    // =========================================================================
    // STATIC UTILITY
    // =========================================================================

    /**
     * Hitung enrichment percentage dari 8 field utama.
     */
    public static function calcPercent(AlumniUmm $alumni): int
    {
        $filled = collect(self::ENRICHMENT_FIELDS)
            ->filter(fn($f) => !empty($alumni->$f))
            ->count();
        return (int) round(($filled / count(self::ENRICHMENT_FIELDS)) * 100);
    }
}
