<?php

namespace App\Http\Controllers;

use App\Models\AlumniUmm;
use App\Services\ScrapingService;
use App\Services\SmartEnrichmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScrapingController extends Controller
{
    public function __construct(private ScrapingService $scrapingService) {}

    /**
     * Halaman utama Auto-Scraping.
     */
    public function index(Request $request)
    {
        $nama = trim($request->input('nama', ''));
        $results = null;

        if ($nama) {
            $results = $this->scrapingService->scrapeByName($nama);
        }

        return view('scraping.index', compact('nama', 'results'));
    }

    /**
     * AJAX endpoint: jalankan scraping real-time.
     */
    public function scrape(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|min:3|max:255',
        ]);

        $nama = trim($request->input('nama'));

        try {
            $results = $this->scrapingService->scrapeByName($nama);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('[Scraping] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan saat scraping: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cache for a name and re-scrape.
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|min:3|max:255',
        ]);

        $nama = trim($request->input('nama'));
        $this->scrapingService->clearCache($nama);

        try {
            $results = $this->scrapingService->scrapeByName($nama);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save scraping results to alumni_umm database.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'linkedin' => 'nullable|string|max:500',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:500',
            'tiktok' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'tempat_kerja' => 'nullable|string|max:255',
            'alamat_kerja' => 'nullable|string|max:500',
            'posisi' => 'nullable|string|max:255',
            'status_kerja' => 'nullable|in:PNS,Swasta,Wirausaha,BUMN',
            'sosmed_perusahaan' => 'nullable|string|max:500',
        ]);

        // Check if alumni already exists in alumni_umm by name
        $existing = AlumniUmm::whereRaw('LOWER(nama) = ?', [strtolower($validated['nama'])])->first();

        if ($existing) {
            // Update existing record — only fill empty fields
            $updateData = [];
            foreach ($validated as $key => $value) {
                if ($key === 'nama') continue;
                if (!empty($value) && empty($existing->$key)) {
                    $updateData[$key] = $value;
                }
            }

            if (!empty($updateData)) {
                $existing->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data alumni berhasil diperbarui.',
                'alumni' => $existing->fresh(),
                'updated_fields' => array_keys($updateData),
            ]);
        } else {
            // Create new record
            $alumni = AlumniUmm::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data alumni baru berhasil disimpan.',
                'alumni' => $alumni,
                'is_new' => true,
            ]);
        }
    }

    /**
     * AJAX: Auto-scrape + auto-save for an alumni.
     * Called automatically when detail page loads and fields are empty.
     */
    public function autoScrape(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|min:3|max:255',
            'alumni_id' => 'nullable|integer',
        ]);

        $nama = trim($request->input('nama'));
        $alumniId = $request->input('alumni_id');

        try {
            $results = $this->scrapingService->scrapeByName($nama);

            if (!$results || empty($results['platforms'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tidak ada hasil dari scraping.',
                ]);
            }

            $best = $results['summary']['best_results'] ?? [];

            // Build update data from best results
            $updateData = [];

            // LinkedIn
            if (!empty($best['linkedin']['url'])) {
                $updateData['linkedin'] = $best['linkedin']['url'];
            }
            if (!empty($best['linkedin']['company'])) {
                $updateData['tempat_kerja'] = $best['linkedin']['company'];
            }
            if (!empty($best['linkedin']['headline'])) {
                $updateData['posisi'] = $best['linkedin']['headline'];
            }

            // Instagram
            if (!empty($best['instagram']['username'])) {
                $updateData['instagram'] = $best['instagram']['username'];
            }

            // Facebook
            if (!empty($best['facebook']['url'])) {
                $updateData['facebook'] = $best['facebook']['url'];
            }

            // TikTok
            if (!empty($best['tiktok']['username'])) {
                $updateData['tiktok'] = $best['tiktok']['username'];
            }

            // Email
            if (!empty($best['email']['email'])) {
                $updateData['email'] = $best['email']['email'];
            }

            // Phone
            if (!empty($best['phone']['phone'])) {
                $updateData['no_hp'] = $best['phone']['phone'];
            }

            // Work
            if (!empty($best['work'])) {
                if (empty($updateData['tempat_kerja'])) {
                    $updateData['tempat_kerja'] = $best['work']['company'] ?: \Illuminate\Support\Str::limit($best['work']['title'] ?? '', 100);
                }
                if (empty($updateData['posisi'])) {
                    $updateData['posisi'] = $best['work']['position'] ?: 'Cek URL Pekerjaan';
                }
                if (empty($updateData['sosmed_perusahaan'])) {
                    $updateData['sosmed_perusahaan'] = $best['work']['url'] ?? '';
                }
            }

            // Scholar
            if (!empty($best['scholar']['url']) && empty($updateData['sosmed_perusahaan'])) {
                $updateData['sosmed_perusahaan'] = $best['scholar']['url'];
            }

            // GitHub company fallback
            if (empty($updateData['tempat_kerja']) && !empty($best['github']['company'])) {
                $updateData['tempat_kerja'] = $best['github']['company'];
            }

            // Save to DB — only fill empty fields
            $alumni = null;
            if ($alumniId) {
                $alumni = AlumniUmm::find($alumniId);
            }
            if (!$alumni) {
                $alumni = AlumniUmm::whereRaw('LOWER(nama) = ?', [strtolower($nama)])->first();
            }

            $updatedFields = [];
            if ($alumni && !empty($updateData)) {
                foreach ($updateData as $key => $value) {
                    if (empty($alumni->$key) && !empty($value)) {
                        $alumni->$key = $value;
                        $updatedFields[] = $key;
                    }
                }
                if (!empty($updatedFields)) {
                    $alumni->save();
                }
            }

            return response()->json([
                'success' => true,
                'scraped' => $updateData,
                'updated_fields' => $updatedFields,
                'alumni' => $alumni ? $alumni->fresh() : null,
                'platforms' => $results['platforms'],
                'summary' => $results['summary'],
                'from_cache' => $results['from_cache'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error('[AutoScrape] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * AJAX: Enrich a single alumni record using SmartEnrichmentService.
     * Called from the tracking table "Cari Data" button per-row.
     */
    public function enrichSingle(Request $request)
    {
        $request->validate([
            'alumni_id' => 'required_without:nama|nullable|integer|exists:alumni_umm,id',
            'nama'      => 'required_without:alumni_id|nullable|string|min:2|max:255',
            'force'     => 'nullable|boolean',
        ]);

        // Resolve alumni record by ID or by name
        if ($request->filled('alumni_id')) {
            $alumni = AlumniUmm::findOrFail($request->input('alumni_id'));
        } else {
            $alumni = AlumniUmm::whereRaw('LOWER(nama) = ?', [strtolower(trim($request->input('nama')))])->first();
            if (!$alumni) {
                return response()->json(['success' => false, 'error' => 'Alumni tidak ditemukan.'], 404);
            }
        }

        // If force = true, clear scraping cache so fresh results are fetched
        if ($request->boolean('force')) {
            $this->scrapingService->clearCache($alumni->nama);
        }

        try {
            $service = app(SmartEnrichmentService::class);

            // Force re-enrich — allow overwriting existing fields when force=true
            $result = $service->enrich($alumni, $request->boolean('force'));

            return response()->json([
                'success'        => true,
                'alumni'         => $result['alumni'],
                'updated_fields' => $result['updated_fields'],
                'source'         => $result['source'],
                'enrichment_pct' => $result['enrichment_pct'],
                'from_cache'     => $result['from_cache'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error('[EnrichSingle] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Generate data untuk 1000 alumni sekaligus (no scraping, super cepat).
     * Dipanggil berulang-ulang dari frontend sampai semua selesai.
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'offset' => 'nullable|integer|min:0',
            'chunk'  => 'nullable|integer|min:1|max:2000',
            'force'  => 'nullable|boolean',
        ]);

        $offset    = (int) $request->input('offset', 0);
        $chunkSize = (int) $request->input('chunk', 1000);
        $force     = $request->boolean('force', false);

        $fields = ['linkedin','instagram','facebook','tiktok','email','no_hp','tempat_kerja','posisi'];

        // Total yang belum terisi (untuk progress)
        $totalAll = AlumniUmm::count();

        // Ambil batch berdasarkan offset (ID order supaya konsisten)
        $batch = AlumniUmm::orderBy('id')->skip($offset)->take($chunkSize)->get();

        /** @var SmartEnrichmentService $enrichment */
        $enrichment = app(SmartEnrichmentService::class);

        $updated = 0;
        $skipped = 0;

        foreach ($batch as $alumni) {
            $generated  = $enrichment->generateFallbackData($alumni);
            $hasChanges = false;

            foreach ($generated as $key => $value) {
                if (!empty($value) && ($force || empty($alumni->$key))) {
                    $alumni->$key = $value;
                    $hasChanges   = true;
                }
            }

            if ($hasChanges) {
                if ($force || empty($alumni->data_source)) {
                    $alumni->data_source = 'generated';
                }
                $alumni->save();
                $updated++;
            } else {
                $skipped++;
            }
        }

        $nextOffset  = $offset + $chunkSize;
        $processed   = $batch->count();
        $isDone      = $nextOffset >= $totalAll || $processed < $chunkSize;

        return response()->json([
            'success'     => true,
            'processed'   => $processed,
            'updated'     => $updated,
            'skipped'     => $skipped,
            'next_offset' => $nextOffset,
            'total'       => $totalAll,
            'done'        => $isDone,
        ]);
    }
}
