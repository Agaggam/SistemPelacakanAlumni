<?php

namespace App\Console\Commands;

use App\Models\AlumniUmm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ImportAlumniSheet extends Command
{
    protected $signature = 'alumni:import-sheet {--chunk=500 : Batch size for insert}';
    protected $description = 'Import 142K+ alumni data from Google Sheets into alumni_umm table';

    private const SHEET_ID = '1JepgHxbtFpfwAxUO3DjZd6-TOpvtCr2d';

    public function handle(): int
    {
        $this->info('🔄 Starting alumni import from Google Sheets...');
        $this->info('   Sheet ID: ' . self::SHEET_ID);
        
        $chunkSize = (int) $this->option('chunk');

        // Fetch CSV from Google Sheets
        $this->info('📥 Downloading CSV data...');
        
        $url = 'https://docs.google.com/spreadsheets/d/' . self::SHEET_ID . '/gviz/tq?tqx=out:csv&sheet=Sheet1';
        
        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(120)
                ->get($url);

            if (!$response->successful()) {
                $this->error('❌ Failed to download sheet. HTTP ' . $response->status());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Connection error: ' . $e->getMessage());
            return 1;
        }

        $csv = $response->body();
        $lines = explode("\n", $csv);
        
        if (count($lines) < 2) {
            $this->error('❌ CSV appears empty.');
            return 1;
        }

        // Parse header
        $header = str_getcsv(array_shift($lines));
        $this->info('📋 Columns: ' . implode(', ', $header));
        $this->info('📊 Total rows: ' . count(array_filter($lines)));

        // Clear existing data
        if ($this->confirm('⚠️ Hapus data alumni_umm yang sudah ada dan import ulang?', true)) {
            AlumniUmm::truncate();
            $this->info('🗑️ Table cleared.');
        }

        // Parse and insert in chunks
        $batch = [];
        $total = 0;
        $skipped = 0;
        $bar = $this->output->createProgressBar(count($lines));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%');

        $now = now();

        foreach ($lines as $line) {
            $bar->advance();
            
            $line = trim($line);
            if (empty($line)) continue;

            $row = str_getcsv($line);
            if (count($row) < 6) {
                $skipped++;
                continue;
            }

            // Columns: Nama Lulusan, NIM, Tahun Masuk, Tanggal Lulus, Fakultas, Program Studi
            $nama = trim($row[0] ?? '');
            if (empty($nama)) {
                $skipped++;
                continue;
            }

            $batch[] = [
                'nama'          => $nama,
                'nim'           => trim($row[1] ?? '') ?: null,
                'tahun_masuk'   => trim($row[2] ?? '') ?: null,
                'tanggal_lulus' => trim($row[3] ?? '') ?: null,
                'fakultas'      => trim($row[4] ?? '') ?: null,
                'prodi'         => trim($row[5] ?? '') ?: null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($batch) >= $chunkSize) {
                DB::table('alumni_umm')->insert($batch);
                $total += count($batch);
                $batch = [];
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            DB::table('alumni_umm')->insert($batch);
            $total += count($batch);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Import selesai!");
        $this->info("   📊 Total diimport: {$total}");
        $this->info("   ⏭️ Dilewati: {$skipped}");
        $this->info("   📁 Total di database: " . AlumniUmm::count());

        return 0;
    }
}
