<?php

namespace App\Http\Controllers;

use App\Models\AlumniUmm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnrichmentController extends Controller
{
    /**
     * Search Google for alumni enrichment data.
     * Uses Google Custom Search JSON API (100 free queries/day).
     */
    public function searchGoogle(Request $request)
    {
        $nama = $request->input('nama');
        $type = $request->input('type', 'all'); // linkedin, instagram, facebook, work, all
        
        if (!$nama) {
            return response()->json(['error' => 'Nama diperlukan'], 400);
        }

        $apiKey = env('GOOGLE_SEARCH_API_KEY');
        $cx = env('GOOGLE_SEARCH_CX');

        // Build search query based on type
        $queries = $this->buildSearchQueries($nama, $type);
        $results = [];

        foreach ($queries as $label => $query) {
            if ($apiKey && $cx) {
                // Use Google Custom Search API
                try {
                    $response = Http::withOptions(['verify' => false])
                        ->timeout(10)
                        ->get('https://www.googleapis.com/customsearch/v1', [
                            'key' => $apiKey,
                            'cx' => $cx,
                            'q' => $query,
                            'num' => 5,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $items = collect($data['items'] ?? [])->map(function ($item) {
                            return [
                                'title' => $item['title'] ?? '-',
                                'link' => $item['link'] ?? '#',
                                'snippet' => $item['snippet'] ?? '-',
                            ];
                        });
                        $results[$label] = $items;
                    }
                } catch (\Exception $e) {
                    Log::warning("[Enrichment Google] {$label}: " . $e->getMessage());
                    $results[$label] = ['error' => 'Gagal mencari'];
                }
            } else {
                // No API key — return search URLs for manual search
                $results[$label] = [
                    'manual_url' => 'https://www.google.com/search?q=' . urlencode($query),
                    'query' => $query,
                ];
            }
        }

        return response()->json([
            'nama' => $nama,
            'type' => $type,
            'has_api' => !empty($apiKey),
            'results' => $results,
        ]);
    }

    /**
     * Build search queries for different enrichment types.
     */
    private function buildSearchQueries(string $nama, string $type): array
    {
        $queries = [];
        $namaClean = trim($nama);

        if ($type === 'all' || $type === 'linkedin') {
            $queries['LinkedIn'] = "site:linkedin.com/in \"{$namaClean}\" UMM";
        }
        if ($type === 'all' || $type === 'instagram') {
            $queries['Instagram'] = "site:instagram.com \"{$namaClean}\"";
        }
        if ($type === 'all' || $type === 'facebook') {
            $queries['Facebook'] = "site:facebook.com \"{$namaClean}\"";
        }
        if ($type === 'all' || $type === 'tiktok') {
            $queries['TikTok'] = "site:tiktok.com \"{$namaClean}\"";
        }
        if ($type === 'all' || $type === 'work') {
            $queries['Tempat Kerja'] = "\"{$namaClean}\" UMM pekerjaan OR kerja OR perusahaan OR company";
        }
        if ($type === 'all' || $type === 'email') {
            $queries['Email'] = "\"{$namaClean}\" email OR @gmail.com OR @yahoo.com";
        }

        return $queries;
    }

    /**
     * Generate all search URLs for an alumni (no API needed).
     */
    public function getSearchLinks(Request $request)
    {
        $nama = $request->input('nama');
        if (!$nama) {
            return response()->json(['error' => 'Nama diperlukan'], 400);
        }

        $nClean = trim($nama);

        return response()->json([
            'nama' => $nama,
            'links' => [
                [
                    'platform' => 'LinkedIn',
                    'icon' => '<img src="https://cdn.simpleicons.org/linkedin/0077b5" style="width:20px;height:20px;" alt="LI">',
                    'color' => '#0077b5',
                    'url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" linkedin"),
                ],
                [
                    'platform' => 'Instagram',
                    'icon' => '<img src="https://cdn.simpleicons.org/instagram/e4405f" style="width:20px;height:20px;" alt="IG">',
                    'color' => '#e4405f',
                    'url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" instagram"),
                ],
                [
                    'platform' => 'Facebook',
                    'icon' => '<img src="https://cdn.simpleicons.org/facebook/1877f2" style="width:20px;height:20px;" alt="FB">',
                    'color' => '#1877f2',
                    'url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" facebook"),
                ],
                [
                    'platform' => 'TikTok',
                    'icon' => '<img src="https://cdn.simpleicons.org/tiktok/ffffff" style="width:20px;height:20px;" alt="TK">',
                    'color' => '#000000',
                    'url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" tiktok"),
                ],
                [
                    'platform' => 'Google (Kerja)',
                    'icon' => '<img src="https://cdn.simpleicons.org/google/4285F4" style="width:20px;height:20px;" alt="Work">',
                    'color' => '#4285f4',
                    'url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" pekerjaan OR kerja OR perusahaan"),
                ],
                [
                    'platform' => 'Google (Email)',
                    'icon' => '<img src="https://cdn.simpleicons.org/gmail/ea4335" style="width:20px;height:20px;" alt="Email">',
                    'color' => '#ea4335',
                    'url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" email"),
                ],
                [
                    'platform' => 'Google (Kontak)',
                    'icon' => '<img src="https://cdn.simpleicons.org/whatsapp/25D366" style="width:20px;height:20px;" alt="WA">',
                    'color' => '#34a853',
                    'url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" no hp OR whatsapp"),
                ],
            ],
        ]);
    }
}
