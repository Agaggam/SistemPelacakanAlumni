<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlumniUmm;
use App\Services\ScrapingService;
use Illuminate\Support\Facades\Log;

class EnrichAlumniSocial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alumni:enrich-social {--limit=100 : Limit the number of records to process} {--force : Force search even if social links exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrich alumni data with social media profiles using Brave, DiscoverProfile, and Lullar';

    /**
     * Execute the console command.
     */
    public function handle(ScrapingService $scrapingService)
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        $this->info("Starting enrichment for up to $limit alumni...");

        $query = AlumniUmm::query();
        
        if (!$force) {
            $query->whereNull('linkedin')
                  ->whereNull('instagram')
                  ->whereNull('facebook');
        }

        $alumniList = $query->limit($limit)->get();
        $total = $alumniList->count();

        if ($total === 0) {
            $this->info("No alumni found needing enrichment.");
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($alumniList as $alumni) {
            try {
                $this->line("\nScraping for: " . $alumni->nama);
                
                $results = $scrapingService->scrapeByName($alumni->nama);
                
                $updates = [];
                $summary = $results['summary']['best_results'] ?? [];

                if (!empty($summary['linkedin']['url'])) {
                    $updates['linkedin'] = $summary['linkedin']['url'];
                    $this->info(" Found LinkedIn: " . $updates['linkedin']);
                }

                if (!empty($summary['instagram']['url'])) {
                    $updates['instagram'] = $summary['instagram']['url'];
                }

                if (!empty($summary['facebook']['url'])) {
                    $updates['facebook'] = $summary['facebook']['url'];
                }

                if (!empty($summary['tiktok']['url'])) {
                    $updates['tiktok'] = $summary['tiktok']['url'];
                }

                if (!empty($updates)) {
                    $alumni->update($updates);
                } else {
                    $this->warn(" No results found.");
                }

                // Rate limiting to avoid blocks
                sleep(2);

            } catch (\Exception $e) {
                $this->error(" Error scraping {$alumni->nama}: " . $e->getMessage());
                Log::error("Enrichment error for ID {$alumni->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nEnrichment completed.");
    }
}
