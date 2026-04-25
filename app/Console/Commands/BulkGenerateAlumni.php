<?php

namespace App\Console\Commands;

use App\Models\AlumniUmm;
use App\Services\SmartEnrichmentService;
use Illuminate\Console\Command;

class BulkGenerateAlumni extends Command
{
    protected $signature = 'alumni:bulk-generate
                            {--chunk=1000 : Jumlah record per batch}
                            {--offset=0   : Mulai dari record ke-N}
                            {--limit=0    : Batas total record (0 = semua)}
                            {--force      : Overwrite field yang sudah ada}
                            {--empty-only : Hanya proses alumni yang belum punya data sama sekali}';

    protected $description = 'Bulk generate enrichment data untuk alumni (tanpa scraping, super cepat)';

    public function handle(SmartEnrichmentService $enrichment): int
    {
        $chunkSize  = (int) $this->option('chunk');
        $offset     = (int) $this->option('offset');
        $limit      = (int) $this->option('limit');
        $force      = (bool) $this->option('force');
        $emptyOnly  = (bool) $this->option('empty-only');

        $fields = ['linkedin', 'instagram', 'facebook', 'tiktok', 'email', 'no_hp', 'tempat_kerja', 'posisi'];

        $query = AlumniUmm::query();

        if ($emptyOnly) {
            $query->where(function ($q) use ($fields) {
                foreach ($fields as $f) {
                    $q->orWhereNull($f)->orWhere($f, '');
                }
            });
        }

        if ($offset > 0) {
            $query->skip($offset);
        }

        $total = $limit > 0 ? min($limit, $query->count()) : $query->count();

        if ($total === 0) {
            $this->info('Tidak ada alumni yang perlu di-generate.');
            return 0;
        }

        $this->info("🚀 Bulk Generate Alumni Data");
        $this->info("Total target : " . number_format($total));
        $this->info("Chunk size   : " . number_format($chunkSize));
        $this->info("Force        : " . ($force ? 'YA (overwrite)' : 'TIDAK (skip jika sudah ada)'));
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% elapsed | ETA: %estimated:-6s% | ✅ %message%');
        $bar->start();

        $processed = 0;
        $updated   = 0;
        $skipped   = 0;

        $query->chunk($chunkSize, function ($batch) use (
            $enrichment, $force, $fields, &$processed, &$updated, &$skipped, $bar, $limit
        ) {
            foreach ($batch as $alumni) {
                if ($limit > 0 && $processed >= $limit) return false;

                // Hitung field yang sudah ada
                $existingCount = collect($fields)->filter(fn($f) => !empty($alumni->$f))->count();

                if (!$force && $existingCount === count($fields)) {
                    // Sudah 100% lengkap, skip
                    $skipped++;
                } else {
                    // Generate hanya (tidak scrape, jauh lebih cepat)
                    $generated = $enrichment->generateFallbackData($alumni);

                    $hasUpdates = false;
                    foreach ($generated as $key => $value) {
                        if (!empty($value) && ($force || empty($alumni->$key))) {
                            $alumni->$key = $value;
                            $hasUpdates = true;
                        }
                    }

                    if ($hasUpdates) {
                        // Set data_source hanya jika belum ada atau force
                        if ($force || empty($alumni->data_source)) {
                            $alumni->data_source = 'generated';
                        }
                        $alumni->save();
                        $updated++;
                    } else {
                        $skipped++;
                    }
                }

                $processed++;
                $bar->advance();
                $bar->setMessage("{$updated} diisi, {$skipped} diskip");
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Selesai!");
        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Total diproses', number_format($processed)],
                ['Record diisi',   number_format($updated)],
                ['Record diskip',  number_format($skipped)],
            ]
        );

        return 0;
    }
}
