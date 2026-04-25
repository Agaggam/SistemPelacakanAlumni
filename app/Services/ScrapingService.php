<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapingService
{
    private const GOOGLE_API_URL = 'https://www.googleapis.com/customsearch/v1';
    private const GITHUB_API_URL = 'https://api.github.com';
    private const WIKIPEDIA_API_URL = 'https://id.wikipedia.org/w/api.php';

    private ?string $googleApiKey;
    private ?string $googleCx;
    private ?string $githubToken;
    private ?string $apolloApiKey;
    private ?string $proxycurlApiKey;
    private ?string $scraperApiKey;
    private int $cacheTtl;

    public function __construct()
    {
        $this->googleApiKey = env('GOOGLE_SEARCH_API_KEY');
        $this->googleCx = env('GOOGLE_SEARCH_CX');
        $this->githubToken = env('GITHUB_TOKEN');
        $this->apolloApiKey = env('APOLLO_API_KEY');
        $this->proxycurlApiKey = env('PROXYCURL_API_KEY');
        $this->scraperApiKey = env('SCRAPER_API_KEY');
        $this->cacheTtl = (int) env('SCRAPING_CACHE_TTL', 86400);
    }

    /**
     * Main entry point — scrape all platforms for a given name.
     */
    public function scrapeByName(string $nama): array
    {
        $nama = trim($nama);
        $cacheKey = 'scraping_' . md5(strtolower($nama));

        // Check cache first
        if ($cached = Cache::get($cacheKey)) {
            $cached['from_cache'] = true;
            return $cached;
        }

        $hasGoogleApi = !empty($this->googleApiKey) && !empty($this->googleCx);

        $results = [
            'nama' => $nama,
            'timestamp' => now()->toIso8601String(),
            'from_cache' => false,
            'has_google_api' => $hasGoogleApi,
            'search_engine' => $hasGoogleApi ? 'google_cse' : 'duckduckgo',
            'platforms' => [],
            'summary' => [],
        ];

        // === 0. Premium API Layer (Apollo / Proxycurl) ===
        $premium = $this->fetchPremiumData($nama);

        // === 1. Platform searches (Google CSE or DuckDuckGo fallback) ===
        // LinkedIn
        $results['platforms']['linkedin'] = $premium['linkedin'] ?? $this->searchLinkedIn($nama);

        // Instagram
        $results['platforms']['instagram'] = $this->searchInstagram($nama);

        // Facebook
        $results['platforms']['facebook'] = $this->searchFacebook($nama);

        // TikTok
        $results['platforms']['tiktok'] = $this->searchTikTok($nama);

        // Email
        $results['platforms']['email'] = $premium['email'] ?? $this->searchEmail($nama);

        // Phone / WhatsApp
        $results['platforms']['phone'] = $premium['phone'] ?? $this->searchPhone($nama);

        // Work / Career
        $results['platforms']['work'] = $premium['work'] ?? $this->searchWork($nama);

        // Google Scholar
        $results['platforms']['scholar'] = $this->searchScholar($nama);

        // === 2. GitHub API (free, no key needed) ===
        $results['platforms']['github'] = $this->searchGitHub($nama);

        // === 3. Wikipedia API (free, no key needed) ===
        $results['platforms']['wikipedia'] = $this->searchWikipedia($nama);

        // === 4. Build summary ===
        $results['summary'] = $this->buildSummary($results['platforms']);

        // Cache results
        Cache::put($cacheKey, $results, $this->cacheTtl);

        return $results;
    }

    /**
     * Clear cached results for a name.
     */
    public function clearCache(string $nama): void
    {
        $cacheKey = 'scraping_' . md5(strtolower(trim($nama)));
        Cache::forget($cacheKey);
    }

    // =========================================================================
    // PREMIUM API ENRICHMENT (Layer 1)
    // =========================================================================

    /**
     * Try fetching data from Apollo.io or Proxycurl if keys are available.
     * Returns an array with pre-populated platforms if found.
     */
    private function fetchPremiumData(string $nama): array
    {
        $data = [];

        if ($this->apolloApiKey) {
            $data = $this->searchApollo($nama);
        }

        if (empty($data) && $this->proxycurlApiKey) {
            $data = $this->searchProxycurl($nama);
        }

        return $data;
    }

    private function searchApollo(string $nama): array
    {
        try {
            $response = Http::withHeaders([
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->apolloApiKey,
            ])->post('https://api.apollo.io/v1/people/match', [
                'name' => $nama,
            ]);

            if ($response->successful() && !empty($response->json('person'))) {
                $person = $response->json('person');
                return [
                    'linkedin' => [
                        'platform' => 'LinkedIn', 'icon' => 'linkedin', 'color' => '#0a66c2',
                        'status' => 'found', 'confidence' => 0.9,
                        'data' => [['url' => $person['linkedin_url'] ?? '', 'confidence' => 0.9]]
                    ],
                    'email' => [
                        'platform' => 'Email', 'icon' => 'envelope', 'color' => '#ef4444',
                        'status' => 'found', 'confidence' => 0.9,
                        'data' => [['email' => $person['email'] ?? '', 'confidence' => 0.9]]
                    ],
                    'work' => [
                        'platform' => 'Pekerjaan', 'icon' => 'work', 'color' => '#f59e0b',
                        'status' => 'found', 'confidence' => 0.9,
                        'data' => [[
                            'company' => $person['organization']['name'] ?? '',
                            'position' => $person['title'] ?? '',
                            'confidence' => 0.9
                        ]]
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('Apollo API Error: ' . $e->getMessage());
        }
        return [];
    }

    private function searchProxycurl(string $nama): array
    {
        try {
            // Proxycurl usually uses LinkedIn URL, but they have a Person Search Endpoint
            $response = Http::withToken($this->proxycurlApiKey)
                ->get('https://nubela.co/proxycurl/api/v2/search/person', [
                    'first_name' => explode(' ', $nama)[0],
                    'last_name' => explode(' ', $nama, 2)[1] ?? '',
                ]);

            if ($response->successful() && !empty($response->json('results'))) {
                $result = $response->json('results')[0];
                return [
                    'linkedin' => [
                        'platform' => 'LinkedIn', 'icon' => 'linkedin', 'color' => '#0a66c2',
                        'status' => 'found', 'confidence' => 0.9,
                        'data' => [['url' => $result['profile_url'] ?? '', 'confidence' => 0.9]]
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('Proxycurl API Error: ' . $e->getMessage());
        }
        return [];
    }

    // =========================================================================
    // WEB SEARCH (Google CSE → DuckDuckGo fallback)
    // =========================================================================

    /**
     * Universal web search: Google CSE if available, Brave Search as fallback.
     */
    private function webSearch(string $query, int $num = 5): array
    {
        // Try Google CSE first (it might work if they fix billing/permissions)
        if ($this->googleApiKey && $this->googleCx) {
            $result = $this->googleSearch($query, $num);
            if ($result && !empty($result['items'])) {
                return $result['items'];
            }
        }

        // Fallback to Bing, Yahoo, dan DiscoverProfile
        $allResults = [];

        // 1. Bing HTML scraping (reliable, free, dengan decode redirect URLs)
        $allResults = array_merge($allResults, $this->bingSearch($query, $num));

        // 2. Yahoo Search HTML (fallback jika Bing kosong)
        if (count($allResults) < $num) {
            $allResults = array_merge($allResults, $this->yahooSearch($query, $num));
        }

        // 3. DiscoverProfile (Highly specific for social media)
        if (count($allResults) < $num) {
            $allResults = array_merge($allResults, $this->discoverProfileSearch($query, $num));
        }

        return $allResults;
    }

    private function googleSearch(string $query, int $num = 5): ?array
    {
        try {
            $referer = config('app.url', 'http://localhost');
            
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Referer' => $referer,
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AlumniScraper/1.0',
                ])
                ->timeout(12)
                ->get(self::GOOGLE_API_URL, [
                    'key' => $this->googleApiKey,
                    'cx' => $this->googleCx,
                    'q' => $query,
                    'num' => $num,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            // If 403, it's likely a restriction issue (Like Referrer restriction)
            $errorDesc = $response->json()['error']['message'] ?? 'Unknown Error';
            Log::warning("[Scraping Google] HTTP {$response->status()} - {$errorDesc} for query: {$query}");
            
            return null;
        } catch (\Exception $e) {
            Log::warning('[Scraping Google] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * DiscoverProfile.com scraping — specialized social profile search.
     */
    private function discoverProfileSearch(string $query, int $limit = 5): array
    {
        try {
            Log::info("Attempting DiscoverProfile for: " . $query);
            $response = Http::withOptions(['verify' => false])
                ->timeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get('https://discoverprofile.com/search', [
                    'q' => $query,
                ]);

            if ($response->successful()) {
                return $this->extractLinksFromHtml($response->body(), 'DiscoverProfile', $limit);
            }
        } catch (\Exception $e) {
            Log::warning('[DiscoverProfile] ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Lullar.com scraping — broad social/username search.
     */
    private function lullarSearch(string $query, int $limit = 5): array
    {
        try {
            Log::info("Attempting Lullar for: " . $query);
            $response = Http::withOptions(['verify' => false])
                ->timeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get('https://www.lullar.com/search.php', [
                    'q' => $query,
                ]);

            if ($response->successful()) {
                return $this->extractLinksFromHtml($response->body(), 'Lullar', $limit);
            }
        } catch (\Exception $e) {
            Log::warning('[Lullar] ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Generic social link extractor from HTML specialized for directory sites.
     */
    private function extractLinksFromHtml(string $html, string $source, int $limit = 5): array
    {
        $results = [];
        // Extract links that look like social media profiles
        // We look for <a> tags with hrefs containing common social platforms
        if (preg_match_all('/<a[^>]+href="(https?:\/\/(?:www\.)?(?:linkedin\.com\/in|facebook\.com|instagram\.com|tiktok\.com|twitter\.com|x\.com)\/[^"]+)"[^>]*>(.*?)<\/a>/is', $html, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                if (count($results) >= $limit) break;
                
                $url = html_entity_decode($matches[1][$i], ENT_QUOTES, 'UTF-8');
                $text = trim(strip_tags($matches[2][$i]));

                // Skip repetitive or common junk
                if (str_contains($url, 'share') || str_contains($url, 'intent')) continue;

                $results[] = [
                    'title' => $text ?: $url,
                    'link' => $url,
                    'snippet' => "Found via {$source}",
                ];
            }
        }
        
        Log::info("[{$source}] Extracted " . count($results) . " social links.");
        return $results;
    }

    /**
     * Bing HTML scraping — reliable, free, decodes Bing redirect URLs.
     * Bing wraps result URLs in redirect: /ck/a?...&u=a1<base64url>&ntb=1
     */
    private function bingSearch(string $query, int $limit = 5): array
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        ];
        $ua = $userAgents[array_rand($userAgents)];

        try {
            Log::info('[Bing] Searching: ' . substr($query, 0, 80));

            $targetUrl = 'https://www.bing.com/search?q=' . urlencode($query) . '&count=' . ($limit * 2) . '&mkt=en-US&setLang=en';
            
            if ($this->scraperApiKey) {
                $targetUrl = 'http://api.scraperapi.com?api_key=' . $this->scraperApiKey . '&url=' . urlencode($targetUrl);
                Log::info('[Bing] Routing through ScraperAPI');
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $targetUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_TIMEOUT        => 30, // Increase timeout for ScraperAPI
                CURLOPT_ENCODING       => '', // auto-decompress gzip
                CURLOPT_HTTPHEADER     => [
                    "User-Agent: {$ua}",
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                    'Cache-Control: no-cache',
                    'Cookie: SRCHHPGUSR=SRCHLANG=en',
                ],
            ]);
            $html = curl_exec($ch);
            $bingErr = curl_error($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($bingErr || !$html || $status < 200 || $status >= 400) {
                Log::warning('[Bing] HTTP ' . $status . ($bingErr ? ' - ' . $bingErr : ''));
                return [];
            }

            return $this->parseBingHtml($html, $limit);
        } catch (\Exception $e) {
            Log::error('[Bing] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse Bing Search HTML results.
     * Bing encodes actual URLs in base64 inside redirect links (/ck/a?...&u=a1<b64>).
     */
    private function parseBingHtml(string $html, int $limit = 5): array
    {
        $results = [];

        // Bing results usually have their main links enclosed in <h2><a>
        if (preg_match_all('/<h2[^>]*><a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a><\/h2>/is', $html, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                if (count($results) >= $limit) break;

                $url = html_entity_decode(trim($matches[1][$i]), ENT_QUOTES, 'UTF-8');
                $title = html_entity_decode(strip_tags($matches[2][$i]), ENT_QUOTES, 'UTF-8');

                // Extract Bing redirect URLs and decode them
                // Pattern: /ck/a?!&&p=...&u=a1<base64url>&ntb=1
                if (preg_match('/[&?]u=a1([A-Za-z0-9_\-]+)/i', $url, $uMatch)) {
                    // Restore base64 padding and decode
                    $b64 = str_replace(['-', '_'], ['+', '/'], $uMatch[1]);
                    $b64 = $b64 . str_repeat('=', (4 - strlen($b64) % 4) % 4);
                    $decoded = base64_decode($b64, true);
                    if ($decoded && str_starts_with($decoded, 'http')) {
                        $url = $decoded;
                    }
                }

                // Remove duplicates and bing-internal URLs in place
                if (
                    empty($url) ||
                    str_contains($url, 'bing.com') ||
                    str_contains($url, 'microsoft.com') ||
                    str_contains($url, '.css') ||
                    str_contains($url, '.js')
                ) {
                    continue;
                }

                // Append if unique globally within the result set
                $isDuplicate = false;
                foreach ($results as $res) {
                    if ($res['link'] === $url) {
                        $isDuplicate = true;
                        break;
                    }
                }

                if (!$isDuplicate) {
                    $results[] = [
                        'title'   => trim($title) ?: $url,
                        'link'    => $url,
                        'snippet' => '', // Bing HTML snippet logic can be augmented later if needed
                    ];
                }
            }
        }

        Log::info('[Bing] Found ' . count($results) . ' results.');
        return $results;
    }

    /**
     * Yahoo Search HTML scraping — fallback engine.
     */
    private function yahooSearch(string $query, int $limit = 5): array
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        ];
        $ua = $userAgents[array_rand($userAgents)];

        try {
            Log::info('[Yahoo] Searching: ' . substr($query, 0, 80));

            $targetUrl = 'https://search.yahoo.com/search?p=' . urlencode($query) . '&ei=UTF-8&n=' . $limit;

            if ($this->scraperApiKey) {
                $targetUrl = 'http://api.scraperapi.com?api_key=' . $this->scraperApiKey . '&url=' . urlencode($targetUrl);
                Log::info('[Yahoo] Routing through ScraperAPI');
            }

            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->withHeaders([
                    'User-Agent'      => $ua,
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer'         => 'https://search.yahoo.com/',
                ])
                ->get($targetUrl);

            if (!$response->successful()) {
                Log::warning('[Yahoo] HTTP ' . $response->status());
                return [];
            }

            return $this->parseYahooHtml($response->body(), $limit);
        } catch (\Exception $e) {
            Log::warning('[Yahoo] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse Yahoo Search HTML results.
     */
    private function parseYahooHtml(string $html, int $limit = 5): array
    {
        $results = [];

        // Yahoo result links are in <h3 class="title"><a href="...">...
        if (preg_match_all('/<h3[^>]*class="title[^>]*>\s*<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/is', $html, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                if (count($results) >= $limit) break;

                $url = html_entity_decode(trim($matches[1][$i]), ENT_QUOTES, 'UTF-8');
                $title = html_entity_decode(strip_tags($matches[2][$i]), ENT_QUOTES, 'UTF-8');

                // Yahoo wraps links through redirect — decode if needed
                if (str_contains($url, '/RU=')) {
                    if (preg_match('/RU=([^\/]+)/', $url, $urlMatch)) {
                        $url = urldecode($urlMatch[1]);
                    }
                }

                if (empty($url) || str_contains($url, 'yahoo.com')) continue;

                $results[] = [
                    'title'   => trim($title) ?: $url,
                    'link'    => $url,
                    'snippet' => '',
                ];
            }
        }

        // Fallback: generic link extraction for Yahoo
        if (empty($results)) {
            if (preg_match_all('/href="\/RU=([^\/)]+)/i', $html, $m)) {
                for ($i = 0; $i < count($m[0]); $i++) {
                    if (count($results) >= $limit) break;
                    $url = urldecode($m[1][$i]);
                    if (!str_starts_with($url, 'http')) continue;
                    $results[] = ['title' => $url, 'link' => $url, 'snippet' => ''];
                }
            }
        }

        Log::info('[Yahoo] Found ' . count($results) . ' results.');
        return $results;
    }

    /**
     * Search LinkedIn profiles via Google CSE.
     */
    private function searchLinkedIn(string $nama): array
    {
        $result = [
            'platform' => 'LinkedIn',
            'icon' => 'linkedin',
            'color' => '#0077b5',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("site:linkedin.com/in \"{$nama}\"", 5);

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        foreach ($items as $item) {
            $title = $item['title'] ?? '';
            $link = $item['link'] ?? '';
            $snippet = $item['snippet'] ?? '';

            // Extract profile info from LinkedIn title format: "Name - Title - Company | LinkedIn"
            $profileInfo = $this->parseLinkedInTitle($title);
            $confidence = $this->calculateNameConfidence($nama, $profileInfo['name'] ?? $title);

            $result['data'][] = [
                'url' => $link,
                'title' => $title,
                'snippet' => $snippet,
                'name' => $profileInfo['name'] ?? '-',
                'headline' => $profileInfo['headline'] ?? '-',
                'company' => $profileInfo['company'] ?? '-',
                'confidence' => $confidence,
            ];
        }

        // Sort by confidence
        usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        $topConfidence = $result['data'][0]['confidence'] ?? 0;
        if ($topConfidence < 0.2) {
            $result['status'] = 'not_found';
            $result['data'] = [];
            $result['confidence'] = 0;
        } else {
            $result['status'] = 'found';
            $result['confidence'] = $topConfidence;
        }

        return $result;
    }

    /**
     * Search Instagram via Google CSE.
     */
    private function searchInstagram(string $nama): array
    {
        $result = [
            'platform' => 'Instagram',
            'icon' => 'instagram',
            'color' => '#e4405f',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("site:instagram.com \"{$nama}\"", 5);

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        foreach ($items as $item) {
            $link = $item['link'] ?? '';
            $title = $item['title'] ?? '';
            $snippet = $item['snippet'] ?? '';

            // Extract username from URL
            $username = $this->extractInstagramUsername($link);
            $confidence = $this->calculateNameConfidence($nama, $title);

            $result['data'][] = [
                'url' => $link,
                'username' => $username,
                'title' => $title,
                'snippet' => $snippet,
                'confidence' => $confidence,
            ];
        }

        usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        
        $topConfidence = $result['data'][0]['confidence'] ?? 0;
        if ($topConfidence < 0.2) {
            $result['status'] = 'not_found';
            $result['data'] = [];
            $result['confidence'] = 0;
        } else {
            $result['status'] = 'found';
            $result['confidence'] = $topConfidence;
        }

        return $result;
    }

    /**
     * Search Facebook via Google CSE.
     */
    private function searchFacebook(string $nama): array
    {
        $result = [
            'platform' => 'Facebook',
            'icon' => 'facebook',
            'color' => '#1877f2',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("site:facebook.com \"{$nama}\"", 5);

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        foreach ($items as $item) {
            $confidence = $this->calculateNameConfidence($nama, $item['title'] ?? '');
            $result['data'][] = [
                'url' => $item['link'] ?? '',
                'title' => $item['title'] ?? '-',
                'snippet' => $item['snippet'] ?? '-',
                'confidence' => $confidence,
            ];
        }

        usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        $topConfidence = $result['data'][0]['confidence'] ?? 0;
        if ($topConfidence < 0.2) {
            $result['status'] = 'not_found';
            $result['data'] = [];
            $result['confidence'] = 0;
        } else {
            $result['status'] = 'found';
            $result['confidence'] = $topConfidence;
        }

        return $result;
    }

    /**
     * Search TikTok via Google CSE.
     */
    private function searchTikTok(string $nama): array
    {
        $result = [
            'platform' => 'TikTok',
            'icon' => 'tiktok',
            'color' => '#ff0050',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("site:tiktok.com \"{$nama}\"", 5);

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        foreach ($items as $item) {
            $link = $item['link'] ?? '';
            $username = $this->extractTikTokUsername($link);
            $confidence = $this->calculateNameConfidence($nama, $item['title'] ?? '');

            $result['data'][] = [
                'url' => $link,
                'username' => $username,
                'title' => $item['title'] ?? '-',
                'snippet' => $item['snippet'] ?? '-',
                'confidence' => $confidence,
            ];
        }

        usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        $topConfidence = $result['data'][0]['confidence'] ?? 0;
        if ($topConfidence < 0.2) {
            $result['status'] = 'not_found';
            $result['data'] = [];
            $result['confidence'] = 0;
        } else {
            $result['status'] = 'found';
            $result['confidence'] = $topConfidence;
        }

        return $result;
    }

    /**
     * Search for email addresses via Google CSE.
     */
    private function searchEmail(string $nama): array
    {
        $result = [
            'platform' => 'Email',
            'icon' => 'email',
            'color' => '#ea4335',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("\"{$nama}\" email OR @gmail.com OR @yahoo.com OR @outlook.com", 5);

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        $emails = [];
        foreach ($items as $item) {
            $snippet = ($item['snippet'] ?? '') . ' ' . ($item['title'] ?? '');
            // Extract email addresses from text
            if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $snippet, $matches)) {
                foreach ($matches[0] as $email) {
                    $email = strtolower($email);
                    if (!in_array($email, $emails) && !str_contains($email, 'example.com') && !str_contains($email, 'sentry.')) {
                        $emails[] = $email;
                        $result['data'][] = [
                            'email' => $email,
                            'source_url' => $item['link'] ?? '',
                            'source_title' => $item['title'] ?? '-',
                            'confidence' => $this->calculateEmailConfidence($nama, $email),
                        ];
                    }
                }
            }
        }

        $result['status'] = empty($result['data']) ? 'not_found' : 'found';
        if (!empty($result['data'])) {
            usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
            $result['confidence'] = $result['data'][0]['confidence'] ?? 0;
        }

        return $result;
    }

    /**
     * Search for phone numbers via Google CSE.
     */
    private function searchPhone(string $nama): array
    {
        $result = [
            'platform' => 'Phone',
            'icon' => 'phone',
            'color' => '#25D366',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("\"{$nama}\" +62 OR 08 OR whatsapp OR \"no hp\"", 5);

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        $phones = [];
        foreach ($items as $item) {
            $snippet = ($item['snippet'] ?? '') . ' ' . ($item['title'] ?? '');
            // Extract Indonesian phone numbers
            if (preg_match_all('/(?:\+62|62|0)[\s-]?8[0-9]{1,2}[\s.-]?[0-9]{3,4}[\s.-]?[0-9]{3,5}/', $snippet, $matches)) {
                foreach ($matches[0] as $phone) {
                    $phone = preg_replace('/[\s.-]/', '', $phone);
                    if (!in_array($phone, $phones)) {
                        $phones[] = $phone;
                        $result['data'][] = [
                            'phone' => $phone,
                            'source_url' => $item['link'] ?? '',
                            'source_title' => $item['title'] ?? '-',
                            'confidence' => 0.4, // Phone numbers are low confidence
                        ];
                    }
                }
            }
        }

        $result['status'] = empty($result['data']) ? 'not_found' : 'found';
        if (!empty($result['data'])) {
            $result['confidence'] = $result['data'][0]['confidence'] ?? 0;
        }

        return $result;
    }

    /**
     * Search for work/career info via Google CSE.
     */
    private function searchWork(string $nama): array
    {
        $result = [
            'platform' => 'Pekerjaan',
            'icon' => 'work',
            'color' => '#f59e0b',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("\"{$nama}\" pekerjaan OR kerja OR perusahaan OR company OR \"bekerja di\"", 5);

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        foreach ($items as $item) {
            $snippet = $item['snippet'] ?? '';
            $title = $item['title'] ?? '';
            $confidence = $this->calculateNameConfidence($nama, $title . ' ' . $snippet);

            // Try to extract company/position from snippet
            $workInfo = $this->extractWorkFromSnippet($snippet, $nama);

            $result['data'][] = [
                'url' => $item['link'] ?? '',
                'title' => $title,
                'snippet' => $snippet,
                'company' => $workInfo['company'] ?? null,
                'position' => $workInfo['position'] ?? null,
                'confidence' => $confidence,
            ];
        }

        usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        $topConfidence = $result['data'][0]['confidence'] ?? 0;
        if ($topConfidence < 0.15) {
            $result['status'] = 'not_found';
            $result['data'] = [];
            $result['confidence'] = 0;
        } else {
            $result['status'] = 'found';
            $result['confidence'] = $topConfidence;
        }

        return $result;
    }

    /**
     * Search Google Scholar via Google CSE.
     */
    private function searchScholar(string $nama): array
    {
        $result = [
            'platform' => 'Google Scholar',
            'icon' => 'scholar',
            'color' => '#4285f4',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        $items = $this->webSearch("site:scholar.google.com \"{$nama}\"", 3);

        if (empty($items)) {
            // Try alternative search
            $items = $this->webSearch("\"{$nama}\" jurnal OR paper OR publikasi OR skripsi", 3);
        }

        if (empty($items)) {
            $result['status'] = 'not_found';
            return $result;
        }

        foreach ($items as $item) {
            $confidence = $this->calculateNameConfidence($nama, $item['title'] ?? '');
            $result['data'][] = [
                'url' => $item['link'] ?? '',
                'title' => $item['title'] ?? '-',
                'snippet' => $item['snippet'] ?? '-',
                'confidence' => $confidence,
            ];
        }

        usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        $topConfidence = $result['data'][0]['confidence'] ?? 0;
        if ($topConfidence < 0.15) {
            $result['status'] = 'not_found';
            $result['data'] = [];
            $result['confidence'] = 0;
        } else {
            $result['status'] = 'found';
            $result['confidence'] = $topConfidence;
        }

        return $result;
    }

    // =========================================================================
    // GITHUB API
    // =========================================================================

    /**
     * Search GitHub users by name.
     */
    private function searchGitHub(string $nama): array
    {
        $result = [
            'platform' => 'GitHub',
            'icon' => 'github',
            'color' => '#f0f6fc',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        try {
            $headers = ['Accept' => 'application/vnd.github.v3+json'];
            if ($this->githubToken) {
                $headers['Authorization'] = 'token ' . $this->githubToken;
            }

            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->timeout(10)
                ->get(self::GITHUB_API_URL . '/search/users', [
                    'q' => $nama . ' type:user',
                    'per_page' => 5,
                ]);

            if (!$response->successful()) {
                $result['status'] = 'error';
                $result['error'] = 'GitHub API: HTTP ' . $response->status();
                return $result;
            }

            $data = $response->json();
            $items = $data['items'] ?? [];

            if (empty($items)) {
                $result['status'] = 'not_found';
                return $result;
            }

            foreach ($items as $user) {
                // Fetch user details for bio, company, etc.
                $userDetail = $this->getGitHubUserDetail($user['login'] ?? '', $headers);

                $displayName = $userDetail['name'] ?? $user['login'] ?? '-';
                $confidence = $this->calculateNameConfidence($nama, $displayName);

                $result['data'][] = [
                    'username' => $user['login'] ?? '-',
                    'url' => $user['html_url'] ?? '',
                    'avatar' => $user['avatar_url'] ?? '',
                    'name' => $displayName,
                    'bio' => $userDetail['bio'] ?? null,
                    'company' => $userDetail['company'] ?? null,
                    'location' => $userDetail['location'] ?? null,
                    'public_repos' => $userDetail['public_repos'] ?? 0,
                    'followers' => $userDetail['followers'] ?? 0,
                    'confidence' => $confidence,
                ];
            }

            usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
            $result['status'] = 'found';
            $result['confidence'] = $result['data'][0]['confidence'] ?? 0;
        } catch (\Exception $e) {
            Log::warning('[Scraping GitHub] ' . $e->getMessage());
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    private function getGitHubUserDetail(string $username, array $headers): array
    {
        if (!$username) return [];

        try {
            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->timeout(8)
                ->get(self::GITHUB_API_URL . '/users/' . $username);

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // =========================================================================
    // WIKIPEDIA API
    // =========================================================================

    /**
     * Search Wikipedia for the person.
     */
    private function searchWikipedia(string $nama): array
    {
        $result = [
            'platform' => 'Wikipedia',
            'icon' => 'wikipedia',
            'color' => '#f1f5f9',
            'status' => 'searching',
            'data' => [],
            'confidence' => 0,
        ];

        try {
            // Search Indonesian Wikipedia first
            $response = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->get(self::WIKIPEDIA_API_URL, [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $nama,
                    'srlimit' => 3,
                    'format' => 'json',
                    'utf8' => 1,
                ]);

            if (!$response->successful()) {
                $result['status'] = 'error';
                return $result;
            }

            $data = $response->json();
            $items = $data['query']['search'] ?? [];

            if (empty($items)) {
                // Try English Wikipedia
                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get('https://en.wikipedia.org/w/api.php', [
                        'action' => 'query',
                        'list' => 'search',
                        'srsearch' => $nama,
                        'srlimit' => 3,
                        'format' => 'json',
                        'utf8' => 1,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $items = $data['query']['search'] ?? [];
                }
            }

            if (empty($items)) {
                $result['status'] = 'not_found';
                return $result;
            }

            foreach ($items as $item) {
                $title = $item['title'] ?? '';
                $confidence = $this->calculateNameConfidence($nama, $title);
                $snippet = strip_tags($item['snippet'] ?? '');

                $result['data'][] = [
                    'title' => $title,
                    'snippet' => $snippet,
                    'url' => 'https://id.wikipedia.org/wiki/' . urlencode(str_replace(' ', '_', $title)),
                    'word_count' => $item['wordcount'] ?? 0,
                    'confidence' => $confidence,
                ];
            }

            usort($result['data'], fn($a, $b) => $b['confidence'] <=> $a['confidence']);
            $result['status'] = 'found';
            $result['confidence'] = $result['data'][0]['confidence'] ?? 0;
        } catch (\Exception $e) {
            Log::warning('[Scraping Wikipedia] ' . $e->getMessage());
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    // =========================================================================
    // PARSERS & HELPERS
    // =========================================================================

    /**
     * Parse LinkedIn title format: "Name - Headline - Company | LinkedIn"
     */
    private function parseLinkedInTitle(string $title): array
    {
        $result = ['name' => '', 'headline' => '', 'company' => ''];

        // Remove " | LinkedIn" or " - LinkedIn"
        $title = preg_replace('/\s*[\|–-]\s*LinkedIn\s*$/i', '', $title);

        // Split by " - "
        $parts = preg_split('/\s*-\s*/', $title, 3);

        $result['name'] = trim($parts[0] ?? '');
        $result['headline'] = trim($parts[1] ?? '');
        $result['company'] = trim($parts[2] ?? '');

        return $result;
    }

    /**
     * Extract Instagram username from URL.
     */
    private function extractInstagramUsername(string $url): ?string
    {
        if (preg_match('#instagram\.com/([a-zA-Z0-9._]+)#', $url, $m)) {
            $username = $m[1];
            // Filter out non-profile pages
            if (!in_array(strtolower($username), ['p', 'explore', 'reels', 'stories', 'accounts', 'about', 'legal'])) {
                return '@' . $username;
            }
        }
        return null;
    }

    /**
     * Extract TikTok username from URL.
     */
    private function extractTikTokUsername(string $url): ?string
    {
        if (preg_match('#tiktok\.com/@([a-zA-Z0-9._]+)#', $url, $m)) {
            return '@' . $m[1];
        }
        return null;
    }

    /**
     * Try to extract work info from a text snippet.
     */
    private function extractWorkFromSnippet(string $snippet, string $nama): array
    {
        $result = ['company' => null, 'position' => null];

        // Common patterns: "bekerja di X", "works at X", "employee at X"
        $patterns = [
            '/bekerja\s+di\s+([^,.;]+)/i',
            '/kerja\s+di\s+([^,.;]+)/i',
            '/works?\s+at\s+([^,.;]+)/i',
            '/employee\s+(?:at|of)\s+([^,.;]+)/i',
            '/(?:perusahaan|company)\s*:?\s*([^,.;]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $snippet, $m)) {
                $result['company'] = trim($m[1]);
                break;
            }
        }

        // Position patterns
        $posPatterns = [
            '/(?:sebagai|as)\s+([^,.;]+)/i',
            '/(?:posisi|position|jabatan)\s*:?\s*([^,.;]+)/i',
        ];

        foreach ($posPatterns as $pattern) {
            if (preg_match($pattern, $snippet, $m)) {
                $result['position'] = trim($m[1]);
                break;
            }
        }

        return $result;
    }

    /**
     * Calculate confidence score for name matching.
     * Returns a value between 0 and 1.
     */
    private function calculateNameConfidence(string $searchName, string $foundText): float
    {
        $searchName = strtolower(trim($searchName));
        $foundText = strtolower(trim($foundText));

        if (empty($searchName) || empty($foundText)) return 0;

        // Exact match
        if (str_contains($foundText, $searchName)) {
            return 0.95;
        }

        // Check each word of the search name
        $searchWords = explode(' ', $searchName);
        $matchedWords = 0;

        foreach ($searchWords as $word) {
            if (strlen($word) >= 3 && str_contains($foundText, $word)) {
                $matchedWords++;
            }
        }

        $totalWords = count(array_filter($searchWords, fn($w) => strlen($w) >= 3));

        if ($totalWords === 0) return 0;

        $ratio = $matchedWords / $totalWords;

        // Scale: 100% words matched = 0.85, 50% = 0.4
        return round($ratio * 0.85, 2);
    }

    /**
     * Calculate confidence for email matches.
     */
    private function calculateEmailConfidence(string $nama, string $email): float
    {
        $namaParts = explode(' ', strtolower(trim($nama)));
        $emailLocal = explode('@', strtolower($email))[0];
        $score = 0.2;

        foreach ($namaParts as $part) {
            if (strlen($part) >= 3 && str_contains($emailLocal, $part)) {
                $score += 0.25;
            }
        }

        return min($score, 0.95);
    }

    /**
     * Get manual search URLs when no API key is available.
     */
    private function getManualSearchUrls(string $nama): array
    {
        $nClean = trim($nama);
        $platforms = [
            'linkedin' => [
                'platform' => 'LinkedIn',
                'icon' => 'linkedin',
                'color' => '#0077b5',
                'status' => 'manual',
                'manual_url' => 'https://www.google.com/search?q=' . urlencode("site:linkedin.com/in \"{$nClean}\""),
                'data' => [],
                'confidence' => 0,
            ],
            'instagram' => [
                'platform' => 'Instagram',
                'icon' => 'instagram',
                'color' => '#e4405f',
                'status' => 'manual',
                'manual_url' => 'https://www.google.com/search?q=' . urlencode("site:instagram.com \"{$nClean}\""),
                'data' => [],
                'confidence' => 0,
            ],
            'facebook' => [
                'platform' => 'Facebook',
                'icon' => 'facebook',
                'color' => '#1877f2',
                'status' => 'manual',
                'manual_url' => 'https://www.google.com/search?q=' . urlencode("site:facebook.com \"{$nClean}\""),
                'data' => [],
                'confidence' => 0,
            ],
            'tiktok' => [
                'platform' => 'TikTok',
                'icon' => 'tiktok',
                'color' => '#ff0050',
                'status' => 'manual',
                'manual_url' => 'https://www.google.com/search?q=' . urlencode("site:tiktok.com \"{$nClean}\""),
                'data' => [],
                'confidence' => 0,
            ],
            'email' => [
                'platform' => 'Email',
                'icon' => 'email',
                'color' => '#ea4335',
                'status' => 'manual',
                'manual_url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" email"),
                'data' => [],
                'confidence' => 0,
            ],
            'phone' => [
                'platform' => 'Phone',
                'icon' => 'phone',
                'color' => '#25D366',
                'status' => 'manual',
                'manual_url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" no hp OR whatsapp"),
                'data' => [],
                'confidence' => 0,
            ],
            'work' => [
                'platform' => 'Pekerjaan',
                'icon' => 'work',
                'color' => '#f59e0b',
                'status' => 'manual',
                'manual_url' => 'https://www.google.com/search?q=' . urlencode("\"{$nClean}\" pekerjaan OR kerja OR perusahaan"),
                'data' => [],
                'confidence' => 0,
            ],
            'scholar' => [
                'platform' => 'Google Scholar',
                'icon' => 'scholar',
                'color' => '#4285f4',
                'status' => 'manual',
                'manual_url' => 'https://scholar.google.com/scholar?q=' . urlencode("\"{$nClean}\""),
                'data' => [],
                'confidence' => 0,
            ],
        ];

        return $platforms;
    }

    /**
     * Build a summary of all results.
     */
    private function buildSummary(array $platforms): array
    {
        $found = 0;
        $notFound = 0;
        $manual = 0;
        $errors = 0;
        $totalConfidence = 0;
        $confCount = 0;
        $bestResults = [];

        foreach ($platforms as $key => $platform) {
            $status = $platform['status'] ?? 'unknown';

            if ($status === 'found') {
                $found++;
                if (!empty($platform['data'][0])) {
                    $bestResults[$key] = $platform['data'][0];
                }
                $totalConfidence += ($platform['confidence'] ?? 0);
                $confCount++;
            } elseif ($status === 'not_found') {
                $notFound++;
            } elseif ($status === 'manual') {
                $manual++;
            } else {
                $errors++;
            }
        }

        return [
            'platforms_found' => $found,
            'platforms_not_found' => $notFound,
            'platforms_manual' => $manual,
            'platforms_error' => $errors,
            'average_confidence' => $confCount > 0 ? round($totalConfidence / $confCount, 2) : 0,
            'best_results' => $bestResults,
        ];
    }
}
